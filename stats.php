<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth-check.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

requireLogin();

$user = getCurrentUser();
$currentLang = getCurrentLanguage();
$currentTheme = getCurrentTheme();

// دریافت آمار کامل
$db = Database::getInstance();
$conn = $db->getConnection();

// آمار کلی
$stats = getUserStats($user['id']);

// محاسبه پیشرفت تا سطح بعدی
$currentLevel = $user['level'];
$xpForNextLevel = $currentLevel * XP_PER_LEVEL;
$xpProgress = $user['total_xp'] % XP_PER_LEVEL;
$progressPercent = ($xpProgress / XP_PER_LEVEL) * 100;

// آمار 30 روز اخیر
$sql = "SELECT 
    DATE(updated_at) as date,
    AVG(wpm) as avg_wpm,
    AVG(accuracy) as avg_accuracy,
    COUNT(DISTINCT lesson_id) as lessons_count
FROM user_progress
WHERE user_id = :user_id AND updated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY DATE(updated_at)
ORDER BY date ASC";
$stmt = $conn->prepare($sql);
$stmt->execute(['user_id' => $user['id']]);
$monthlyStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// پیشرفت درس‌ها
$sql = "SELECT 
    l.level,
    COUNT(*) as total,
    SUM(CASE WHEN up.is_completed = 1 THEN 1 ELSE 0 END) as completed
FROM lessons l
LEFT JOIN user_progress up ON l.id = up.lesson_id AND up.user_id = :user_id
WHERE l.is_active = 1
GROUP BY l.level";
$stmt = $conn->prepare($sql);
$stmt->execute(['user_id' => $user['id']]);
$lessonsByLevel = $stmt->fetchAll(PDO::FETCH_ASSOC);

// بهترین 10 نتیجه
$sql = "SELECT 
    l.title_fa,
    l.title_en,
    l.level,
    up.best_wpm,
    up.best_accuracy,
    up.stars
FROM user_progress up
JOIN lessons l ON up.lesson_id = l.id
WHERE up.user_id = :user_id AND up.is_completed = 1
ORDER BY up.best_wpm DESC
LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->execute(['user_id' => $user['id']]);
$topLessons = $stmt->fetchAll(PDO::FETCH_ASSOC);

// نشان‌ها
$achievements = getUserAchievements($user['id']);

// رتبه کاربر
$sql = "SELECT COUNT(*) + 1 as rank
FROM users
WHERE total_xp > :user_xp";
$stmt = $conn->prepare($sql);
$stmt->execute(['user_xp' => $user['total_xp']]);
$userRank = $stmt->fetch(PDO::FETCH_ASSOC)['rank'];

