<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth-check.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// فقط کاربران لاگین شده
requireLogin();

$user = getCurrentUser();
$stats = getUserStats($user['id']);
$achievements = getUserAchievements($user['id']);

// محاسبه پیشرفت تا سطح بعد
$currentXP = $user['total_xp'];
$currentLevel = $user['level'];
$xpForNextLevel = $currentLevel * XP_PER_LEVEL;
$xpProgress = $currentXP % XP_PER_LEVEL;
$xpNeeded = XP_PER_LEVEL - $xpProgress;
$progressPercent = ($xpProgress / XP_PER_LEVEL) * 100;

$currentLang = getCurrentLanguage();
$currentTheme = getCurrentTheme();

// ترجمه‌ها
$t = [
    'fa' => [
        'welcome' => 'خوش آمدید',
        'dashboard' => 'داشبورد',
        'lessons' => 'درس‌ها',
        'games' => 'بازی‌ها',
        'stats' => 'آمار',
        'profile' => 'پروفایل',
        'logout' => 'خروج',
        'level' => 'سطح',
        'xp' => 'امتیاز',
        'next_level' => 'تا سطح بعدی',
        'overview' => 'آمار کلی',
        'lessons_completed' => 'درس‌های تکمیل شده',
        'lesson' => 'درس',
        'avg_wpm' => 'میانگین سرعت',
        'wpm' => 'کلمه/دقیقه',
        'avg_accuracy' => 'میانگین دقت',
        'total_time' => 'زمان تمرین',
        'minutes' => 'دقیقه',
        'games_played' => 'بازی انجام شده',
        'game' => 'بازی',
        'achievements_earned' => 'نشان کسب شده',
        'badge' => 'نشان',
        'recent_achievements' => 'نشان‌های اخیر',
        'view_all' => 'مشاهده همه',
        'quick_actions' => 'دسترسی سریع',
        'start_lesson' => 'شروع درس',
        'play_game' => 'بازی کن',
        'view_stats' => 'مشاهده آمار',
        'edit_profile' => 'ویرایش پروفایل',
        'no_achievements' => 'هنوز نشانی کسب نکرده‌اید'
    ],
    'en' => [
        'welcome' => 'Welcome',
        'dashboard' => 'Dashboard',
        'lessons' => 'Lessons',
        'games' => 'Games',
        'stats' => 'Statistics',
        'profile' => 'Profile',
        'logout' => 'Logout',
        'level' => 'Level',
        'xp' => 'XP',
        'next_level' => 'To Next Level',
        'overview' => 'Overview',
        'lessons_completed' => 'Lessons Completed',
        'lesson' => 'Lessons',
        'avg_wpm' => 'Average Speed',
        'wpm' => 'WPM',
        'avg_accuracy' => 'Average Accuracy',
        'total_time' => 'Practice Time',
        'minutes' => 'minutes',
        'games_played' => 'Games Played',
        'game' => 'Games',
        'achievements_earned' => 'Achievements Earned',
        'badge' => 'Badges',
        'recent_achievements' => 'Recent Achievements',
        'view_all' => 'View All',
        'quick_actions' => 'Quick Actions',
        'start_lesson' => 'Start Lesson',
        'play_game' => 'Play Game',
        'view_stats' => 'View Stats',
        'edit_profile' => 'Edit Profile',
        'no_achievements' => 'No achievements yet'
    ]
];

