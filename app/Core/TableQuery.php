<?php

namespace App\Core;

class TableQuery
{
    private const RESERVED = ['sort', 'dir'];

    public static function apply(array $items, array $params): array
    {
        return self::sort(self::filter($items, $params), $params);
    }

    private static function filter(array $items, array $params): array
    {
        $filters = array_diff_key($params, array_flip(self::RESERVED));

        foreach ($filters as $column => $value) {
            if (!is_string($column) || !is_scalar($value) || $value === '') {
                continue;
            }

            $items = array_values(array_filter(
                $items,
                fn (array $row) => array_key_exists($column, $row) && (string) $row[$column] === (string) $value
            ));
        }

        return $items;
    }

    private static function sort(array $items, array $params): array
    {
        $column = $params['sort'] ?? null;
        if (!is_string($column) || $items === [] || !array_key_exists($column, $items[0])) {
            return $items;
        }

        $direction = (($params['dir'] ?? 'asc') === 'desc') ? -1 : 1;

        usort($items, function (array $a, array $b) use ($column, $direction) {
            $av = $a[$column] ?? null;
            $bv = $b[$column] ?? null;
            $aEmpty = $av === null || $av === '';
            $bEmpty = $bv === null || $bv === '';

            if ($aEmpty || $bEmpty) {
                return $aEmpty <=> $bEmpty;
            }

            return self::compare($av, $bv) * $direction;
        });

        return $items;
    }

    private static function compare(mixed $a, mixed $b): int
    {
        if (is_numeric($a) && is_numeric($b)) {
            return $a <=> $b;
        }

        if (self::isDate($a) && self::isDate($b)) {
            return strtotime((string) $a) <=> strtotime((string) $b);
        }

        return strnatcasecmp((string) $a, (string) $b);
    }

    private static function isDate(mixed $value): bool
    {
        return is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}([ T]\d{2}:\d{2}(:\d{2})?)?$/', $value) === 1;
    }
}
