<?php
/**
 * Административная панель
 * Доступна только пользователям с ролью admin
 * Функции: CRUD пользователей, CRUD идей, создание админов, управление БД
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

requireAdmin();

$db = Database::getInstance();
$message = '';
$error = '';

// Обработка действий
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'create_admin':
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            
            if (strlen($username) >= 3 && filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($password) >= 6) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                try {
                    $db->query(
                        "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')",
                        [$username, $email, $hashedPassword]
                    );
                    $message = "Администратор $username создан";
                } catch (PDOException $e) {
                    $error = "Ошибка: пользователь уже существует";
                }
            } else {
                $error = "Заполните все поля корректно";
            }
            break;
            
        case 'edit_user':
            $user_id = (int)($_POST['user_id'] ?? 0);
            $role = $_POST['role'] ?? 'user';
            if ($user_id > 0 && in_array($role, ['user', 'admin'])) {
                $db->query("UPDATE users SET role = ? WHERE id = ?", [$role, $user_id]);
                $message = "Роль пользователя обновлена";
            }
            break;
            
        case 'delete_user':
            $user_id = (int)($_POST['user_id'] ?? 0);
            if ($user_id > 0 && $user_id != $_SESSION['user_id']) {
                $db->query("DELETE FROM users WHERE id = ?", [$user_id]);
                $message = "Пользователь удален";
            } else {
                $error = "Нельзя удалить самого себя";
            }
            break;
            
        case 'edit_idea':
            $idea_id = (int)($_POST['idea_id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $status = $_POST['status'] ?? 'published';
            $priority = (int)($_POST['priority'] ?? 1);
            if ($idea_id > 0) {
                $db->query(
                    "UPDATE ideas SET title = ?, status = ?, priority = ? WHERE id = ?",
                    [$title, $status, $priority, $idea_id]
                );
                $message = "Идея обновлена";
            }
            break;

        case 'delete_idea':
            $idea_id = (int)($_POST['idea_id'] ?? 0);
            if ($idea_id > 0) {
                $db->query("DELETE FROM ideas WHERE id = ?", [$idea_id]);
                $message = "Идея удалена";
            }
            break;

        case 'add_idea':
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $category = $_POST['category'] ?? '';
            $priority = (int)($_POST['priority'] ?? 1);
            $status = $_POST['status'] ?? 'published';
            $is_public = isset($_POST['is_public']) ? 1 : 0;
            
            if (strlen($title) >= 3 && strlen($description) >= 10) {
                $db->query(
                    "INSERT INTO ideas (title, description, category, priority, status, is_public, user_id) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [$title, $description, $category, $priority, $status, $is_public, $_SESSION['user_id']]
                );
                $message = "Идея добавлена";
            } else {
                $error = "Заполните все поля корректно";
            }
            break;
    }
}

// ===== ВСЕГДА ПОЛУЧАЕМ ДАННЫЕ ДЛЯ ОТОБРАЖЕНИЯ =====
$users = $db->query("SELECT id, username, email, role FROM users ORDER BY id")->fetchAll();
$ideas = $db->query(
    "SELECT i.*, u.username 
     FROM ideas i 
     JOIN users u ON i.user_id = u.id 
     ORDER BY i.created_at DESC"
)->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-4">
        <h1>⚙️ Административная панель</h1>
        <a href="/index.php" class="btn btn-secondary mb-3">← На главную</a>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- БЛОК 1: Создание администратора -->
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Создание администратора</h5>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-2">
                    <input type="hidden" name="action" value="create_admin">
                    <div class="col-md-4">
                        <input type="text" name="username" class="form-control" placeholder="Имя" required>
                    </div>
                    <div class="col-md-4">
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                    </div>
                    <div class="col-md-3">
                        <input type="password" name="password" class="form-control" placeholder="Пароль" required>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-success w-100">+</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- БЛОК 2: Управление пользователями (просмотр, редактирование, удаление) -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Управление пользователями</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr><th>ID</th><th>Username</th><th>Email</th><th>Роль</th><th>Действия</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="edit_user">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <select name="role" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                                            <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>user</option>
                                            <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>admin</option>
                                        </select>
                                    </form>
                                 </td>
                                 <td>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Удалить пользователя?')">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">🗑 Удалить</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted">Вы</span>
                                    <?php endif; ?>
                                 </td>
                            </table>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- БЛОК 3: Добавление идеи -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Добавление идеи</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add_idea">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <input type="text" name="title" class="form-control" placeholder="Название" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <textarea name="description" class="form-control" placeholder="Описание" rows="2" required></textarea>
                        </div>
                        <div class="col-md-3 mb-2">
                            <select name="category" class="form-select" required>
                                <option value="">Категория</option>
                                <option value="technology">Технологии</option>
                                <option value="art">Искусство</option>
                                <option value="business">Бизнес</option>
                                <option value="health">Здоровье</option>
                                <option value="education">Образование</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <select name="priority" class="form-select">
                                <?php for($i=1;$i<=5;$i++): ?>
                                    <option value="<?= $i ?>">Приор <?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <select name="status" class="form-select">
                                <option value="draft">Черновик</option>
                                <option value="published" selected>Опубликовать</option>
                                <option value="archived">Архив</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2 form-check pt-2">
                            <input type="checkbox" name="is_public" class="form-check-input" id="is_public" checked>
                            <label class="form-check-label" for="is_public">Публичная</label>
                        </div>
                        <div class="col-md-1 mb-2">
                            <button type="submit" class="btn btn-success w-100">+</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- БЛОК 4: Управление идеями (просмотр, редактирование, удаление) -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Управление идеями</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr><th>ID</th><th>Название</th><th>Автор</th><th>Приор</th><th>Статус</th><th>Действия</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ideas as $idea): ?>
                            <tr>
                                <td><?= $idea['id'] ?></td>
                                <td><?= htmlspecialchars($idea['title']) ?></td>
                                <td><?= htmlspecialchars($idea['username']) ?></td>
                                <td><?= $idea['priority'] ?></td>
                                <td><?= $idea['status'] ?></td>
                                <td>
                                    <!-- Редактирование -->
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="edit_idea">
                                        <input type="hidden" name="idea_id" value="<?= $idea['id'] ?>">
                                        <input type="text" name="title" value="<?= htmlspecialchars($idea['title']) ?>" class="form-control form-control-sm d-inline w-auto" required>
                                        <select name="status" class="form-select form-select-sm d-inline w-auto">
                                            <option value="draft" <?= $idea['status'] == 'draft' ? 'selected' : '' ?>>draft</option>
                                            <option value="published" <?= $idea['status'] == 'published' ? 'selected' : '' ?>>pub</option>
                                            <option value="archived" <?= $idea['status'] == 'archived' ? 'selected' : '' ?>>arch</option>
                                        </select>
                                        <select name="priority" class="form-select form-select-sm d-inline w-auto">
                                            <?php for($i=1;$i<=5;$i++): ?>
                                                <option value="<?= $i ?>" <?= $idea['priority'] == $i ? 'selected' : '' ?>><?= $i ?></option>
                                            <?php endfor; ?>
                                        </select>
                                        <button type="submit" class="btn btn-warning btn-sm">✏</button>
                                    </form>
                                    
                                    <!-- Удаление -->
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Удалить идею?')">
                                        <input type="hidden" name="action" value="delete_idea">
                                        <input type="hidden" name="idea_id" value="<?= $idea['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                                    </form>
                                 </td>
                            </td>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>