<?php

namespace App\Modules\Tools\Models;

use App\Core\Database;
use App\Core\Model;

class PhonebookJobModel extends Model
{
    protected static string $table = 'phonebook_jobs';
    protected static array $fillable = ['original_filename', 'stored_path', 'status'];

    public static function markProcessing(int $id): void
    {
        Database::pdo()->prepare("UPDATE phonebook_jobs SET status = 'processing' WHERE id = ?")->execute([$id]);
    }

    public static function markDone(int $id, string $resultPath, int $contactCount): void
    {
        $stmt = Database::pdo()->prepare(
            "UPDATE phonebook_jobs SET status = 'done', result_path = :result_path,
                contact_count = :contact_count, processed_at = NOW() WHERE id = :id"
        );
        $stmt->execute(['result_path' => $resultPath, 'contact_count' => $contactCount, 'id' => $id]);
    }

    public static function markError(int $id, string $message): void
    {
        $stmt = Database::pdo()->prepare(
            "UPDATE phonebook_jobs SET status = 'error', error_message = :error_message, processed_at = NOW() WHERE id = :id"
        );
        $stmt->execute(['error_message' => $message, 'id' => $id]);
    }
}
