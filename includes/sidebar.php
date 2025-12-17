<?php
/**
 * Sidebar Component
 * کامپوننت منوی کناری
 */

// دریافت صفحه فعلی
$currentPage = basename($_SERVER['PHP_SELF']);

// ترجمه‌ها
$sidebarText = [
    'fa' => [
        'dashboard' => 'داشبورد',
        'lessons' => 'درس‌ها',
        'games' => 'بازی‌ها',
        'stats' => 'آمار',
        'profile' => 'پروفایل',
        'leaderboard' => 'رتبه‌بندی',
        'certificates' => 'گواهینامه‌ها',
        'settings' => 'تنظیمات',
        'logout' => 'خروج'
    ],
    'en' => [
        'dashboard' => 'Dashboard',
        'lessons' => 'Lessons',
        'games' => 'Games',
        'stats' => 'Statistics',
        'profile' => 'Profile',
        'leaderboard' => 'Leaderboard',
        'certificates' => 'Certificates',
        'settings' => 'Settings',
        'logout' => 'Logout'
    ]
];

$currentLang = getCurrentLanguage();
$st = $sidebarText[$currentLang];

// تابع برای چک کردن فعال بودن منو
function isActive($page) {
    global $currentPage;
    return $currentPage === $page ? 'active' : '';
}
?>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <h2>⌨️ <?= SITE_NAME ?></h2>
        </div>
        <button class="sidebar-toggle" id="sidebarToggle">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
    
    <nav class="sidebar-nav">
        <!-- Dashboard -->
        <a href="dashboard.php" class="nav-item <?= isActive('dashboard.php') ?>">
            <span class="nav-icon">🏠</span>
            <span class="nav-text"><?= $st['dashboard'] ?></span>
        </a>
        
        <!-- Lessons -->
        <a href="lessons.php" class="nav-item <?= isActive('lessons.php') ?> <?= isActive('lesson-detail.php') ?> <?= isActive('practice.php') ?>">
            <span class="nav-icon">📚</span>
            <span class="nav-text"><?= $st['lessons'] ?></span>
        </a>
        
        <!-- Games -->
        <a href="games.php" class="nav-item <?= isActive('games.php') ?> <?= strpos($currentPage, 'game-') === 0 ? 'active' : '' ?>">
            <span class="nav-icon">🎮</span>
            <span class="nav-text"><?= $st['games'] ?></span>
        </a>
        
        <!-- Stats -->
        <a href="stats.php" class="nav-item <?= isActive('stats.php') ?>">
            <span class="nav-icon">📊</span>
            <span class="nav-text"><?= $st['stats'] ?></span>
        </a>
        
        <!-- Profile -->
        <a href="profile.php" class="nav-item <?= isActive('profile.php') ?>">
            <span class="nav-icon">👤</span>
            <span class="nav-text"><?= $st['profile'] ?></span>
        </a>
        
        <!-- Certificates -->
        <a href="certificates.php" class="nav-item <?= isActive('certificates.php') ?>">
            <span class="nav-icon">📜</span>
            <span class="nav-text"><?= $st['certificates'] ?></span>
        </a>
        
        <!-- Settings -->
        <a href="settings.php" class="nav-item <?= isActive('settings.php') ?>">
            <span class="nav-icon">⚙️</span>
            <span class="nav-text"><?= $st['settings'] ?></span>
        </a>
    </nav>
    
    <div class="sidebar-footer">
        <a href="api/logout.php" class="nav-item logout-btn">
            <span class="nav-icon">🚪</span>
            <span class="nav-text"><?= $st['logout'] ?></span>
        </a>
    </div>
</aside>