<?php
// PHP Session Configuration Check

echo "<h1>PHP Session Configuration Check</h1>";

echo "<h2>PHP Version & Session Status</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Session Status:</strong> " . session_status() . " (1=disabled, 2=active)</p>";

// Session konfigurieren und starten
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);

session_start();

echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";

echo "<h2>Session Configuration</h2>";
$session_config = [
    'session.save_path' => session_save_path(),
    'session.name' => session_name(),
    'session.cookie_lifetime' => ini_get('session.cookie_lifetime'),
    'session.cookie_path' => ini_get('session.cookie_path'),
    'session.cookie_domain' => ini_get('session.cookie_domain'),
    'session.cookie_secure' => ini_get('session.cookie_secure'),
    'session.cookie_httponly' => ini_get('session.cookie_httponly'),
    'session.use_only_cookies' => ini_get('session.use_only_cookies'),
    'session.use_strict_mode' => ini_get('session.use_strict_mode'),
    'session.gc_maxlifetime' => ini_get('session.gc_maxlifetime'),
];

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Setting</th><th>Value</th></tr>";
foreach ($session_config as $key => $value) {
    echo "<tr><td>$key</td><td>" . ($value ?: 'empty') . "</td></tr>";
}
echo "</table>";

echo "<h2>Session Test</h2>";

// Test Session Write
if (isset($_POST['test_write'])) {
    $_SESSION['test_key'] = 'test_value_' . time();
    echo "<p style='color: green;'>✓ Session variable written</p>";
}

// Test Session Read
if (isset($_SESSION['test_key'])) {
    echo "<p style='color: blue;'>✓ Session variable exists: " . $_SESSION['test_key'] . "</p>";
} else {
    echo "<p style='color: red;'>✗ No test session variable found</p>";
}

echo "<h2>Current Session Data</h2>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

echo "<h2>Headers Sent</h2>";
if (headers_sent($file, $line)) {
    echo "<p style='color: red;'>✗ Headers already sent in $file on line $line</p>";
} else {
    echo "<p style='color: green;'>✓ Headers not sent yet</p>";
}

echo "<h2>Actions</h2>";
echo "<form method='post'>";
echo "<button type='submit' name='test_write'>Write Test Session</button>";
echo "</form>";

echo "<p><a href='php_session_test.php'>Reload Page</a></p>";
echo "<p><a href='simple_login_test.php'>Simple Login Test</a></p>";
echo "<p><a href='admin.php'>Admin Panel</a></p>";
?>
