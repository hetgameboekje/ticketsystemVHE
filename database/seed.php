<?php
// Eenmalig uitvoeren: php database/seed.php
// Maakt demo-gebruikers aan met een door PHP gegenereerde wachtwoord-hash.
//
// Powershell
// & "C:\xampp\php\php.exe" database\seed.php
//
//
//
require __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;

$pdo = Database::pdo();
$wachtwoord = 'demo123';
$hash = password_hash($wachtwoord, PASSWORD_DEFAULT);

$gebruikers = [
    ['Timo Bergthaler', 'timo@bergthaler.nl', 'admin']

];

$stmt = $pdo->prepare(
    'INSERT IGNORE INTO users (naam, email, wachtwoord_hash, rol) VALUES (:naam, :email, :hash, :rol)'
);

foreach ($gebruikers as [$naam, $email, $rol]) {
    $stmt->execute(['naam' => $naam, 'email' => $email, 'hash' => $hash, 'rol' => $rol]);
}

echo "Klaar. Demo-gebruikers aangemaakt met wachtwoord: {$wachtwoord}\n";
