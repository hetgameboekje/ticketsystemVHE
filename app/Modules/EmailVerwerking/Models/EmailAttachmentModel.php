<?php

namespace App\Modules\EmailVerwerking\Models;

use App\Core\Database;
use App\Core\Model;

class EmailAttachmentModel extends Model
{
    protected static string $table = 'email_attachments';
    protected static array $fillable = ['imported_email_id', 'bestandsnaam', 'pad', 'mime_type', 'grootte_bytes'];

    public static function forEmail(int $importedEmailId): array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM email_attachments WHERE imported_email_id = ? ORDER BY id ASC');
        $stmt->execute([$importedEmailId]);
        return $stmt->fetchAll();
    }
}
