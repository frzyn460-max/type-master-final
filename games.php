<?php
/**
 * ====================================
 * Games Page
 * صفحه بازی‌های تایپینگ
 * ====================================
 */

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

// دریافت آمار بازی‌های کاربر
$db = Database::getInstance();
$conn = $db->getConnection();

$sql = "SELECT 
    game_type,
    MAX(score) as best_score,
    MAX(wpm) as best_wpm,
    MAX(accuracy) as best_accuracy,
    MAX(level_reached) as max_level,
    COUNT(*) as plays
FROM game_scores 
WHERE user_id = :user_id
GROUP BY game_type";

$stmt = $conn->prepare($sql);
$stmt->execute(['user_id' => $user['id']]);

$gameStats = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $gameStats[$row['game_type']] = $row;
}

// ترجمه‌ها
$t = [
    'fa' => [
        'games' => 'بازی‌ها',
        'typing_games' => 'بازی‌های تایپینگ',
        'subtitle' => 'مهارت‌های خود را به چالش بکشید',
        'play' => 'بازی کن',
        'best_score' => 'بهترین امتیاز',
        'plays' => 'بازی',
        'level' => 'سطح',
        'new' => 'جدید',

        // بازی 1
        'word_rain' => 'بارش کلمات',
        'word_rain_desc' => 'کلمات از آسمان می‌بارند! قبل از اینکه به زمین برسند تایپ کن',
        'word_rain_features' => '⚡ سرعت بالا | 🎯 دقت | 🏆 امتیاز',

        // بازی 2
        'speed_race' => 'مسابقه سرعت',
        'speed_race_desc' => 'در زمان محدود بیشترین کلمه را تایپ کن و رکورد بزن',
        'speed_race_features' => '⏱️ زمان محدود | 🔥 هیجان | 🥇 رقابت',

        // بازی 3
        'accuracy' => 'چالش دقت',
        'accuracy_desc' => 'با حداقل خطا، کلمات پیچیده را تایپ کن و دقت خود را ثابت کن',
        'accuracy_features' => '🎯 دقت 100% | 💎 کلمات سخت | ⭐ چالش',

        // بازی 4
        'survival' => 'حالت بقا',
        'survival_desc' => 'تا جایی که می‌توانی ادامه بده! هر اشتباه جان کم می‌شود',
        'survival_features' => '❤️ جان محدود | 📈 سختی افزایش | 🎮 بقا',

        // بازی 5 - جدید!
        'code_master' => 'استاد کدنویسی',
        'code_master_desc' => 'کدهای واقعی برنامه‌نویسی تایپ کن! 7 زبان، 3 سطح',
        'code_master_features' => '💻 کدهای واقعی | 🚀 7 زبان | 🎯 چالش',
    ],
    'en' => [
        'games' => 'Games',
        'typing_games' => 'Typing Games',
        'subtitle' => 'Challenge your skills',
        'play' => 'Play',
        'best_score' => 'Best Score',
        'plays' => 'Plays',
        'level' => 'Level',
        'new' => 'New',

        'word_rain' => 'Word Rain',
        'word_rain_desc' => 'Words fall from the sky! Type them before they hit the ground',
        'word_rain_features' => '⚡ High Speed | 🎯 Accuracy | 🏆 Score',

        'speed_race' => 'Speed Race',
        'speed_race_desc' => 'Type as many words as possible in limited time and break records',
        'speed_race_features' => '⏱️ Time Limit | 🔥 Excitement | 🥇 Competition',

        'accuracy' => 'Accuracy Challenge',
        'accuracy_desc' => 'Type complex words with minimal errors and prove your precision',
        'accuracy_features' => '🎯 100% Accuracy | 💎 Hard Words | ⭐ Challenge',

        'survival' => 'Survival Mode',
        'survival_desc' => 'Keep going as long as you can! Each mistake costs a life',
        'survival_features' => '❤️ Limited Lives | 📈 Increasing Difficulty | 🎮 Survival',

        // Game 5 - New!
        'code_master' => 'Code Master',
        'code_master_desc' => 'Type real code snippets! 7 languages, 3 levels',
        'code_master_features' => '💻 Real Code | 🚀 7 Languages | 🎯 Challenge',
    ]
];

$tr = $t[$currentLang];

