<?php
// Automatisches Datenbank-Setup für IcebreakerNews
// Prüft welche Tabellen existieren und führt nur nötige Setup-Schritte aus

// Nur functions.php laden wenn verfügbar
if (file_exists(__DIR__ . '/functions.php')) {
    require_once 'functions.php';
}

/**
 * Prüft ob eine Tabelle existiert
 */
function tableExists($pdo, $tableName) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_name = ?");
        $stmt->execute([$tableName]);
        return $stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Prüft ob eine Spalte in einer Tabelle existiert
 */
function columnExists($pdo, $tableName, $columnName) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_name = ? AND column_name = ?");
        $stmt->execute([$tableName, $columnName]);
        return $stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Führt das automatische Datenbank-Setup durch
 */
function setupDatabase($silent = false) {
    try {
        $pdo = require 'db.php';
        $actions = [];
        $errors = [];
        
        if (!$silent) {
            echo "<h1>Datenbank Setup</h1>\n";
        }
        
        // Prüfe welche Tabellen bereits existieren
        $tablesExist = [
            'articles' => tableExists($pdo, 'articles'),
            'users' => tableExists($pdo, 'users'),
            'comments' => tableExists($pdo, 'comments')
        ];
        
        // Wenn keine Tabellen existieren, führe vollständiges Setup aus
        if (!$tablesExist['articles'] && !$tablesExist['users'] && !$tablesExist['comments']) {
            try {
                $schema = file_get_contents(__DIR__ . '/db/schema.sql');
                $pdo->exec($schema);
                $actions[] = "✓ Alle Tabellen erstellt";
                
                // Test-Artikel nur bei komplettem Setup erstellen
                createTestArticles($pdo);
                $actions[] = "✓ Test-Artikel erstellt";
                
            } catch (Exception $e) {
                $errors[] = "Fehler beim Erstellen der Tabellen: " . $e->getMessage();
            }
        } else {
            // Einzelne fehlende Tabellen erstellen
            if (!$tablesExist['articles']) {
                try {
                    $pdo->exec("
                        CREATE TABLE articles (
                            id SERIAL PRIMARY KEY,
                            title VARCHAR(255) NOT NULL,
                            slug TEXT UNIQUE,
                            summary TEXT,
                            content TEXT,
                            author VARCHAR(100),
                            image_url VARCHAR(500),
                            published_at TIMESTAMPTZ,
                            created_at TIMESTAMPTZ DEFAULT now(),
                            updated_at TIMESTAMPTZ DEFAULT now()
                        )
                    ");
                    $actions[] = "✓ Articles-Tabelle erstellt";
                } catch (Exception $e) {
                    $errors[] = "Fehler beim Erstellen der Articles-Tabelle: " . $e->getMessage();
                }
            }
            
            if (!$tablesExist['users']) {
                try {
                    $pdo->exec("
                        CREATE TABLE users (
                            id SERIAL PRIMARY KEY,
                            username VARCHAR(100) UNIQUE NOT NULL,
                            password_hash VARCHAR(255) NOT NULL,
                            is_admin BOOLEAN DEFAULT FALSE,
                            created_at TIMESTAMPTZ DEFAULT now()
                        )
                    ");
                    $actions[] = "✓ Users-Tabelle erstellt";
                } catch (Exception $e) {
                    $errors[] = "Fehler beim Erstellen der Users-Tabelle: " . $e->getMessage();
                }
            }
            
            if (!$tablesExist['comments']) {
                try {
                    $pdo->exec("
                        CREATE TABLE comments (
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
                    
                    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_comments_article_id ON comments(article_id)");
                    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_comments_approved ON comments(is_approved)");
                    $actions[] = "✓ Comments-Tabelle erstellt";
                } catch (Exception $e) {
                    $errors[] = "Fehler beim Erstellen der Comments-Tabelle: " . $e->getMessage();
                }
            }
            
            // Prüfe und füge fehlende Spalten hinzu
            if ($tablesExist['articles'] && !columnExists($pdo, 'articles', 'image_url')) {
                try {
                    $pdo->exec("ALTER TABLE articles ADD COLUMN image_url VARCHAR(500)");
                    $actions[] = "✓ Spalte image_url zu articles hinzugefügt";
                } catch (Exception $e) {
                    $errors[] = "Fehler beim Hinzufügen der image_url Spalte: " . $e->getMessage();
                }
            }
        }
        
        // Ausgabe der Ergebnisse
        if (!$silent) {
            foreach ($actions as $action) {
                echo "<p>$action</p>\n";
            }
            
            foreach ($errors as $error) {
                echo "<p style='color: red;'>! $error</p>\n";
            }
            
            if (empty($errors)) {
                echo "<p><strong>Setup erfolgreich!</strong></p>\n";
                echo "<p><a href='/'>Zur Startseite</a> | <a href='/admin.php'>Admin-Bereich</a></p>\n";
                echo "<p><em>Admin-Passwort: admin123</em></p>\n";
            } else {
                echo "<p><strong>Setup mit Fehlern abgeschlossen.</strong></p>\n";
            }
        }
        
        return empty($errors);
        
    } catch (Exception $e) {
        if (!$silent) {
            echo "<h1>Fehler beim Setup</h1>\n";
            echo "<p>Fehler: " . htmlspecialchars($e->getMessage()) . "</p>\n";
        }
        return false;
    }
}

/**
 * Erstellt Test-Artikel
 */
function createTestArticles($pdo) {
    // Prüfe ob bereits Artikel existieren
    $stmt = $pdo->query("SELECT COUNT(*) FROM articles");
    if ($stmt->fetchColumn() > 0) {
        return; // Bereits Artikel vorhanden
    }
    
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
}

/**
 * Prüft ob das Setup erforderlich ist
 */
function isSetupRequired() {
    try {
        $pdo = require 'db.php';
        return !tableExists($pdo, 'articles');
    } catch (Exception $e) {
        return true;
    }
}

// Wenn die Datei direkt aufgerufen wird, führe Setup aus
if (basename($_SERVER['PHP_SELF']) === 'setup_db.php') {
    setupDatabase();
}
