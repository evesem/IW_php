<?php
/**
 * Функции аутентификации (регистрация, вход)
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Регистрация нового пользователя
 * @param string $username
 * @param string $email
 * @param string $password
 * @return array ['success' => bool, 'message' => string]
 */
function registerUser($username, $email, $password) {
    // Валидация
    if (strlen($username) < 3) {
        return ['success' => false, 'message' => 'Имя пользователя должно содержать минимум 3 символа'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Неверный формат email'];
    }
    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Пароль должен содержать минимум 6 символов'];
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Проверка на существующего пользователя
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Пользователь с таким именем или email уже существует'];
    }
    
    // Хеширование пароля
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Создание пользователя
    $stmt = $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
    if ($stmt->execute([$username, $email, $hashedPassword])) {
        return ['success' => true, 'message' => 'Регистрация успешна'];
    }
    
    return ['success' => false, 'message' => 'Ошибка при регистрации'];
}

/**
 * Аутентификация пользователя (вход)
 * @param string $login Имя пользователя или email
 * @param string $password
 * @return array ['success' => bool, 'message' => string, 'user' => array|null]
 */
function loginUser($login, $password) {
    $db = Database::getInstance()->getConnection();
    
    // Поиск пользователя по username или email
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch();
    
    if (!$user) {
        return ['success' => false, 'message' => 'Неверный логин или пароль'];
    }
    
    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Неверный логин или пароль'];
    }
    
    // Создание сессии
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_role'] = $user['role'];
    
    return [
        'success' => true,
        'message' => 'Вход выполнен успешно',
        'user' => $user
    ];
}
?>