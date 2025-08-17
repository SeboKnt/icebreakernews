<?php
// Helper Functions für IcebreakerNews

function h($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

function formatDate($datetime) {
    if (!$datetime) return '';
    $date = new DateTime($datetime);
    return $date->format('d.m.Y, H:i') . ' Uhr';
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function isAdmin() {
    // Sessions sind jetzt über .htaccess konfiguriert
    return isset($_SESSION['admin']) && $_SESSION['admin'] === true;
}

function ensureSessionDirectory() {
    $session_path = __DIR__ . '/sessions';
    
    // Session-Verzeichnis erstellen falls nicht vorhanden
    if (!is_dir($session_path)) {
        @mkdir($session_path, 0755, true);
    }
    
    // .htaccess für Session-Schutz erstellen
    $htaccess_file = $session_path . '/.htaccess';
    if (!file_exists($htaccess_file)) {
        file_put_contents($htaccess_file, "Order deny,allow\nDeny from all");
    }
    
    // Session-Path programmatisch setzen (überschreibt .htaccess)
    if (is_writable($session_path)) {
        session_save_path($session_path);
        return true;
    }
    
    return false;
}

function debugSession() {
    return [
        'session_id' => session_id(),
        'session_status' => session_status(),
        'session_save_path' => session_save_path(),
        'session_writable' => is_writable(session_save_path()),
        'admin_session' => isset($_SESSION['admin']) ? $_SESSION['admin'] : 'NOT SET',
        'session_data' => $_SESSION
    ];
}

function requireAdmin() {
    if (!isAdmin()) {
        redirect('admin.php?login=1');
    }
}
