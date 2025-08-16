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
    // Sicherstellen, dass Session gestartet ist
    if (session_status() === PHP_SESSION_ACTIVE) {
        return isset($_SESSION['admin']) && $_SESSION['admin'] === true;
    } else {
        // Cookie-Fallback wenn Sessions nicht funktionieren
        return isset($_COOKIE['admin_auth']) && strpos($_COOKIE['admin_auth'], 'authenticated_') === 0;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        redirect('admin.php?login=1');
    }
}
