<?php
/**
 * ====================================
 * Accuracy Challenge Game
 * بازی چالش دقت
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

$sql = "SELECT MAX(score) as best_score, MAX(accuracy) as best_accuracy 
        FROM game_scores 
        WHERE user_id = :user_id AND game_type = 'accuracy'";
$stmt = $conn->prepare($sql);
$stmt->execute(['user_id' => $user['id']]);
$bestStats = $stmt->fetch(PDO::FETCH_ASSOC);

// کلمات پیچیده برای چالش
$words_fa = [
    'برنامه‌نویسی', 'دیتابیس', 'الگوریتم', 'اینترفیس', 'کامپایلر',
    'نرم‌افزار', 'سخت‌افزار', 'پردازنده', 'شبکه‌سازی', 'رمزنگاری',
    'دستورالعمل', 'پیکربندی', 'مستندسازی', 'بهینه‌سازی', 'توسعه‌دهنده',
    'معماری', 'چارچوب', 'کتابخانه', 'ماژول', 'پکیج'
];

$words_en = [
    'programming', 'database', 'algorithm', 'interface', 'compiler',
    'software', 'hardware', 'processor', 'networking', 'encryption',
    'implementation', 'configuration', 'documentation', 'optimization', 'developer',
    'architecture', 'framework', 'library', 'module', 'package'
];

// ترجمه‌ها
$t = [
    'fa' => [
        'accuracy_challenge' => 'چالش دقت',
        'back' => 'بازگشت',
        'accuracy' => 'دقت',
        'perfect' => 'کامل',
        'mistakes' => 'اشتباهات',
        'words_left' => 'کلمات باقی‌مانده',
        'current_word' => 'کلمه فعلی',
        'type_here' => 'کلمه را تایپ کنید...',
        'start_challenge' => 'شروع چالش',
        'challenge_complete' => 'چالش تکمیل شد!',
        'perfect_score' => 'امتیاز کامل!',
        'excellent' => 'عالی!',
        'good' => 'خوب!',
        'try_again' => 'دوباره تلاش کن',
        'score' => 'امتیاز',
        'perfect_words' => 'کلمات کامل',
        'total_words' => 'کل کلمات',
        'exit' => 'خروج',
        'retry' => 'تلاش مجدد',
        'instructions_title' => 'راهنما',
        'inst_1' => '🎯 20 کلمه پیچیده داری',
        'inst_2' => '❌ هر اشتباه امتیاز کم می‌شه',
        'inst_3' => '💎 دقت 100% = امتیاز کامل',
        'inst_4' => '⚡ کلمه کامل = امتیاز بیشتر',
        'new_record' => 'رکورد جدید دقت!',
        'almost_perfect' => 'تقریباً کامل!',
        'need_practice' => 'نیاز به تمرین بیشتر'
    ],
    'en' => [
        'accuracy_challenge' => 'Accuracy Challenge',
        'back' => 'Back',
        'accuracy' => 'Accuracy',
        'perfect' => 'Perfect',
        'mistakes' => 'Mistakes',
        'words_left' => 'Words Left',
        'current_word' => 'Current Word',
        'type_here' => 'Type the word...',
        'start_challenge' => 'Start Challenge',
        'challenge_complete' => 'Challenge Complete!',
        'perfect_score' => 'Perfect Score!',
        'excellent' => 'Excellent!',
        'good' => 'Good!',
        'try_again' => 'Try Again',
        'score' => 'Score',
        'perfect_words' => 'Perfect Words',
        'total_words' => 'Total Words',
        'exit' => 'Exit',
        'retry' => 'Retry',
        'instructions_title' => 'Instructions',
        'inst_1' => '🎯 You have 20 complex words',
        'inst_2' => '❌ Each mistake reduces score',
        'inst_3' => '💎 100% accuracy = perfect score',
        'inst_4' => '⚡ Perfect word = bonus points',
        'new_record' => 'New Accuracy Record!',
        'almost_perfect' => 'Almost Perfect!',
        'need_practice' => 'Need More Practice'
    ]
];

$tr = $t[$currentLang];
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>" dir="<?= $currentLang === 'fa' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tr['accuracy_challenge'] ?> - <?= SITE_NAME ?></title>
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
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
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
            border-bottom: 2px solid rgba(16, 185, 129, 0.3);
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
            background: linear-gradient(135deg, #10b981, #059669);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-family: 'JetBrains Mono', monospace;
        }

        .stat-value.perfect {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-label {
            font-size: 0.75rem;
            color: #cbd5e0;
            text-transform: uppercase;
            margin-top: 4px;
        }

        .game-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            gap: 2rem;
        }

        .word-display-container {
            background: rgba(15, 23, 42, 0.8);
            border: 3px solid #10b981;
            border-radius: 20px;
            padding: 3rem 5rem;
            box-shadow: 0 10px 40px rgba(16, 185, 129, 0.3);
        }

        .word-number {
            text-align: center;
            color: #10b981;
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            text-transform: uppercase;
        }

        .word-display {
            font-family: 'JetBrains Mono', monospace;
            font-size: 4rem;
            font-weight: 900;
            color: white;
            text-align: center;
            letter-spacing: 4px;
            min-width: 400px;
        }

        .word-display.correct {
            animation: correctPulse 0.5s ease;
        }

        .word-display.wrong {
            animation: shake 0.5s ease;
            color: #ef4444;
        }

        @keyframes correctPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        @keyframes shake {
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
            border: 3px solid rgba(16, 185, 129, 0.5);
            border-radius: 15px;
            background: rgba(30, 41, 59, 0.8);
            color: white;
            text-align: center;
            transition: all 0.3s ease;
        }

        .typing-input:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2);
        }

        .typing-input.error {
            border-color: #ef4444;
            animation: inputShake 0.3s ease;
        }

        @keyframes inputShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .progress-container {
            width: 600px;
            text-align: center;
        }

        .progress-bar {
            height: 12px;
            background: rgba(30, 41, 59, 0.8);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 1rem;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #059669);
            width: 0%;
            transition: width 0.3s ease;
        }

        .progress-text {
            color: #cbd5e0;
            font-size: 0.9rem;
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
            border: 2px solid rgba(16, 185, 129, 0.3);
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
            background: linear-gradient(135deg, #10b981, #059669);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .instructions {
            text-align: right;
            margin: 2rem 0;
            padding: 1.5rem;
            background: rgba(16, 185, 129, 0.1);
            border-radius: 15px;
            border: 2px solid rgba(16, 185, 129, 0.3);
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
            background: linear-gradient(135deg, #10b981, #059669);
            border: none;
            border-radius: 20px;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .start-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.5);
        }

        .result-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .result-stat {
            padding: 1.5rem;
            background: rgba(16, 185, 129, 0.1);
            border-radius: 15px;
            border: 2px solid rgba(16, 185, 129, 0.3);
        }

        .result-stat-value {
            font-size: 2.5rem;
            font-weight: 900;
            background: linear-gradient(135deg, #10b981, #059669);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .result-stat-label {
            color: #cbd5e0;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .badge {
            display: inline-block;
            padding: 10px 20px;
            margin: 1rem 0;
            border-radius: 20px;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .badge-gold {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }

        .badge-silver {
            background: linear-gradient(135deg, #64748b, #475569);
            color: white;
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
            background: linear-gradient(135deg, #10b981, #059669);
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
    </style>
</head>
<body>
    <div class="game-container">
        <!-- Header -->
        <div class="game-header">
            <h1 class="game-title">
                <span>🎯</span>
                <span><?= $tr['accuracy_challenge'] ?></span>
            </h1>

            <div class="stats-bar">
                <div class="stat-box">
                    <div class="stat-value" id="accuracyDisplay">100%</div>
                    <div class="stat-label"><?= $tr['accuracy'] ?></div>
                </div>
                <div class="stat-box">
                    <div class="stat-value" id="mistakesDisplay">0</div>
                    <div class="stat-label"><?= $tr['mistakes'] ?></div>
                </div>
                <div class="stat-box">
                    <div class="stat-value" id="wordsLeftDisplay">20</div>
                    <div class="stat-label"><?= $tr['words_left'] ?></div>
                </div>
                <div class="stat-box">
                    <div class="stat-value perfect" id="perfectDisplay">0</div>
                    <div class="stat-label"><?= $tr['perfect'] ?></div>
                </div>
            </div>

            <a href="games.php" class="control-btn"></a>
        </div>

        <!-- Game Content -->
        <div class="game-content">
            <div class="word-display-container">
                <div class="word-number" id="wordNumber"><?= $tr['current_word'] ?> 1/20</div>
                <div class="word-display" id="wordDisplay">...</div>
            </div>

            <input 
                type="text" 
                class="typing-input" 
                id="typingInput" 
                placeholder="<?= $tr['type_here'] ?>"
                autocomplete="off"
                disabled
            >

            <div class="progress-container">
                <div class="progress-bar">
                    <div class="progress-fill" id="progressBar"></div>
                </div>
                <div class="progress-text" id="progressText">0 / 20</div>
            </div>
        </div>

        <!-- Start Screen -->
        <div class="start-screen" id="startScreen">
            <div class="screen-content">
                <div class="screen-icon">🎯</div>
                <h2 class="screen-title"><?= $tr['accuracy_challenge'] ?></h2>
                
                <ul class="instructions">
                    <li><?= $tr['inst_1'] ?></li>
                    <li><?= $tr['inst_2'] ?></li>
                    <li><?= $tr['inst_3'] ?></li>
                    <li><?= $tr['inst_4'] ?></li>
                </ul>

                <?php if ($bestStats && $bestStats['best_accuracy']): ?>
                    <div style="margin: 1.5rem 0; padding: 1rem; background: rgba(16, 185, 129, 0.1); border-radius: 10px;">
                        <div style="color: #cbd5e0; margin-bottom: 5px;">
                            <?= $currentLang === 'fa' ? 'بهترین دقت:' : 'Best Accuracy:' ?>
                        </div>
                        <div style="font-size: 2rem; font-weight: 700; color: #10b981;">
                            <?= round($bestStats['best_accuracy']) ?>%
                        </div>
                    </div>
                <?php endif; ?>

                <button class="start-btn" id="startBtn">
                    🚀 <?= $tr['start_challenge'] ?>
                </button>
            </div>
        </div>

        <!-- Game Over Screen -->
        <div class="gameover-screen" id="gameoverScreen" style="display: none;">
            <div class="screen-content">
                <div class="screen-icon" id="resultIcon">🏆</div>
                <h2 class="screen-title" id="resultTitle"><?= $tr['challenge_complete'] ?></h2>
                
                <div id="resultBadge"></div>
                
                <div class="result-stats">
                    <div class="result-stat">
                        <div class="result-stat-value" id="finalAccuracy">0%</div>
                        <div class="result-stat-label"><?= $tr['accuracy'] ?></div>
                    </div>
                    <div class="result-stat">
                        <div class="result-stat-value" id="finalPerfect">0</div>
                        <div class="result-stat-label"><?= $tr['perfect_words'] ?></div>
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
                        🔄 <?= $tr['retry'] ?>
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
            bestAccuracy: <?= $bestStats['best_accuracy'] ?? 0 ?>
        };
    </script>
    <script src="<?= SITE_URL ?>/assets/js/game-accuracy.js"></script>
</body>
</html>