<?php
// Zoekt tickets met dezelfde taak + opdrachtgever en verwijdert de duplicaten (oudste blijft staan).
// Gebruik: php database/dedupe_tickets.php          (proefdraai, toont alleen wat er zou gebeuren)
//          php database/dedupe_tickets.php --delete (verwijdert de duplicaten echt)

require __DIR__ . '/../app/bootstrap.php';

use App\Modules\Ticket\Models\TicketModel;

$apply = in_array('--delete', $argv, true);

$groups = TicketModel::findDuplicateGroups();

if (empty($groups)) {
    echo "Geen dubbele tickets gevonden (op basis van taak + opdrachtgever).\n";
    exit(0);
}

$totalToDelete = 0;

foreach ($groups as $group) {
    $tickets = TicketModel::findByTitelEnOpdrachtgeverKey($group['titel_key'], $group['opdrachtgever_key']);
    $keep = array_shift($tickets);

    echo "\n\"{$keep['titel']}\" — {$keep['opdrachtgever_naam']} ({$group['aantal']}x)\n";
    echo "  behouden:  #{$keep['id']} (aangemaakt {$keep['created_at']}, status {$keep['status']})\n";

    foreach ($tickets as $dup) {
        $logNote = $dup['log_count'] > 0 ? " — LET OP: heeft {$dup['log_count']} opmerking(en) die ook verdwijnen" : '';
        $action = $apply ? 'verwijderd:' : 'zou weg:  ';
        echo "  {$action} #{$dup['id']} (aangemaakt {$dup['created_at']}, status {$dup['status']}){$logNote}\n";

        if ($apply) {
            TicketModel::delete((int) $dup['id']);
        }

        $totalToDelete++;
    }
}

echo "\n";
if ($apply) {
    echo "Klaar: {$totalToDelete} dubbele ticket(s) verwijderd.\n";
} else {
    echo "{$totalToDelete} dubbele ticket(s) gevonden (proefdraai, er is niets verwijderd).\n";
    echo "Voer uit met --delete om ze daadwerkelijk te verwijderen: php database/dedupe_tickets.php --delete\n";
}
