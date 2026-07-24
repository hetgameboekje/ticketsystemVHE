<?php

namespace App\Modules\EmailVerwerking\Services;

/**
 * Roept de geconfigureerde AI-provider (config/config.php 'ai', zie .env: AI_API_KEY/AI_API_URL/AI_MODEL)
 * aan om één e-mail te classificeren en dwingt een vast JSON-schema af via de systeemprompt. Default
 * implementatie praat tegen de Anthropic Messages API; andere providers kunnen door deze klasse te
 * vervangen zolang analyseer() hetzelfde array-schema teruggeeft (zie validateer()).
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

    private const SYSTEEMPROMPT = <<<'PROMPT'
        Je bent een IT-supportanalist. Analyseer de meegegeven e-mail (onderwerp + inhoud) en geef
        UITSLUITEND een geldig JSON-object terug, zonder omliggende tekst of markdown-codeblok, met
        exact deze velden:
        {
          "onderwerp": string (kort, herkend onderwerp/probleemtype),
          "categorie": string (kennisbankcategorie, bv. "Server Documentation", "Password & Security"),
          "subcategorie": string of null,
          "sentiment": string (bv. "neutraal", "gefrustreerd", "urgent"),
          "urgentie": "laag" | "normaal" | "hoog",
          "samenvatting": string (1-2 zinnen),
          "probleem": string (wat er precies misgaat),
          "oplossing_suggestie": string (voorgestelde aanpak),
          "voorgestelde_titel": string (titel voor een kennisbankartikel),
          "tags": array van maximaal 5 losse tag-strings,
          "confidence": number tussen 0 en 1,
          "mens_review_aanbevolen": boolean
        }
        PROMPT;

    /**
     * @return array{
     *   onderwerp:string, categorie:string, subcategorie:?string, sentiment:string, urgentie:string,
     *   samenvatting:string, probleem:string, oplossing_suggestie:string, voorgestelde_titel:string,
     *   tags:string[], confidence:float, mens_review_aanbevolen:bool, ruwe_response:string, model_versie:string
     * }
     * @throws \RuntimeException bij een configuratie-, netwerk- of parsefout (vangen in de aanroeper).
     */
    public function analyseer(string $onderwerp, string $body): array
    {
        $config = self::config();
        if ($config['apiKey'] === '') {
            throw new \RuntimeException('Geen AI-provider geconfigureerd (AI_API_KEY ontbreekt in .env).');
        }

        $payload = json_encode([
            'model' => $config['model'],
            'max_tokens' => 1024,
            'system' => self::SYSTEEMPROMPT,
            'messages' => [
                ['role' => 'user', 'content' => "Onderwerp: {$onderwerp}\n\nInhoud:\n{$body}"],
            ],
        ], JSON_THROW_ON_ERROR);

        $ch = curl_init($config['apiUrl']);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-key: ' . $config['apiKey'],
                'anthropic-version: 2023-06-01',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new \RuntimeException("AI-provider onbereikbaar: {$curlError}");
        }
        if ($httpCode >= 400) {
            throw new \RuntimeException("AI-provider gaf HTTP {$httpCode} terug: " . substr($response, 0, 500));
        }

        $tekst = self::extraheerTekst($response);
        $analyse = self::parseJson($tekst);
        self::valideer($analyse);

        $analyse['ruwe_response'] = $response;
        $analyse['model_versie'] = $config['model'];

        return $analyse;
    }

    private static function extraheerTekst(string $response): string
    {
        $decoded = json_decode($response, true);
        $tekst = $decoded['content'][0]['text'] ?? null;
        if (!is_string($tekst) || $tekst === '') {
            throw new \RuntimeException('Onverwacht antwoordformaat van de AI-provider (geen content[0].text).');
        }

        return $tekst;
    }

    /** De AI kan ondanks instructie toch een codeblok om de JSON heen zetten — dit strippen we zonder te falen. */
    private static function parseJson(string $tekst): array
    {
        $tekst = trim($tekst);
        $tekst = preg_replace('/^```(?:json)?|```$/m', '', $tekst);
        $tekst = trim($tekst);

        $decoded = json_decode($tekst, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('AI-response is geen geldig JSON-object: ' . substr($tekst, 0, 300));
        }

        return $decoded;
    }

    private static function valideer(array $analyse): void
    {
        $ontbrekend = array_diff(self::VERPLICHTE_VELDEN, array_keys($analyse));
        if ($ontbrekend !== []) {
            throw new \RuntimeException('AI-response mist verplichte velden: ' . implode(', ', $ontbrekend));
        }
    }

    private static function config(): array
    {
        $config = require APP_ROOT . '/config/config.php';
        return $config['ai'];
    }
}
