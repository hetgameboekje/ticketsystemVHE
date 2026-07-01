<?php

namespace App\Modules\Voorraad\Models;

use App\Core\Model;

class VoorraadTypeModel extends Model
{
    protected static string $table = 'voorraad_types';
    protected static array $fillable = ['naam', 'code'];

    public static function all(string $orderBy = 'naam ASC'): array
    {
        return parent::all($orderBy);
    }
}
