<?php

namespace App\Shared\Log\Models;

use App\Core\Database;
use App\Core\Model;

class PaginaBezoekModel extends Model
{
    protected static string $table = 'paginabezoeken';
    protected static array $fillable = ['user_id', 'ip_adres', 'methode', 'url', 'parameters', 'user_agent'];

    /**
     * @param array{user_id?:string, ip_adres?:string, q?:string} $filters
     * @return array{items: array, total: int}
     */
    public static function search(array $filters, int $page, int $perPage = 50): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['user_id'])) {
            $where[] = 'p.user_id = :user_id';
            $params['user_id'] = (int) $filters['user_id'];
        }
        if (!empty($filters['ip_adres'])) {
            $where[] = 'p.ip_adres = :ip_adres';
            $params['ip_adres'] = $filters['ip_adres'];
        }
        if (!empty($filters['q'])) {
            $where[] = 'p.url LIKE :q';
            $params['q'] = '%' . $filters['q'] . '%';
        }

        $whereSql = $where === [] ? '' : ('WHERE ' . implode(' AND ', $where));

        $countStmt = Database::pdo()->prepare("SELECT COUNT(*) FROM paginabezoeken p {$whereSql}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT p.*, u.naam AS gebruiker_naam
                FROM paginabezoeken p
                LEFT JOIN users u ON u.id = p.user_id
                {$whereSql}
                ORDER BY p.created_at DESC
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

    public static function distinctIpAdressen(): array
    {
        $stmt = Database::pdo()->query('SELECT DISTINCT ip_adres FROM paginabezoeken ORDER BY ip_adres');
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
}
