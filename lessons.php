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

// دریافت فیلترها
$filterLevel = isset($_GET['level']) ? $_GET['level'] : null;
$filterLanguage = isset($_GET['lang']) ? $_GET['lang'] : $currentLang;

// دریافت درس‌ها
$lessons = getLessons($filterLanguage, $filterLevel);

// دریافت پیشرفت کاربر برای همه درس‌ها
$userProgressList = [];
foreach ($lessons as $lesson) {
    $progress = getUserLessonProgress($user['id'], $lesson['id']);
    $userProgressList[$lesson['id']] = $progress;
}

// ترجمه‌ها
$t = [
    'fa' => [
        'lessons' => 'درس‌ها',
        'all_levels' => 'همه سطوح',
        'beginner' => 'مبتدی',
        'intermediate' => 'متوسط',
        'advanced' => 'پیشرفته',
        'persian' => 'فارسی',
        'english' => 'انگلیسی',
        'filter' => 'فیلتر',
        'start' => 'شروع',
        'continue' => 'ادامه',
        'completed' => 'تکمیل شده',
        'locked' => 'قفل',
        'unlock_at_level' => 'در سطح :level باز می‌شود',
        'best_wpm' => 'بهترین سرعت',
        'best_accuracy' => 'بهترین دقت',
        'attempts' => 'تلاش',
        'xp_reward' => 'پاداش امتیاز',
        'lesson' => 'درس',
        'no_lessons' => 'درسی یافت نشد',
        'total_lessons' => 'مجموع درس‌ها',
        'completed_count' => 'تکمیل شده',
        'in_progress' => 'در حال انجام'
    ],
    'en' => [
        'lessons' => 'Lessons',
        'all_levels' => 'All Levels',
        'beginner' => 'Beginner',
        'intermediate' => 'Intermediate',
        'advanced' => 'Advanced',
        'persian' => 'Persian',
        'english' => 'English',
        'filter' => 'Filter',
        'start' => 'Start',
        'continue' => 'Continue',
        'completed' => 'Completed',
        'locked' => 'Locked',
        'unlock_at_level' => 'Unlocks at level :level',
        'best_wpm' => 'Best WPM',
        'best_accuracy' => 'Best Accuracy',
        'attempts' => 'Attempts',
        'xp_reward' => 'XP Reward',
        'lesson' => 'Lesson',
        'no_lessons' => 'No lessons found',
        'total_lessons' => 'Total Lessons',
        'completed_count' => 'Completed',
        'in_progress' => 'In Progress'
    ]
];

$tr = $t[$currentLang];

// محاسبه آمار
$totalLessons = count($lessons);
$completedLessons = 0;
$inProgressLessons = 0;

