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

// Persian words
$words_fa = [
    'کتاب',
    'مداد',
    'کاغذ',
    'میز',
    'صندلی',
    'قلم',
    'دفتر',
    'کیف',
    'ساعت',
    'تلفن',
    'کامپیوتر',
    'صفحه',
    'موس',
    'کیبورد',
    'چاپگر',
    'مانیتور',
    'کابل',
    'شارژر',
    'هدفون',
    'خانه',
    'اتاق',
    'در',
    'پنجره',
    'دیوار',
    'سقف',
    'کف',
    'پله',
    'آشپزخانه',
    'حمام',
    'باغ',
    'درخت',
    'گل',
    'برگ',
    'شاخه',
    'ریشه',
    'میوه',
    'سبزی',
    'آب',
    'خاک',
    'آسمان',
    'ابر',
    'باران',
    'برف',
    'باد',
    'خورشید',
    'ماه',
    'ستاره',
    'کهکشان',
    'سیاره',
    'دریا',
    'رودخانه',
    'کوه',
    'تپه',
    'دشت',
    'جنگل',
    'بیابان',
    'ساحل',
    'جزیره',
    'دره',
    'سلام',
    'خداحافظ',
    'ممنون',
    'متشکرم',
    'ببخشید',
    'لطفا',
    'بله',
    'خیر',
    'شاید',
    'حتما',
    'امروز',
    'دیروز',
    'فردا',
    'هفته',
    'ماه',
    'سال',
    'صبح',
    'ظهر',
    'عصر',
    'شب',
    'بهار',
    'تابستان',
    'پاییز',
    'زمستان',
    'گرما',
    'سرما',
    'معتدل',
    'فصل',
    'هوا',
    'آب‌وهوا',
    'مادر',
    'پدر',
    'برادر',
    'خواهر',
    'پسر',
    'دختر',
    'عمو',
    'دایی',
    'عمه',
    'خاله'
];

// English words
$words_en = [
    'book',
    'pencil',
    'paper',
    'desk',
    'chair',
    'pen',
    'notebook',
    'bag',
    'clock',
    'phone',
    'computer',
    'screen',
    'mouse',
    'keyboard',
    'printer',
    'monitor',
    'cable',
    'charger',
    'headset',
    'house',
    'room',
    'door',
    'window',
    'wall',
    'ceiling',
    'floor',
    'stairs',
    'kitchen',
    'bathroom',
    'garden',
    'tree',
    'flower',
    'leaf',
    'branch',
    'root',
    'fruit',
    'vegetable',
    'water',
    'soil',
    'sky',
    'cloud',
    'rain',
    'snow',
    'wind',
    'sun',
    'moon',
    'star',
    'galaxy',
    'planet',
    'sea',
    'river',
    'mountain',
    'hill',
    'plain',
    'forest',
    'desert',
    'beach',
    'island',
    'valley',
    'hello',
    'goodbye',
    'thanks',
    'sorry',
    'please',
    'yes',
    'no',
    'maybe',
    'sure',
    'okay',
    'today',
    'yesterday',
    'tomorrow',
    'week',
    'month',
    'year',
    'morning',
    'noon',
    'evening',
    'night',
    'spring',
    'summer',
    'autumn',
    'winter',
    'hot',
    'cold',
    'warm',
    'season',
    'weather',
    'climate',
    'mother',
    'father',
    'brother',
    'sister',
    'son',
    'daughter',
    'uncle',
    'aunt',
    'cousin',
    'friend'
];

