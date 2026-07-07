<?php

namespace App\Shared\Auth\Models;

use App\Core\Database;
use App\Core\Model;

/** Audit-log van login-pogingen — basis voor lockout na herhaald falen en het "nieuw IP"-signaal. */
class LoginAttemptModel extends Model
{
    protected static string $table = 'login_attempts';
    protected static array $fillable = ['email', 'user_id', 'ip_address', 'user_agent', 'success', 'is_new_ip'];

    public static function recentFailedCount(string $email, int $minutes = 15): int
    {
        $stmt = Database::pdo()->prepare(
            'SELECT COUNT(*) FROM login_attempts
             WHERE LOWER(email) = LOWER(?) AND success = 0 AND created_at >= (NOW() - INTERVAL ? MINUTE)'
        );
        $stmt->execute([$email, $minutes]);
        return (int) $stmt->fetchColumn();
    }

    /** True als deze gebruiker ooit al succesvol is ingelogd (vanaf willekeurig IP). */
    public static function hasAnyPriorSuccessfulLogin(int $userId): bool
    {
        $stmt = Database::pdo()->prepare('SELECT 1 FROM login_attempts WHERE user_id = ? AND success = 1 LIMIT 1');
        $stmt->execute([$userId]);
        return $stmt->fetchColumn() !== false;
    }

    /** True als deze gebruiker al eerder succesvol inlogde vanaf precies dit IP-adres. */
    public static function hasSuccessfulLoginFromIp(int $userId, string $ip): bool
    {
        $stmt = Database::pdo()->prepare(
            'SELECT 1 FROM login_attempts WHERE user_id = ? AND success = 1 AND ip_address = ? LIMIT 1'
        );
        $stmt->execute([$userId, $ip]);
        return $stmt->fetchColumn() !== false;
    }

    public static function record(
        string $email,
        ?int $userId,
        string $ip,
        ?string $userAgent,
        bool $success,
        bool $isNewIp
    ): void {
        self::create([
            'email' => $email,
            'user_id' => $userId,
            'ip_address' => $ip,
            'user_agent' => $userAgent !== null ? substr($userAgent, 0, 255) : null,
            'success' => $success ? 1 : 0,
            'is_new_ip' => $isNewIp ? 1 : 0,
        ]);
    }

    /**
     * @param array{user_id?:string, alleen_verdacht?:string} $filters
     * @return array{items: array, total: int, page: int, perPage: int, totalPages: int}
     */
    public static function search(array $filters, int $page, int $perPage = 50): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['user_id'])) {
            $where[] = 'l.user_id = :user_id';
            $params['user_id'] = (int) $filters['user_id'];
        }
        if (!empty($filters['alleen_verdacht'])) {
            $where[] = '(l.success = 0 OR l.is_new_ip = 1)';
        }

        $whereSql = $where === [] ? '' : ('WHERE ' . implode(' AND ', $where));

        $countStmt = Database::pdo()->prepare("SELECT COUNT(*) FROM login_attempts l {$whereSql}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT l.*, u.naam AS gebruiker_naam
                FROM login_attempts l
                LEFT JOIN users u ON u.id = l.user_id
                {$whereSql}
                ORDER BY l.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";

        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);

        return [
            'items' => $stmt->fetchAll(),
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'total' => $total,
        ];
    }
}
