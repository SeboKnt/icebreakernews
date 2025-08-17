<?php
// 404 Error Page
http_response_code(404);

// Load configuration
try {
    require_once __DIR__ . '/functions.php';
    $config = require __DIR__ . '/config.php';
} catch (Exception $e) {
    $config = [
        'site' => [
            'title' => 'IcebreakerNews',
            'description' => 'Aktuelle Nachrichten und Berichte'
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Seite nicht gefunden | <?= h($config['site']['title']) ?></title>
    <meta name="description" content="Die gewünschte Seite wurde nicht gefunden.">
    <style>
        /* Reset und Base Styles */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6; 
            color: #1a1a1a; 
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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
        
        /* Error Content */
        .error-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 0;
        }
        
        .error-card {
            background: white;
            border-radius: 20px;
            padding: 60px 40px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .error-image {
            margin-bottom: 30px;
            border-radius: 15px;
            overflow: hidden;
            max-width: 300px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .error-image img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }
        
        .error-joke {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            font-style: italic;
            font-size: 16px;
            box-shadow: 0 5px 15px rgba(240, 147, 251, 0.3);
        }
        
        .error-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #e74c3c, #f39c12, #f1c40f);
        }
        
        .error-number {
            font-size: 120px;
            font-weight: 800;
            color: #e74c3c;
            margin-bottom: 20px;
            text-shadow: 3px 3px 6px rgba(231, 76, 60, 0.2);
        }
        
        .error-title {
            font-size: 32px;
            color: #2c3e50;
            margin-bottom: 20px;
            font-weight: 700;
        }
        
        .error-message {
            font-size: 18px;
            color: #7f8c8d;
            margin-bottom: 40px;
            line-height: 1.6;
        }
        
        .error-actions {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-block;
            padding: 15px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
        }
        
        .btn-secondary:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }
        
        /* Footer */
        footer { 
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white; 
            text-align: center; 
            padding: 20px 0; 
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
            
            .error-card {
                padding: 40px 25px;
                margin: 0 20px;
            }
            
            .error-number {
                font-size: 80px;
            }
            
            .error-title {
                font-size: 24px;
            }
            
            .error-actions {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 200px;
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
        
        .error-card {
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
                </nav>
            </div>
        </div>
    </header>

    <main class="error-content">
        <div class="container">
            <div class="error-card">
                <div class="error-number">404</div>
                <div class="error-image">
                    <img src="https://picsum.photos/300/200?random=404" alt="Zufälliges Bild" loading="lazy">
                </div>
                <div class="error-joke">
                    "Diese Seite ist so gut versteckt, dass sogar wir sie nicht finden können! 🕵️‍♂️"
                </div>
                <h1 class="error-title">Seite nicht gefunden</h1>
                <p class="error-message">
                    Die gewünschte Seite existiert leider nicht oder wurde verschoben. 
                    Überprüfen Sie die URL oder kehren Sie zur Startseite zurück.
                </p>
                <div class="error-actions">
                    <a href="/" class="btn btn-primary">Zur Startseite</a>
                    <a href="javascript:history.back()" class="btn btn-secondary">Zurück</a>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> <?= h($config['site']['title']) ?> | Alle Rechte vorbehalten</p>
        </div>
    </footer>
</body>
</html>
