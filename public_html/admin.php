<?php
// Session-Konfiguration und -Start
ini_set('session.save_handler', 'files');
ini_set('session.save_path', sys_get_temp_dir());
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Für HTTP (nicht HTTPS)
ini_set('session.gc_maxlifetime', 3600);
ini_set('session.cookie_lifetime', 0);

// Session starten mit Fehlerbehandlung
if (session_status() === PHP_SESSION_NONE) {
    if (!session_start()) {
        // Fallback: Cookie-basierte "Session"
        $session_disabled = true;
    }
}

require_once 'functions.php';
$pdo = require 'db.php';
$config = require 'config.php';

// Debug Mode aktivieren
$debug_mode = isset($_GET['debug']);

// Cookie-basierte Fallback-Funktion für kaputte Sessions
function setAdminCookie($value = true) {
    setcookie('admin_auth', $value ? 'authenticated_' . time() : '', time() + 3600, '/', '', false, true);
}

function isAdminByCookie() {
    return isset($_COOKIE['admin_auth']) && strpos($_COOKIE['admin_auth'], 'authenticated_') === 0;
}

function isAdminFallback() {
    global $session_disabled;
    if ($session_disabled) {
        return isAdminByCookie();
    }
    return isset($_SESSION['admin']) && $_SESSION['admin'] === true;
}

// Login verarbeiten
if (isset($_POST['login'])) {
    $password = trim($_POST['password'] ?? '');
    
    if ($debug_mode) {
        error_log("Login attempt with password: " . $password);
        error_log("Expected password: admin123");
    }
    
    if ($password === 'admin123') {  // Einfaches Passwort
        if (!isset($session_disabled)) {
            $_SESSION['admin'] = true;
            $_SESSION['login_time'] = time();
        }
        
        // Cookie-Fallback setzen
        setAdminCookie(true);
        
        if ($debug_mode) {
            error_log("Login successful, session set");
            error_log("Session data: " . print_r($_SESSION ?? [], true));
        }
        
        // Force session write wenn verfügbar
        if (!isset($session_disabled)) {
            session_write_close();
            session_start();
        }
        
        // Redirect nach erfolgreichem Login - relativer Pfad ohne führenden Slash
        header('Location: admin.php');
        exit();
    } else {
        $error = 'Falsches Passwort - Verwenden Sie: admin123';
        if ($debug_mode) {
            error_log("Login failed: " . $error);
        }
    }
}

// Debug Info für Entwicklung
$debug_info = '';
if ($debug_mode) {
    $debug_info = "<strong>Debug Info:</strong><br>";
    $debug_info .= "Session ID: " . session_id() . "<br>";
    $debug_info .= "Session Status: " . session_status() . " (1=disabled, 2=active)<br>";
    $debug_info .= "Session Disabled: " . (isset($session_disabled) ? 'YES' : 'NO') . "<br>";
    $debug_info .= "Admin Session: " . (isset($_SESSION['admin']) ? 'SET (' . $_SESSION['admin'] . ')' : 'NOT SET') . "<br>";
    $debug_info .= "Admin Cookie: " . (isAdminByCookie() ? 'SET' : 'NOT SET') . "<br>";
    $debug_info .= "isAdminFallback(): " . (isAdminFallback() ? 'TRUE' : 'FALSE') . "<br>";
    $debug_info .= "POST data: " . print_r($_POST, true) . "<br>";
    $debug_info .= "Session Data: " . print_r($_SESSION ?? [], true) . "<br>";
    $debug_info .= "Cookies: " . print_r($_COOKIE, true) . "<br>";
}

// Logout
if (isset($_GET['logout'])) {
    if (!isset($session_disabled)) {
        session_destroy();
    }
    setAdminCookie(false); // Cookie löschen
    redirect('admin.php');
}

