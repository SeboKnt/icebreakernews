<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

echo "<h1>Database Schema Installation</h1>";

try {
    $pdo = require __DIR__ . '/db.php';
    
    // Schema-Datei einlesen
    $schemaPath = __DIR__ . '/db/schema.sql';
    if (!file_exists($schemaPath)) {
        die("<p>ERROR: Schema file not found at: " . htmlspecialchars($schemaPath) . "</p>");
    }
    
    $sql = file_get_contents($schemaPath);
    
    // Schema ausführen
    echo "<p>Executing schema...</p>";
    $result = $pdo->exec($sql);
    
    echo "<p style='color:green'>SUCCESS: Schema installed!</p>";
    
    // Test-Artikel einfügen
    $pdo->exec("INSERT INTO articles (title, summary, content, author, published_at) VALUES 
                ('Willkommen bei IcebreakerNews', 'Unsere erste Nachricht', 'Inhalt der Nachricht', 'Admin', NOW())");
    
    echo "<p>Test article created.</p>";
    echo "<p><a href='index.php'>Go to homepage</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
}