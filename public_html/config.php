<?php
// IcebreakerNews Configuration
return [
    'db' => [
        'host' => 'j3vm.your-database.de',
        'port' => 5432,
        'dbname' => 'youngau_db1',
        'user' => 'youngau_1',
        'password' => 'W3LHLZc8E7xGev71',
        'sslmode' => 'verify-full',
        'sslrootcert' => __DIR__ . '/sqlca.pem',
    ],
    'site' => [
        'title' => 'IcebreakerNews',
        'description' => 'Aktuelle Nachrichten und Berichte',
    ],
];