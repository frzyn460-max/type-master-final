<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth-check.php';

// شروع Session بعد از بارگذاری تنظیمات
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// اگر لاگین است، به داشبورد برود
if (isLoggedIn()) {
    redirect(SITE_URL . '/dashboard.php');
}

// تشخیص زبان (از cookie یا پیش‌فرض)
if (isset($_COOKIE['language']) && in_array($_COOKIE['language'], AVAILABLE_LANGUAGES)) {
    $currentLang = $_COOKIE['language'];
} else {
    $currentLang = getCurrentLanguage();
}

$currentTheme = getCurrentTheme();
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>" dir="<?= $currentLang === 'fa' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> - یادگیری تایپ سریع</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="./assets/css/themes.css">
</head>
<body class="theme-<?= $currentTheme ?> lang-<?= $currentLang ?>">
    
    <!-- Header -->
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1>⌨️ <?= SITE_NAME ?></h1>
                </div>
                <div class="header-actions">
                    <button class="btn-icon" id="themeToggle" title="تغییر تم">
                        <span class="icon"></span>
                    </button>
                    <button class="btn-icon" id="langToggle" title="تغییر زبان">
                        <span class="icon"><?= $currentLang === 'fa' ? '🇬🇧' : '🇮🇷' ?></span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h2 class="hero-title animate-slide-up">
                        <?= $currentLang === 'fa' ? 'استاد تایپینگ شوید!' : 'Master Your Typing!' ?>
                    </h2>
                    <p class="hero-subtitle animate-slide-up delay-1">
                        <?= $currentLang === 'fa' 
                            ? 'با Typing Master Pro سرعت و دقت تایپ خود را به سطح حرفه‌ای برسانید' 
                            : 'Improve your typing speed and accuracy with Typing Master Pro' ?>
                    </p>
                    <div class="hero-stats animate-slide-up delay-2">
                        <div class="stat-item">
                            <div class="stat-number">1000+</div>
                            <div class="stat-label"><?= $currentLang === 'fa' ? 'کاربر فعال' : 'Active Users' ?></div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">50+</div>
                            <div class="stat-label"><?= $currentLang === 'fa' ? 'درس آموزشی' : 'Lessons' ?></div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">4</div>
                            <div class="stat-label"><?= $currentLang === 'fa' ? 'بازی جذاب' : 'Fun Games' ?></div>
                        </div>
                    </div>
                    <div class="hero-buttons animate-slide-up delay-3">
                        <button class="btn btn-primary btn-lg" id="showRegister">
                            <?= $currentLang === 'fa' ? '🚀 شروع رایگان' : '🚀 Start Free' ?>
                        </button>
                        <button class="btn btn-secondary btn-lg" id="showLogin">
                            <?= $currentLang === 'fa' ? '🔑 ورود' : '🔑 Login' ?>
                        </button>
                    </div>
                </div>
                <div class="hero-image animate-float">
                    <div class="keyboard-animation">
                        <div class="keyboard-glow"></div>
                        <div class="typing-hands">⌨️</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <h3 class="section-title">
                <?= $currentLang === 'fa' ? 'چرا Typing Master؟' : 'Why Typing Master?' ?>
            </h3>
            <div class="features-grid">
                <div class="feature-card animate-fade-in">
                    <div class="feature-icon">🎯</div>
                    <h4><?= $currentLang === 'fa' ? 'درس‌های تعاملی' : 'Interactive Lessons' ?></h4>
                    <p><?= $currentLang === 'fa' ? 'بیش از 50 درس از مبتدی تا پیشرفته' : 'Over 50 lessons from beginner to advanced' ?></p>
                </div>
                <div class="feature-card animate-fade-in delay-1">
                    <div class="feature-icon">🎮</div>
                    <h4><?= $currentLang === 'fa' ? 'بازی‌های سرگرم‌کننده' : 'Fun Games' ?></h4>
                    <p><?= $currentLang === 'fa' ? 'یادگیری با بازی و چالش‌های هیجان‌انگیز' : 'Learn through exciting games and challenges' ?></p>
                </div>
                <div class="feature-card animate-fade-in delay-2">
                    <div class="feature-icon">📊</div>
                    <h4><?= $currentLang === 'fa' ? 'پیگیری پیشرفت' : 'Track Progress' ?></h4>
                    <p><?= $currentLang === 'fa' ? 'آمار کامل و نمودارهای پیشرفت' : 'Complete statistics and progress charts' ?></p>
                </div>
                <div class="feature-card animate-fade-in delay-3">
                    <div class="feature-icon">🏆</div>
                    <h4><?= $currentLang === 'fa' ? 'سیستم امتیازدهی' : 'Gamification' ?></h4>
                    <p><?= $currentLang === 'fa' ? 'کسب نشان، سطح و گواهینامه' : 'Earn badges, levels and certificates' ?></p>
                </div>
                <div class="feature-card animate-fade-in delay-4">
                    <div class="feature-icon">🌙</div>
                    <h4><?= $currentLang === 'fa' ? 'دارک مود' : 'Dark Mode' ?></h4>
                    <p><?= $currentLang === 'fa' ? 'حالت شب برای راحتی چشم شما' : 'Night mode for your comfort' ?></p>
                </div>
                <div class="feature-card animate-fade-in delay-5">
                    <div class="feature-icon">🌍</div>
                    <h4><?= $currentLang === 'fa' ? 'دو زبانه' : 'Bilingual' ?></h4>
                    <p><?= $currentLang === 'fa' ? 'فارسی و انگلیسی' : 'Persian & English' ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Auth Modals -->
    <div class="modal" id="loginModal">
        <div class="modal-overlay"></div>
        <div class="modal-content animate-scale">
            <button class="modal-close">&times;</button>
            <div class="modal-header">
                <h3><?= $currentLang === 'fa' ? '🔑 ورود به حساب' : '🔑 Login' ?></h3>
            </div>
            <form id="loginForm">
                <div class="form-group">
                    <label><?= $currentLang === 'fa' ? 'نام کاربری' : 'Username' ?></label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label><?= $currentLang === 'fa' ? 'رمز عبور' : 'Password' ?></label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-error" id="loginError"></div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    <?= $currentLang === 'fa' ? 'ورود' : 'Login' ?>
                </button>
                <div class="form-footer">
                    <?= $currentLang === 'fa' ? 'حساب ندارید؟' : "Don't have an account?" ?>
                    <a href="#" id="switchToRegister"><?= $currentLang === 'fa' ? 'ثبت‌نام' : 'Register' ?></a>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="registerModal">
        <div class="modal-overlay"></div>
        <div class="modal-content animate-scale">
            <button class="modal-close">&times;</button>
            <div class="modal-header">
                <h3><?= $currentLang === 'fa' ? '🚀 ثبت‌نام رایگان' : '🚀 Register Free' ?></h3>
            </div>
            <form id="registerForm">
                <div class="form-group">
                    <label><?= $currentLang === 'fa' ? 'نام کاربری' : 'Username' ?></label>
                    <input type="text" name="username" class="form-control" required minlength="3">
                </div>
                <div class="form-group">
                    <label><?= $currentLang === 'fa' ? 'ایمیل' : 'Email' ?></label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label><?= $currentLang === 'fa' ? 'رمز عبور' : 'Password' ?></label>
                    <input type="password" name="password" class="form-control" required minlength="6">
                </div>
                <div class="form-group">
                    <label><?= $currentLang === 'fa' ? 'تکرار رمز عبور' : 'Confirm Password' ?></label>
                    <input type="password" name="password_confirm" class="form-control" required>
                </div>
                <div class="form-error" id="registerError"></div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    <?= $currentLang === 'fa' ? 'ثبت‌نام' : 'Register' ?>
                </button>
                <div class="form-footer">
                    <?= $currentLang === 'fa' ? 'قبلاً ثبت‌نام کرده‌اید؟' : 'Already have an account?' ?>
                    <a href="#" id="switchToLogin"><?= $currentLang === 'fa' ? 'ورود' : 'Login' ?></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast" id="toast">
        <span class="toast-icon">✓</span>
        <span class="toast-message"></span>
    </div>

    <!-- Scripts -->
    <script>
        // خواندن زبان از localStorage و ساخت cookie
        const savedLang = localStorage.getItem('language');
        if (savedLang && (savedLang === 'fa' || savedLang === 'en')) {
            document.cookie = `language=${savedLang}; path=/; max-age=31536000`;
        }
    </script>
    <script src="./assets/js/app.js"></script>
    <script>
        // داده‌های PHP برای JavaScript
        const APP_CONFIG = {
            siteUrl: '<?= SITE_URL ?>',
            language: '<?= $currentLang ?>',
            theme: '<?= $currentTheme ?>'
        };
    </script>
</body>
</html>