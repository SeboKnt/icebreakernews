<?php
// Sichere Konfiguration für Produktionsserver
// Diese Datei sollte außerhalb des public_html Verzeichnisses stehen

return [
    // Admin Login - gehashtes Passwort
    'ADMIN_PASSWORD_HASH' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // admin123
    
    // Datenbank Credentials - Produktionsserver
    'DB_HOST' => 'j3vm.your-database.de',
    'DB_PORT' => '5432',
    'DB_NAME' => 'youngau_db1',
    'DB_USER' => 'youngau_1',
    'DB_PASS' => 'W3LHLZc8E7xGev71',
    'DB_SSLMODE' => 'verify-full',
];
?>
