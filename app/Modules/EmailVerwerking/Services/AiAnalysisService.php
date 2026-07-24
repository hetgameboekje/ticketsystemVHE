<?php

namespace App\Modules\EmailVerwerking\Services;

/**
 * Stuurt een e-mail door naar de externe n8n-orkestratielaag (config/config.php 'n8n', zie .env:
 * N8N_WEBHOOK_URL/N8N_API_KEY) die de eigenlijke classificatie/extractie uitvoert, en verwacht
 * daar het vaste JSON-schema terug dat validateer() afdwingt (zelfde schema als voorheen door de
 * Anthropic-call gevuld) zodat EmailAnalysisController en KbDraftGenerator ongewijzigd blijven.
 *
 * Bevat bewust geen retry/backoff-logica — EmailAnalysisController::verwerken() laat een mislukte
 * e-mail gewoon op status 'stored' staan zodat de eerstvolgende cron-run het opnieuw probeert.
 */
class AiAnalysisService
{
    private const VERPLICHTE_VELDEN = [
        'onderwerp', 'categorie', 'sentiment', 'urgentie', 'samenvatting', 'probleem',
        'oplossing_suggestie', 'voorgestelde_titel', 'tags', 'confidence',
    ];

    /**
     * @param array $email rij uit imported_emails (id, bron_message_id, afzender_email,
     *   afzender_naam, onderwerp, body_schoon, ontvangen_at, ...)
     * @return array{
     *   onderwerp:string, categorie:string, subcategorie:?string, sentiment:string, urgentie:string,
     *   samenvatting:string, probleem:string, oplossing_suggestie:string, voorgestelde_titel:string,
     *   tags:string[], confidence:float, mens_review_aanbevolen:bool, ruwe_response:string, model_versie:string
     * }
     * @throws \RuntimeException bij een configuratie-, netwerk- of parsefout (vangen in de aanroeper).
     */
    public function analyseer(array $email): array
    {
        $config = self::config();
        if ($config['webhookUrl'] === '') {
            throw new \RuntimeException('Geen n8n-webhook geconfigureerd (N8N_WEBHOOK_URL ontbreekt in .env).');
        }

        $payload = json_encode([
            'source' => 'email',
            'messageId' => $email['bron_message_id'],
            'threadId' => '',
            'receivedAt' => self::isoDatum($email['ontvangen_at'] ?? null),
            'from' => [
                'name' => $email['afzender_naam'] ?? '',
                'email' => $email['afzender_email'],
            ],
            'to' => [],
            'subject' => $email['onderwerp'],
            'bodyText' => $email['body_schoon'],
            'bodyHtml' => '',
            'attachments' => [],
            'labels' => [],
            'metadata' => [
                'tenant' => 'default',
                'environment' => getenv('APP_ENV') ?: 'production',
                'origin' => 'ticketsysteem-leenvanpunt',
            ],
        ], JSON_THROW_ON_ERROR);

        $ch = curl_init($config['webhookUrl']);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-key: ' . $config['apiKey'],
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new \RuntimeException("n8n-webhook onbereikbaar: {$curlError}");
        }
        if ($httpCode >= 400) {
            throw new \RuntimeException("n8n-webhook gaf HTTP {$httpCode} terug: " . substr($response, 0, 500));
        }

        $analyse = self::parseJson($response);
        self::valideer($analyse);

        $analyse['ruwe_response'] = $response;
        $analyse['model_versie'] = 'n8n';

        return $analyse;
    }

    private static function isoDatum(?string $ontvangenAt): string
    {
        if ($ontvangenAt === null || $ontvangenAt === '') {
            return (new \DateTime())->format(DATE_ATOM);
        }

        return (new \DateTime($ontvangenAt))->format(DATE_ATOM);
    }

    /** n8n kan ondanks afspraak toch een codeblok om de JSON heen zetten — dit strippen we zonder te falen. */
    private static function parseJson(string $tekst): array
    {
        $tekst = trim($tekst);
        $tekst = preg_replace('/^```(?:json)?|```$/m', '', $tekst);
        $tekst = trim($tekst);

        $decoded = json_decode($tekst, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('n8n-response is geen geldig JSON-object: ' . substr($tekst, 0, 300));
        }

        return $decoded;
    }

    private static function valideer(array $analyse): void
    {
        $ontbrekend = array_diff(self::VERPLICHTE_VELDEN, array_keys($analyse));
        if ($ontbrekend !== []) {
            throw new \RuntimeException('n8n-response mist verplichte velden: ' . implode(', ', $ontbrekend));
        }
    }

    private static function config(): array
    {
        $config = require APP_ROOT . '/config/config.php';
        return $config['n8n'];
    }
}
