<?php
// Session Debug und Fix für Hetzner
session_start();

echo "<h2>Session Debug Tool</h2>";

// Test 1: Session schreiben
if (isset($_POST['test_session'])) {
    $_SESSION['test'] = 'Session funktioniert!';
    $_SESSION['timestamp'] = time();
    echo "<p style='color: green;'>✅ Session-Daten geschrieben</p>";
}

// Test 2: Session löschen
if (isset($_POST['clear_session'])) {
    session_destroy();
    echo "<p style='color: orange;'>🔄 Session gelöscht</p>";
    header("Refresh: 2; url=session_debug.php");
    exit;
}

// Test 3: Admin Login simulieren
if (isset($_POST['simulate_admin'])) {
    $_SESSION['admin'] = true;
    $_SESSION['login_time'] = time();
    echo "<p style='color: blue;'>👤 Admin-Session simuliert</p>";
}

echo "<h3>Session Info:</h3>";
echo "<strong>Session ID:</strong> " . session_id() . "<br>";
echo "<strong>Session Status:</strong> " . session_status() . " (1=disabled, 2=active)<br>";
echo "<strong>Session Save Path:</strong> " . session_save_path() . "<br>";
echo "<strong>Session Name:</strong> " . session_name() . "<br>";

echo "<h3>Session Daten:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>PHP Session Settings:</h3>";
$session_settings = [
    'session.auto_start',
    'session.use_cookies',
    'session.use_only_cookies',
    'session.cookie_httponly',
    'session.cookie_lifetime',
    'session.gc_maxlifetime',
    'session.save_path',
    'session.name'
];

foreach ($session_settings as $setting) {
    echo "<strong>$setting:</strong> " . ini_get($setting) . "<br>";
}

echo "<h3>Tests:</h3>";
?>

<form method="post" style="margin: 10px 0;">
    <button type="submit" name="test_session">Session schreiben</button>
</form>

<form method="post" style="margin: 10px 0;">
    <button type="submit" name="simulate_admin" style="background: blue; color: white;">Admin-Login simulieren</button>
</form>

<form method="post" style="margin: 10px 0;">
    <button type="submit" name="clear_session" style="background: red; color: white;">Session löschen</button>
</form>

<p><a href="admin.php">← Zurück zum Admin-Panel</a></p>

<?php
// Test Session-Verzeichnis Berechtigung
$save_path = session_save_path();
if (is_writable($save_path)) {
    echo "<p style='color: green;'>✅ Session-Verzeichnis ist beschreibbar: $save_path</p>";
} else {
    echo "<p style='color: red;'>❌ Session-Verzeichnis ist NICHT beschreibbar: $save_path</p>";
    echo "<p>Versuche alternatives Session-Verzeichnis...</p>";
    
    // Alternative Session-Verzeichnisse für Hetzner
    $alt_paths = [
        __DIR__ . '/sessions',
        __DIR__ . '/tmp',
        sys_get_temp_dir()
    ];
    
    foreach ($alt_paths as $alt_path) {
        if (!is_dir($alt_path)) {
            @mkdir($alt_path, 0755, true);
        }
        
        if (is_writable($alt_path)) {
            echo "<p style='color: green;'>✅ Alternatives Verzeichnis gefunden: $alt_path</p>";
            break;
        } else {
            echo "<p style='color: orange;'>⚠️ Nicht beschreibbar: $alt_path</p>";
        }
    }
}
?>
