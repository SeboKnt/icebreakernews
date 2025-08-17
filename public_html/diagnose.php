<?php
// Server Error Diagnose
header('Content-Type: text/html; charset=utf-8');

echo "<h1>🔍 Server Error Diagnose</h1>";

try {
    echo "<h2>✅ PHP läuft</h2>";
    echo "<p>Wenn du diese Seite siehst, funktioniert PHP grundsätzlich.</p>";
    
    echo "<h3>PHP Info:</h3>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><td><strong>PHP Version</strong></td><td>" . PHP_VERSION . "</td></tr>";
    echo "<tr><td><strong>Server Software</strong></td><td>" . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</td></tr>";
    echo "<tr><td><strong>Document Root</strong></td><td>" . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "</td></tr>";
    echo "<tr><td><strong>Current Dir</strong></td><td>" . __DIR__ . "</td></tr>";
    echo "</table>";
    
    echo "<h3>Dateien Check:</h3>";
    $files_to_check = [
        'functions.php',
        'config.php',
        'db.php',
        '.env.php',
        'index.php',
        'admin.php'
    ];
    
    foreach ($files_to_check as $file) {
        $exists = file_exists($file);
        $readable = $exists ? is_readable($file) : false;
        $status = $exists ? ($readable ? '✅ OK' : '⚠️ Nicht lesbar') : '❌ Fehlt';
        echo "<p><strong>$file:</strong> $status</p>";
    }
    
    echo "<h3>Verzeichnis Check:</h3>";
    $dirs_to_check = [
        'sessions',
        'uploads',
        'db'
    ];
    
    foreach ($dirs_to_check as $dir) {
        $exists = is_dir($dir);
        $writable = $exists ? is_writable($dir) : false;
        $status = $exists ? ($writable ? '✅ OK' : '⚠️ Nicht beschreibbar') : '❌ Fehlt';
        echo "<p><strong>$dir/:</strong> $status</p>";
    }
    
    echo "<h3>Session Test:</h3>";
    if (!session_id()) {
        session_start();
    }
    echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
    echo "<p><strong>Session Save Path:</strong> " . session_save_path() . "</p>";
    echo "<p><strong>Session Status:</strong> " . session_status() . "</p>";
    
} catch (Exception $e) {
    echo "<h2>❌ PHP Error:</h2>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h3>🎯 Nächste Schritte:</h3>";
echo "<ul>";
echo "<li><a href='index.php'>Index.php testen</a></li>";
echo "<li><a href='admin.php'>Admin.php testen</a></li>";
echo "<li><a href='direct_login.php'>Direct Login testen</a></li>";
echo "</ul>";

echo "<h3>🔧 Notfall-Links:</h3>";
echo "<ul>";
echo "<li><a href='?phpinfo=1'>PHP Info anzeigen</a></li>";
echo "<li><a href='?test_db=1'>Datenbank testen</a></li>";
echo "</ul>";

// PHP Info anzeigen
if (isset($_GET['phpinfo'])) {
    echo "<h2>PHP Info:</h2>";
    phpinfo();
}

// Datenbank testen
if (isset($_GET['test_db'])) {
    echo "<h2>Datenbank Test:</h2>";
    try {
        if (file_exists('config.php')) {
            $config = require 'config.php';
            echo "<p>✅ Config geladen</p>";
            
            if (file_exists('db.php')) {
                $pdo = require 'db.php';
                echo "<p>✅ Datenbankverbindung erfolgreich</p>";
            } else {
                echo "<p>❌ db.php nicht gefunden</p>";
            }
        } else {
            echo "<p>❌ config.php nicht gefunden</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Datenbank Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}
?>