foreach ($lessons as $lesson) {
    $progress = $userProgressList[$lesson['id']] ?? null;
    if ($progress && $progress['is_completed']) {
        $completedLessons++;
    } elseif ($progress && $progress['attempts'] > 0) {
        $inProgressLessons++;
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>" dir="<?= $currentLang === 'fa' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tr['lessons'] ?> - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/themes.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/lessons.css">
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
                <h1 class="page-title"><?= $tr['lessons'] ?></h1>
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
        
        <!-- Lessons Content -->
        <div class="dashboard-container">
            <!-- Stats Overview -->
            <div class="lessons-overview">
                <div class="overview-card">
                    <div class="overview-icon">📚</div>
                    <div class="overview-content">
                        <div class="overview-value"><?= $totalLessons ?></div>
                        <div class="overview-label"><?= $tr['total_lessons'] ?></div>
                    </div>
                </div>
                <div class="overview-card">
                    <div class="overview-icon">✅</div>
                    <div class="overview-content">
                        <div class="overview-value"><?= $completedLessons ?></div>
                        <div class="overview-label"><?= $tr['completed_count'] ?></div>
                    </div>
                </div>
                <div class="overview-card">
                    <div class="overview-icon">📝</div>
                    <div class="overview-content">
                        <div class="overview-value"><?= $inProgressLessons ?></div>
                        <div class="overview-label"><?= $tr['in_progress'] ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="lessons-filters">
                <form method="GET" class="filter-form">
                    <div class="filter-group">
                        <label><?= $tr['filter'] ?>:</label>
                        <select name="level" class="form-select" onchange="this.form.submit()">
                            <option value=""><?= $tr['all_levels'] ?></option>
                            <option value="beginner" <?= $filterLevel === 'beginner' ? 'selected' : '' ?>><?= $tr['beginner'] ?></option>
                            <option value="intermediate" <?= $filterLevel === 'intermediate' ? 'selected' : '' ?>><?= $tr['intermediate'] ?></option>
                            <option value="advanced" <?= $filterLevel === 'advanced' ? 'selected' : '' ?>><?= $tr['advanced'] ?></option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label><?= $currentLang === 'fa' ? 'زبان:' : 'Language:' ?></label>
                        <select name="lang" class="form-select" onchange="this.form.submit()">
                            <option value="fa" <?= $filterLanguage === 'fa' ? 'selected' : '' ?>><?= $tr['persian'] ?></option>
                            <option value="en" <?= $filterLanguage === 'en' ? 'selected' : '' ?>><?= $tr['english'] ?></option>
                        </select>
                    </div>
                </form>
            </div>
            
            <!-- Lessons Grid -->
            <?php if (count($lessons) > 0): ?>
                <div class="lessons-grid">
                    <?php foreach ($lessons as $lesson): 
                        $progress = $userProgressList[$lesson['id']] ?? null;
                        $isCompleted = $progress && $progress['is_completed'];
                        $isLocked = $user['level'] < $lesson['required_level'];
                        $title = $currentLang === 'fa' ? $lesson['title_fa'] : $lesson['title_en'];
                        $description = $currentLang === 'fa' ? $lesson['description_fa'] : $lesson['description_en'];
                    ?>
                        <div class="lesson-card <?= $isCompleted ? 'completed' : '' ?> <?= $isLocked ? 'locked' : '' ?>">
                            <!-- Lesson Header -->
                            <div class="lesson-header">
                                <div class="lesson-number"><?= $lesson['order_index'] ?></div>
                                <div class="lesson-level <?= $lesson['level'] ?>">
                                    <?= $tr[$lesson['level']] ?>
                                </div>
                            </div>
                            
                            <!-- Lesson Body -->
                            <div class="lesson-body">
                                <h3 class="lesson-title"><?= htmlspecialchars($title) ?></h3>
                                <p class="lesson-description"><?= htmlspecialchars($description) ?></p>
                                
                                <!-- Progress Info -->
                                <?php if ($progress && !$isLocked): ?>
                                    <div class="lesson-progress-info">
                                        <?php if ($isCompleted): ?>
                                            <div class="lesson-stars">
                                                <?php for ($i = 0; $i < 3; $i++): ?>
                                                    <span class="star"><?= $i < $progress['stars'] ? '⭐' : '☆' ?></span>
                                                <?php endfor; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="lesson-stats-mini">
                                            <span><?= $tr['best_wpm'] ?>: <strong><?= $progress['best_wpm'] ?></strong></span>
                                            <span><?= $tr['best_accuracy'] ?>: <strong><?= $progress['best_accuracy'] ?>%</strong></span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Locked Info -->
                                <?php if ($isLocked): ?>
                                    <div class="lesson-locked-info">
                                        <div class="lock-icon">🔒</div>
                                        <p><?= str_replace(':level', $lesson['required_level'], $tr['unlock_at_level']) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Lesson Footer -->
                            <div class="lesson-footer">
                                <div class="lesson-xp">
                                    +<?= $lesson['xp_reward'] ?> XP
                                </div>
                                
                                <?php if (!$isLocked): ?>
                                    <a href="practice.php?lesson=<?= $lesson['id'] ?>" class="btn btn-primary" style="text-decoration: none;">
                                        <?php if ($currentLang === 'fa'): ?>
                                            <?= $isCompleted ? $tr['continue'] : $tr['start'] ?> ←
                                        <?php else: ?>
                                            <?= $isCompleted ? $tr['continue'] : $tr['start'] ?> →
                                        <?php endif; ?>
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-secondary" disabled>
                                        <?= $tr['locked'] ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">📚</div>
                    <p><?= $tr['no_lessons'] ?></p>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <div class="toast" id="toast">
        <span class="toast-icon">✓</span>
        <span class="toast-message"></span>
    </div>
    
    <script src="<?= SITE_URL ?>/assets/js/app.js"></script>
    <script src="<?= SITE_URL ?>/assets/js/dashboard.js"></script>
    <script>
        const APP_CONFIG = {
            siteUrl: '<?= SITE_URL ?>',
            language: '<?= $currentLang ?>',
            theme: '<?= $currentTheme ?>'
        };
    </script>
</body>
</html>