// ترجمه‌ها
$t = [
    'fa' => [
        'statistics' => 'آمار من',
        'level' => 'سطح',
        'rank' => 'رتبه',
        'global' => 'جهانی',
        'xp' => 'امتیاز',
        'to_next_level' => 'تا سطح بعدی',
        'performance_overview' => 'عملکرد کلی',
        'lessons_completed' => 'درس تکمیل شده',
        'total_time' => 'زمان تمرین',
        'hours' => 'ساعت',
        'avg_wpm' => 'میانگین سرعت',
        'wpm' => 'کلمه/دقیقه',
        'avg_accuracy' => 'میانگین دقت',
        'monthly_progress' => 'پیشرفت ماهانه',
        'speed_wpm' => 'سرعت (WPM)',
        'accuracy_percent' => 'دقت (%)',
        'lessons_by_level' => 'پیشرفت بر اساس سطح',
        'beginner' => 'مبتدی',
        'intermediate' => 'متوسط',
        'advanced' => 'پیشرفته',
        'total' => 'کل',
        'completed' => 'تکمیل شده',
        'top_10_performances' => '10 عملکرد برتر',
        'lesson' => 'درس',
        'speed' => 'سرعت',
        'my_achievements' => 'نشان‌های من',
        'badges_earned' => 'نشان کسب شده',
        'no_data' => 'هنوز داده‌ای ثبت نشده',
        'start_learning' => 'شروع یادگیری کنید!',
        'no_achievements' => 'هنوز نشانی کسب نکرده‌اید'
    ],
    'en' => [
        'statistics' => 'My Statistics',
        'level' => 'Level',
        'rank' => 'Rank',
        'global' => 'Global',
        'xp' => 'XP',
        'to_next_level' => 'To Next Level',
        'performance_overview' => 'Performance Overview',
        'lessons_completed' => 'Lessons Completed',
        'total_time' => 'Practice Time',
        'hours' => 'hours',
        'avg_wpm' => 'Average Speed',
        'wpm' => 'WPM',
        'avg_accuracy' => 'Average Accuracy',
        'monthly_progress' => 'Monthly Progress',
        'speed_wpm' => 'Speed (WPM)',
        'accuracy_percent' => 'Accuracy (%)',
        'lessons_by_level' => 'Progress by Level',
        'beginner' => 'Beginner',
        'intermediate' => 'Intermediate',
        'advanced' => 'Advanced',
        'total' => 'Total',
        'completed' => 'Completed',
        'top_10_performances' => 'Top 10 Performances',
        'lesson' => 'Lesson',
        'speed' => 'Speed',
        'my_achievements' => 'My Achievements',
        'badges_earned' => 'Badges Earned',
        'no_data' => 'No data yet',
        'start_learning' => 'Start learning now!',
        'no_achievements' => 'No achievements yet'
    ]
];

$tr = $t[$currentLang];

// آماده‌سازی داده نمودارها
$chartLabels = [];
$chartWpm = [];
$chartAccuracy = [];
foreach ($monthlyStats as $day) {
    $chartLabels[] = date($currentLang === 'fa' ? 'Y/m/d' : 'm/d', strtotime($day['date']));
    $chartWpm[] = round($day['avg_wpm'], 1);
    $chartAccuracy[] = round($day['avg_accuracy'], 1);
}

