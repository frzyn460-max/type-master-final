-- ====================================
-- Typing Master Database Schema
-- فاز 1: ساختار کامل دیتابیس
-- ====================================

-- حذف دیتابیس قدیمی در صورت وجود
DROP DATABASE IF EXISTS typing_master_db;

-- ایجاد دیتابیس جدید
CREATE DATABASE typing_master_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE typing_master_db;

-- ====================================
-- 1️⃣ جدول کاربران (users)
-- ====================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) DEFAULT 'default-avatar.png',
    language ENUM('fa', 'en') DEFAULT 'fa',
    theme ENUM('dark', 'light') DEFAULT 'dark',
    total_xp INT DEFAULT 0,
    level INT DEFAULT 1,
    sound_enabled BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_level (level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- 2️⃣ جدول درس‌ها (lessons)
-- ====================================
CREATE TABLE lessons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title_fa VARCHAR(200) NOT NULL,
    title_en VARCHAR(200) NOT NULL,
    description_fa TEXT,
    description_en TEXT,
    level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    language ENUM('fa', 'en') NOT NULL,
    content TEXT NOT NULL,
    required_level INT DEFAULT 1,
    xp_reward INT DEFAULT 100,
    order_index INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_level (level),
    INDEX idx_language (language),
    INDEX idx_order (order_index)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- 3️⃣ جدول پیشرفت کاربر (user_progress)
-- ====================================
CREATE TABLE user_progress (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    lesson_id INT NOT NULL,
    wpm DECIMAL(5,2) DEFAULT 0.00,
    accuracy DECIMAL(5,2) DEFAULT 0.00,
    time_spent INT DEFAULT 0,
    is_completed BOOLEAN DEFAULT FALSE,
    stars TINYINT DEFAULT 0,
    attempts INT DEFAULT 0,
    best_wpm DECIMAL(5,2) DEFAULT 0.00,
    best_accuracy DECIMAL(5,2) DEFAULT 0.00,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_lesson (user_id, lesson_id),
    INDEX idx_user_id (user_id),
    INDEX idx_completed (is_completed)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- 4️⃣ جدول امتیازات بازی (game_scores)
-- ====================================
CREATE TABLE game_scores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    game_type ENUM('word-rain', 'speed-race', 'accuracy', 'survival') NOT NULL,
    score INT DEFAULT 0,
    wpm DECIMAL(5,2) DEFAULT 0.00,
    accuracy DECIMAL(5,2) DEFAULT 0.00,
    level_reached INT DEFAULT 1,
    duration INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_game_type (game_type),
    INDEX idx_score (score DESC),
    INDEX idx_created (created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- 5️⃣ جدول نشان‌ها (achievements)
-- ====================================
CREATE TABLE achievements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    badge_type VARCHAR(50) NOT NULL,
    title_fa VARCHAR(100) NOT NULL,
    title_en VARCHAR(100) NOT NULL,
    description_fa TEXT,
    description_en TEXT,
    icon VARCHAR(50) DEFAULT '🏆',
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_badge (user_id, badge_type),
    INDEX idx_user_id (user_id),
    INDEX idx_badge_type (badge_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- 6️⃣ جدول گواهینامه‌ها (certificates)
-- ====================================
CREATE TABLE certificates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    certificate_code VARCHAR(20) UNIQUE NOT NULL,
    level_name VARCHAR(50) NOT NULL,
    avg_wpm DECIMAL(5,2) DEFAULT 0.00,
    avg_accuracy DECIMAL(5,2) DEFAULT 0.00,
    lessons_completed INT DEFAULT 0,
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_code (certificate_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- 7️⃣ جدول تنظیمات سیستم (settings)
-- ====================================
CREATE TABLE system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description_fa TEXT,
    description_en TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- 8️⃣ داده‌های پیش‌فرض
-- ====================================

-- تنظیمات سیستم
INSERT INTO system_settings (setting_key, setting_value, description_fa, description_en) VALUES
('site_name', 'Typing Master Pro', 'نام سایت', 'Site Name'),
('default_language', 'fa', 'زبان پیش‌فرض', 'Default Language'),
('default_theme', 'dark', 'تم پیش‌فرض', 'Default Theme'),
('xp_per_lesson', '100', 'امتیاز هر درس', 'XP per Lesson'),
('xp_per_level', '1000', 'امتیاز هر سطح', 'XP per Level');

-- کاربر تستی (username: admin, password: admin123)
INSERT INTO users (username, email, password, language, theme, total_xp, level) VALUES
('admin', 'admin@typingmaster.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'fa', 'dark', 5000, 5);

-- ====================================
-- ✅ دیتابیس آماده است!
-- ====================================

SELECT 'Database Created Successfully! ✅' AS Status;