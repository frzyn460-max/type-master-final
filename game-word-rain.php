<?php
/**
 * ====================================
 * Word Rain Game
 * بازی بارش کلمات
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

// دریافت بهترین امتیاز
$db = Database::getInstance();
$conn = $db->getConnection();

$sql = "SELECT MAX(score) as best_score, MAX(level_reached) as max_level 
        FROM game_scores 
        WHERE user_id = :user_id AND game_type = 'word-rain'";
$stmt = $conn->prepare($sql);
$stmt->execute(['user_id' => $user['id']]);
$bestStats = $stmt->fetch(PDO::FETCH_ASSOC);

// ترجمه‌ها
$t = [
    'fa' => [
        'word_rain' => 'بارش کلمات',
        'back' => 'بازگشت',
        'score' => 'امتیاز',
        'level' => 'سطح',
        'lives' => 'جان',
        'combo' => 'کمبو',
        'best' => 'بهترین',
        'start_game' => 'شروع بازی',
        'game_over' => 'بازی تمام شد!',
        'pause' => 'توقف',
        'resume' => 'ادامه',
        'restart' => 'شروع مجدد',
        'type_here' => 'کلمه را تایپ کنید...',
        'instructions_title' => 'راهنما',
        'inst_1' => '🌧️ کلمات از بالا می‌ریزند',
        'inst_2' => '⌨️ قبل از رسیدن به زمین تایپ کن',
        'inst_3' => '❤️ هر کلمه از دست رفته = یک جان کم',
        'inst_4' => '🔥 کمبوهای بالا = امتیاز بیشتر',
        'inst_5' => '⚡ هر سطح سریع‌تر می‌شود',
        'final_score' => 'امتیاز نهایی',
        'new_record' => 'رکورد جدید!',
        'try_again' => 'دوباره تلاش کن',
        'main_menu' => 'منوی اصلی'
    ],
    'en' => [
        'word_rain' => 'Word Rain',
        'back' => 'Back',
        'score' => 'Score',
        'level' => 'Level',
        'lives' => 'Lives',
        'combo' => 'Combo',
        'best' => 'Best',
        'start_game' => 'Start Game',
        'game_over' => 'Game Over!',
        'pause' => 'Pause',
        'resume' => 'Resume',
        'restart' => 'Restart',
        'type_here' => 'Type the word...',
        'instructions_title' => 'Instructions',
        'inst_1' => '🌧️ Words fall from above',
        'inst_2' => '⌨️ Type them before they hit ground',
        'inst_3' => '❤️ Each missed word = 1 life lost',
        'inst_4' => '🔥 High combos = more points',
        'inst_5' => '⚡ Each level gets faster',
        'final_score' => 'Final Score',
        'new_record' => 'New Record!',
        'try_again' => 'Try Again',
        'main_menu' => 'Main Menu'
    ]
];

$tr = $t[$currentLang];
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>" dir="<?= $currentLang === 'fa' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tr['word_rain'] ?> - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/themes.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/games.css">
    <style>
        body {
            overflow: hidden;
            margin: 0;
            padding: 0;
        }
        
        .game-wrapper {
            display: flex;
            flex-direction: column;
            height: 100vh;
            background: var(--bg-primary);
        }
        
        .game-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background: var(--bg-secondary);
            border-bottom: 2px solid var(--border-color);
            flex-shrink: 0;
        }
        
        .game-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .game-stats {
            display: flex;
            gap: 20px;
            background: var(--bg-tertiary);
            padding: 10px 20px;
            border-radius: 10px;
        }
        
        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
        }
        
        .stat-label {
            font-size: 0.7rem;
            color: var(--text-secondary);
            text-transform: uppercase;
        }
        
        .stat-value {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .lives-display {
            font-size: 1.5rem;
        }
        
        .game-container {
            flex: 1;
            position: relative;
            overflow: hidden;
            background: linear-gradient(180deg, 
                rgba(99, 102, 241, 0.05) 0%, 
                rgba(139, 92, 246, 0.05) 100%);
        }
        
        .falling-word {
            position: absolute;
            font-size: 1.5rem;
            font-weight: 700;
            padding: 10px 20px;
            background: var(--bg-secondary);
            border: 2px solid var(--primary);
            border-radius: 10px;
            color: var(--text-primary);
            animation: fall linear;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
            user-select: none;
            font-family: 'Courier New', monospace;
        }
        
        @keyframes fall {
            from { top: -50px; }
            to { top: 100%; }
        }
        
        .falling-word.matched {
            border-color: var(--success);
            background: rgba(16, 185, 129, 0.1);
        }
        
        .falling-word.explode {
            animation: explode 0.5s ease-out;
        }
        
        @keyframes explode {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            100% {
                transform: scale(2);
                opacity: 0;
            }
        }
        
        .game-input-zone {
            padding: 20px 30px;
            background: var(--bg-secondary);
            border-top: 2px solid var(--border-color);
            flex-shrink: 0;
        }
        
        .game-input {
            width: 100%;
            padding: 15px 20px;
            font-size: 1.3rem;
            background: var(--bg-tertiary);
            border: 2px solid var(--border-color);
            border-radius: 10px;
            color: var(--text-primary);
            text-align: center;
            font-family: 'Courier New', monospace;
        }
        
        .game-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }
        
        .welcome-overlay,
        .gameover-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 100;
        }
        
        .overlay-content {
            background: var(--bg-secondary);
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            max-width: 500px;
            border: 2px solid var(--border-color);
        }
        
        .overlay-icon {
            font-size: 5rem;
            margin-bottom: 20px;
            animation: bounce 1s infinite;
        }
        
        .overlay-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .instructions {
            text-align: right;
            margin: 20px 0;
            padding: 20px;
            background: var(--bg-tertiary);
            border-radius: 10px;
        }
        
        .instructions li {
            padding: 8px 0;
            color: var(--text-secondary);
            line-height: 1.6;
        }
        
        .combo-display {
            position: absolute;
            top: 20%;
            left: 50%;
            transform: translateX(-50%);
            font-size: 3rem;
            font-weight: 900;
            color: var(--warning);
            text-shadow: 0 0 20px rgba(245, 158, 11, 0.5);
            animation: comboAnim 1s ease-out;
            pointer-events: none;
        }
        
        @keyframes comboAnim {
            0% {
                transform: translateX(-50%) scale(0);
                opacity: 0;
            }
            50% {
                transform: translateX(-50%) scale(1.2);
                opacity: 1;
            }
            100% {
                transform: translateX(-50%) scale(1);
                opacity: 0;
            }
        }
    </style>
</head>
<body class="theme-<?= $currentTheme ?> lang-<?= $currentLang ?>">
    
    <div class="game-wrapper">
        <!-- Header -->
        <header class="game-header">
            <div>
                <a href="games.php" class="btn btn-secondary btn-sm">
                    <?= $currentLang === 'fa' ? '→' : '←' ?> <?= $tr['back'] ?>
                </a>
            </div>
            
            <h1 class="game-title">
                <span>🌧️</span>
                <span><?= $tr['word_rain'] ?></span>
            </h1>
            
            <div class="game-stats">
                <div class="stat-item">
                    <span class="stat-label"><?= $tr['score'] ?></span>
                    <span class="stat-value" id="scoreDisplay">0</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label"><?= $tr['level'] ?></span>
                    <span class="stat-value" id="levelDisplay">1</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label"><?= $tr['lives'] ?></span>
                    <span class="lives-display" id="livesDisplay">❤️❤️❤️</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label"><?= $tr['combo'] ?></span>
                    <span class="stat-value" id="comboDisplay">0</span>
                </div>
            </div>
        </header>
        
        <!-- Game Container -->
        <div class="game-container" id="gameContainer">
            <!-- Welcome Screen -->
            <div class="welcome-overlay" id="welcomeScreen">
                <div class="overlay-content">
                    <div class="overlay-icon">🌧️</div>
                    <h2 class="overlay-title"><?= $tr['word_rain'] ?></h2>
                    
                    <ul class="instructions">
                        <li><?= $tr['inst_1'] ?></li>
                        <li><?= $tr['inst_2'] ?></li>
                        <li><?= $tr['inst_3'] ?></li>
                        <li><?= $tr['inst_4'] ?></li>
                        <li><?= $tr['inst_5'] ?></li>
                    </ul>
                    
                    <?php if ($bestStats && $bestStats['best_score']): ?>
                        <div style="margin: 20px 0; padding: 15px; background: var(--bg-tertiary); border-radius: 10px;">
                            <div style="color: var(--text-secondary); margin-bottom: 5px;">
                                <?= $tr['best'] ?>:
                            </div>
                            <div style="font-size: 2rem; font-weight: 700; color: var(--primary);">
                                <?= number_format($bestStats['best_score']) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <button class="btn btn-primary btn-lg" id="startBtn">
                        🚀 <?= $tr['start_game'] ?>
                    </button>
                </div>
            </div>
            
            <!-- Game Over Screen -->
            <div class="gameover-overlay" id="gameoverScreen" style="display: none;">
                <div class="overlay-content">
                    <div class="overlay-icon" id="gameoverIcon">💀</div>
                    <h2 class="overlay-title"><?= $tr['game_over'] ?></h2>
                    
                    <div style="margin: 30px 0;">
                        <div style="color: var(--text-secondary); margin-bottom: 10px;">
                            <?= $tr['final_score'] ?>:
                        </div>
                        <div style="font-size: 3rem; font-weight: 900; color: var(--primary);">
                            <span id="finalScore">0</span>
                        </div>
                        <div id="newRecordBadge" style="display: none; margin-top: 10px;">
                            <span style="background: var(--warning); color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.9rem;">
                                🏆 <?= $tr['new_record'] ?>
                            </span>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 10px; justify-content: center;">
                        <button class="btn btn-primary" id="restartBtn">
                            🔄 <?= $tr['try_again'] ?>
                        </button>
                        <a href="games.php" class="btn btn-secondary">
                            🏠 <?= $tr['main_menu'] ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Input Zone -->
        <div class="game-input-zone">
            <input 
                type="text" 
                id="gameInput" 
                class="game-input" 
                placeholder="<?= $tr['type_here'] ?>"
                autocomplete="off"
                disabled
            >
        </div>
    </div>
    
    <script>
        window.GAME_CONFIG = {
            siteUrl: '<?= SITE_URL ?>',
            userId: <?= $user['id'] ?>,
            language: '<?= $currentLang ?>',
            soundEnabled: <?= $user['sound_enabled'] ? 'true' : 'false' ?>,
            translations: <?= json_encode($tr, JSON_UNESCAPED_UNICODE) ?>,
            bestScore: <?= $bestStats['best_score'] ?? 0 ?>
        };
    </script>
    <script src="<?= SITE_URL ?>/assets/js/game-word-rain.js"></script>
</body>
</html>