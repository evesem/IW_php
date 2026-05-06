<?php
/**
 * Главная страница — общедоступный компонент
 * Динамически отображает последние идеи из базы данных
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';

$db = Database::getInstance();

// Получение последних 6 публичных идей
$stmt = $db->query(
    "SELECT i.*, u.username 
     FROM ideas i 
     JOIN users u ON i.user_id = u.id 
     WHERE i.is_public = 1 AND i.status = 'published'
     ORDER BY i.created_at DESC 
     LIMIT 6"
);
$recentIdeas = $stmt->fetchAll();

// Получение популярных категорий (статистка)
$stmt = $db->query(
    "SELECT category, COUNT(*) as count 
     FROM ideas 
     WHERE is_public = 1 
     GROUP BY category 
     ORDER BY count DESC 
     LIMIT 5"
);
$popularCategories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог идей — главная</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/index.php">💡 Каталог идей</a>
            <div class="navbar-nav ms-auto">
                <?php if (isLoggedIn()): ?>
                    <span class="nav-link text-light">Привет, <?= htmlspecialchars($_SESSION['username']) ?> (<?= $_SESSION['user_role'] ?>)</span>
                    <a class="nav-link" href="/public/create_idea.php">➕ Создать идею</a>
                    <a class="nav-link" href="/public/search.php">🔍 Поиск</a>
                    <?php if (isAdmin()): ?>
                        <a class="nav-link btn btn-danger btn-sm text-white" href="/public/admin.php">⚙️ Админ-панель</a>
                    <?php endif; ?>
                    <a class="nav-link" href="/public/logout.php">🚪 Выход</a>
                <?php else: ?>
                    <a class="nav-link" href="/public/login.php">Вход</a>
                    <a class="nav-link" href="/public/register.php">Регистрация</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <div class="row">
            <div class="col-md-8">
                <h1>✨ Последние идеи</h1>
                <div class="row">
                    <?php if (count($recentIdeas) > 0): ?>
                        <?php foreach ($recentIdeas as $idea): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($idea['title']) ?></h5>
                                        <p class="card-text"><?= htmlspecialchars(mb_substr($idea['description'], 0, 100)) ?>...</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-primary"><?= $idea['category'] ?></span>
                                            <span class="badge bg-warning">⭐ Приоритет <?= $idea['priority'] ?></span>
                                        </div>
                                        <small class="text-muted">Автор: <?= htmlspecialchars($idea['username']) ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Пока нет идей. <a href="/public/create_idea.php">Станьте первым!</a></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">📊 Популярные категории</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($popularCategories) > 0): ?>
                            <ul class="list-group">
                                <?php foreach ($popularCategories as $cat): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?= htmlspecialchars($cat['category']) ?>
                                        <span class="badge bg-secondary rounded-pill"><?= $cat['count'] ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>Нет данных</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>