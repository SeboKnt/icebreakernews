<?php
require_once 'functions.php';
$pdo = require 'db.php';
$config = require 'config.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    redirect('/');
}

$stmt = $pdo->prepare('SELECT * FROM articles WHERE id = ? AND published_at <= NOW()');
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    http_response_code(404);
    redirect('/');
}

// Kommentar verarbeiten
$comment_success = false;
$comment_error = '';

if (isset($_POST['submit_comment'])) {
    $author_name = trim($_POST['author_name'] ?? '');
    $author_email = trim($_POST['author_email'] ?? '');
    $content = trim($_POST['content'] ?? '');
    
    if ($author_name && $content) {
        try {
            $stmt = $pdo->prepare('INSERT INTO comments (article_id, author_name, author_email, content, is_approved) VALUES (?, ?, ?, ?, TRUE)');
            $stmt->execute([$id, $author_name, $author_email, $content]);
            $comment_success = true;
        } catch (Exception $e) {
            $comment_error = 'Fehler beim Speichern des Kommentars.';
        }
    } else {
        $comment_error = 'Name und Kommentar sind erforderlich.';
    }
}

// Kommentare laden
$stmt = $pdo->prepare('SELECT * FROM comments WHERE article_id = ? AND is_approved = TRUE ORDER BY created_at DESC');
$stmt->execute([$id]);
$comments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($article['title']) ?> | <?= h($config['site']['title']) ?></title>
    <meta name="description" content="<?= h($article['summary']) ?>">
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
            max-width: 800px; 
            margin: 0 auto; 
            padding: 0 20px; 
        }
        
        /* Header Styles */
        header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; 
            padding: 20px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        
        .header-content { 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .logo { 
            font-size: 28px; 
            font-weight: 800; 
            text-decoration: none; 
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .nav { 
            display: flex; 
            gap: 30px; 
        }
        
        .nav a { 
            color: white; 
            text-decoration: none;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 25px;
            transition: all 0.3s ease;
            background: rgba(255,255,255,0.1);
        }
        
        .nav a:hover { 
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }
        
        /* Breadcrumb */
        .breadcrumb { 
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(10px);
            padding: 15px 0; 
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .breadcrumb a { 
            color: #667eea; 
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .breadcrumb a:hover { 
            color: #764ba2;
        }
        
        /* Article Styles */
        article { 
            background: white;
            border-radius: 20px;
            padding: 50px;
            margin: 40px 0;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        
        article::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        
        .article-header { 
            border-bottom: 2px solid #ecf0f1;
            padding-bottom: 30px; 
            margin-bottom: 40px; 
        }
        
        .article-title { 
            font-size: 42px; 
            line-height: 1.2; 
            margin-bottom: 25px;
            color: #2c3e50;
            font-weight: 700;
        }
        
        .article-meta { 
            color: #7f8c8d; 
            font-size: 14px; 
            margin-bottom: 25px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .article-summary { 
            font-size: 22px; 
            font-weight: 500; 
            line-height: 1.6; 
            color: #34495e;
            font-style: italic;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            border-left: 4px solid #667eea;
        }
        
        .article-image {
            margin: 30px 0;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        }
        
        .article-image img {
            width: 100%;
            height: auto;
            display: block;
        }
        
        .article-content { 
            font-size: 18px; 
            line-height: 1.8;
            color: #2c3e50;
        }
        
        .article-content p { 
            margin-bottom: 25px; 
        }
        
        .back-link { 
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 40px; 
            color: #667eea; 
            text-decoration: none; 
            font-weight: 600;
            padding: 12px 24px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transform: translateY(-2px);
        }
        
        /* Kommentar-System Styles */
        .comments-section {
            background: white;
            border-radius: 15px;
            padding: 40px;
            margin-top: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .comments-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        
        .comments-section h2 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 30px;
            font-weight: 700;
        }
        
        .comment-form {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 40px;
            border: 1px solid #e9ecef;
        }
        
        .comment-form h3 {
            color: #495057;
            font-size: 20px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #495057;
            font-weight: 500;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .comment-form button {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .comment-form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
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
        
        .comments-list {
            border-top: 2px solid #f1f3f4;
            padding-top: 30px;
        }
        
        .comment {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .comment:hover {
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            border-color: #dee2e6;
        }
        
        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f1f3f4;
        }
        
        .comment-author {
            color: #495057;
            font-size: 16px;
            font-weight: 600;
        }
        
        .comment-date {
            color: #6c757d;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .comment-content {
            color: #495057;
            line-height: 1.6;
            font-size: 14px;
        }
        
        .no-comments {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
            font-style: italic;
        }
        
        /* Footer */
        footer { 
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white; 
            text-align: center; 
            padding: 40px 0; 
            margin-top: 60px;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
        }
        
        footer p {
            font-size: 14px;
            opacity: 0.8;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .header-content { 
                flex-direction: column; 
                gap: 15px; 
            }
            
            .nav { 
                flex-wrap: wrap; 
                justify-content: center; 
            }
            
            .article-title { 
                font-size: 32px; 
            }
            
            article { 
                padding: 30px 20px; 
                margin: 20px 0;
            }
            
            .article-content {
                font-size: 16px;
            }
            
            .article-summary {
                font-size: 18px;
                padding: 15px;
            }
        }
        
        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        article {
            animation: fadeInUp 0.6s ease-out;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <a href="/" class="logo"><?= h($config['site']['title']) ?></a>
                <nav class="nav">
                    <a href="/">Startseite</a>
                    <a href="/admin.php">Admin</a>
                </nav>
            </div>
        </div>
    </header>

    <div class="breadcrumb">
        <div class="container">
            <a href="/">Startseite</a> &gt; Artikel
        </div>
    </div>

    <main class="container">
        <article>
            <header class="article-header">
                <h1 class="article-title"><?= h($article['title']) ?></h1>
                <div class="article-meta">
                    <?= formatDate($article['published_at']) ?>
                    <?php if ($article['author']): ?> | Von <?= h($article['author']) ?><?php endif; ?>
                </div>
                <?php if ($article['summary']): ?>
                <div class="article-summary"><?= h($article['summary']) ?></div>
                <?php endif; ?>
            </header>
            
            <?php if ($article['image_url']): ?>
            <div class="article-image">
                <img src="<?= h($article['image_url']) ?>" alt="<?= h($article['title']) ?>">
            </div>
            <?php endif; ?>
            
            <div class="article-content">
                <?= nl2br(h($article['content'])) ?>
            </div>
            
            <a href="/" class="back-link">← Zurück zur Startseite</a>
        </article>

        <!-- Kommentar-Bereich -->
        <section class="comments-section">
            <h2>Kommentare (<?= count($comments) ?>)</h2>
            
            <!-- Kommentar-Formular -->
            <div class="comment-form">
                <h3>Kommentar hinterlassen</h3>
                
                <?php if ($comment_success): ?>
                <div class="alert alert-success">✓ Ihr Kommentar wurde erfolgreich veröffentlicht!</div>
                <?php endif; ?>
                
                <?php if ($comment_error): ?>
                <div class="alert alert-error">✗ <?= h($comment_error) ?></div>
                <?php endif; ?>
                
                <form method="post">
                    <div class="form-group">
                        <label for="author_name">Name *</label>
                        <input type="text" id="author_name" name="author_name" required 
                               value="<?= h($_POST['author_name'] ?? '') ?>" placeholder="Ihr Name">
                    </div>
                    
                    <div class="form-group">
                        <label for="author_email">E-Mail (optional)</label>
                        <input type="email" id="author_email" name="author_email" 
                               value="<?= h($_POST['author_email'] ?? '') ?>" placeholder="ihre@email.de">
                    </div>
                    
                    <div class="form-group">
                        <label for="content">Kommentar *</label>
                        <textarea id="content" name="content" required rows="4" 
                                  placeholder="Schreiben Sie Ihren Kommentar..."><?= h($_POST['content'] ?? '') ?></textarea>
                    </div>
                    
                    <button type="submit" name="submit_comment">Kommentar absenden</button>
                </form>
            </div>
            
            <!-- Kommentare anzeigen -->
            <?php if ($comments): ?>
            <div class="comments-list">
                <?php foreach ($comments as $comment): ?>
                <div class="comment">
                    <div class="comment-header">
                        <strong class="comment-author"><?= h($comment['author_name']) ?></strong>
                        <span class="comment-date"><?= formatDate($comment['created_at']) ?></span>
                    </div>
                    <div class="comment-content"><?= nl2br(h($comment['content'])) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="no-comments">
                <p>Noch keine Kommentare vorhanden. Seien Sie der Erste!</p>
            </div>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> <?= h($config['site']['title']) ?> | Alle Rechte vorbehalten</p>
        </div>
    </footer>
</body>
</html>
