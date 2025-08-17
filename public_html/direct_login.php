<?php
// Super Simple Direct Login - Keine Session-Probleme
$session_path = __DIR__ . '/sessions';
if (!is_dir($session_path)) {
    mkdir($session_path, 0755, true);
}

// Force lokalen Session-Pfad
ini_set('session.save_path', $session_path);
session_start();

echo "<h1>🔓 Direct Login Tool</h1>";

// Sofort einloggen
if (isset($_GET['force_login'])) {
    $_SESSION = []; // Session leeren
    $_SESSION['admin'] = true;
    $_SESSION['login_time'] = time();
    $_SESSION['direct_login'] = true;
    
    echo "<div style='background: green; color: white; padding: 20px; margin: 20px 0; border-radius: 10px;'>";
    echo "<h2>✅ Login erzwungen!</h2>";
    echo "<p>Session-Daten direkt gesetzt. Admin-Zugang aktiviert.</p>";
    echo "<p><strong><a href='admin.php' style='color: white; font-size: 18px;'>→ JETZT ADMIN-PANEL ÖFFNEN</a></strong></p>";
    echo "</div>";
}

// Session-Info anzeigen
echo "<h3>Session-Status:</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><td><strong>Session ID</strong></td><td>" . session_id() . "</td></tr>";
echo "<tr><td><strong>Session Save Path</strong></td><td>" . session_save_path() . "</td></tr>";
echo "<tr><td><strong>Admin Status</strong></td><td>" . (isset($_SESSION['admin']) && $_SESSION['admin'] ? '✅ EINGELOGGT' : '❌ NICHT EINGELOGGT') . "</td></tr>";
echo "<tr><td><strong>Session Writable</strong></td><td>" . (is_writable(session_save_path()) ? '✅ JA' : '❌ NEIN') . "</td></tr>";
echo "</table>";

echo "<h3>Session-Daten:</h3>";
echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>";
print_r($_SESSION);
echo "</pre>";

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f9f9f9; }
.button { display: inline-block; background: #007cba; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 10px 0; font-size: 16px; }
.button:hover { background: #005a87; }
.danger { background: #d32f2f; }
.success { background: #2e7d32; }
</style>

<div style="text-align: center; margin: 30px 0;">
    <a href="?force_login=1" class="button success">🚀 FORCE LOGIN - SOFORT EINLOGGEN</a>
</div>

<div style="text-align: center; margin: 20px 0;">
    <a href="admin.php" class="button">👤 Admin-Panel öffnen</a>
    <a href="emergency_login.php" class="button">🆘 Emergency Login</a>
    <a href="session_debug.php" class="button">🔧 Session Debug</a>
</div>

<div style="background: white; padding: 20px; border-radius: 10px; margin: 20px 0;">
    <h3>🎯 Anleitung:</h3>
    <ol>
        <li><strong>Klicke auf "FORCE LOGIN"</strong> - Das setzt die Session direkt ohne Passwort</li>
        <li><strong>Öffne dann das Admin-Panel</strong> - Du solltest sofort eingeloggt sein</li>
        <li><strong>Falls es nicht funktioniert</strong> - Nutze die anderen Debug-Tools</li>
    </ol>
</div>