// ترجمه‌ها
$t = [
    'fa' => [
        'survival_mode' => 'حالت بقا',
        'back' => 'بازگشت',
        'type_until_last' => 'تا آخرین نفس تایپ کن!',
        'ready_fight' => 'آماده مرگ و زندگی؟!',
        'stay_alive' => 'زنده بمان یا بمیر... انتخاب با توست!',
        'survival_rules' => 'قوانین بقا',
        'rule_1' => 'شروع با 100 جان',
        'rule_2' => 'هر کلمه صحیح: +10 جان + امتیاز',
        'rule_3' => 'هر اشتباه: -20 جان',
        'rule_4' => 'Combo: 5+ پشت سر هم = بونوس',
        'rule_5' => 'جان در هر ثانیه کم می‌شود!',
        'rule_6' => 'جان به 0 برسه = GAME OVER!',
        'rule_7' => 'هرچه بیشتر تایپ کنی، بیشتر زنده می‌مونی!',
        'start_battle' => 'شروع نبرد',
        'health' => 'جان',
        'score' => 'امتیاز',
        'combo' => 'Combo',
        'get_ready' => 'آماده باش',
        'type_fast' => 'سریع تایپ کن...',
        'game_over' => 'بازی تمام شد!',
        'survival_time' => 'زمان بقا',
        'final_score' => 'امتیاز نهایی',
        'correct_words' => 'کلمات صحیح',
        'max_combo' => 'بیشترین Combo',
        'try_again' => 'تلاش دوباره',
        'back_to_games' => 'بازگشت به بازی‌ها',
        'seconds' => 'ثانیه'
    ],
    'en' => [
        'survival_mode' => 'Survival Mode',
        'back' => 'Back',
        'type_until_last' => 'Type until your last breath!',
        'ready_fight' => 'Ready to fight?!',
        'stay_alive' => 'Stay alive or die... the choice is yours!',
        'survival_rules' => 'Survival Rules',
        'rule_1' => 'Start with 100 health',
        'rule_2' => 'Each correct word: +10 health + score',
        'rule_3' => 'Each mistake: -20 health',
        'rule_4' => 'Combo: 5+ in a row = bonus',
        'rule_5' => 'Health drains every second!',
        'rule_6' => 'Health reaches 0 = GAME OVER!',
        'rule_7' => 'Type more to stay alive longer!',
        'start_battle' => 'Start Battle',
        'health' => 'Health',
        'score' => 'Score',
        'combo' => 'Combo',
        'get_ready' => 'Get Ready',
        'type_fast' => 'Type fast...',
        'game_over' => 'Game Over!',
        'survival_time' => 'Survival Time',
        'final_score' => 'Final Score',
        'correct_words' => 'Correct Words',
        'max_combo' => 'Max Combo',
        'try_again' => 'Try Again',
        'back_to_games' => 'Back to Games',
        'seconds' => 'seconds'
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
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Vazirmatn', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Animated Stars Background */
        .stars {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .star {
            position: absolute;
            width: 2px;
            height: 2px;
            background: white;
            border-radius: 50%;
            animation: twinkle 3s infinite;
        }

        @keyframes twinkle {

            0%,
            100% {
                opacity: 0.3;
            }

            50% {
                opacity: 1;
            }
        }

        /* Game Container */
        .game-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            padding: 2rem;
            max-width: 800px;
            width: 90%;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 30px 90px rgba(0, 0, 0, 0.5);
            position: relative;
            z-index: 1;
        }

        /* Header */
        .game-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 2rem;
            position: relative;
        }

        .back-btn {
            align-self:
                <?= $currentLang === 'fa' ? 'flex-end' : 'flex-start' ?>
            ;
            padding: 0.75rem 1.5rem;
            background: rgba(239, 68, 68, 0.1);
            border: 2px solid #ef4444;
            border-radius: 12px;
            color: #ef4444;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }

        .back-btn:hover {
            background: #ef4444;
            color: white;
            transform: translateY(-2px);
        }

        .game-title {
            font-size: 3rem;
            font-weight: 900;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
            text-align: center;
        }

        .game-subtitle {
            color: #666;
            font-size: 1.1rem;
            padding-bottom: 5px;
            text-align: center;
        }

        /* Health & Stats Bar */
        .game-stats {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .health-bar {
            background: #f0f0f0;
            padding: 1.5rem;
            border-radius: 20px;
            position: relative;
            overflow: hidden;
        }

        .health-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            font-weight: 700;
            color: #333;
            font-size: 1.1rem;
        }

        .health-progress {
            height: 30px;
            background: #ddd;
            border-radius: 15px;
            overflow: hidden;
            position: relative;
        }

        .health-fill {
            height: 100%;
            background: linear-gradient(90deg, #2ecc71, #27ae60);
            border-radius: 15px;
            transition: width 0.3s ease, background 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 900;
            font-size: 1rem;
        }

        .health-fill.warning {
            background: linear-gradient(90deg, #f39c12, #e67e22);
        }

        .health-fill.danger {
            background: linear-gradient(90deg, #e74c3c, #c0392b);
            animation: pulse-danger 0.5s infinite;
        }

        @keyframes pulse-danger {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            padding: 1.5rem;
            border-radius: 20px;
            text-align: center;
            color: white;
        }

        .stat-card.red {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 900;
        }

        /* Word Display Area */
        .word-display {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            border-radius: 25px;
            padding: 4rem 3rem;
            margin-bottom: 2rem;
            min-height: 250px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .word-display::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: rotate-bg 20s linear infinite;
        }

        @keyframes rotate-bg {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .current-word {
            font-size: 4rem;
            font-weight: 900;
            color: white;
            letter-spacing: 8px;
            text-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            position: relative;
            z-index: 1;
            animation: word-pulse 2s ease-in-out infinite;
        }

        @keyframes word-pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .word-shadow {
            position: absolute;
            font-size: 5rem;
            font-weight: 900;
            opacity: 0.05;
            pointer-events: none;
            animation: float-shadow 3s ease-in-out infinite;
        }

        @keyframes float-shadow {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        /* Input Area */
        .input-area {
            margin-bottom: 2rem;
        }

        .input-box {
            width: 100%;
            padding: 2rem;
            font-size: 2rem;
            text-align: center;
            border: 4px solid #e74c3c;
            border-radius: 20px;
            outline: none;
            background: white;
            color: #333;
            font-family: 'Vazirmatn', sans-serif;
            font-weight: 700;
            letter-spacing: 4px;
            transition: all 0.3s ease;
        }

        .input-box:focus {
            border-color: #c0392b;
            box-shadow: 0 0 30px rgba(231, 76, 60, 0.5);
            transform: translateY(-2px);
        }

        .input-box.correct {
            border-color: #2ecc71;
            animation: success-pulse 0.3s ease;
        }

        @keyframes success-pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.02);
            }

            100% {
                transform: scale(1);
            }
        }

        /* Start Screen */
        .start-screen {
            display: flex;
            flex-direction: column;
            height: 50%;
            justify-content: flex-start;
            align-items: stretch;
        }

        .start-content-wrapper {
            text-align: center;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow: hidden;
        }

        .start-icon {
            font-size: 4.5rem;
            margin-bottom: 0.25rem;
            animation: danger-bounce 1s ease-in-out infinite;
        }

        @keyframes danger-bounce {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            25% {
                transform: translateY(-20px) rotate(-10deg);
            }

            75% {
                transform: translateY(-20px) rotate(10deg);
            }
        }

        .start-title {
            font-size: 1.6rem;
            font-weight: 900;
            color: #e74c3c;
            margin-bottom: 0.4rem;
        }

        .start-description {
            font-size: 0.95rem;
            color: #666;
            margin-bottom: 0.75rem;
            line-height: 1.5;
        }

        .game-rules {
            background: linear-gradient(135deg, rgba(231, 76, 60, 0.1), rgba(192, 57, 43, 0.1));
            padding: 0.75rem 1rem;
            border-radius: 15px;
            margin: 0.75rem 0;
            text-align:
                <?= $currentLang === 'fa' ? 'right' : 'left' ?>
            ;
            border: 2px solid rgba(231, 76, 60, 0.2);
        }

        .game-rules h4 {
            color: #e74c3c;
            margin-bottom: 0.5rem;
            font-weight: 800;
            font-size: 1rem;
        }

        .game-rules ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .game-rules li {
            padding: 0.25rem 0;
            color: #333;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .game-rules li i {
            margin-<?= $currentLang === 'fa' ? 'left' : 'right' ?>: 0.75rem;
            color: #e74c3c;
            font-size: 1.2rem;
        }

        .start-btn {
            padding: 1rem 2.5rem;
            font-size: 1.2rem;
            font-weight: 900;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(231, 76, 60, 0.4);
            text-transform: uppercase;
            margin-top: 1rem;
            flex-shrink: 0;
        }

        .start-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 50px rgba(231, 76, 60, 0.7);
        }

        /* Game Over Screen */
        .gameover-screen {
            text-align: center;
        }

        .gameover-icon {
            font-size: 10rem;
            margin-bottom: 2rem;
            animation: gameover-shake 0.5s ease infinite;
        }

        @keyframes gameover-shake {

            0%,
            100% {
                transform: rotate(0deg);
            }

            25% {
                transform: rotate(-5deg);
            }

            75% {
                transform: rotate(5deg);
            }
        }

        .gameover-title {
            font-size: 3rem;
            font-weight: 900;
            color: #e74c3c;
            margin-bottom: 1rem;
        }

        .survival-time {
            font-size: 5rem;
            font-weight: 900;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 2rem;
        }

        .result-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .result-stat {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 20px;
        }

        .result-stat-label {
            color: #666;
            font-size: 1rem;
            margin-bottom: 0.75rem;
            font-weight: 600;
        }

        .result-stat-value {
            font-size: 2.5rem;
            font-weight: 900;
            color: #333;
        }

        .action-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
        }

        .btn-custom {
            padding: 1.25rem 3rem;
            font-size: 1.3rem;
            font-weight: 700;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-danger-custom {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
        }

        .btn-secondary-custom {
            background: white;
            color: #e74c3c;
            border: 3px solid #e74c3c;
        }

        .btn-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        /* Combo System */
        .combo-display {
            position: absolute;
            top: -50px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
            padding: 1rem 2rem;
            border-radius: 20px;
            font-size: 1.5rem;
            font-weight: 900;
            animation: combo-pop 0.5s ease;
            box-shadow: 0 10px 30px rgba(243, 156, 18, 0.5);
        }

        @keyframes combo-pop {
            0% {
                transform: translateX(-50%) scale(0);
            }

            50% {
                transform: translateX(-50%) scale(1.2);
            }

            100% {
                transform: translateX(-50%) scale(1);
            }
        }

        /* Animations */
        .fade-in {
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .game-container {
                padding: 2rem;
                width: 95%;
            }

            .game-title {
                font-size: 2rem;
            }

            .current-word {
                font-size: 2.5rem;
                letter-spacing: 4px;
            }

            .input-box {
                font-size: 1.5rem;
                padding: 1.5rem;
            }

            .game-stats {
                grid-template-columns: 1fr;
            }

            .result-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <!-- Animated Stars -->
    <div class="stars" id="stars"></div>

    <div class="game-container fade-in">
        <!-- Game Header -->
        <div class="game-header">
            <a href="games.php" class="back-btn">
                <?= $currentLang === 'fa' ? '→ ' : '← ' ?><?= $tr['back'] ?>
            </a>
            <h1 class="game-title">💀 <?= $tr['survival_mode'] ?></h1>
            <p class="game-subtitle"><?= $tr['type_until_last'] ?></p>
        </div>

        <!-- Start Screen -->
        <div id="startScreen" class="start-screen">
            <div class="start-content-wrapper">
                <div class="start-icon">💀</div>
                <h2 class="start-title"><?= $tr['ready_fight'] ?></h2>
                <p class="start-description"><?= $tr['stay_alive'] ?></p>

                <div class="game-rules">
                    <h4>⚠️ <?= $tr['survival_rules'] ?>:</h4>
                    <ul>
                        <li>❤️ <?= $tr['rule_1'] ?></li>
                        <li>⚡ <?= $tr['rule_2'] ?></li>
                        <li>💀 <?= $tr['rule_3'] ?></li>
                        <li>🔥 <?= $tr['rule_4'] ?></li>
                        <li>⏱️ <?= $tr['rule_5'] ?></li>
                        <li>☠️ <?= $tr['rule_6'] ?></li>
                        <li>🏆 <?= $tr['rule_7'] ?></li>
                    </ul>
                </div>
            </div>

            <button class="start-btn" onclick="startGame()">
                💀 <?= $tr['start_battle'] ?>
            </button>
        </div>

        <!-- Game Screen -->
        <div id="gameScreen" style="display: none;">
            <!-- Stats -->
            <div class="game-stats">
                <div class="health-bar">
                    <div class="health-label">
                        <span>❤️ <?= $tr['health'] ?></span>
                        <span id="healthText">100 / 100</span>
                    </div>
                    <div class="health-progress">
                        <div class="health-fill" id="healthFill" style="width: 100%">100%</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-label"><?= $tr['score'] ?></div>
                    <div class="stat-value" id="score">0</div>
                </div>
                <div class="stat-card red">
                    <div class="stat-label"><?= $tr['combo'] ?></div>
                    <div class="stat-value" id="combo">0</div>
                </div>
            </div>

            <!-- Word Display -->
            <div class="word-display">
                <div class="word-shadow" id="wordShadow"></div>
                <div class="current-word" id="currentWord"><?= $tr['get_ready'] ?></div>
                <div class="combo-display" id="comboDisplay" style="display: none;"></div>
            </div>

            <!-- Input -->
            <div class="input-area">
                <input type="text" class="input-box" id="inputBox" placeholder="<?= $tr['type_fast'] ?>"
                    autocomplete="off" disabled>
            </div>
        </div>

        <!-- Game Over Screen -->
        <div id="gameoverScreen" style="display: none;">
            <div class="gameover-screen">
                <div class="gameover-icon">💀</div>
                <h2 class="gameover-title"><?= $tr['game_over'] ?></h2>
                <div class="survival-time" id="survivalTime">0<?= $currentLang === 'fa' ? 'ث' : 's' ?></div>

                <div class="result-stats">
                    <div class="result-stat">
                        <div class="result-stat-label"><?= $tr['final_score'] ?></div>
                        <div class="result-stat-value" id="finalScore">0</div>
                    </div>
                    <div class="result-stat">
                        <div class="result-stat-label"><?= $tr['correct_words'] ?></div>
                        <div class="result-stat-value" id="finalWords">0</div>
                    </div>
                    <div class="result-stat">
                        <div class="result-stat-label"><?= $tr['max_combo'] ?></div>
                        <div class="result-stat-value" id="finalCombo">0</div>
                    </div>
                </div>

                <div class="action-buttons">
                    <button class="btn-custom btn-danger-custom" onclick="restartGame()">
                        🔄 <?= $tr['try_again'] ?>
                    </button>
                    <button class="btn-custom btn-secondary-custom" onclick="window.location.href='games.php'">
                        🏠 <?= $tr['back_to_games'] ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const WORDS = {
            fa: <?= json_encode($words_fa) ?>,
            en: <?= json_encode($words_en) ?>
        };

        let health = 100;
        let maxHealth = 100;
        let score = 0;
        let combo = 0;
        let maxCombo = 0;
        let correctWords = 0;
        let startTime = 0;
        let healthDrainInterval;
        let currentWord = '';
        let currentLang = '<?= $currentLang ?>';

        // Generate stars
        const starsContainer = document.getElementById('stars');
        for (let i = 0; i < 100; i++) {
            const star = document.createElement('div');
            star.className = 'star';
            star.style.left = Math.random() * 100 + '%';
            star.style.top = Math.random() * 100 + '%';
            star.style.animationDelay = Math.random() * 3 + 's';
            starsContainer.appendChild(star);
        }

        function startGame() {
            document.getElementById('startScreen').style.display = 'none';
            document.getElementById('gameScreen').style.display = 'block';

            health = 100;
            score = 0;
            combo = 0;
            maxCombo = 0;
            correctWords = 0;
            startTime = Date.now();

            loadNewWord();
            document.getElementById('inputBox').disabled = false;
            document.getElementById('inputBox').focus();

            updateStats();
            startHealthDrain();
        }

        function loadNewWord() {
            const words = WORDS[currentLang];
            currentWord = words[Math.floor(Math.random() * words.length)];
            document.getElementById('currentWord').textContent = currentWord;
            document.getElementById('wordShadow').textContent = currentWord;
            document.getElementById('inputBox').value = '';
        }

        function startHealthDrain() {
            healthDrainInterval = setInterval(() => {
                health = Math.max(0, health - 1);
                updateStats();

                if (health <= 0) {
                    gameOver();
                }
            }, 1000);
        }

        document.getElementById('inputBox').addEventListener('input', function (e) {
            const input = e.target.value.trim();

            if (input === currentWord) {
                correctWords++;
                combo++;
                maxCombo = Math.max(maxCombo, combo);

                health = Math.min(maxHealth, health + 10);

                let points = 100;
                if (combo >= 5) {
                    points *= 2;
                    showComboMessage(`🔥 COMBO x${combo}!`);
                }
                score += points;

                e.target.classList.add('correct');
                setTimeout(() => e.target.classList.remove('correct'), 300);

                loadNewWord();
                updateStats();
            }
        });

        document.getElementById('inputBox').addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && e.target.value.trim() !== '' && e.target.value.trim() !== currentWord) {
                health = Math.max(0, health - 20);
                combo = 0;
                e.target.value = '';
                updateStats();

                if (health <= 0) {
                    gameOver();
                }
            }
        });

        function showComboMessage(message) {
            const comboEl = document.getElementById('comboDisplay');
            comboEl.textContent = message;
            comboEl.style.display = 'block';

            setTimeout(() => {
                comboEl.style.display = 'none';
            }, 1000);
        }

        function updateStats() {
            const healthPercent = (health / maxHealth) * 100;
            const healthFill = document.getElementById('healthFill');
            healthFill.style.width = healthPercent + '%';
            healthFill.textContent = Math.round(healthPercent) + '%';
            document.getElementById('healthText').textContent = `${Math.round(health)} / ${maxHealth}`;

            healthFill.classList.remove('warning', 'danger');
            if (healthPercent <= 30) {
                healthFill.classList.add('danger');
            } else if (healthPercent <= 60) {
                healthFill.classList.add('warning');
            }

            document.getElementById('score').textContent = score;
            document.getElementById('combo').textContent = combo;
        }

        function gameOver() {
            clearInterval(healthDrainInterval);
            document.getElementById('inputBox').disabled = true;

            const survivalTime = Math.round((Date.now() - startTime) / 1000);

            document.getElementById('gameScreen').style.display = 'none';
            document.getElementById('gameoverScreen').style.display = 'block';

            const timeUnit = currentLang === 'fa' ? 'ث' : 's';
            document.getElementById('survivalTime').textContent = survivalTime + timeUnit;
            document.getElementById('finalScore').textContent = score;
            document.getElementById('finalWords').textContent = correctWords;
            document.getElementById('finalCombo').textContent = maxCombo;

            saveScore(survivalTime);
        }

        function saveScore(time) {
            const wpm = Math.round((correctWords / time) * 60);

            fetch('<?= SITE_URL ?>/api/save-game-score.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    game_type: 'survival',
                    score: score,
                    wpm: wpm,
                    accuracy: 100,
                    level_reached: 1,
                    duration: time
                })
            });
        }

        function restartGame() {
            clearInterval(healthDrainInterval);

            document.getElementById('gameoverScreen').style.display = 'none';
            document.getElementById('gameScreen').style.display = 'block';

            health = 100;
            score = 0;
            combo = 0;
            maxCombo = 0;
            correctWords = 0;
            startTime = Date.now();

            loadNewWord();
            document.getElementById('inputBox').disabled = false;
            document.getElementById('inputBox').focus();

            updateStats();
            startHealthDrain();
        }
    </script>
</body>

</html>