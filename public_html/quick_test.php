<?php
// Quick Session Test
session_start();

echo "<h2>Quick Session Test</h2>";

// Test Admin-Login direkt setzen
$_SESSION['admin'] = true;
$_SESSION['test_time'] = time();

echo "Session gesetzt:<br>";
echo "- admin: " . ($_SESSION['admin'] ? 'true' : 'false') . "<br>";
echo "- test_time: " . $_SESSION['test_time'] . "<br>";
echo "- Session ID: " . session_id() . "<br>";

// Test isAdmin() Funktion
require_once 'functions.php';
echo "- isAdmin(): " . (isAdmin() ? 'TRUE' : 'FALSE') . "<br>";

echo "<p><a href='admin.php'>→ Jetzt Admin-Panel testen</a></p>";
?>
