<?php
/**
 * Страница входа в систему
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Если уже залогинен — перенаправляем на главную
if (isLoggedIn()) {
    header('Location: /index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($login) || empty($password)) {
        $error = 'Заполните все поля';
    } else {
        $result = loginUser($login, $password);
        if ($result['success']) {
            // Перенаправляем на главную
            header('Location: /index.php');
            exit();
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - Каталог идей</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-light">
    <div class="container mt-5" style="max-width: 500px;">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">🔐 Вход в систему</h4>
            </div>
            <div class="card-body">
                <?php if (isset($_GET['registered'])): ?>
                    <div class="alert alert-success">Регистрация успешна! Теперь вы можете войти.</div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= h($error) ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="login" class="form-label">Логин или Email *</label>
                        <input type="text" name="login" id="login" class="form-control" 
                               value="<?= h($_POST['login'] ?? '') ?>" 
                               required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Пароль *</label>
                        <input type="password" name="password" id="password" class="form-control" 
                               required>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100">Войти</button>
                </form>
                
                <div class="mt-3 text-center">
                    <a href="/public/register.php">Нет аккаунта? Зарегистрируйтесь</a>
                    <br>
                    <small class="text-muted">Тестовый админ: admin / Admin123!</small>
                </div>
            </div>
        </div>
    </div>
</body>
</html>