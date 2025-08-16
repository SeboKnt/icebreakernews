<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>PostgreSQL Connection Test</h1>";

$config = require __DIR__ . '/config.php';
$db = $config['db'];

echo "<p>Testing connection to: " . htmlspecialchars($db['host']) . "</p>";

if (!file_exists($db['sslrootcert'])) {
    die("<p>ERROR: SSL Root certificate not found at: " . htmlspecialchars($db['sslrootcert']) . "</p>");
}

echo "<p>SSL Certificate found: " . htmlspecialchars($db['sslrootcert']) . "</p>";

// Set SSL environment variables
putenv('PGSSLMODE=' . $db['sslmode']);
putenv('PGSSLROOTCERT=' . realpath($db['sslrootcert']));

$dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', $db['host'], $db['port'], $db['dbname']);

try {
    $pdo = new PDO($dsn, $db['user'], $db['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "<p style='color:green'>SUCCESS: Connected to database!</p>";
    
    $result = $pdo->query("SELECT current_timestamp")->fetchColumn();
    echo "<p>Server time: " . htmlspecialchars($result) . "</p>";
} catch (PDOException $e) {
    echo "<p style='color:red'>ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
}