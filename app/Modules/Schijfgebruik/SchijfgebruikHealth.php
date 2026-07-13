<?php

namespace App\Modules\Schijfgebruik;

class SchijfgebruikHealth
{
    private const OFFLINE_ALS_UUR = 2;
    private const OFFLINE_WAARSCHUWING_DAGEN = 7;
    private const HERSTART_DREMPEL_DAGEN = 14;
    private const SCHIJF_WAARSCHUWING_PERCENTAGE = 90;

    /**
     * Berekent gezondheidsinfo bovenop een schijf/apparaat-rij uit SchijfgebruikSchijfModel::allWithDevice().
     * Rekent puur op wat de laatste import aanleverde — geen eigen opslag, dus altijd actueel
     * t.o.v. het moment van weergave (bv. "5 dagen offline" loopt door zonder herimport).
     *
     * @param array<string, mixed> $row
     * @return array{is_online: bool, dagen_offline: ?int, dagen_sinds_boot: ?int, herstart_nodig: bool, waarschuwingen: string[]}
     */
    public static function evaluate(array $row): array
    {
        $nu = new \DateTimeImmutable();
        $waarschuwingen = [];

        $laatstOnline = self::parse($row['laatst_online'] ?? null);
        $dagenOffline = null;
        $isOnline = false;

        if ($laatstOnline !== null) {
            $urenGeleden = ($nu->getTimestamp() - $laatstOnline->getTimestamp()) / 3600;
            $isOnline = $urenGeleden <= self::OFFLINE_ALS_UUR;

            if (!$isOnline) {
                $dagenOffline = (int) floor($urenGeleden / 24);
                if ($dagenOffline >= self::OFFLINE_WAARSCHUWING_DAGEN) {
                    $waarschuwingen[] = "Al {$dagenOffline} dagen offline";
                }
            }
        }

        $laatsteBoot = self::parse($row['laatste_boot'] ?? null);
        $dagenSindsBoot = null;
        $herstartNodig = false;

        if ($laatsteBoot !== null) {
            $dagenSindsBoot = (int) floor(($nu->getTimestamp() - $laatsteBoot->getTimestamp()) / 86400);
            if ($dagenSindsBoot >= self::HERSTART_DREMPEL_DAGEN) {
                $herstartNodig = true;
                $waarschuwingen[] = "Herstart aanbevolen (laatste boot {$dagenSindsBoot} dagen geleden)";
            }
        }

        $gebruikPercentage = (int) ($row['gebruik_percentage'] ?? 0);
        if ($gebruikPercentage >= self::SCHIJF_WAARSCHUWING_PERCENTAGE) {
            $waarschuwingen[] = "Schijf bijna vol ({$gebruikPercentage}%)";
        }

        return [
            'is_online' => $isOnline,
            'dagen_offline' => $dagenOffline,
            'dagen_sinds_boot' => $dagenSindsBoot,
            'herstart_nodig' => $herstartNodig,
            'waarschuwingen' => $waarschuwingen,
        ];
    }

    private static function parse(mixed $raw): ?\DateTimeImmutable
    {
        if (!is_string($raw) || $raw === '') {
            return null;
        }

        try {
            return new \DateTimeImmutable($raw);
        } catch (\Exception) {
            return null;
        }
    }
}
