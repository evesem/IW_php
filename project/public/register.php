<?php
/**
 * Страница регистрации нового пользователя
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
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Дополнительная проверка совпадения паролей
    if ($password !== $confirm_password) {
        $error = 'Пароли не совпадают';
    } else {
        $result = registerUser($username, $email, $password);
        if ($result['success']) {
            $success = $result['message'];
            // Перенаправляем на страницу входа через 2 секунды
            header('refresh:2;url=/public/login.php');
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
    <title>Регистрация - Каталог идей</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-light">
    <div class="container mt-5" style="max-width: 500px;">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">📝 Регистрация нового пользователя</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= h($error) ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= h($success) ?> Перенаправление на вход...</div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Имя пользователя *</label>
                        <input type="text" name="username" id="username" class="form-control" 
                               value="<?= h($_POST['username'] ?? '') ?>" 
                               required minlength="3" maxlength="50">
                        <small class="text-muted">Минимум 3 символа</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" name="email" id="email" class="form-control" 
                               value="<?= h($_POST['email'] ?? '') ?>" 
                               required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Пароль *</label>
                        <input type="password" name="password" id="password" class="form-control" 
                               required minlength="6">
                        <small class="text-muted">Минимум 6 символов</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Подтверждение пароля *</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" 
                               required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">Зарегистрироваться</button>
                </form>
                
                <div class="mt-3 text-center">
                    <a href="/public/login.php">Уже есть аккаунт? Войдите</a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Простая клиентская валидация совпадения паролей
        document.querySelector('form').addEventListener('submit', function(e) {
            var password = document.getElementById('password').value;
            var confirm = document.getElementById('confirm_password').value;
            if (password !== confirm) {
                e.preventDefault();
                alert('Пароли не совпадают!');
            }
        });
    </script>
</body>
</html>