// Artikel erstellen/bearbeiten
if (isAdminFallback() && isset($_POST['save_article'])) {
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $published = isset($_POST['published']);
    
    if ($title && $content) {
        if ($id > 0) {
            // Update
            $publishedAt = $published ? 'NOW()' : 'NULL';
            $stmt = $pdo->prepare('UPDATE articles SET title=?, summary=?, content=?, author=?, published_at=COALESCE(?,published_at), updated_at=NOW() WHERE id=?');
            $stmt->execute([$title, $summary, $content, $author, $published ? date('Y-m-d H:i:s') : null, $id]);
            $success = 'Artikel erfolgreich aktualisiert';
        } else {
            // Insert
            $publishedAt = $published ? 'NOW()' : 'NULL';
            $stmt = $pdo->prepare('INSERT INTO articles (title, summary, content, author, published_at) VALUES (?, ?, ?, ?, ' . $publishedAt . ')');
            $stmt->execute([$title, $summary, $content, $author]);
            $success = 'Artikel erfolgreich erstellt';
        }
        // Redirect to clear form
        redirect('admin.php?success=' . urlencode($success));
    } else {
        $error = 'Titel und Inhalt sind erforderlich';
    }
}

// Artikel veröffentlichen/entziehen
if (isAdminFallback() && isset($_GET['toggle_publish'])) {
    $id = (int)$_GET['toggle_publish'];
    $stmt = $pdo->prepare('SELECT published_at FROM articles WHERE id = ?');
    $stmt->execute([$id]);
    $article = $stmt->fetch();
    
    if ($article) {
        $newStatus = $article['published_at'] ? 'NULL' : 'NOW()';
        $stmt = $pdo->prepare('UPDATE articles SET published_at = ' . $newStatus . ' WHERE id = ?');
        $stmt->execute([$id]);
        $success = $article['published_at'] ? 'Artikel zurückgezogen' : 'Artikel veröffentlicht';
        redirect('admin.php?success=' . urlencode($success));
    }
}

// Artikel löschen
if (isAdminFallback() && isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM articles WHERE id = ?');
    $stmt->execute([$id]);
    $success = 'Artikel gelöscht';
}

// Artikel zum Bearbeiten laden
$editArticle = null;
if (isAdminFallback() && isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare('SELECT * FROM articles WHERE id = ?');
    $stmt->execute([$id]);
    $editArticle = $stmt->fetch();
}

// Success message from URL parameter
if (isset($_GET['success'])) {
    $success = $_GET['success'];
}

