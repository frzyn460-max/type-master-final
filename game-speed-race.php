<?php
/**
 * ====================================
 * Speed Race Game - Racing Edition
 * بازی مسابقه سرعت با رقبا
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

$sql = "SELECT MAX(score) as best_score, MAX(wpm) as best_wpm 
        FROM game_scores 
        WHERE user_id = :user_id AND game_type = 'speed-race'";
$stmt = $conn->prepare($sql);
$stmt->execute(['user_id' => $user['id']]);
$bestStats = $stmt->fetch(PDO::FETCH_ASSOC);

// متن‌های تمرینی
$texts_fa = [
    'تایپ سریع مهارتی است که با تمرین بهبود می‌یابد',
    'سرعت و دقت هر دو در تایپ اهمیت دارند',
    'با تمرین روزانه می‌توانید سریع‌تر تایپ کنید',
    'کیبورد را بدون نگاه کردن استفاده کنید',
    'تمرین منظم کلید موفقیت در تایپ است'
];

$texts_en = [
    'Fast typing is a skill that improves with practice',
    'Both speed and accuracy are important in typing',
    'With daily practice you can type faster',
    'Use the keyboard without looking at it',
    'Regular practice is the key to typing success'
];

// ترجمه‌ها
$t = [
    'fa' => [
        'speed_race' => 'مسابقه سرعت',
        'back' => 'بازگشت',
        'wpm' => 'WPM',
        'accuracy' => 'دقت',
        'time' => 'زمان',
        'you' => 'شما',
        'start_race' => 'شروع مسابقه',
        'start_typing' => 'شروع کنید...',
        'you_won' => 'شما برنده شدید!',
        'second_place' => 'رتبه دوم!',
        'third_place' => 'رتبه سوم',
        'excellent' => 'عالی! رتبه اول!',
        'good_job' => 'خوب بود! می‌تونی بهتر شوی',
        'try_again' => 'دوباره تلاش کن!',
        'position' => 'رتبه',
        'score' => 'امتیاز',
        'exit' => 'خروج',
        'race_again' => 'مسابقه مجدد',
        'race_finished' => 'مسابقه به پایان رسید'
    ],
    'en' => [
        'speed_race' => 'Speed Race',
        'back' => 'Back',
        'wpm' => 'WPM',
        'accuracy' => 'Accuracy',
        'time' => 'Time',
        'you' => 'You',
        'start_race' => 'Start Race',
        'start_typing' => 'Start typing...',
        'you_won' => 'You Won!',
        'second_place' => 'Second Place!',
        'third_place' => 'Third Place',
        'excellent' => 'Great! First place!',
        'good_job' => 'Good! You can do better',
        'try_again' => 'Try again!',
        'position' => 'Position',
        'score' => 'Score',
        'exit' => 'Exit',
        'race_again' => 'Race Again',
        'race_finished' => 'Race finished'
    ]
];

$tr = $t[$currentLang];
$texts = $currentLang === 'fa' ? $texts_fa : $texts_en;
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>" dir="<?= $currentLang === 'fa' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tr['speed_race'] ?> - <?= SITE_NAME ?></title>
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
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
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

        /* Header */
        .game-header {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(20px);
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid rgba(102, 126, 234, 0.3);
        }

        .game-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .race-info {
            display: flex;
            gap: 2rem;
            color: white;
        }

        .stat {
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

        .stat-label {
            font-size: 0.75rem;
            color: #cbd5e0;
            text-transform: uppercase;
        }

        /* Race Track */
        .track-container {
            flex: 1;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            overflow-y: auto;
        }

        .racer {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.5rem;
            background: rgba(15, 23, 42, 0.6);
            border-radius: 20px;
            border: 2px solid rgba(102, 126, 234, 0.3);
            position: relative;
            transition: all 0.3s ease;
        }

        .racer.player {
            border-color: #10b981;
            box-shadow: 0 5px 20px rgba(16, 185, 129, 0.3);
        }

        .racer.bot {
            opacity: 0.7;
        }

        .racer.winner {
            animation: winnerPulse 1s ease infinite;
        }

        @keyframes winnerPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }

        .racer-icon {
            font-size: 2.5rem;
        }

        .racer-label {
            min-width: 80px;
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .racer-track {
            flex: 1;
            height: 50px;
            background: rgba(30, 41, 59, 0.8);
            border-radius: 25px;
            position: relative;
            overflow: hidden;
        }

        .racer-progress {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #059669);
            border-radius: 25px;
            transition: width 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 1rem;
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.5);
        }

        .racer-car {
            font-size: 2rem;
            filter: drop-shadow(0 0 10px rgba(255, 255, 255, 0.5));
        }

        /* Text Display */
        .text-display {
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(20px);
            padding: 2rem 3rem;
            border-radius: 20px;
            border: 2px solid rgba(102, 126, 234, 0.3);
            margin: 0 2rem 1rem;
        }

        .text-content {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.6rem;
            line-height: 2.2;
            color: #94a3b8;
            text-align: center;
            letter-spacing: 1px;
        }

        .text-content span.correct {
            color: #10b981;
            font-weight: 700;
        }

        .text-content span.incorrect {
            color: #ef4444;
            background: rgba(239, 68, 68, 0.2);
            border-radius: 4px;
            padding: 0 2px;
        }

        .text-content span.current {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 4px 8px;
            border-radius: 6px;
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        /* Input */
        .input-container {
            padding: 1rem 2rem 2rem;
        }

        .typing-input {
            width: 100%;
            padding: 1.2rem 2rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.3rem;
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

        /* Start Screen */
        .start-screen {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .start-content {
            text-align: center;
            color: white;
        }

        .start-title {
            font-size: 3.5rem;
            font-weight: 900;
            margin-bottom: 2rem;
            background: linear-gradient(135deg, #10b981, #059669);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .countdown {
            font-size: 8rem;
            font-weight: 900;
            animation: countPulse 1s ease;
        }

        @keyframes countPulse {
            0% { transform: scale(0.5); opacity: 0; }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); opacity: 1; }
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

        /* Game Over Modal */
        .game-over-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(10px);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .game-over-modal.show {
            display: flex;
        }

        .modal-content {
            background: linear-gradient(135deg, #1e293b, #334155);
            border-radius: 30px;
            padding: 3rem;
            text-align: center;
            max-width: 600px;
            border: 2px solid rgba(16, 185, 129, 0.3);
            animation: modalSlide 0.5s ease;
        }

        @keyframes modalSlide {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-icon {
            font-size: 6rem;
            margin-bottom: 1.5rem;
            animation: bounce 1s ease infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .modal-title {
            font-size: 2.5rem;
            font-weight: 900;
            color: white;
            margin-bottom: 0.5rem;
        }

        .modal-subtitle {
            color: #cbd5e0;
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }

        .modal-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .modal-stat {
            padding: 1.5rem;
            background: rgba(16, 185, 129, 0.1);
            border-radius: 15px;
            border: 2px solid rgba(16, 185, 129, 0.3);
        }

        .modal-stat-value {
            font-size: 2.5rem;
            font-weight: 900;
            background: linear-gradient(135deg, #10b981, #059669);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .modal-stat-label {
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
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.5);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .control-btn {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            /* color: white; */
            cursor: pointer;
            align-items: center;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }

        .control-btn:hover {
            background: rgba(239, 68, 68, 0.32);
            border-color: #fa2b2bff;
        }
    </style>
</head>
<body>
    <div class="game-container">
        <!-- Header -->
        <div class="game-header">
            <h1 class="game-title">
                <span>🏎️</span>
                <span><?= $tr['speed_race'] ?></span>
            </h1>

            <div class="race-info">
                <div class="stat">
                    <div class="stat-value" id="wpmDisplay">0</div>
                    <div class="stat-label"><?= $tr['wpm'] ?></div>
                </div>
                <div class="stat">
                    <div class="stat-value" id="accuracyDisplay">100%</div>
                    <div class="stat-label"><?= $tr['accuracy'] ?></div>
                </div>
                <div class="stat">
                    <div class="stat-value" id="timeDisplay">0:00</div>
                    <div class="stat-label"><?= $tr['time'] ?></div>
                </div>
            </div>

            <a href="games.php" class="control-btn">
            </a>
        </div>

        <!-- Race Track -->
        <div class="track-container">
            <div class="racer player">
                <span class="racer-label"><?= $tr['you'] ?></span>
                <div class="racer-track">
                    <div class="racer-progress" id="playerProgress" style="width: 0%">
                        <span class="racer-car">🏎️</span>
                    </div>
                </div>
                <span class="racer-icon">😎</span>
            </div>

            <div class="racer bot">
                <span class="racer-label">Bot 1</span>
                <div class="racer-track">
                    <div class="racer-progress" id="bot1Progress" style="width: 0%; background: linear-gradient(90deg, #ef4444, #dc2626);">
                        <span class="racer-car">🚗</span>
                    </div>
                </div>
                <span class="racer-icon">🤖</span>
            </div>

            <div class="racer bot">
                <span class="racer-label">Bot 2</span>
                <div class="racer-track">
                    <div class="racer-progress" id="bot2Progress" style="width: 0%; background: linear-gradient(90deg, #3b82f6, #2563eb);">
                        <span class="racer-car">🚙</span>
                    </div>
                </div>
                <span class="racer-icon">🤖</span>
            </div>
        </div>

        <!-- Text Display -->
        <div class="text-display">
            <div class="text-content" id="textDisplay"></div>
        </div>

        <!-- Input -->
        <div class="input-container">
            <input 
                type="text" 
                class="typing-input" 
                id="typingInput" 
                placeholder="<?= $tr['start_typing'] ?>" 
                autocomplete="off"
                disabled
            >
        </div>

        <!-- Start Screen -->
        <div class="start-screen" id="startScreen">
            <div class="start-content">
                <h1 class="start-title">🏁 <?= $tr['speed_race'] ?></h1>
                <button class="start-btn" id="startBtn">
                    🚀 <?= $tr['start_race'] ?>
                </button>
            </div>
        </div>

        <!-- Game Over Modal -->
        <div class="game-over-modal" id="gameOverModal">
            <div class="modal-content">
                <div class="modal-icon" id="resultIcon">🏆</div>
                <h2 class="modal-title" id="resultTitle"><?= $tr['you_won'] ?></h2>
                <p class="modal-subtitle" id="resultSubtitle"><?= $tr['race_finished'] ?></p>
                
                <div class="modal-stats">
                    <div class="modal-stat">
                        <div class="modal-stat-value" id="finalPosition">1</div>
                        <div class="modal-stat-label"><?= $tr['position'] ?></div>
                    </div>
                    <div class="modal-stat">
                        <div class="modal-stat-value" id="finalWPM">0</div>
                        <div class="modal-stat-label"><?= $tr['wpm'] ?></div>
                    </div>
                    <div class="modal-stat">
                        <div class="modal-stat-value" id="finalScore">0</div>
                        <div class="modal-stat-label"><?= $tr['score'] ?></div>
                    </div>
                </div>

                <div class="modal-actions">
                    <a href="games.php" class="btn btn-secondary">
                        🏠 <?= $tr['exit'] ?>
                    </a>
                    <button class="btn btn-primary" onclick="window.location.reload()">
                        🔄 <?= $tr['race_again'] ?>
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
            texts: <?= json_encode($texts, JSON_UNESCAPED_UNICODE) ?>,
            translations: <?= json_encode($tr, JSON_UNESCAPED_UNICODE) ?>,
            bestWpm: <?= $bestStats['best_wpm'] ?? 0 ?>
        };
    </script>
    <script src="<?= SITE_URL ?>/assets/js/game-speed-race.js"></script>
</body>
</html>