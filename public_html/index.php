<?php
// Error handling und debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    // Check if functions.php exists
    if (!file_exists(__DIR__ . '/functions.php')) {
        die('Error: functions.php not found');
    }
    require_once __DIR__ . '/functions.php';
    
    // Check if config exists
    if (!file_exists(__DIR__ . '/config.php')) {
        die('Error: config.php not found');
    }
    $config = require __DIR__ . '/config.php';
    
    // Check if db.php exists  
    if (!file_exists(__DIR__ . '/db.php')) {
        die('Error: db.php not found');
    }
    $pdo = require __DIR__ . '/db.php';
    
    // Test database connection
    $pdo->query('SELECT 1');
    
    // Check if articles table exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_name = 'articles'");
    if ($stmt->fetchColumn() == 0) {
        die('Error: Articles table not found. Please run <a href="/setup_db.php">setup_db.php</a> first.');
    }
    
    // Load articles
    $stmt = $pdo->prepare('SELECT id, title, summary, author, image_url, published_at FROM articles WHERE published_at <= NOW() ORDER BY published_at DESC LIMIT 10');
    $stmt->execute();
    $articles = $stmt->fetchAll();
    
    // Separate top story
    $topStory = $articles[0] ?? null;
    $otherArticles = array_slice($articles, 1);
    
} catch (Exception $e) {
    die('Database Error: ' . htmlspecialchars($e->getMessage()) . '<br><br>Please check your database configuration and run <a href="/install_db.php">install_db.php</a>');
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($config['site']['title']) ?></title>
    <meta name="description" content="<?= h($config['site']['description']) ?>">
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
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 0 20px; 
        }
        
        /* Header Styles */
        header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; 
            padding: 20px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-content { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        
        .logo { 
            font-size: 32px; 
            font-weight: 800; 
            text-decoration: none; 
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            transition: transform 0.3s ease;
        }
        
        .logo:hover {
            transform: scale(1.05);
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
            backdrop-filter: blur(10px);
            background: rgba(255,255,255,0.1);
        }
        
        .nav a:hover { 
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }
        
        /* Main Content */
        main { 
            margin: 40px 0;
        }
        
        /* Top Story Card */
        .top-story { 
            background: white;
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .top-story:hover {
            transform: translateY(-5px);
        }
        
        .top-story::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        
        .top-story h1 { 
            font-size: 36px; 
            margin-bottom: 20px; 
            line-height: 1.2;
            color: #2c3e50;
            font-weight: 700;
        }
        
        .top-story h1 a {
            color: inherit;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .top-story h1 a:hover {
            color: #667eea;
        }
        
        .top-story .meta { 
            color: #7f8c8d; 
            margin-bottom: 20px; 
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .top-story .summary { 
            font-size: 20px; 
            line-height: 1.6;
            color: #34495e;
            font-weight: 400;
        }
        
        .top-story-image {
            margin-bottom: 25px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .top-story-image img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            display: block;
        }
        
        /* Articles Grid */
        .articles-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); 
            gap: 25px;
            margin-top: 40px;
        }
        
        .article-card { 
            background: white;
            border-radius: 20px;
            padding: 0;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 25px rgba(0,0,0,0.08);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.06);
        }
        
        .article-image {
            width: 100%;
            height: 200px;
            overflow: hidden;
        }
        
        .article-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }
        
        .article-card:hover .article-image img {
            transform: scale(1.05);
        }
        
        .article-content {
            padding: 25px;
        }
        
        .article-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }
        
        .article-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #667eea, #764ba2, #ff7e5f, #feb47b);
            background-size: 300% 300%;
            animation: gradientMove 6s ease infinite;
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }
        
        .article-card:hover::before {
            transform: scaleX(1);
        }
        
        @keyframes gradientMove {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        .article-card h2 { 
            font-size: 20px; 
            margin-bottom: 15px; 
            line-height: 1.4;
            color: #2c3e50;
            font-weight: 600;
        }
        
        .article-card h2 a { 
            color: inherit; 
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .article-card h2 a:hover { 
            color: #667eea;
        }
        
        .article-card .meta { 
            color: #95a5a6; 
            font-size: 12px; 
            margin-bottom: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .article-card .summary { 
            font-size: 15px; 
            color: #5a6c7d;
            line-height: 1.5;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        
        .empty-state h2 {
            color: #7f8c8d;
            font-size: 28px;
            margin-bottom: 15px;
            font-weight: 300;
        }
        
        .empty-state p {
            color: #95a5a6;
            font-size: 16px;
        }
        
        .empty-state a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        
        .empty-state a:hover {
            color: #764ba2;
        }
        
        /* Footer */
        footer { 
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white; 
            text-align: center; 
            padding: 40px 0; 
            margin-top: 80px;
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
                gap: 20px; 
            }
            
            .nav { 
                flex-wrap: wrap; 
                justify-content: center; 
            }
            
            .logo {
                font-size: 28px;
            }
            
            .top-story {
                padding: 25px;
            }
            
            .top-story h1 { 
                font-size: 28px; 
            }
            
            .articles-grid { 
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .article-card {
                padding: 20px;
            }
            
            .container {
                padding: 0 15px;
            }
        }
        
        /* Animations */
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
        
        .top-story, .article-card {
            animation: fadeInUp 0.6s ease-out;
        }
        
        .article-card:nth-child(even) {
            animation-delay: 0.1s;
        }
        
        .article-card:nth-child(odd) {
            animation-delay: 0.2s;
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
                </nav>
            </div>
        </div>
    </header>

    <main class="container">
        <?php if ($topStory): ?>
        <article class="top-story">
            <?php if ($topStory['image_url']): ?>
            <div class="top-story-image">
                <img src="<?= h($topStory['image_url']) ?>" alt="<?= h($topStory['title']) ?>">
            </div>
            <?php endif; ?>
            <h1><a href="/article.php?id=<?= $topStory['id'] ?>" style="color: inherit; text-decoration: none;"><?= h($topStory['title']) ?></a></h1>
            <div class="meta">
                <?= formatDate($topStory['published_at']) ?>
                <?php if ($topStory['author']): ?> | Von <?= h($topStory['author']) ?><?php endif; ?>
            </div>
            <div class="summary"><?= h($topStory['summary']) ?></div>
        </article>
        <?php endif; ?>

        <?php if ($otherArticles): ?>
        <div class="articles-grid">
            <?php foreach ($otherArticles as $article): ?>
            <article class="article-card">
                <?php if ($article['image_url']): ?>
                <div class="article-image">
                    <img src="<?= h($article['image_url']) ?>" alt="<?= h($article['title']) ?>">
                </div>
                <?php endif; ?>
                <div class="article-content">
                    <h2><a href="/article.php?id=<?= $article['id'] ?>"><?= h($article['title']) ?></a></h2>
                    <div class="meta">
                        <?= formatDate($article['published_at']) ?>
                        <?php if ($article['author']): ?> | <?= h($article['author']) ?><?php endif; ?>
                    </div>
                    <div class="summary"><?= h($article['summary']) ?></div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (empty($articles)): ?>
        <div class="empty-state">
            <h2>Noch keine Artikel verfügbar</h2>
            <p>Kommen Sie später wieder für aktuelle Nachrichten.</p>
        </div>
        <?php endif; ?>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> <?= h($config['site']['title']) ?> | Alle Rechte vorbehalten | <a href="/admin.php" style="color: #7f8c8d; text-decoration: none; font-size: 12px;">Admin</a></p>
        </div>
    </footer>
</body>
</html>