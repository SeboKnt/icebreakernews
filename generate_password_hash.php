<?php
// Password Hash Generator
// Verwenden Sie dieses Script um sichere Passwort-Hashes zu erstellen

$password = 'admin123'; // Ändern Sie dieses Passwort
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Passwort: $password\n";
echo "Hash: $hash\n\n";

echo "Fügen Sie folgende Zeile zu Ihrer .env Datei hinzu:\n";
echo "export ADMIN_PASSWORD_HASH='$hash'\n\n";

// Test des Hashes
if (password_verify($password, $hash)) {
    echo "✓ Hash-Verifikation erfolgreich\n";
} else {
    echo "✗ Hash-Verifikation fehlgeschlagen\n";
}
?>
