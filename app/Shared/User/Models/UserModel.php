<?php

namespace App\Shared\User\Models;

use App\Core\Database;
use App\Core\Model;

class UserModel extends Model
{
    protected static string $table = 'users';
    protected static array $fillable = ['naam', 'email', 'wachtwoord_hash', 'rol'];

    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
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
}
