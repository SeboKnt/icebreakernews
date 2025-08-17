<?php
// Test des Passwort-Hashs
$password = 'admin123';
$hash = '$2y$10$MXy6EWeWGD2wNgLZQhVsfe5R3Cg8G8vHdvdqjjdLJ9BHO8zlKkDvu';

echo "<h2>Passwort-Hash-Test</h2>";
echo "<p><strong>Passwort:</strong> $password</p>";
echo "<p><strong>Hash:</strong> $hash</p>";

$result = password_verify($password, $hash);
echo "<p><strong>Ergebnis:</strong> " . ($result ? '✅ KORREKT' : '❌ FALSCH') . "</p>";

if ($result) {
    echo "<div style='background: green; color: white; padding: 20px; border-radius: 10px;'>";
    echo "<h3>✅ Hash ist korrekt!</h3>";
    echo "<p>Das Passwort 'admin123' sollte jetzt funktionieren.</p>";
    echo "</div>";
} else {
    echo "<div style='background: red; color: white; padding: 20px; border-radius: 10px;'>";
    echo "<h3>❌ Hash ist falsch!</h3>";
    echo "<p>Der Hash muss neu erstellt werden.</p>";
    echo "</div>";
}

// Neuen Hash erstellen
$new_hash = password_hash($password, PASSWORD_DEFAULT);
echo "<h3>Neuer Hash:</h3>";
echo "<code>$new_hash</code>";

echo "<p><a href='admin.php'>← Zurück zum Admin-Panel</a></p>";
?>
