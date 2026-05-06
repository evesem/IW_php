<?php
/**
 * Форма поиска идей по критериям
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

$results = [];
$searchParams = ['keyword' => '', 'category' => '', 'min_priority' => '', 'status' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keyword = trim($_POST['keyword'] ?? '');
    $category = $_POST['category'] ?? '';
    $min_priority = (int)($_POST['min_priority'] ?? 0);
    $status = $_POST['status'] ?? '';
    
    $searchParams = compact('keyword', 'category', 'min_priority', 'status');
    
    $sql = "SELECT i.*, u.username 
            FROM ideas i 
            JOIN users u ON i.user_id = u.id 
            WHERE 1=1";
    $params = [];
    
    if (!empty($keyword)) {
        $sql .= " AND (i.title LIKE ? OR i.description LIKE ?)";
        $params[] = "%$keyword%";
        $params[] = "%$keyword%";
    }
    
    if (!empty($category) && $category !== 'all') {
        $sql .= " AND i.category = ?";
        $params[] = $category;
    }
    
    if ($min_priority > 0) {
        $sql .= " AND i.priority >= ?";
        $params[] = $min_priority;
    }
    
    if (!empty($status) && $status !== 'all') {
        $sql .= " AND i.status = ?";
        $params[] = $status;
    }
    
    // Если не админ — показываем только публичные
    if (!isAdmin()) {
        $sql .= " AND i.is_public = 1";
    }
    
    $sql .= " ORDER BY i.created_at DESC";
    
    $db = Database::getInstance();
    $stmt = $db->query($sql, $params);
    $results = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Поиск идей</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-4">
        <h1>🔍 Поиск идей</h1>
        <a href="/index.php" class="btn btn-secondary mb-3">← На главную</a>
        
        <div class="card mb-4">
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label>Ключевое слово</label>
                            <input type="text" name="keyword" class="form-control" 
                                   value="<?= htmlspecialchars($searchParams['keyword']) ?>"
                                   placeholder="Поиск по названию/описанию">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label>Категория</label>
                            <select name="category" class="form-select">
                                <option value="all">Все</option>
                                <option value="technology" <?= $searchParams['category'] == 'technology' ? 'selected' : '' ?>>Технологии</option>
                                <option value="art" <?= $searchParams['category'] == 'art' ? 'selected' : '' ?>>Искусство</option>
                                <option value="business" <?= $searchParams['category'] == 'business' ? 'selected' : '' ?>>Бизнес</option>
                                <option value="health" <?= $searchParams['category'] == 'health' ? 'selected' : '' ?>>Здоровье</option>
                                <option value="education" <?= $searchParams['category'] == 'education' ? 'selected' : '' ?>>Образование</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label>Мин. приоритет</label>
                            <select name="min_priority" class="form-select">
                                <option value="0">Любой</option>
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <option value="<?= $i ?>" <?= $searchParams['min_priority'] == $i ? 'selected' : '' ?>><?= $i ?>+</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <?php if (isAdmin()): ?>
                        <div class="col-md-2 mb-2">
                            <label>Статус</label>
                            <select name="status" class="form-select">
                                <option value="all">Все</option>
                                <option value="published" <?= $searchParams['status'] == 'published' ? 'selected' : '' ?>>Опубликовано</option>
                                <option value="draft" <?= $searchParams['status'] == 'draft' ? 'selected' : '' ?>>Черновик</option>
                                <option value="archived" <?= $searchParams['status'] == 'archived' ? 'selected' : '' ?>>Архив</option>
                            </select>
                        </div>
                        <?php endif; ?>
                        <div class="col-md-2 mb-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Искать</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <h3>Результаты (<?= count($results) ?> найденo)</h3>
            <div class="row">
                <?php foreach ($results as $idea): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5><?= htmlspecialchars($idea['title']) ?></h5>
                                <p><?= htmlspecialchars(mb_substr($idea['description'], 0, 80)) ?>...</p>
                                <span class="badge bg-primary"><?= $idea['category'] ?></span>
                                <span class="badge bg-warning">Приор: <?= $idea['priority'] ?></span>
                                <small class="d-block text-muted">Автор: <?= htmlspecialchars($idea['username']) ?></small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (count($results) === 0): ?>
                    <p class="text-muted">Ничего не найдено</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>