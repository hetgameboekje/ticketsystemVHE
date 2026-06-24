<?php

namespace App\Shared\Afdeling\Models;

use App\Core\Model;

class AfdelingModel extends Model
{
    protected static string $table = 'afdelingen';
    protected static array $fillable = ['naam'];

    public static function all(string $orderBy = 'naam ASC'): array
    {
        return parent::all($orderBy);
    }
}