$levelLabels = [];
$levelTotal = [];
$levelCompleted = [];
foreach ($lessonsByLevel as $level) {
    $levelLabels[] = $tr[$level['level']];
    $levelTotal[] = $level['total'];
    $levelCompleted[] = $level['completed'];
}
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>" dir="<?= $currentLang === 'fa' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tr['statistics'] ?> - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/themes.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/stats.css">
</head>
<body class="theme-<?= $currentTheme ?> lang-<?= $currentLang ?>">
    
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>
    
    <main class="main-content">
        <header class="top-bar">
            <div class="top-bar-left">
                <button class="btn-icon mobile-menu-btn" id="mobileMenuBtn">
                    <span>☰</span>
                </button>
                <h1 class="page-title">📊 <?= $tr['statistics'] ?></h1>
            </div>
            <div class="top-bar-right">
                <button class="btn-icon" id="themeToggle">
                    <span class="icon"></span>
                </button>
                <button class="btn-icon" id="langToggle">
                    <span class="icon"><?= $currentLang === 'fa' ? '🇬🇧' : '🇮🇷' ?></span>
                </button>
                <div class="user-menu">
                    <img src="./uploads/avatars/<?= $user['avatar'] ?>" alt="Avatar" class="user-avatar" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($user['username']) ?>&background=6366f1&color=fff'">
                    <span class="user-name"><?= htmlspecialchars($user['username']) ?></span>
                </div>
            </div>
        </header>
        
        <div class="dashboard-container">
            <!-- Hero Section -->
            <div class="stats-hero">
                <div class="stats-hero-content">
                    <div class="stats-hero-top">
                        <div class="stats-user-info">
                            <h2>👋 <?= htmlspecialchars($user['username']) ?></h2>
                            <div class="stats-user-meta">
                                <div class="stats-user-meta-item">
                                    <span>🏆 <?= $tr['level'] ?></span>
                                    <strong><?= $currentLevel ?></strong>
                                </div>
                                <div class="stats-user-meta-item">
                                    <span>🌟 <?= $tr['rank'] ?></span>
                                    <strong>#<?= $userRank ?> <?= $tr['global'] ?></strong>
                                </div>
                            </div>
                        </div>

                        <div class="stats-xp-progress">
                            <div class="xp-bar">
                                <div class="xp-fill" style="width: <?= $progressPercent ?>%"></div>
                            </div>
                            <div class="xp-text">
                                <span><?= number_format($user['total_xp']) ?> <?= $tr['xp'] ?></span>
                                <span><?= number_format($xpForNextLevel - $xpProgress) ?> <?= $tr['to_next_level'] ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Overview -->
            <div class="performance-grid">
                <div class="perf-card">
                    <div class="perf-icon">📚</div>
                    <div class="perf-value"><?= $stats['lessons_completed'] ?></div>
                    <div class="perf-label"><?= $tr['lessons_completed'] ?></div>
                </div>

                <div class="perf-card">
                    <div class="perf-icon">⏱️</div>
                    <div class="perf-value"><?= round($stats['total_time'] / 3600, 1) ?></div>
                    <div class="perf-label"><?= $tr['total_time'] ?> (<?= $tr['hours'] ?>)</div>
                </div>

                <div class="perf-card">
                    <div class="perf-icon">⚡</div>
                    <div class="perf-value"><?= $stats['avg_wpm'] ?></div>
                    <div class="perf-label"><?= $tr['avg_wpm'] ?> (<?= $tr['wpm'] ?>)</div>
                </div>

                <div class="perf-card">
                    <div class="perf-icon">🎯</div>
                    <div class="perf-value"><?= $stats['avg_accuracy'] ?>%</div>
                    <div class="perf-label"><?= $tr['avg_accuracy'] ?></div>
                </div>
            </div>

            <!-- Charts -->
            <div class="charts-section">
                <!-- Monthly Progress - Full Width -->
                <div class="chart-box chart-full">
                    <div class="chart-header-fancy">
                        <h3 class="chart-title">📈 <?= $tr['monthly_progress'] ?></h3>
                        <div class="chart-subtitle"><?= $currentLang === 'fa' ? 'آخرین 30 روز' : 'Last 30 Days' ?></div>
                    </div>
                    <?php if (count($monthlyStats) > 0): ?>
                        <div class="chart-container-large">
                            <canvas id="progressChart"></canvas>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">📊</div>
                            <div class="empty-text"><?= $tr['no_data'] ?></div>
                            <div class="empty-subtext"><?= $tr['start_learning'] ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Level Progress Cards -->
            <div class="level-progress-section">
                <h3 class="chart-title">📊 <?= $tr['lessons_by_level'] ?></h3>
                <div class="level-cards-grid">
                    <?php 
                    $levelColors = [
                        'beginner' => ['icon' => '🌱', 'color' => '#10b981', 'bg' => 'rgba(16, 185, 129, 0.1)'],
                        'intermediate' => ['icon' => '🔥', 'color' => '#f59e0b', 'bg' => 'rgba(245, 158, 11, 0.1)'],
                        'advanced' => ['icon' => '⚡', 'color' => '#ef4444', 'bg' => 'rgba(239, 68, 68, 0.1)']
                    ];
                    
                    foreach ($lessonsByLevel as $level): 
                        $config = $levelColors[$level['level']];
                        $percent = $level['total'] > 0 ? round(($level['completed'] / $level['total']) * 100) : 0;
                    ?>
                        <div class="level-progress-card" style="--level-color: <?= $config['color'] ?>; --level-bg: <?= $config['bg'] ?>;">
                            <div class="level-card-header">
                                <div class="level-icon"><?= $config['icon'] ?></div>
                                <div class="level-name"><?= $tr[$level['level']] ?></div>
                            </div>
                            <div class="level-stats-big">
                                <div class="level-stat-item">
                                    <div class="level-stat-value"><?= $level['completed'] ?></div>
                                    <div class="level-stat-label"><?= $tr['completed'] ?></div>
                                </div>
                                <div class="level-stat-divider">/</div>
                                <div class="level-stat-item">
                                    <div class="level-stat-value"><?= $level['total'] ?></div>
                                    <div class="level-stat-label"><?= $tr['total'] ?></div>
                                </div>
                            </div>
                            <div class="level-progress-bar">
                                <div class="level-progress-fill" style="width: 0%" data-width="<?= $percent ?>%">
                                    <span class="level-progress-text"><?= $percent ?>%</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Top Performances -->
            <div class="top-performances">
                <h3 class="chart-title">🏆 <?= $tr['top_10_performances'] ?></h3>
                <?php if (count($topLessons) > 0): ?>
                    <div class="top-list">
                        <?php foreach ($topLessons as $index => $lesson): 
                            $rankClass = '';
                            if ($index === 0) $rankClass = 'gold';
                            elseif ($index === 1) $rankClass = 'silver';
                            elseif ($index === 2) $rankClass = 'bronze';
                        ?>
                            <div class="top-item">
                                <div class="top-rank <?= $rankClass ?>"><?= $index + 1 ?></div>
                                <div class="top-content">
                                    <div class="top-lesson-name">
                                        <?= $currentLang === 'fa' ? $lesson['title_fa'] : $lesson['title_en'] ?>
                                    </div>
                                    <div class="top-stats">
                                        <div class="top-stat">
                                            <span>⚡</span>
                                            <strong><?= $lesson['best_wpm'] ?></strong>
                                            <span>WPM</span>
                                        </div>
                                        <div class="top-stat">
                                            <span>🎯</span>
                                            <strong><?= $lesson['best_accuracy'] ?>%</strong>
                                        </div>
                                        <div class="stars">
                                            <?php for ($i = 0; $i < 3; $i++): ?>
                                                <span><?= $i < $lesson['stars'] ? '⭐' : '☆' ?></span>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">🏆</div>
                        <div class="empty-text"><?= $tr['no_data'] ?></div>
                        <div class="empty-subtext"><?= $tr['start_learning'] ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Achievements -->
            <div class="achievements-section">
                <h3 class="chart-title">🎖️ <?= $tr['my_achievements'] ?> (<?= count($achievements) ?>)</h3>
                <?php if (count($achievements) > 0): ?>
                    <div class="achievements-grid">
                        <?php foreach ($achievements as $achievement): ?>
                            <div class="achievement-badge">
                                <div class="badge-icon"><?= $achievement['icon'] ?></div>
                                <div class="badge-name">
                                    <?= $currentLang === 'fa' ? $achievement['title_fa'] : $achievement['title_en'] ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">🎖️</div>
                        <div class="empty-text"><?= $tr['no_achievements'] ?></div>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>

    <div class="toast" id="toast">
        <span class="toast-icon">✓</span>
        <span class="toast-message"></span>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="<?= SITE_URL ?>/assets/js/app.js"></script>
    <script src="<?= SITE_URL ?>/assets/js/dashboard.js"></script>
    <script>
        const APP_CONFIG = {
            siteUrl: '<?= SITE_URL ?>',
            language: '<?= $currentLang ?>',
            theme: '<?= $currentTheme ?>'
        };

        const CHART_DATA = {
            progress: {
                labels: <?= json_encode($chartLabels) ?>,
                wpm: <?= json_encode($chartWpm) ?>,
                accuracy: <?= json_encode($chartAccuracy) ?>
            },
            levels: {
                labels: <?= json_encode($levelLabels) ?>,
                total: <?= json_encode($levelTotal) ?>,
                completed: <?= json_encode($levelCompleted) ?>
            },
            translations: <?= json_encode($tr) ?>
        };
    </script>
    <script src="<?= SITE_URL ?>/assets/js/stats.js"></script>
</body>
</html>