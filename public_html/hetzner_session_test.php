<?php
// Hetzner .htaccess Session Test

session_start();

echo "<h1>Hetzner Session Test (.htaccess)</h1>";

// Test Session schreiben
if (isset($_POST['test_write'])) {
    $_SESSION['test_key'] = 'test_value_' . time();
    $_SESSION['hetzner_test'] = true;
    echo "<div style='color: green; padding: 10px; background: #d4edda; border-radius: 5px; margin: 10px 0;'>✓ Session geschrieben</div>";
}

// Test Session löschen
if (isset($_POST['test_clear'])) {
    session_destroy();
    echo "<div style='color: red; padding: 10px; background: #f8d7da; border-radius: 5px; margin: 10px 0;'>✓ Session gelöscht</div>";
}

echo "<h2>Session Information</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Setting</th><th>Value</th></tr>";

$session_settings = [
    'Session ID' => session_id(),
    'Session Status' => session_status() . ' (1=disabled, 2=active)',
    'Session Name' => session_name(),
    'Session Save Path' => session_save_path(),
    'use_cookies' => ini_get('session.use_cookies') ? 'Yes' : 'No',
    'use_only_cookies' => ini_get('session.use_only_cookies') ? 'Yes' : 'No',
    'cookie_httponly' => ini_get('session.cookie_httponly') ? 'Yes' : 'No',
    'cookie_lifetime' => ini_get('session.cookie_lifetime'),
    'gc_maxlifetime' => ini_get('session.gc_maxlifetime'),
];

foreach ($session_settings as $key => $value) {
    $color = ($key === 'Session Status' && $value === '2 (1=disabled, 2=active)') ? 'color: green;' : '';
    echo "<tr><td>$key</td><td style='$color'>" . ($value ?: 'empty') . "</td></tr>";
}
echo "</table>";

echo "<h2>Current Session Data</h2>";
if (empty($_SESSION)) {
    echo "<p style='color: orange;'>Keine Session-Daten vorhanden</p>";
} else {
    echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>" . print_r($_SESSION, true) . "</pre>";
}

echo "<h2>Test Actions</h2>";
echo "<form method='post' style='margin: 10px 0;'>";
echo "<button type='submit' name='test_write' style='background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; margin-right: 10px;'>Session schreiben</button>";
echo "<button type='submit' name='test_clear' style='background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 5px;'>Session löschen</button>";
echo "</form>";

echo "<h2>Navigation</h2>";
echo "<p><a href='hetzner_session_test.php'>Seite neu laden</a></p>";
echo "<p><a href='admin.php'>Admin Panel</a></p>";
echo "<p><a href='admin.php?debug=1'>Admin Panel (Debug)</a></p>";

// .htaccess Check
echo "<h2>.htaccess Status</h2>";
if (file_exists('.htaccess')) {
    echo "<p style='color: green;'>✓ .htaccess Datei gefunden</p>";
    $htaccess_content = file_get_contents('.htaccess');
    if (strpos($htaccess_content, 'session.use_cookies') !== false) {
        echo "<p style='color: green;'>✓ Session-Konfiguration in .htaccess gefunden</p>";
    } else {
        echo "<p style='color: red;'>✗ Keine Session-Konfiguration in .htaccess gefunden</p>";
    }
} else {
    echo "<p style='color: red;'>✗ .htaccess Datei nicht gefunden</p>";
}
?>
