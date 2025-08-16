<?php
// Simple Login Test - ohne externe Dependencies
session_start();

// Login verarbeiten
if (isset($_POST['login'])) {
    $password = trim($_POST['password'] ?? '');
    if ($password === 'admin123') {
        $_SESSION['admin'] = true;
        $_SESSION['login_time'] = time();
        echo "<div style='color: green; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0;'>";
        echo "✓ Login erfolgreich! Session gesetzt.";
        echo "</div>";
        echo "<p><a href='simple_login_test.php'>Seite neu laden</a> | <a href='admin.php'>Zum Admin Panel</a></p>";
    } else {
        echo "<div style='color: red; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0;'>";
        echo "✗ Falsches Passwort";
        echo "</div>";
    }
}

// Logout verarbeiten
if (isset($_GET['logout'])) {
    session_destroy();
    echo "<div style='color: orange; padding: 10px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; margin: 10px 0;'>";
    echo "Session zerstört";
    echo "</div>";
}

$isLoggedIn = isset($_SESSION['admin']) && $_SESSION['admin'] === true;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Simple Login Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .box { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; }
        input, button { padding: 10px; margin: 5px 0; font-size: 16px; }
        button { background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Simple Login Test</h1>
    
    <div class="box">
        <h3>Session Status:</h3>
        <p><strong>Session ID:</strong> <?= session_id() ?></p>
        <p><strong>Admin Session:</strong> <?= $isLoggedIn ? '✓ GESETZT' : '✗ NICHT GESETZT' ?></p>
        <p><strong>Session Data:</strong></p>
        <pre><?= print_r($_SESSION, true) ?></pre>
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
        <h3>Actions:</h3>
        <p><a href="simple_login_test.php">Seite neu laden</a></p>
        <p><a href="admin.php">Admin Panel</a></p>
        <p><a href="session_test.php">Session Test</a></p>
    </div>
</body>
</html>
