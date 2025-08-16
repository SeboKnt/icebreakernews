<?php
session_start();

// Debugging für Session-Probleme
echo "<h2>Session Test</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Status: " . session_status() . " (1=disabled, 2=active)</p>";
echo "<p>Session Save Path: " . session_save_path() . "</p>";
echo "<p>Session Name: " . session_name() . "</p>";

echo "<h3>Session Data:</h3>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

echo "<h3>POST Data:</h3>";
echo "<pre>" . print_r($_POST, true) . "</pre>";

echo "<h3>GET Data:</h3>";
echo "<pre>" . print_r($_GET, true) . "</pre>";

// Test Login
if (isset($_POST['test_login'])) {
    $_SESSION['admin'] = true;
    $_SESSION['test_time'] = time();
    echo "<p style='color: green;'>Session admin gesetzt!</p>";
    echo "<p><a href='session_test.php'>Seite neu laden</a></p>";
}

// Test Logout
if (isset($_POST['test_logout'])) {
    session_destroy();
    echo "<p style='color: red;'>Session zerstört!</p>";
    echo "<p><a href='session_test.php'>Seite neu laden</a></p>";
    exit;
}

echo "<h3>Test Actions:</h3>";
echo "<form method='post'>";
echo "<button type='submit' name='test_login'>Test Login</button> ";
echo "<button type='submit' name='test_logout'>Test Logout</button>";
echo "</form>";

echo "<p><a href='admin.php'>Zurück zum Admin</a></p>";
?>
