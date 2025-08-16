<?php
try {
    $config = require __DIR__ . '/config.php';
    $db = $config['db'];

    // Wenn vorhanden, Umgebungsvariablen vor der Verbindung setzen
    if (!empty($db['sslrootcert']) && file_exists($db['sslrootcert'])) {
        putenv('PGSSLMODE=' . $db['sslmode']);
        putenv('PGSSLROOTCERT=' . realpath($db['sslrootcert']));
    }

    $dsn = sprintf(
        'pgsql:host=%s;port=%d;dbname=%s',
        $db['host'],
        (int)$db['port'],
        $db['dbname']
    );

    $opts = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 10,
    ];

    $pdo = new PDO($dsn, $db['user'], $db['password'], $opts);
    return $pdo;
    
} catch (PDOException $e) {
    die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
} catch (Exception $e) {
    die('Configuration error: ' . htmlspecialchars($e->getMessage()));
}