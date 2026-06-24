<?php

namespace App\Core;

abstract class Model
{
    protected static string $table = '';
    protected static array $fillable = [];

    public static function all(string $orderBy = 'id DESC'): array
    {
        $sql = sprintf('SELECT * FROM %s ORDER BY %s', static::$table, $orderBy);
        return Database::pdo()->query($sql)->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(sprintf('SELECT * FROM %s WHERE id = ?', static::$table));
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function create(array $data): int
    {
        $data = array_intersect_key($data, array_flip(static::$fillable));
        $columns = array_keys($data);
        $placeholders = array_map(fn (string $c) => ':' . $c, $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            static::$table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $pdo = Database::pdo();
        $pdo->prepare($sql)->execute($data);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $data = array_intersect_key($data, array_flip(static::$fillable));
        $sets = array_map(fn (string $c) => "$c = :$c", array_keys($data));

        $sql = sprintf(
            'UPDATE %s SET %s, updated_at = NOW() WHERE id = :id',
            static::$table,
            implode(', ', $sets)
        );

        $data['id'] = $id;
        Database::pdo()->prepare($sql)->execute($data);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::pdo()->prepare(sprintf('DELETE FROM %s WHERE id = ?', static::$table));
        $stmt->execute([$id]);
    }
}
