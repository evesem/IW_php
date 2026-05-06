<?php
/**
 * Выход из системы
 */

require_once __DIR__ . '/../includes/session.php';

logout();
header('Location: /index.php');
exit();
?>