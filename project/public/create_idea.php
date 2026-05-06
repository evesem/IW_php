<?php
/**
 * Форма создания идеи (ресурса)
 * 5+ полей: title, description, category, priority, status, is_public
 * Валидация: клиентская (HTML5) + серверная (PHP)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

requireAuth();

$errors = [];
$oldInput = [];

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = $_POST['category'] ?? '';
    $priority = (int)($_POST['priority'] ?? 1);
    $status = $_POST['status'] ?? 'published';
    $is_public = isset($_POST['is_public']) ? 1 : 0;
    
    $oldInput = compact('title', 'description', 'category', 'priority', 'status', 'is_public');
    
    // Серверная валидация
    if (strlen($title) < 3) {
        $errors[] = 'Название идеи должно содержать минимум 3 символа';
    }
    if (strlen($title) > 200) {
        $errors[] = 'Название не должно превышать 200 символов';
    }
    if (strlen($description) < 10) {
        $errors[] = 'Описание должно содержать минимум 10 символов';
    }
    if (empty($category)) {
        $errors[] = 'Выберите категорию';
    }
    if ($priority < 1 || $priority > 5) {
        $errors[] = 'Приоритет должен быть от 1 до 5';
    }
    $allowedStatuses = ['draft', 'published', 'archived'];
    if (!in_array($status, $allowedStatuses)) {
        $errors[] = 'Неверный статус';
    }
    
    // Защита от XSS и SQL injection через PDO
    if (empty($errors)) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare(
            "INSERT INTO ideas (title, description, category, priority, status, is_public, user_id) 
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        
        if ($stmt->execute([$title, $description, $category, $priority, $status, $is_public, $_SESSION['user_id']])) {
            header('Location: /index.php?success=idea_created');
            exit();
        } else {
            $errors[] = 'Ошибка при сохранении идеи';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Создание идеи</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .error-border { border: 2px solid #dc3545; }
        .error-text { color: #dc3545; font-size: 0.875em; }
    </style>
</head>
<body>
    <div class="container my-4">
        <h1>✨ Создание новой идеи</h1>
        <a href="/index.php" class="btn btn-secondary mb-3">← На главную</a>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" class="needs-validation" novalidate>
            <!-- Поле 1: Название -->
            <div class="mb-3">
                <label for="title" class="form-label">Название идеи *</label>
                <input type="text" class="form-control <?= in_array('Название идеи должно содержать минимум 3 символа', $errors) ? 'error-border' : '' ?>" 
                       id="title" name="title" 
                       value="<?= htmlspecialchars($oldInput['title'] ?? '') ?>"
                       required minlength="3" maxlength="200">
                <div class="invalid-feedback">Минимум 3 символа</div>
            </div>
            
            <!-- Поле 2: Описание (textarea) -->
            <div class="mb-3">
                <label for="description" class="form-label">Описание *</label>
                <textarea class="form-control" id="description" name="description" rows="5" 
                          required minlength="10"><?= htmlspecialchars($oldInput['description'] ?? '') ?></textarea>
                <div class="invalid-feedback">Минимум 10 символов</div>
            </div>
            
            <!-- Поле 3: Категория (select) -->
            <div class="mb-3">
                <label for="category" class="form-label">Категория *</label>
                <select class="form-select" id="category" name="category" required>
                    <option value="">Выберите категорию</option>
                    <option value="technology" <?= ($oldInput['category'] ?? '') == 'technology' ? 'selected' : '' ?>>Технологии</option>
                    <option value="art" <?= ($oldInput['category'] ?? '') == 'art' ? 'selected' : '' ?>>Искусство</option>
                    <option value="business" <?= ($oldInput['category'] ?? '') == 'business' ? 'selected' : '' ?>>Бизнес</option>
                    <option value="health" <?= ($oldInput['category'] ?? '') == 'health' ? 'selected' : '' ?>>Здоровье</option>
                    <option value="education" <?= ($oldInput['category'] ?? '') == 'education' ? 'selected' : '' ?>>Образование</option>
                    <option value="other" <?= ($oldInput['category'] ?? '') == 'other' ? 'selected' : '' ?>>Другое</option>
                </select>
            </div>
            
            <!-- Поле 4: Приоритет (range / number) -->
            <div class="mb-3">
                <label for="priority" class="form-label">Приоритет (1-5) *</label>
                <input type="range" class="form-range" id="priority" name="priority" min="1" max="5" 
                       value="<?= $oldInput['priority'] ?? 3 ?>">
                <span id="priorityValue" class="badge bg-warning"><?= $oldInput['priority'] ?? 3 ?></span>
            </div>
            
            <!-- Поле 5: Статус (radio) -->
            <div class="mb-3">
                <label class="form-label">Статус</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="status" value="draft" 
                           <?= ($oldInput['status'] ?? '') == 'draft' ? 'checked' : '' ?>>
                    <label class="form-check-label">Черновик</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="status" value="published" 
                           <?= (!isset($oldInput['status']) || $oldInput['status'] == 'published') ? 'checked' : '' ?>>
                    <label class="form-check-label">Опубликовать</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="status" value="archived" 
                           <?= ($oldInput['status'] ?? '') == 'archived' ? 'checked' : '' ?>>
                    <label class="form-check-label">Архив</label>
                </div>
            </div>
            
            <!-- Поле 6: Публичность (checkbox) -->
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="is_public" name="is_public" 
                       <?= (isset($oldInput['is_public']) && $oldInput['is_public']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_public">Доступно всем (публичная)</label>
            </div>
            
            <button type="submit" class="btn btn-primary">Сохранить идею</button>
        </form>
    </div>
    
    <script>
        // Клиентская валидация и отображение приоритета
        document.getElementById('priority').addEventListener('input', function() {
            document.getElementById('priorityValue').textContent = this.value;
        });
        
        // Bootstrap валидация
        (function() {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html>