// تعریف بازی‌ها
$games = [
    [
        'id' => 'word-rain',
        'title' => $tr['word_rain'],
        'desc' => $tr['word_rain_desc'],
        'features' => $tr['word_rain_features'],
        'icon' => '🌧️',
        'color' => '#3b82f6',
        'available' => true,
        'file' => 'game-word-rain.php'
    ],
    [
        'id' => 'speed-race',
        'title' => $tr['speed_race'],
        'desc' => $tr['speed_race_desc'],
        'features' => $tr['speed_race_features'],
        'icon' => '🏎️',
        'color' => '#ef4444',
        'available' => true,
        'file' => 'game-speed-race.php'
    ],
    [
        'id' => 'accuracy',
        'title' => $tr['accuracy'],
        'desc' => $tr['accuracy_desc'],
        'features' => $tr['accuracy_features'],
        'icon' => '🎯',
        'color' => '#10b981',
        'available' => true,
        'file' => 'game-accuracy.php'
    ],
    [
        'id' => 'survival',
        'title' => $tr['survival'],
        'desc' => $tr['survival_desc'],
        'features' => $tr['survival_features'],
        'icon' => '❤️',
        'color' => '#f59e0b',
        'available' => true,
        'file' => 'game-survival.php'
    ],

    [
        'id' => 'code-master',
        'title' => $tr['code_master'],
        'desc' => $tr['code_master_desc'],
        'features' => $tr['code_master_features'],
        'icon' => '💻',
        'color' => '#8b5cf6',
        'available' => true,
        'file' => 'game-code-master.php'
    ]
];
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>" dir="<?= $currentLang === 'fa' ? 'rtl' : 'ltr' ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tr['games'] ?> - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/themes.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/games.css">
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
                <h1 class="page-title"><?= $tr['games'] ?></h1>
            </div>
            <div class="top-bar-right">
                <button class="btn-icon" id="themeToggle">
                    <span class="icon"></span>
                </button>
                <button class="btn-icon" id="langToggle">
                    <span class="icon"><?= $currentLang === 'fa' ? '🇬🇧' : '🇮🇷' ?></span>
                </button>
                <div class="user-menu">
                    <img src="./uploads/avatars/<?= $user['avatar'] ?>" alt="Avatar" class="user-avatar"
                        onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($user['username']) ?>&background=6366f1&color=fff'">
                    <span class="user-name"><?= htmlspecialchars($user['username']) ?></span>
                </div>
            </div>
        </header>

        <!-- Games Content -->
        <div class="dashboard-container">
            <!-- Hero Section -->
            <div class="games-hero">
                <div class="hero-content">
                    <h2 class="hero-title"><?= $tr['typing_games'] ?></h2>
                    <p class="hero-subtitle"><?= $tr['subtitle'] ?></p>
                </div>
                <div class="hero-icon">🎮</div>
            </div>

            <!-- Games Grid -->
            <div class="games-grid">
                <?php foreach ($games as $game):
                    $stats = $gameStats[$game['id']] ?? null;
                    ?>
                    <div class="game-card <?= $game['available'] ? '' : 'coming-soon' ?>"
                        style="--game-color: <?= $game['color'] ?>">

                        <!-- Card Header -->
                        <div class="game-card-header">
                            <div class="game-icon"><?= $game['icon'] ?></div>
                            <?php if (!$game['available']): ?>
                                <span class="badge badge-warning"><?= $tr['coming_soon'] ?></span>
                            <?php elseif (!$stats): ?>
                                <span class="badge badge-info"><?= $tr['new'] ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Card Body -->
                        <div class="game-card-body">
                            <h3 class="game-title"><?= $game['title'] ?></h3>
                            <p class="game-desc"><?= $game['desc'] ?></p>
                            <div class="game-features"><?= $game['features'] ?></div>
                        </div>

                        <!-- Card Stats -->
                        <?php if ($stats): ?>
                            <div class="game-stats">
                                <div class="game-stat">
                                    <span class="stat-label"><?= $tr['best_score'] ?></span>
                                    <span class="stat-value"><?= number_format($stats['best_score']) ?></span>
                                </div>
                                <div class="game-stat">
                                    <span class="stat-label"><?= $tr['plays'] ?></span>
                                    <span class="stat-value"><?= $stats['plays'] ?></span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Card Footer -->
                        <div class="game-card-footer">
                            <?php if ($game['available']): ?>
                                <a href="<?= $game['file'] ?>" class="btn btn-primary btn-block">
                                    🎮 <?= $tr['play'] ?>
                                </a>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-block" disabled>
                                    <?= $tr['coming_soon'] ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <script src="<?= SITE_URL ?>/assets/js/app.js"></script>
    <script src="<?= SITE_URL ?>/assets/js/dashboard.js"></script>
</body>

</html>