$tr = $t[$currentLang];
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>" dir="<?= $currentLang === 'fa' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tr['dashboard'] ?> - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/themes.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/dashboard.css">
</head>
<body class="theme-<?= $currentTheme ?> lang-<?= $currentLang ?>">
    
    <!-- Sidebar -->
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Bar -->
        <header class="top-bar">
            <div class="top-bar-left">
                <button class="btn-icon mobile-menu-btn" id="mobileMenuBtn">
                    <span>☰</span>
                </button>
                <h1 class="page-title"><?= $tr['dashboard'] ?></h1>
            </div>
            <div class="top-bar-right">
                <button class="btn-icon" id="themeToggle" title="<?= $currentLang === 'fa' ? 'تغییر تم' : 'Toggle Theme' ?>">
                    <span class="icon"></span>
                </button>
                <button class="btn-icon" id="langToggle" title="<?= $currentLang === 'fa' ? 'تغییر زبان' : 'Change Language' ?>">
                    <span class="icon"><?= $currentLang === 'fa' ? '🇬🇧' : '🇮🇷' ?></span>
                </button>
                <div class="user-menu">
                    <img src="./uploads/avatars/<?= $user['avatar'] ?>" alt="Avatar" class="user-avatar" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($user['username']) ?>&background=6366f1&color=fff'">
                    <span class="user-name"><?= htmlspecialchars($user['username']) ?></span>
                </div>
            </div>
        </header>
        
        <!-- Dashboard Content -->
        <div class="dashboard-container">
            <!-- Welcome Card -->
            <div class="welcome-card">
                <div class="welcome-content">
                    <h2><?= $tr['welcome'] ?>, <?= htmlspecialchars($user['username']) ?>! 👋</h2>
                    <p><?= $currentLang === 'fa' ? 'آماده‌اید تا مهارت تایپ خود را ارتقا دهید؟' : 'Ready to improve your typing skills?' ?></p>
                </div>
                <div class="user-level-card">
                    <div class="level-badge">
                        <div class="level-number"><?= $currentLevel ?></div>
                        <div class="level-label"><?= $tr['level'] ?></div>
                    </div>
                    <div class="xp-info">
                        <div class="xp-text">
                            <span class="xp-current"><?= number_format($currentXP) ?></span>
                            <span class="xp-label"><?= $tr['xp'] ?></span>
                        </div>
                        <div class="xp-progress-bar">
                            <div class="xp-progress-fill" style="width: <?= $progressPercent ?>%"></div>
                        </div>
                        <div class="xp-next"><?= number_format($xpNeeded) ?> <?= $tr['next_level'] ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📚</div>
                    <div class="stat-content">
                        <div class="stat-value"><?= $stats['lessons_completed'] ?></div>
                        <div class="stat-label"><?= $tr['lessons_completed'] ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">⚡</div>
                    <div class="stat-content">
                        <div class="stat-value"><?= $stats['avg_wpm'] ?></div>
                        <div class="stat-label"><?= $tr['avg_wpm'] ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">🎯</div>
                    <div class="stat-content">
                        <div class="stat-value"><?= $stats['avg_accuracy'] ?>%</div>
                        <div class="stat-label"><?= $tr['avg_accuracy'] ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">⏱️</div>
                    <div class="stat-content">
                        <div class="stat-value"><?= round($stats['total_time'] / 60) ?></div>
                        <div class="stat-label"><?= $tr['total_time'] ?> (<?= $tr['minutes'] ?>)</div>
                    </div>
                </div>
            </div>
            
            <!-- Two Column Layout -->
            <div class="two-column-layout">
                <!-- Recent Achievements -->
                <div class="card">
                    <div class="card-header">
                        <h3><?= $tr['recent_achievements'] ?></h3>
                        <a href="profile.php#achievements" class="btn-link"><?= $tr['view_all'] ?></a>
                    </div>
                    <div class="card-body">
                        <?php if (count($achievements) > 0): ?>
                            <div class="achievements-list">
                                <?php foreach (array_slice($achievements, 0, 5) as $achievement): ?>
                                    <div class="achievement-item">
                                        <div class="achievement-icon"><?= $achievement['icon'] ?></div>
                                        <div class="achievement-info">
                                            <div class="achievement-title">
                                                <?= $currentLang === 'fa' ? $achievement['title_fa'] : $achievement['title_en'] ?>
                                            </div>
                                            <div class="achievement-date">
                                                <?= date('Y/m/d', strtotime($achievement['earned_at'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon">🏆</div>
                                <p><?= $tr['no_achievements'] ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3><?= $tr['quick_actions'] ?></h3>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions-grid">
                            <a href="lessons.php" class="quick-action-btn">
                                <div class="quick-action-icon">📖</div>
                                <div class="quick-action-text"><?= $tr['start_lesson'] ?></div>
                            </a>
                            <a href="games.php" class="quick-action-btn">
                                <div class="quick-action-icon">🎮</div>
                                <div class="quick-action-text"><?= $tr['play_game'] ?></div>
                            </a>
                            <a href="stats.php" class="quick-action-btn">
                                <div class="quick-action-icon">📊</div>
                                <div class="quick-action-text"><?= $tr['view_stats'] ?></div>
                            </a>
                            <a href="profile.php" class="quick-action-btn">
                                <div class="quick-action-icon">👤</div>
                                <div class="quick-action-text"><?= $tr['edit_profile'] ?></div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Toast -->
    <div class="toast" id="toast">
        <span class="toast-icon">✓</span>
        <span class="toast-message"></span>
    </div>
    
    <script src="./assets/js/app.js"></script>
    <script src="./assets/js/dashboard.js"></script>
    <script>
        const APP_CONFIG = {
            siteUrl: '<?= SITE_URL ?>',
            language: '<?= $currentLang ?>',
            theme: '<?= $currentTheme ?>'
        };
    </script>
</body>
</html>