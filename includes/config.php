<?php
/**
 * ====================================
 * Typing Master - Configuration File
 * فایل تنظیمات اصلی پروژه
 * ====================================
 */

// جلوگیری از دسترسی مستقیم
defined('TYPING_MASTER') or define('TYPING_MASTER', true);

// ====================================
// 🗄️ تنظیمات دیتابیس
// ====================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'typing_master_db');
define('DB_USER', 'root');
define('DB_PASS', ''); // پسورد خود را وارد کنید
define('DB_CHARSET', 'utf8mb4');

// ====================================
// 🌐 تنظیمات سایت
// ====================================
define('SITE_NAME', 'Typing Master Pro');

// تشخیص خودکار URL سایت
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$scriptPath = rtrim($scriptPath, '/');

define('SITE_URL', $protocol . '://' . $host . $scriptPath);
define('BASE_PATH', __DIR__ . '/../');

// ====================================
// 🌍 تنظیمات زبان
// ====================================
define('DEFAULT_LANGUAGE', 'fa');
define('AVAILABLE_LANGUAGES', ['fa', 'en']);

// ====================================
// 🎨 تنظیمات تم
// ====================================
define('DEFAULT_THEME', 'dark');
define('AVAILABLE_THEMES', ['dark', 'light']);

// ====================================
// 🎮 تنظیمات بازی
// ====================================
define('XP_PER_LESSON', 100);
define('XP_PER_LEVEL', 1000);
define('MAX_LEVEL', 100);

// تنظیمات ستاره‌ها
define('STARS_WPM_THRESHOLD_1', 20);  // 1 ستاره
define('STARS_WPM_THRESHOLD_2', 40);  // 2 ستاره
define('STARS_WPM_THRESHOLD_3', 60);  // 3 ستاره

define('STARS_ACCURACY_MIN', 85);     // حداقل دقت برای دریافت ستاره

// ====================================
// 🔒 تنظیمات امنیتی
// ====================================
define('SESSION_LIFETIME', 3600 * 24 * 7); // 7 روز
define('PASSWORD_MIN_LENGTH', 6);
define('USERNAME_MIN_LENGTH', 3);
define('USERNAME_MAX_LENGTH', 50);

// کلید رمزنگاری (برای توکن‌ها)
define('ENCRYPTION_KEY', 'your-secret-key-here-change-this');

// ====================================
// 📁 مسیرها
// ====================================
define('UPLOADS_PATH', BASE_PATH . 'uploads/');
define('AVATARS_PATH', UPLOADS_PATH . 'avatars/');
define('SOUNDS_PATH', SITE_URL . '/assets/sounds/');
define('IMAGES_PATH', SITE_URL . '/assets/images/');

// ====================================
// 📧 تنظیمات ایمیل (اختیاری)
// ====================================
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-password');
define('SMTP_FROM', 'noreply@typingmaster.com');
define('SMTP_FROM_NAME', 'Typing Master');

// ====================================
// 🎯 تنظیمات بازی‌ها
// ====================================
define('GAME_WORD_RAIN_SPEED_MIN', 2000);
define('GAME_WORD_RAIN_SPEED_MAX', 500);
define('GAME_SURVIVAL_TIME_LIMIT', 60);

// ====================================
// 📊 تنظیمات آمار
// ====================================
define('LEADERBOARD_TOP_COUNT', 10);
define('RECENT_STATS_DAYS', 30);

// ====================================
// 🔧 تنظیمات توسعه
// ====================================
define('DEBUG_MODE', true); // در حالت Production به false تغییر دهید
define('SHOW_ERRORS', true);

// نمایش خطاها در حالت توسعه
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ====================================
// ⏰ تنظیمات زمان
// ====================================
date_default_timezone_set('Asia/Tehran');

// ====================================
// 🍪 تنظیمات Session و Cookie
// ====================================
// این تنظیمات فقط قبل از شروع Session اعمال می‌شوند
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_lifetime', SESSION_LIFETIME);
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
}

// ====================================
// 📱 تنظیمات آپلود فایل
// ====================================
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// ====================================
// 🎵 تنظیمات صدا
// ====================================
define('SOUND_ENABLED', true);
define('SOUND_VOLUME', 0.5); // 0.0 تا 1.0

// ====================================
// 📜 تنظیمات گواهینامه
// ====================================
define('CERTIFICATE_MIN_LESSONS', 10);
define('CERTIFICATE_MIN_WPM', 40);
define('CERTIFICATE_MIN_ACCURACY', 90);

// ====================================
// 🏆 تنظیمات نشان‌ها
// ====================================
$GLOBALS['badges'] = [
    'first_lesson' => [
        'icon' => '🔰',
        'title_fa' => 'تازه‌وارد',
        'title_en' => 'Beginner',
        'condition' => 'lessons_completed >= 1'
    ],
    'speed_master' => [
        'icon' => '⚡',
        'title_fa' => 'استاد سرعت',
        'title_en' => 'Speed Master',
        'condition' => 'avg_wpm >= 60'
    ],
    'accuracy_king' => [
        'icon' => '🎯',
        'title_fa' => 'پادشاه دقت',
        'title_en' => 'Accuracy King',
        'condition' => 'avg_accuracy >= 95'
    ],
    'complete_all' => [
        'icon' => '🏅',
        'title_fa' => 'تکمیل همه',
        'title_en' => 'Complete All',
        'condition' => 'lessons_completed >= total_lessons'
    ],
    'champion' => [
        'icon' => '👑',
        'title_fa' => 'قهرمان',
        'title_en' => 'Champion',
        'condition' => 'leaderboard_rank == 1'
    ]
];

// ====================================
// 🎨 تنظیمات رنگ‌ها (برای نمودارها)
// ====================================
$GLOBALS['chart_colors'] = [
    'primary' => '#6366f1',
    'success' => '#10b981',
    'warning' => '#f59e0b',
    'danger' => '#ef4444',
    'info' => '#3b82f6'
];

// ====================================
// ✅ اتمام فایل تنظیمات
// ====================================
?>