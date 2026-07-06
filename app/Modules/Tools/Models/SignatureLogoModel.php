<?php

namespace App\Modules\Tools\Models;

use App\Core\Model;

class SignatureLogoModel extends Model
{
    protected static string $table = 'signature_logos';
    protected static array $fillable = ['naam', 'bestand', 'breedte'];

    public static function all(string $orderBy = 'naam ASC'): array
    {
        return parent::all($orderBy);
    }
}
