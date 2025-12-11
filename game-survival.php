<?php
/**
 * ====================================
 * Survival Mode Game
 * بازی حالت بقا
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
        WHERE user_id = :user_id AND game_type = 'survival'";
$stmt = $conn->prepare($sql);
$stmt->execute(['user_id' => $user['id']]);
$bestStats = $stmt->fetch(PDO::FETCH_ASSOC);

// کلمات برای حالت بقا
$words_fa = [
    'کار', 'خانه', 'کتاب', 'مدرسه', 'دانشگاه', 'کامپیوتر',
    'تایپ', 'بازی', 'سرعت', 'دقت', 'تمرین', 'درس',
    'آموزش', 'یادگیری', 'پیشرفت', 'موفقیت', 'صفحه', 'کلید',
    'برنامه', 'سیستم', 'فایل', 'داده', 'شبکه', 'اینترنت',
    'وب', 'سایت', 'نرم‌افزار', 'سخت‌افزار', 'حافظه', 'دیسک'
];

$words_en = [
    'work', 'house', 'book', 'school', 'university', 'computer',
    'type', 'game', 'speed', 'accuracy', 'practice', 'lesson',
    'education', 'learning', 'progress', 'success', 'screen', 'key',
    'program', 'system', 'file', 'data', 'network', 'internet',
    'web', 'site', 'software', 'hardware', 'memory', 'disk'
];

// ترجمه‌ها
$t = [
    'fa' => [
        'survival_mode' => 'حالت بقا',
        'back' => 'بازگشت',
        'lives' => 'جان',
        'level' => 'سطح',
        'score' => 'امتیاز',
        'words_typed' => 'کلمات',
        'type_here' => 'کلمه را تایپ کنید...',
        'start_survival' => 'شروع بقا',
        'game_over' => 'بازی تمام شد!',
        'you_survived' => 'شما زنده ماندید!',
        'exit' => 'خروج',
        'try_again' => 'تلاش مجدد',
        'instructions_title' => 'راهنما',
        'inst_1' => '❤️ با 3 جان شروع می‌کنی',
        'inst_2' => '⚡ هر کلمه درست = امتیاز',
        'inst_3' => '❌ هر اشتباه = 1 جان کم',
        'inst_4' => '📈 هر سطح سخت‌تر می‌شه',
        'inst_5' => '🏆 تا آخر دووم بیار!',
        'level_up' => 'سطح بالا رفت!',
        'new_record' => 'رکورد جدید!',
        'final_level' => 'سطح نهایی',
        'total_words' => 'کل کلمات'
    ],
    'en' => [
        'survival_mode' => 'Survival Mode',
        'back' => 'Back',
        'lives' => 'Lives',
        'level' => 'Level',
        'score' => 'Score',
        'words_typed' => 'Words',
        'type_here' => 'Type the word...',
        'start_survival' => 'Start Survival',
        'game_over' => 'Game Over!',
        'you_survived' => 'You Survived!',
        'exit' => 'Exit',
        'try_again' => 'Try Again',
        'instructions_title' => 'Instructions',
        'inst_1' => '❤️ Start with 3 lives',
        'inst_2' => '⚡ Each correct word = points',
        'inst_3' => '❌ Each mistake = lose 1 life',
        'inst_4' => '📈 Each level gets harder',
        'inst_5' => '🏆 Survive as long as you can!',
        'level_up' => 'Level Up!',
        'new_record' => 'New Record!',
        'final_level' => 'Final Level',
        'total_words' => 'Total Words'
    ]
];

$tr = $t[$currentLang];
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>" dir="<?= $currentLang === 'fa' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tr['survival_mode'] ?> - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/themes.css">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Tahoma', sans-serif;
            background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 100%);
            min-height: 100vh;
            overflow: hidden;
        }

        .game-container {
            position: relative;
            width: 100vw;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .game-header {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(20px);
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid rgba(239, 68, 68, 0.5);
        }

        .game-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stats-bar {
            display: flex;
            gap: 2rem;
        }

        .stat-box {
            text-align: center;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 900;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-family: 'JetBrains Mono', monospace;
        }

        .stat-label {
            font-size: 0.75rem;
            color: #cbd5e0;
            text-transform: uppercase;
            margin-top: 4px;
        }

        .lives-display {
            font-size: 2rem;
        }

        .lives-display.critical {
            animation: pulse 0.5s ease infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        .game-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            gap: 2rem;
            position: relative;
        }

        .word-display {
            font-family: 'JetBrains Mono', monospace;
            font-size: 4rem;
            font-weight: 900;
            color: white;
            text-align: center;
            padding: 2rem 4rem;
            background: rgba(15, 23, 42, 0.8);
            border: 3px solid #f59e0b;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(245, 158, 11, 0.4);
            animation: wordEnter 0.3s ease;
        }

        @keyframes wordEnter {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .word-display.correct {
            animation: correctFlash 0.5s ease;
        }

        .word-display.wrong {
            animation: wrongShake 0.5s ease;
            border-color: #ef4444;
        }

        @keyframes correctFlash {
            0%, 100% { background: rgba(15, 23, 42, 0.8); }
            50% { background: rgba(16, 185, 129, 0.3); }
        }

        @keyframes wrongShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-20px); }
            75% { transform: translateX(20px); }
        }

        .typing-input {
            width: 600px;
            padding: 1.5rem 2rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 2rem;
            font-weight: 600;
            border: 3px solid rgba(245, 158, 11, 0.5);
            border-radius: 15px;
            background: rgba(30, 41, 59, 0.8);
            color: white;
            text-align: center;
            transition: all 0.3s ease;
        }

        .typing-input:focus {
            outline: none;
            border-color: #f59e0b;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.2);
        }

        .level-notification {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 5rem;
            font-weight: 900;
            color: #f59e0b;
            text-shadow: 0 0 30px rgba(245, 158, 11, 0.8);
            animation: levelUpAnim 2s ease;
            pointer-events: none;
            z-index: 50;
        }

        @keyframes levelUpAnim {
            0% {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0);
            }
            50% {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1.2);
            }
            100% {
                opacity: 0;
                transform: translate(-50%, -50%) scale(1);
            }
        }

        .start-screen, .gameover-screen {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.95);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 100;
        }

        .screen-content {
            background: rgba(15, 23, 42, 0.95);
            padding: 3rem;
            border-radius: 30px;
            text-align: center;
            max-width: 600px;
            border: 3px solid rgba(245, 158, 11, 0.5);
        }

        .screen-icon {
            font-size: 6rem;
            margin-bottom: 1.5rem;
            animation: bounce 1s ease infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .screen-title {
            font-size: 2.5rem;
            font-weight: 900;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .instructions {
            text-align: right;
            margin: 2rem 0;
            padding: 1.5rem;
            background: rgba(245, 158, 11, 0.1);
            border-radius: 15px;
            border: 2px solid rgba(245, 158, 11, 0.3);
        }

        .instructions li {
            padding: 8px 0;
            color: #cbd5e0;
            line-height: 1.6;
        }

        .start-btn {
            padding: 1.5rem 3rem;
            font-size: 1.4rem;
            font-weight: 700;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            border: none;
            border-radius: 20px;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .start-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(245, 158, 11, 0.5);
        }

        .result-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .result-stat {
            padding: 1.5rem;
            background: rgba(245, 158, 11, 0.1);
            border-radius: 15px;
            border: 2px solid rgba(245, 158, 11, 0.3);
        }

        .result-stat-value {
            font-size: 2.5rem;
            font-weight: 900;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .result-stat-label {
            color: #cbd5e0;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .modal-actions {
            display: flex;
            gap: 1rem;
        }

        .btn {
            flex: 1;
            padding: 1.25rem;
            border: none;
            border-radius: 15px;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .control-btn {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            color: white;
            cursor: pointer;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }

        .control-btn:hover {
            background: rgba(239, 68, 68, 0.2);
            border-color: #ef4444;
        }

        .badge {
            display: inline-block;
            padding: 10px 20px;
            margin: 1rem 0;
            border-radius: 20px;
            font-weight: 700;
            font-size: 1.1rem;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }
    </style>
</head>
<body>
    <div class="game-container">
        <!-- Header -->
        <div class="game-header">
            <h1 class="game-title">
                <span>❤️</span>
                <span><?= $tr['survival_mode'] ?></span>
            </h1>

            <div class="stats-bar">
                <div class="stat-box">
                    <div class="lives-display" id="livesDisplay">❤️❤️❤️</div>
                    <div class="stat-label"><?= $tr['lives'] ?></div>
                </div>
                <div class="stat-box">
                    <div class="stat-value" id="levelDisplay">1</div>
                    <div class="stat-label"><?= $tr['level'] ?></div>
                </div>
                <div class="stat-box">
                    <div class="stat-value" id="scoreDisplay">0</div>
                    <div class="stat-label"><?= $tr['score'] ?></div>
                </div>
                <div class="stat-box">
                    <div class="stat-value" id="wordsDisplay">0</div>
                    <div class="stat-label"><?= $tr['words_typed'] ?></div>
                </div>
            </div>

            <a href="games.php" class="control-btn"></a>
        </div>

        <!-- Game Content -->
        <div class="game-content">
            <div class="word-display" id="wordDisplay">...</div>

            <input 
                type="text" 
                class="typing-input" 
                id="typingInput" 
                placeholder="<?= $tr['type_here'] ?>"
                autocomplete="off"
                disabled
            >
        </div>

        <!-- Start Screen -->
        <div class="start-screen" id="startScreen">
            <div class="screen-content">
                <div class="screen-icon">❤️</div>
                <h2 class="screen-title"><?= $tr['survival_mode'] ?></h2>
                
                <ul class="instructions">
                    <li><?= $tr['inst_1'] ?></li>
                    <li><?= $tr['inst_2'] ?></li>
                    <li><?= $tr['inst_3'] ?></li>
                    <li><?= $tr['inst_4'] ?></li>
                    <li><?= $tr['inst_5'] ?></li>
                </ul>

                <?php if ($bestStats && $bestStats['best_score']): ?>
                    <div style="margin: 1.5rem 0; padding: 1rem; background: rgba(245, 158, 11, 0.1); border-radius: 10px;">
                        <div style="color: #cbd5e0; margin-bottom: 5px;">
                            <?= $currentLang === 'fa' ? 'بهترین امتیاز:' : 'Best Score:' ?>
                        </div>
                        <div style="font-size: 2rem; font-weight: 700; color: #f59e0b;">
                            <?= number_format($bestStats['best_score']) ?>
                        </div>
                        <div style="color: #cbd5e0; font-size: 0.9rem; margin-top: 5px;">
                            <?= $tr['level'] ?>: <?= $bestStats['max_level'] ?>
                        </div>
                    </div>
                <?php endif; ?>

                <button class="start-btn" id="startBtn">
                    🚀 <?= $tr['start_survival'] ?>
                </button>
            </div>
        </div>

        <!-- Game Over Screen -->
        <div class="gameover-screen" id="gameoverScreen" style="display: none;">
            <div class="screen-content">
                <div class="screen-icon" id="resultIcon">💀</div>
                <h2 class="screen-title" id="resultTitle"><?= $tr['game_over'] ?></h2>
                
                <div id="resultBadge"></div>
                
                <div class="result-stats">
                    <div class="result-stat">
                        <div class="result-stat-value" id="finalLevel">0</div>
                        <div class="result-stat-label"><?= $tr['final_level'] ?></div>
                    </div>
                    <div class="result-stat">
                        <div class="result-stat-value" id="finalWords">0</div>
                        <div class="result-stat-label"><?= $tr['total_words'] ?></div>
                    </div>
                    <div class="result-stat">
                        <div class="result-stat-value" id="finalScore">0</div>
                        <div class="result-stat-label"><?= $tr['score'] ?></div>
                    </div>
                </div>

                <div class="modal-actions">
                    <a href="games.php" class="btn btn-secondary">
                        🏠 <?= $tr['exit'] ?>
                    </a>
                    <button class="btn btn-primary" onclick="window.location.reload()">
                        🔄 <?= $tr['try_again'] ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.GAME_CONFIG = {
            siteUrl: '<?= SITE_URL ?>',
            userId: <?= $user['id'] ?>,
            language: '<?= $currentLang ?>',
            words: <?= json_encode($currentLang === 'fa' ? $words_fa : $words_en, JSON_UNESCAPED_UNICODE) ?>,
            translations: <?= json_encode($tr, JSON_UNESCAPED_UNICODE) ?>,
            bestScore: <?= $bestStats['best_score'] ?? 0 ?>
        };
    </script>
    <script src="<?= SITE_URL ?>/assets/js/game-survival.js"></script>
</body>
</html>