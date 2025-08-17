<?php
// Datenbank-Update für Bild-Support und Kommentare
require_once 'db.php';

try {
    $pdo = require 'db.php';
    
    echo "Starte Datenbank-Update...\n";
    
    // Image-Spalte zu articles hinzufügen
    try {
        $pdo->exec("ALTER TABLE articles ADD COLUMN image_url VARCHAR(500)");
        echo "✓ Spalte image_url zu articles hinzugefügt\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'already exists') !== false || strpos($e->getMessage(), 'duplicate column') !== false) {
            echo "- Spalte image_url existiert bereits\n";
        } else {
            echo "! Fehler beim Hinzufügen der image_url Spalte: " . $e->getMessage() . "\n";
        }
    }
    
    // Kommentar-Tabelle erstellen
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS comments (
                id SERIAL PRIMARY KEY,
                article_id INTEGER NOT NULL REFERENCES articles(id) ON DELETE CASCADE,
                author_name VARCHAR(100) NOT NULL,
                author_email VARCHAR(255),
                content TEXT NOT NULL,
                is_approved BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMPTZ DEFAULT now(),
                updated_at TIMESTAMPTZ DEFAULT now()
            )
        ");
        echo "✓ Kommentar-Tabelle erstellt\n";
        
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_comments_article_id ON comments(article_id)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_comments_approved ON comments(is_approved)");
        echo "✓ Kommentar-Indizes erstellt\n";
        
    } catch (Exception $e) {
        echo "! Fehler beim Erstellen der Kommentar-Tabelle: " . $e->getMessage() . "\n";
    }
    
    echo "\nDatenbank-Update abgeschlossen!\n";
    
} catch (Exception $e) {
    echo "Fehler beim Datenbank-Update: " . $e->getMessage() . "\n";
}
?>
