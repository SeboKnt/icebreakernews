<?php
// Simple debug script to test database connection
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Debug Information</h1>";

echo "<h2>PHP Version</h2>";
echo "PHP Version: " . phpversion() . "<br>";

echo "<h2>File Check</h2>";
$files = ['config.php', 'db.php', 'functions.php', 'sqlca.pem'];
foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    echo "$file: " . (file_exists($path) ? "✓ EXISTS" : "❌ MISSING") . "<br>";
}

echo "<h2>Database Connection Test</h2>";
try {
    $config = require __DIR__ . '/config.php';
    echo "Config loaded: ✓<br>";
    echo "Database host: " . htmlspecialchars($config['db']['host']) . "<br>";
    
    $pdo = require __DIR__ . '/db.php';
    echo "Database connection: ✓<br>";
    
    $stmt = $pdo->query('SELECT version()');
    $version = $stmt->fetchColumn();
    echo "PostgreSQL version: " . htmlspecialchars($version) . "<br>";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_name = 'articles'");
    $tableExists = $stmt->fetchColumn();
    echo "Articles table exists: " . ($tableExists ? "✓" : "❌") . "<br>";
    
    if ($tableExists) {
        $stmt = $pdo->query('SELECT COUNT(*) FROM articles');
        $count = $stmt->fetchColumn();
        echo "Articles count: " . $count . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . htmlspecialchars($e->getMessage()) . "<br>";
}

echo "<h2>Actions</h2>";
echo "<a href='/install_db.php'>Setup Database</a> | ";
echo "<a href='/index.php'>Go to Homepage</a> | ";
echo "<a href='/admin.php'>Admin Panel</a>";
?>
