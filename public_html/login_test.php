<?php
// Simple Login Test
session_start();

echo "<h1>Login Debug Test</h1>";

// Process login
if (isset($_POST['test_login'])) {
    if ($_POST['password'] === 'admin123') {
        $_SESSION['admin'] = true;
        echo "<div style='color: green; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "✓ Login erfolgreich! Session gesetzt.";
        echo "</div>";
    } else {
        echo "<div style='color: red; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "❌ Falsches Passwort!";
        echo "</div>";
    }
}

// Show session info
echo "<h2>Session Information</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; font-family: monospace;'>";
echo "Session ID: " . session_id() . "<br>";
echo "Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? 'ACTIVE' : 'NOT ACTIVE') . "<br>";
echo "Admin in Session: " . (isset($_SESSION['admin']) ? 'YES' : 'NO') . "<br>";
echo "Admin Value: " . (isset($_SESSION['admin']) ? $_SESSION['admin'] : 'NOT SET') . "<br>";
echo "All Session Data: " . print_r($_SESSION, true) . "<br>";
echo "</div>";

// Test form
echo "<h2>Test Login</h2>";
echo "<form method='post' style='background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>";
echo "<label>Passwort: <input type='password' name='password' value='admin123' style='margin-left: 10px; padding: 5px;'></label><br><br>";
echo "<button type='submit' name='test_login' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;'>Test Login</button>";
echo "</form>";

echo "<h2>Navigation</h2>";
echo "<a href='/admin.php' style='color: #007bff;'>Zurück zum Admin</a> | ";
echo "<a href='/debug.php' style='color: #007bff;'>Database Debug</a> | ";
echo "<a href='/' style='color: #007bff;'>Homepage</a>";

if (isset($_SESSION['admin']) && $_SESSION['admin']) {
    echo "<br><br><div style='color: green;'>✓ Sie sind als Admin eingeloggt! Das Problem liegt nicht an den Sessions.</div>";
}
?>
