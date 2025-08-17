<?php
// IcebreakerNews Configuration

// Lade sichere Konfiguration aus .env.php
$env = [];
if (file_exists(__DIR__ . '/.env.php')) {
    $env = require __DIR__ . '/.env.php';
}

return [
    'db' => [
        'host' => $env['DB_HOST'] ?? 'localhost',
        'port' => (int)($env['DB_PORT'] ?? 5432),
        'dbname' => $env['DB_NAME'] ?? 'icebreaker_news',
        'user' => $env['DB_USER'] ?? 'root',
        'password' => $env['DB_PASS'] ?? '',
        'sslmode' => $env['DB_SSLMODE'] ?? 'prefer',
        'sslrootcert' => __DIR__ . '/sqlca.pem',
    ],
    'site' => [
        'title' => 'IcebreakerNews',
        'description' => 'Aktuelle Nachrichten und Berichte',
    ],
];