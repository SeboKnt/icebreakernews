<?php
// Emergency Session Fix und Login
$session_path = __DIR__ . '/sessions';
if (!is_dir($session_path)) {
    mkdir($session_path, 0755, true);
}
if (is_writable($session_path)) {
    session_save_path($session_path);
}

session_start();

echo "<h2>🚨 Emergency Session Fix</h2>";

// Debug aktuelle Session-Info
echo "<h3>Aktuelle Session-Info:</h3>";
echo "Session ID: " . session_id() . "<br>";
echo "Session Save Path: " . session_save_path() . "<br>";
echo "Session Status: " . session_status() . "<br>";
echo "Session Writable: " . (is_writable(session_save_path()) ? 'YES' : 'NO') . "<br>";

// Test 1: Session-Daten direkt setzen
if (isset($_POST['emergency_login'])) {
    $_SESSION['admin'] = true;
    $_SESSION['login_time'] = time();
    $_SESSION['emergency_login'] = true;
    
    echo "<p style='color: green; font-weight: bold;'>✅ Emergency Login durchgeführt!</p>";
    echo "<p>Session-Daten gesetzt:</p>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
    
    echo "<p><a href='admin.php' style='background: green; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>→ Jetzt Admin-Panel öffnen</a></p>";
}

// Test 2: Session komplett zurücksetzen
if (isset($_POST['reset_session'])) {
    session_destroy();
    session_start();
    echo "<p style='color: orange;'>🔄 Session zurückgesetzt</p>";
}

// Test 3: Mit korrektem Passwort einloggen
if (isset($_POST['normal_login'])) {
    $password = trim($_POST['password'] ?? '');
    
    // Lade .env.php für Passwort-Hash
    $env_config = file_exists('.env.php') ? require '.env.php' : [];
    $password_hash = $env_config['ADMIN_PASSWORD_HASH'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    
    if (password_verify($password, $password_hash)) {
        $_SESSION['admin'] = true;
        $_SESSION['login_time'] = time();
        
        echo "<p style='color: green; font-weight: bold;'>✅ Normaler Login erfolgreich!</p>";
        echo "<p><a href='admin.php' style='background: blue; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>→ Zum Admin-Panel</a></p>";
    } else {
        echo "<p style='color: red;'>❌ Falsches Passwort</p>";
    }
}

echo "<h3>Session-Inhalt:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.box { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px; margin: 5px; }
input[type="password"] { padding: 8px; margin: 5px; border: 1px solid #ccc; border-radius: 3px; }
</style>

<div class="box">
    <h3>🆘 Emergency Login (Umgeht Passwort)</h3>
    <form method="post">
        <button type="submit" name="emergency_login">Emergency Login durchführen</button>
    </form>
</div>

<div class="box">
    <h3>🔐 Normaler Login</h3>
    <form method="post">
        <input type="password" name="password" placeholder="Admin-Passwort" required>
        <button type="submit" name="normal_login">Normal einloggen</button>
    </form>
    <small>Standard-Passwort: admin123</small>
</div>

<div class="box">
    <h3>🔄 Session zurücksetzen</h3>
    <form method="post">
        <button type="submit" name="reset_session" style="background: orange;">Session zurücksetzen</button>
    </form>
</div>

<p><a href="admin.php">← Zurück zum Admin-Panel</a></p>
<p><a href="session_debug.php">🔧 Session Debug Tool</a></p>
