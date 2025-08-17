<?php
// Passwort-Hash-Generator und -Reparatur

echo "<h2>🔐 Passwort-Hash-Generator</h2>";

// Standard-Passwort "admin123" hashen
$standard_password = 'admin123';
$new_hash = password_hash($standard_password, PASSWORD_DEFAULT);

echo "<h3>Neuer Hash für 'admin123':</h3>";
echo "<code style='background: #f5f5f5; padding: 10px; display: block; word-break: break-all;'>$new_hash</code>";

// Test des Hashs
$test = password_verify($standard_password, $new_hash);
echo "<p>Hash-Test: " . ($test ? '✅ KORREKT' : '❌ FEHLER') . "</p>";

// Aktuellen Hash aus .env.php laden
if (file_exists('.env.php')) {
    $env_config = require '.env.php';
    $current_hash = $env_config['ADMIN_PASSWORD_HASH'] ?? 'NICHT GEFUNDEN';
    
    echo "<h3>Aktueller Hash in .env.php:</h3>";
    echo "<code style='background: #ffe6e6; padding: 10px; display: block; word-break: break-all;'>$current_hash</code>";
    
    // Test des aktuellen Hashs
    $current_test = password_verify($standard_password, $current_hash);
    echo "<p>Aktueller Hash-Test: " . ($current_test ? '✅ KORREKT' : '❌ FEHLER - HIER IST DAS PROBLEM!') . "</p>";
} else {
    echo "<p style='color: red;'>❌ .env.php nicht gefunden!</p>";
}

// Fix-Funktion
if (isset($_POST['fix_password'])) {
    $env_content = "<?php\n// Sichere Konfiguration für Produktionsserver\n// Diese Datei sollte außerhalb des public_html Verzeichnisses stehen\n\nreturn [\n    // Admin Login - gehashtes Passwort\n    'ADMIN_PASSWORD_HASH' => '$new_hash',\n    \n    // Datenbank Credentials - Produktionsserver\n    'DB_HOST' => 'j3vm.your-database.de',\n    'DB_PORT' => '5432',\n    'DB_NAME' => 'youngau_db1',\n    'DB_USER' => 'youngau_1',\n    'DB_PASS' => 'W3LHLZc8E7xGev71',\n    'DB_SSLMODE' => 'verify-full',\n];\n?>";
    
    if (file_put_contents('.env.php', $env_content)) {
        echo "<div style='background: green; color: white; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
        echo "<h3>✅ .env.php erfolgreich repariert!</h3>";
        echo "<p>Das Passwort 'admin123' sollte jetzt funktionieren.</p>";
        echo "<p><strong><a href='admin.php' style='color: white;'>→ Jetzt Admin-Panel testen</a></strong></p>";
        echo "</div>";
    } else {
        echo "<p style='color: red;'>❌ Fehler beim Schreiben der .env.php Datei</p>";
    }
}

// Custom Passwort
if (isset($_POST['custom_password'])) {
    $custom_pass = $_POST['new_password'];
    if (!empty($custom_pass)) {
        $custom_hash = password_hash($custom_pass, PASSWORD_DEFAULT);
        echo "<h3>Hash für '$custom_pass':</h3>";
        echo "<code style='background: #e6ffe6; padding: 10px; display: block; word-break: break-all;'>$custom_hash</code>";
    }
}

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f9f9f9; }
.button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px; margin: 5px; cursor: pointer; }
.fix { background: #2e7d32; }
input[type="text"] { padding: 8px; margin: 5px; border: 1px solid #ccc; border-radius: 3px; width: 200px; }
</style>

<div style="background: white; padding: 20px; border-radius: 10px; margin: 20px 0;">
    <h3>🔧 Sofort-Reparatur</h3>
    <p>Dies repariert die .env.php mit dem korrekten Hash für 'admin123':</p>
    <form method="post">
        <button type="submit" name="fix_password" class="button fix">🚀 .env.php REPARIEREN</button>
    </form>
</div>

<div style="background: white; padding: 20px; border-radius: 10px; margin: 20px 0;">
    <h3>🎯 Custom Passwort hashen</h3>
    <form method="post">
        <input type="text" name="new_password" placeholder="Neues Passwort eingeben" required>
        <button type="submit" name="custom_password" class="button">Hash erstellen</button>
    </form>
</div>

<div style="background: #fff3cd; padding: 15px; border-radius: 10px; margin: 20px 0;">
    <h3>ℹ️ Information</h3>
    <p>Der Hash in .env.php stimmt nicht mit dem Passwort "admin123" überein. Das kann passieren wenn:</p>
    <ul>
        <li>Das Passwort geändert wurde</li>
        <li>Der Hash beschädigt wurde</li>
        <li>Die .env.php nicht korrekt hochgeladen wurde</li>
    </ul>
</div>

<p><a href="admin.php">← Zurück zum Admin-Panel</a></p>
<p><a href="direct_login.php">🔓 Direct Login (ohne Passwort)</a></p>
