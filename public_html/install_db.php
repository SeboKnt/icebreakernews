<?php
// Database Setup für IcebreakerNews
require_once 'functions.php';

try {
    $pdo = require 'db.php';
    echo "<h1>Datenbank Setup</h1>\n";
    
    // Tabellen erstellen
    $schema = file_get_contents(__DIR__ . '/db/schema.sql');
    $pdo->exec($schema);
    echo "<p>✓ Tabellen erstellt</p>\n";
    
    // Test-Artikel erstellen
    $stmt = $pdo->prepare('INSERT INTO articles (title, summary, content, author, published_at) VALUES (?, ?, ?, ?, NOW())');
    
    $articles = [
        [
            'Breaking News: IcebreakerNews ist online',
            'Unsere neue Nachrichtenplattform bietet aktuelle Informationen und Berichte aus aller Welt.',
            "IcebreakerNews ist eine moderne Nachrichtenplattform, die es sich zur Aufgabe gemacht hat, aktuelle und relevante Informationen schnell und zuverlässig zu verbreiten.\n\nUnser Team aus erfahrenen Journalisten arbeitet rund um die Uhr daran, Ihnen die wichtigsten Nachrichten aus Politik, Wirtschaft, Sport und Kultur zu präsentieren.\n\nDabei legen wir besonderen Wert auf Objektivität und Faktentreue. Jeder Artikel wird sorgfältig recherchiert und geprüft, bevor er veröffentlicht wird.",
            'Chefredaktion'
        ],
        [
            'Neue Features für bessere Nutzererfahrung',
            'Die Plattform wurde mit modernem Design und optimierter Performance entwickelt.',
            "Unsere Website wurde von Grund auf neu entwickelt, um Ihnen das bestmögliche Leseerlebnis zu bieten.\n\nDas responsive Design passt sich automatisch an Ihr Gerät an - egal ob Desktop, Tablet oder Smartphone.\n\nDie intuitive Navigation ermöglicht es Ihnen, schnell die gewünschten Informationen zu finden.",
            'Tech-Team'
        ]
    ];
    
    foreach ($articles as $article) {
        $stmt->execute($article);
    }
    echo "<p>✓ Test-Artikel erstellt</p>\n";
    
    echo "<p><strong>Setup erfolgreich!</strong></p>\n";
    echo "<p><a href='/'>Zur Startseite</a> | <a href='/admin.php'>Admin-Bereich</a></p>\n";
    echo "<p><em>Admin-Passwort: admin123</em></p>\n";
    
} catch (Exception $e) {
    echo "<h1>Fehler beim Setup</h1>\n";
    echo "<p>Fehler: " . h($e->getMessage()) . "</p>\n";
}