// Alle Artikel für Admin-Übersicht
$articles = [];
if (isAdminFallback()) {
    $stmt = $pdo->query('SELECT id, title, author, published_at, created_at, updated_at FROM articles ORDER BY created_at DESC');
    $articles = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin | <?= h($config['site']['title']) ?></title>
    <style>
        /* Reset und Base Styles */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6; 
            color: #1a1a1a; 
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .container { 
            max-width: 1000px; 
            margin: 0 auto; 
            padding: 20px; 
        }
        
        /* Header */
        header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; 
            padding: 20px 0; 
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        
        .header-content { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            max-width: 1000px; 
            margin: 0 auto; 
            padding: 0 20px; 
        }
        
        .logo { 
            font-size: 24px; 
            font-weight: 800; 
            text-decoration: none; 
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .nav a { 
            color: white; 
            text-decoration: none; 
            margin-left: 20px;
            padding: 10px 20px;
            border-radius: 25px;
            background: rgba(255,255,255,0.1);
            transition: all 0.3s ease;
        }
        
        .nav a:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }
        
        /* Cards */
        .card { 
            background: white;
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
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
        
        .card h2 {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 25px;
            font-size: 28px;
        }
        
        /* Form Styles */
        .form-group { 
            margin-bottom: 25px; 
        }
        
        label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: 600;
            color: #34495e;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        input, textarea, select { 
            width: 100%; 
            padding: 15px 20px; 
            border: 2px solid #ecf0f1; 
            border-radius: 12px; 
            font-size: 16px;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        textarea { 
            min-height: 200px; 
            resize: vertical;
            line-height: 1.6;
        }
        
        /* Buttons */
        button { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; 
            padding: 15px 30px; 
            border: none; 
            border-radius: 25px; 
            cursor: pointer; 
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        button:hover { 
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary { 
            background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
            box-shadow: 0 4px 15px rgba(149, 165, 166, 0.3);
        }
        
        .btn-danger { 
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }
        
        /* Alerts */
        .alert { 
            padding: 20px; 
            margin-bottom: 25px; 
            border-radius: 12px;
            font-weight: 500;
        }
        
        .alert-success { 
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724; 
            border: 2px solid #b8dabd;
        }
        
        .alert-error { 
            background: linear-gradient(135deg, #f8d7da 0%, #f1aeb5 100%);
            color: #721c24; 
            border: 2px solid #f5c6cb;
        }
        
        /* Table */
        table { 
            width: 100%; 
            border-collapse: collapse;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        th, td { 
            padding: 18px 20px; 
            text-align: left; 
        }
        
        th { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 12px;
        }
        
        td {
            border-bottom: 1px solid #ecf0f1;
            color: #2c3e50;
        }
        
        tr:hover td {
            background: #f8f9fa;
        }
        
        .actions { 
            white-space: nowrap; 
        }
        
        .actions a { 
            margin-right: 15px; 
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        
        .actions a:first-child {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .actions a:nth-child(2) {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
        }
        
        .actions a:last-child {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
        }
        
        .actions a:hover {
            transform: translateY(-2px);
        }
        
        /* Login Form */
        .login-form { 
            max-width: 450px; 
            margin: 100px auto;
        }
        
        /* Checkbox */
        input[type="checkbox"] {
            width: auto;
            margin-right: 10px;
            transform: scale(1.2);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .header-content { 
                flex-direction: column; 
                gap: 15px; 
            }
            
            .container { 
                padding: 10px; 
            }
            
            .card { 
                padding: 25px 20px; 
            }
            
            table {
                font-size: 14px;
            }
            
            th, td {
                padding: 12px 8px;
            }
            
            .actions a {
                display: block;
                margin: 5px 0;
                text-align: center;
            }
        }
        
        /* Dashboard Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Enhanced Form Styles */
        .form-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }
        
        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #ecf0f1;
        }
        
        .button-group {
            display: flex;
            gap: 15px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
            color: white;
            padding: 15px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        /* Toggle Switch */
        .publish-toggle {
            display: flex;
            align-items: center;
            gap: 15px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .toggle-slider {
            position: relative;
            width: 60px;
            height: 30px;
            background: #ccc;
            border-radius: 15px;
            transition: 0.3s;
        }
        
        .toggle-slider:before {
            content: '';
            position: absolute;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: white;
            top: 2px;
            left: 2px;
            transition: 0.3s;
        }
        
        input[type="checkbox"]:checked + .toggle-slider {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        input[type="checkbox"]:checked + .toggle-slider:before {
            transform: translateX(30px);
        }
        
        /* Editor Toolbar */
        .editor-toolbar {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 12px;
            flex-wrap: wrap;
        }
        
        .toolbar-btn {
            padding: 8px 16px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .toolbar-btn:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .preview-btn {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
            border: none;
        }
        
        /* Table Enhancements */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .filter-buttons {
            display: flex;
            gap: 10px;
        }
        
        .filter-btn {
            padding: 8px 20px;
            background: white;
            border: 2px solid #ecf0f1;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .filter-btn.active,
        .filter-btn:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        .title-cell {
            max-width: 300px;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-badge.published {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
        }
        
        .status-badge.draft {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
        }
        
        .action-btn {
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-decoration: none;
            margin-right: 8px;
            transition: all 0.3s ease;
        }
        
        .action-btn.edit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .action-btn.view {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
        }
        
        .action-btn.publish {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        
        .action-btn.unpublish {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
            color: white;
        }
        
        .action-btn.delete {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 20px;
            width: 90%;
            max-width: 800px;
            max-height: 80vh;
            overflow: hidden;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .modal-header h3 {
            margin: 0;
            font-weight: 700;
        }
        
        .close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            transition: background 0.3s ease;
        }
        
        .close-btn:hover {
            background: rgba(255,255,255,0.2);
        }
        
        .modal-body {
            padding: 30px;
            overflow-y: auto;
            max-height: 60vh;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }
        
        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 10px;
            font-weight: 300;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <a href="/" class="logo"><?= h($config['site']['title']) ?> Admin</a>
            <nav>
                <a href="/">Zur Website</a>
                <?php if (isAdminFallback()): ?>
                    <a href="?logout=1">Logout</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <div class="container">
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= h($success) ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?= h($error) ?></div>
        <?php endif; ?>

        <?php if (isset($debug_info) && $debug_info): ?>
            <div class="alert alert-error"><?= $debug_info ?></div>
        <?php endif; ?>

        <?php if (!isAdminFallback()): ?>
            <div class="card login-form">
                <h2>Admin Login</h2>
                
                <?php if (isset($session_disabled)): ?>
                <div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin-bottom: 20px; color: #856404; border: 1px solid #ffeaa7;">
                    <strong>⚠️ Session-Problem erkannt!</strong><br>
                    PHP Sessions sind auf diesem Server deaktiviert oder funktionieren nicht. 
                    Das System verwendet jetzt Cookies als Fallback-Lösung.
                </div>
                <?php endif; ?>
                
                <div style="background: #e8f4fd; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
                    <strong>Login-Daten:</strong><br>
                    Passwort: <code>admin123</code>
                </div>
                
                <form method="post">
                    <div class="form-group">
                        <label for="password">Passwort:</label>
                        <input type="password" id="password" name="password" required placeholder="Geben Sie das Admin-Passwort ein">
                    </div>
                    <button type="submit" name="login">Anmelden</button>
                </form>
                
                <div style="margin-top: 20px; font-size: 12px; color: #666;">
                    <a href="?debug=1">Debug-Informationen anzeigen</a> | 
                    <a href="cookie_login_test.php">Cookie-Test</a>
                </div>
            </div>
        <?php else: ?>
            <!-- Quick Stats -->
            <div class="card">
                <h2>Dashboard</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?= count($articles) ?></div>
                        <div class="stat-label">Artikel gesamt</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?= count(array_filter($articles, fn($a) => $a['published_at'])) ?></div>
                        <div class="stat-label">Veröffentlicht</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?= count(array_filter($articles, fn($a) => !$a['published_at'])) ?></div>
                        <div class="stat-label">Entwürfe</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?= count(array_filter($articles, fn($a) => $a['created_at'] > date('Y-m-d', strtotime('-7 days')))) ?></div>
                        <div class="stat-label">Diese Woche</div>
                    </div>
                </div>
            </div>

            <!-- Artikel Editor -->
            <div class="card">
                <h2><?= $editArticle ? 'Artikel bearbeiten' : 'Neuer Artikel' ?></h2>
                
                <!-- Editor Toolbar -->
                <div class="editor-toolbar">
                    <button type="button" onclick="insertText('**', '**')" class="toolbar-btn">Fett</button>
                    <button type="button" onclick="insertText('*', '*')" class="toolbar-btn">Kursiv</button>
                    <button type="button" onclick="insertText('\n\n### ', '')" class="toolbar-btn">Überschrift</button>
                    <button type="button" onclick="insertText('\n- ', '')" class="toolbar-btn">Liste</button>
                    <button type="button" onclick="insertText('\n\n---\n\n', '')" class="toolbar-btn">Trenner</button>
                    <button type="button" onclick="previewArticle()" class="toolbar-btn preview-btn">Vorschau</button>
                </div>

                <form method="post" id="articleForm">
                    <?php if ($editArticle): ?>
                        <input type="hidden" name="id" value="<?= $editArticle['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="title">Titel *</label>
                            <input type="text" id="title" name="title" value="<?= h($editArticle['title'] ?? '') ?>" required placeholder="Geben Sie den Artikel-Titel ein...">
                        </div>
                        <div class="form-group">
                            <label for="author">Autor</label>
                            <input type="text" id="author" name="author" value="<?= h($editArticle['author'] ?? 'Redaktion') ?>" placeholder="Name des Autors">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="summary">Zusammenfassung</label>
                        <textarea id="summary" name="summary" rows="3" placeholder="Kurze Zusammenfassung des Artikels für die Startseite..."><?= h($editArticle['summary'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="content">Inhalt *</label>
                        <textarea id="content" name="content" required placeholder="Schreiben Sie hier den vollständigen Artikel-Inhalt..."><?= h($editArticle['content'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <label class="publish-toggle">
                            <input type="checkbox" name="published" value="1" <?= (!$editArticle || $editArticle['published_at']) ? 'checked' : '' ?>>
                            <span class="toggle-slider"></span>
                            Sofort veröffentlichen
                        </label>
                        
                        <div class="button-group">
                            <button type="submit" name="save_article" class="btn-primary">
                                <?= $editArticle ? 'Artikel aktualisieren' : 'Artikel erstellen' ?>
                            </button>
                            <?php if ($editArticle): ?>
                                <a href="/admin.php" class="btn-secondary">Abbrechen</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Preview Modal -->
            <div id="previewModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Artikel-Vorschau</h3>
                        <button type="button" onclick="closePreview()" class="close-btn">&times;</button>
                    </div>
                    <div class="modal-body" id="previewContent">
                        <!-- Preview content will be inserted here -->
                    </div>
                </div>
            </div>

            <!-- Artikel Liste -->
            <div class="card">
                <div class="section-header">
                    <h2>Alle Artikel verwalten</h2>
                    <div class="filter-buttons">
                        <button onclick="filterArticles('all')" class="filter-btn active" data-filter="all">Alle</button>
                        <button onclick="filterArticles('published')" class="filter-btn" data-filter="published">Veröffentlicht</button>
                        <button onclick="filterArticles('draft')" class="filter-btn" data-filter="draft">Entwürfe</button>
                    </div>
                </div>
                
                <?php if ($articles): ?>
                    <div class="table-container">
                        <table id="articlesTable">
                            <thead>
                                <tr>
                                    <th>Titel</th>
                                    <th>Autor</th>
                                    <th>Status</th>
                                    <th>Erstellt</th>
                                    <th>Aktualisiert</th>
                                    <th>Aktionen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($articles as $article): ?>
                                <tr data-status="<?= $article['published_at'] ? 'published' : 'draft' ?>">
                                    <td class="title-cell">
                                        <strong><?= h($article['title']) ?></strong>
                                    </td>
                                    <td><?= h($article['author'] ?: 'Redaktion') ?></td>
                                    <td>
                                        <span class="status-badge <?= $article['published_at'] ? 'published' : 'draft' ?>">
                                            <?= $article['published_at'] ? 'Veröffentlicht' : 'Entwurf' ?>
                                        </span>
                                    </td>
                                    <td><?= formatDate($article['created_at']) ?></td>
                                    <td><?= $article['updated_at'] ? formatDate($article['updated_at']) : '-' ?></td>
                                    <td class="actions">
                                        <a href="?edit=<?= $article['id'] ?>" class="action-btn edit">Bearbeiten</a>
                                        <a href="/article.php?id=<?= $article['id'] ?>" target="_blank" class="action-btn view">Ansehen</a>
                                        <a href="?toggle_publish=<?= $article['id'] ?>" class="action-btn <?= $article['published_at'] ? 'unpublish' : 'publish' ?>">
                                            <?= $article['published_at'] ? 'Zurückziehen' : 'Veröffentlichen' ?>
                                        </a>
                                        <a href="?delete=<?= $article['id'] ?>" onclick="return confirm('Artikel wirklich löschen?')" class="action-btn delete">Löschen</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <h3>Noch keine Artikel vorhanden</h3>
                        <p>Erstellen Sie Ihren ersten Artikel mit dem Editor oben.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Text Editor Functions
        function insertText(before, after) {
            const textarea = document.getElementById('content');
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selected = textarea.value.substring(start, end);
            const replacement = before + selected + after;
            
            textarea.value = textarea.value.substring(0, start) + replacement + textarea.value.substring(end);
            textarea.focus();
            textarea.setSelectionRange(start + before.length, start + before.length + selected.length);
        }
        
        // Article Preview
        function previewArticle() {
            const title = document.getElementById('title').value;
            const summary = document.getElementById('summary').value;
            const content = document.getElementById('content').value;
            const author = document.getElementById('author').value;
            
            if (!title || !content) {
                alert('Bitte füllen Sie mindestens Titel und Inhalt aus.');
                return;
            }
            
            const previewContent = `
                <article style="font-family: inherit; line-height: 1.6;">
                    <header style="border-bottom: 2px solid #ecf0f1; padding-bottom: 20px; margin-bottom: 25px;">
                        <h1 style="font-size: 32px; color: #2c3e50; margin-bottom: 15px;">${escapeHtml(title)}</h1>
                        <div style="color: #7f8c8d; font-size: 14px; margin-bottom: 15px;">
                            ${new Date().toLocaleDateString('de-DE')} | Von ${escapeHtml(author || 'Redaktion')}
                        </div>
                        ${summary ? `<div style="font-size: 18px; font-weight: 500; color: #34495e; font-style: italic;">${escapeHtml(summary)}</div>` : ''}
                    </header>
                    <div style="font-size: 16px; line-height: 1.8;">
                        ${escapeHtml(content).replace(/\n/g, '<br>')}
                    </div>
                </article>
            `;
            
            document.getElementById('previewContent').innerHTML = previewContent;
            document.getElementById('previewModal').style.display = 'block';
        }
        
        function closePreview() {
            document.getElementById('previewModal').style.display = 'none';
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Article Filtering
        function filterArticles(status) {
            const rows = document.querySelectorAll('#articlesTable tbody tr');
            const buttons = document.querySelectorAll('.filter-btn');
            
            // Update button states
            buttons.forEach(btn => btn.classList.remove('active'));
            document.querySelector(`[data-filter="${status}"]`).classList.add('active');
            
            // Filter rows
            rows.forEach(row => {
                const rowStatus = row.getAttribute('data-status');
                if (status === 'all' || status === rowStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
        // Auto-save functionality
        let autoSaveTimer;
        function setupAutoSave() {
            const form = document.getElementById('articleForm');
            if (!form) return;
            
            const inputs = form.querySelectorAll('input, textarea');
            inputs.forEach(input => {
                input.addEventListener('input', () => {
                    clearTimeout(autoSaveTimer);
                    autoSaveTimer = setTimeout(() => {
                        saveToLocalStorage();
                    }, 2000);
                });
            });
            
            // Load from localStorage on page load
            loadFromLocalStorage();
        }
        
        function saveToLocalStorage() {
            const formData = {
                title: document.getElementById('title').value,
                summary: document.getElementById('summary').value,
                content: document.getElementById('content').value,
                author: document.getElementById('author').value,
                timestamp: Date.now()
            };
            localStorage.setItem('articleDraft', JSON.stringify(formData));
            
            // Show save indicator
            showSaveIndicator('Entwurf gespeichert');
        }
        
        function loadFromLocalStorage() {
            const saved = localStorage.getItem('articleDraft');
            if (!saved) return;
            
            try {
                const data = JSON.parse(saved);
                // Only load if it's recent (within 24 hours)
                if (Date.now() - data.timestamp < 24 * 60 * 60 * 1000) {
                    if (confirm('Ein gespeicherter Entwurf wurde gefunden. Möchten Sie ihn laden?')) {
                        document.getElementById('title').value = data.title || '';
                        document.getElementById('summary').value = data.summary || '';
                        document.getElementById('content').value = data.content || '';
                        document.getElementById('author').value = data.author || '';
                    }
                }
            } catch (e) {
                console.error('Error loading draft:', e);
            }
        }
        
        function showSaveIndicator(message) {
            // Create or update save indicator
            let indicator = document.getElementById('saveIndicator');
            if (!indicator) {
                indicator = document.createElement('div');
                indicator.id = 'saveIndicator';
                indicator.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: rgba(102, 126, 234, 0.9);
                    color: white;
                    padding: 10px 20px;
                    border-radius: 25px;
                    font-size: 14px;
                    z-index: 1000;
                    transition: opacity 0.3s ease;
                `;
                document.body.appendChild(indicator);
            }
            
            indicator.textContent = message;
            indicator.style.opacity = '1';
            
            setTimeout(() => {
                indicator.style.opacity = '0';
            }, 2000);
        }
        
        // Character and word count
        function setupWordCount() {
            const contentTextarea = document.getElementById('content');
            if (!contentTextarea) return;
            
            const counter = document.createElement('div');
            counter.style.cssText = `
                text-align: right;
                font-size: 12px;
                color: #7f8c8d;
                margin-top: 5px;
            `;
            contentTextarea.parentNode.appendChild(counter);
            
            function updateCount() {
                const text = contentTextarea.value;
                const chars = text.length;
                const words = text.trim() ? text.trim().split(/\s+/).length : 0;
                counter.textContent = `${words} Wörter, ${chars} Zeichen`;
            }
            
            contentTextarea.addEventListener('input', updateCount);
            updateCount();
        }
        
        // Initialize everything when page loads
        document.addEventListener('DOMContentLoaded', function() {
            setupAutoSave();
            setupWordCount();
            
            // Close modal when clicking outside
            window.addEventListener('click', function(event) {
                const modal = document.getElementById('previewModal');
                if (event.target === modal) {
                    closePreview();
                }
            });
        });
    </script>
</body>
</html>
