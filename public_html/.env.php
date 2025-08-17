<?php
// Sichere Konfiguration für Produktionsserver
// Diese Datei sollte außerhalb des public_html Verzeichnisses stehen

return [
    // Admin Login - gehashtes Passwort
    'ADMIN_PASSWORD_HASH' => '$2y$10$MXy6EWeWGD2wNgLZQhVsfe5R3Cg8G8vHdvdqjjdLJ9BHO8zlKkDvu', // admin123
    
    // Datenbank Credentials - Produktionsserver
    'DB_HOST' => 'j3vm.your-database.de',
    'DB_PORT' => '5432',
    'DB_NAME' => 'youngau_db1',
    'DB_USER' => 'youngau_1',
    'DB_PASS' => 'W3LHLZc8E7xGev71',
    'DB_SSLMODE' => 'verify-full',
];
?>
