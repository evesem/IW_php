CREATE DATABASE IF NOT EXISTS idea_catalog;
USE idea_catalog;

-- Таблица пользователей
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    reset_token VARCHAR(64) NULL,
    reset_expires DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица идей (ресурсов)
CREATE TABLE ideas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    category ENUM('technology', 'art', 'business', 'health', 'education', 'other') NOT NULL,
    priority TINYINT DEFAULT 1 CHECK (priority BETWEEN 1 AND 5),
    status ENUM('draft', 'published', 'archived') DEFAULT 'published',
    is_public BOOLEAN DEFAULT TRUE,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Вставка тестового администратора (пароль: Admin123!)
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@example.com', '$2y$10$YourHashHere', 'admin');
-- Для получения реального хеша используйте: echo password_hash('Admin123!', PASSWORD_DEFAULT);