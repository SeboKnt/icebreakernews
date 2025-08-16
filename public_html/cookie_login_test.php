<?php
// Cookie-only Login Test - komplett ohne Sessions

// Cookie-Funktionen
function setAuthCookie($value = true) {
    $cookie_value = $value ? 'auth_' . time() : '';
    $expires = $value ? time() + 3600 : time() - 3600;
    setcookie('simple_auth', $cookie_value, $expires, '/', '', false, true);
    return $cookie_value;
}

function isAuthenticated() {
    return isset($_COOKIE['simple_auth']) && strpos($_COOKIE['simple_auth'], 'auth_') === 0;
}

// Login verarbeiten
$message = '';
if (isset($_POST['login'])) {
    $password = trim($_POST['password'] ?? '');
    if ($password === 'admin123') {
        $cookie_value = setAuthCookie(true);
        $message = "<div style='color: green; padding: 10px; background: #d4edda; border-radius: 5px; margin: 10px 0;'>✓ Login erfolgreich! Cookie gesetzt: $cookie_value</div>";
    } else {
        $message = "<div style='color: red; padding: 10px; background: #f8d7da; border-radius: 5px; margin: 10px 0;'>✗ Falsches Passwort</div>";
    }
}

// Logout verarbeiten
if (isset($_GET['logout'])) {
    setAuthCookie(false);
    $message = "<div style='color: orange; padding: 10px; background: #fff3cd; border-radius: 5px; margin: 10px 0;'>Abgemeldet - Cookie gelöscht</div>";
}

$isLoggedIn = isAuthenticated();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cookie-Only Login Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .box { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; }
        input, button { padding: 10px; margin: 5px 0; font-size: 16px; width: 200px; }
        button { background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Cookie-Only Login Test</h1>
    
    <?= $message ?>
    
    <div class="box">
        <h3>Authentication Status:</h3>
        <p><strong>Authenticated:</strong> <?= $isLoggedIn ? '✓ YES' : '✗ NO' ?></p>
        <p><strong>Auth Cookie:</strong> <?= $_COOKIE['simple_auth'] ?? 'not set' ?></p>
        <p><strong>All Cookies:</strong></p>
        <pre><?= print_r($_COOKIE, true) ?></pre>
    </div>
    
    <?php if (!$isLoggedIn): ?>
        <div class="box">
            <h3>Login:</h3>
            <form method="post">
                <p>Passwort: <input type="password" name="password" placeholder="admin123" required></p>
                <button type="submit" name="login">Anmelden</button>
            </form>
        </div>
    <?php else: ?>
        <div class="box" style="background: #d4edda;">
            <h3>✓ Erfolgreich eingeloggt!</h3>
            <p><a href="?logout=1">Abmelden</a></p>
            <p><a href="admin.php">Zum Admin Panel</a></p>
        </div>
    <?php endif; ?>
    
    <div class="box">
        <h3>Navigation:</h3>
        <p><a href="cookie_login_test.php">Seite neu laden</a></p>
        <p><a href="admin.php">Admin Panel</a></p>
        <p><a href="admin.php?debug=1">Admin Panel (Debug)</a></p>
    </div>
</body>
</html>
