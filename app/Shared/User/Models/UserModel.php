<?php

namespace App\Shared\User\Models;

use App\Core\Database;
use App\Core\Model;

class UserModel extends Model
{
    protected static string $table = 'users';
    protected static array $fillable = ['naam', 'email', 'wachtwoord_hash', 'rol', 'foto', 'telefoon', 'adres'];
    protected static bool $softDeletes = true;

    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM users WHERE email = ? AND deleted_at IS NULL');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /** Zoals find(), maar vindt ook gedeactiveerde logins — nodig voor Beheer > Rechten om ze te kunnen beheren/heractiveren. */
    public static function findIncludingDeleted(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function allGedeactiveerd(string $orderBy = 'naam ASC'): array
    {
        $stmt = Database::pdo()->query("SELECT * FROM users WHERE deleted_at IS NOT NULL ORDER BY {$orderBy}");
        return $stmt->fetchAll();
    }

    public static function emailExists(string $email, ?int $exceptId = null): bool
    {
        $sql = 'SELECT 1 FROM users WHERE LOWER(email) = LOWER(?)';
        $params = [$email];
        if ($exceptId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $exceptId;
        }

        $stmt = Database::pdo()->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        return $stmt->fetchColumn() !== false;
    }

    public static function authenticate(string $email, string $password): ?array
    {
        $user = self::findByEmail($email);
        if ($user === null) {
            return null;
        }

        if (!password_verify($password, $user['wachtwoord_hash'])) {
            return null;
        }

        return $user;
    }

    /** Zoekt een actieve gebruiker wiens naam de zoekterm bevat (bv. "Timo" -> "Timo Bergthaler"). */
    public static function findIdByNaamBevat(string $zoekterm): ?int
    {
        $stmt = Database::pdo()->prepare('SELECT id FROM users WHERE naam LIKE ? AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['%' . $zoekterm . '%']);
        $id = $stmt->fetchColumn();
        return $id === false ? null : (int) $id;
    }

    public static function findOrCreateByNaam(string $naam): int
    {
        $naam = trim($naam);

        $stmt = Database::pdo()->prepare('SELECT id FROM users WHERE LOWER(naam) = LOWER(?) AND deleted_at IS NULL');
        $stmt->execute([$naam]);
        $id = $stmt->fetchColumn();

        if ($id !== false) {
            return (int) $id;
        }

        $slug = preg_replace('/[^a-z0-9]+/', '.', strtolower($naam));
        $email = trim($slug, '.') . '@geimporteerd.local';

        $i = 1;
        $baseEmail = $email;
        while (self::findByEmail($email) !== null) {
            $email = str_replace('@geimporteerd.local', $i++ . '@geimporteerd.local', $baseEmail);
        }

        return self::create([
            'naam' => $naam,
            'email' => $email,
            'wachtwoord_hash' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
            'rol' => 'medewerker',
        ]);
    }
}
