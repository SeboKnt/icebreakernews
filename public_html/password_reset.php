<?php
// Passwort Reset System
require_once 'functions.php';
$config = require 'config.php';

$message = '';
$error = '';

// Reset-Request verarbeiten
if (isset($_POST['request_reset'])) {
    $email = trim($_POST['email'] ?? '');
    
    if ($email === 'passwd@youngandhungry.org') {
        // Temporäres Reset-Token generieren
        $reset_token = bin2hex(random_bytes(32));
        $reset_expires = time() + 3600; // 1 Stunde gültig
        
        // Token in Datei speichern (in Produktion: Datenbank verwenden)
        $token_data = json_encode([
            'token' => $reset_token,
            'expires' => $reset_expires,
            'email' => $email
        ]);
        file_put_contents('reset_token.json', $token_data);
        
        // E-Mail simulieren (in Produktion: echte E-Mail senden)
        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/password_reset.php?token=" . $reset_token;
        
        $message = "Reset-Link wurde generiert:<br><br>";
        $message .= "<strong>Simulierte E-Mail an " . h($email) . ":</strong><br>";
        $message .= "Klicken Sie hier um Ihr Passwort zurückzusetzen:<br>";
        $message .= "<a href='" . $reset_link . "' style='color: #667eea;'>" . $reset_link . "</a><br><br>";
        $message .= "<small>Link ist 1 Stunde gültig.</small>";
    } else {
        $error = 'Diese E-Mail-Adresse ist nicht autorisiert.';
    }
}

// Reset durchführen
if (isset($_GET['token']) && isset($_POST['reset_password'])) {
    $token = $_GET['token'];
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    
    // Token validieren
    if (file_exists('reset_token.json')) {
        $token_data = json_decode(file_get_contents('reset_token.json'), true);
        
        if ($token_data['token'] === $token && $token_data['expires'] > time()) {
            if ($new_password === $confirm_password && strlen($new_password) >= 6) {
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                
                // .env.php aktualisieren
                $env_content = "<?php\n// Sichere Konfiguration für Produktionsserver\n// Diese Datei sollte außerhalb des public_html Verzeichnisses stehen\n\nreturn [\n    // Admin Login - gehashtes Passwort\n    'ADMIN_PASSWORD_HASH' => '$new_hash',\n    \n    // Datenbank (werden von config.php überschrieben wenn gesetzt)\n    'DB_HOST' => 'localhost',\n    'DB_NAME' => 'icebreaker_news',\n    'DB_USER' => 'root',\n    'DB_PASS' => '',\n];\n?>";
                
                if (file_put_contents('.env.php', $env_content)) {
                    // Token löschen
                    unlink('reset_token.json');
                    $message = 'Passwort erfolgreich zurückgesetzt! Sie können sich jetzt mit dem neuen Passwort anmelden.';
                } else {
                    $error = 'Fehler beim Speichern des neuen Passworts.';
                }
            } else {
                $error = 'Passwort muss mindestens 6 Zeichen lang sein und die Bestätigung muss übereinstimmen.';
            }
        } else {
            $error = 'Reset-Token ist ungültig oder abgelaufen.';
        }
    } else {
        $error = 'Kein gültiger Reset-Token gefunden.';
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Passwort zurücksetzen | <?= h($config['site']['title']) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            line-height: 1.6; 
            color: #1a1a1a; 
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .container {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        
        h1 {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #34495e;
            font-size: 14px;
        }
        
        input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #ecf0f1;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            font-size: 16px;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <?php if (isset($_GET['token'])): ?>
                <h1>🔑 Neues Passwort setzen</h1>
                
                <?php if ($message): ?>
                <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
                <?php endif; ?>
                
                <?php if (!$message): ?>
                <form method="post">
                    <div class="form-group">
                        <label for="new_password">Neues Passwort</label>
                        <input type="password" id="new_password" name="new_password" required placeholder="Mindestens 6 Zeichen" minlength="6">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Passwort bestätigen</label>
                        <input type="password" id="confirm_password" name="confirm_password" required placeholder="Neues Passwort wiederholen">
                    </div>
                    
                    <button type="submit" name="reset_password">Passwort zurücksetzen</button>
                </form>
                <?php endif; ?>
                
            <?php else: ?>
                <h1>🔓 Passwort zurücksetzen</h1>
                
                <?php if ($message): ?>
                <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
                <?php endif; ?>
                
                <p style="margin-bottom: 20px; color: #7f8c8d;">
                    Geben Sie Ihre E-Mail-Adresse ein, um einen Reset-Link zu erhalten.
                </p>
                
                <form method="post">
                    <div class="form-group">
                        <label for="email">E-Mail-Adresse</label>
                        <input type="email" id="email" name="email" required placeholder="passwd@youngandhungry.org">
                        <small style="color: #7f8c8d; font-size: 12px;">Nur autorisierte E-Mail-Adressen</small>
                    </div>
                    
                    <button type="submit" name="request_reset">Reset-Link anfordern</button>
                </form>
            <?php endif; ?>
            
            <div class="back-link">
                <a href="/admin.php">← Zurück zum Admin-Login</a>
            </div>
        </div>
    </div>
</body>
</html>
