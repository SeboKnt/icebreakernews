<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
$pdo = require __DIR__ . '/db.php';
$config = require __DIR__ . '/config.php';
$title = htmlspecialchars($config['site']['title'], ENT_QUOTES, 'UTF-8');

$stmt = $pdo->prepare('SELECT id, title, summary, published_at FROM articles ORDER BY published_at DESC LIMIT 20');
$stmt->execute();
$articles = $stmt->fetchAll();
?>
<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<title><?= $title ?></title>
<link rel="stylesheet" href="/styles.css">
</head>
<body>
<header><h1><?= $title ?></h1></header>
<main>
<?php if ($articles): ?>
    <ul class="articles">
    <?php foreach ($articles as $a): ?>
        <li>
            <article>
                <h2><a href="/article.php?id=<?= (int)$a['id'] ?>"><?= htmlspecialchars($a['title']) ?></a></h2>
                <time><?= htmlspecialchars($a['published_at']) ?></time>
                <p><?= htmlspecialchars($a['summary']) ?></p>
            </article>
        </li>
    <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>Keine Artikel gefunden.</p>
<?php endif; ?>
</main>
</body>
</html>