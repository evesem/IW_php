<?php
/**
 * Управление сессиями и проверка прав доступа
 */

// Запуск сессии, если ещё не запущена
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Проверка, авторизован ли пользователь
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

/**
 * Проверка, является ли пользователь администратором
 * @return bool
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Получение данных текущего пользователя
 * @return array|null
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['user_role']
    ];
}

/**
 * Требование авторизации (редирект если не авторизован)
 */
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: /public/login.php');
        exit();
    }
}

/**
 * Требование роли администратора
 */
function requireAdmin() {
    if (!isAdmin()) {
        http_response_code(403);
        die('Доступ запрещен. Требуется роль администратора.');
    }
}

/**
 * Выход из системы
 */
function logout() {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
?>