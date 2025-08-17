<?php
// Einfacher Test um zu prüfen ob PHP funktioniert
echo "<h1>PHP Test</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";

// Test database connection
try {
    if (file_exists(__DIR__ . '/db.php')) {
        echo "<p>db.php found</p>";
        $pdo = require __DIR__ . '/db.php';
        echo "<p>Database connection: OK</p>";
        
        // Test if functions.php exists
        if (file_exists(__DIR__ . '/functions.php')) {
            echo "<p>functions.php found</p>";
            require_once __DIR__ . '/functions.php';
            echo "<p>functions.php loaded</p>";
        } else {
            echo "<p style='color: red;'>functions.php NOT found</p>";
        }
        
        // Test database
        $pdo->query('SELECT 1');
        echo "<p>Database query: OK</p>";
        
        // Check tables
        $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p>Found tables: " . implode(', ', $tables) . "</p>";
        
    } else {
        echo "<p style='color: red;'>db.php NOT found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Database Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<p><a href='/'>Back to index</a> | <a href='/setup_db.php'>Run setup</a></p>";
?>
