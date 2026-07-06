<?php

namespace App\Modules\Tools\Models;

use App\Core\Model;

class EmailSignatureModel extends Model
{
    protected static string $table = 'email_signatures';
    protected static array $fillable = ['name', 'lines_json'];

    public static function all(string $orderBy = 'name ASC'): array
    {
        return parent::all($orderBy);
    }

    /** Zoals find(), maar met 'lines' als gedecodeerde array i.p.v. de ruwe 'lines_json'-kolom. */
    public static function findWithLines(int $id): ?array
    {
        $signature = self::find($id);
        if ($signature === null) {
            return null;
        }

        $signature['lines'] = json_decode($signature['lines_json'], true) ?: [];

        return $signature;
    }

    public static function createWithLines(string $name, array $lines): int
    {
        return self::create([
            'name' => $name,
            'lines_json' => json_encode($lines, JSON_UNESCAPED_UNICODE),
        ]);
    }

    public static function updateWithLines(int $id, string $name, array $lines): void
    {
        self::update($id, [
            'name' => $name,
            'lines_json' => json_encode($lines, JSON_UNESCAPED_UNICODE),
        ]);
    }
}
