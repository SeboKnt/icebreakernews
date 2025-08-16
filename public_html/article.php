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
            
            <div class="article-content">
                <?= nl2br(h($article['content'])) ?>
            </div>
            
            <a href="/" class="back-link">← Zurück zur Startseite</a>
        </article>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> <?= h($config['site']['title']) ?> | Alle Rechte vorbehalten</p>
        </div>
    </footer>
</body>
</html>
