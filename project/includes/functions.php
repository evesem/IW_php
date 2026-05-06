<?php
/**
 * Вспомогательные функции для всего приложения
 */

/**
 * Безопасное экранирование для HTML (защита от XSS)
 * @param string $string
 * @return string
 */
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Редирект с flash-сообщением
 * @param string $url
 * @param string $message
 * @param string $type
 */
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header("Location: $url");
    exit();
}

/**
 * Показ flash-сообщения (вызывать в представлении)
 */
function showFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_type'] ?? 'info';
        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">
                ' . h($_SESSION['flash_message']) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
    }
}

/**
 * Обрезка текста до нужной длины
 * @param string $text
 * @param int $length
 * @return string
 */
function truncate($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . '...';
}

/**
 * Получение названия категории на русском
 * @param string $category
 * @return string
 */
function getCategoryName($category) {
    $categories = [
        'technology' => 'Технологии',
        'art' => 'Искусство',
        'business' => 'Бизнес',
        'health' => 'Здоровье',
        'education' => 'Образование',
        'other' => 'Другое'
    ];
    return $categories[$category] ?? $category;
}

/**
 * Получение цвета приоритета
 * @param int $priority
 * @return string
 */
function getPriorityColor($priority) {
    $colors = [
        1 => 'success',
        2 => 'info',
        3 => 'warning',
        4 => 'orange',
        5 => 'danger'
    ];
    return $colors[$priority] ?? 'secondary';
}

/**
 * Валидация email
 * @param string $email
 * @return bool
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Генерация CSRF-токена (для защиты форм)
 * @return string
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Проверка CSRF-токена
 * @param string $token
 * @return bool
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>