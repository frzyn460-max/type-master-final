<?php
/**
 * ====================================
 * Practice Page - Improved Version
 * صفحه تمرین - نسخه بهبود یافته
 * ====================================
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// دریافت ID درس
$lessonId = isset($_GET['lesson']) ? (int)$_GET['lesson'] : null;

if (!$lessonId) {
    redirect(SITE_URL . '/lessons.php');
}

// دریافت اطلاعات درس
$lesson = getLessonById($lessonId);

if (!$lesson) {
    $_SESSION['error'] = $currentLang === 'fa' ? 'درس یافت نشد!' : 'Lesson not found!';
    redirect(SITE_URL . '/lessons.php');
}

// چک سطح مورد نیاز
if ($user['level'] < $lesson['required_level']) {
    $_SESSION['error'] = $currentLang === 'fa' 
        ? "این درس در سطح {$lesson['required_level']} باز می‌شود!" 
        : "This lesson unlocks at level {$lesson['required_level']}!";
    redirect(SITE_URL . '/lessons.php');
}

// دریافت پیشرفت قبلی
$progress = getUserLessonProgress($user['id'], $lessonId);

// ترجمه‌ها
$t = [
    'fa' => [
        'practice' => 'تمرین',
        'lesson' => 'درس',
        'back' => 'بازگشت',
        'speed' => 'سرعت',
        'accuracy' => 'دقت',
        'errors' => 'خطاها',
        'time' => 'زمان',
        'progress' => 'پیشرفت',
        'type_here' => 'اینجا تایپ کنید...',
        'restart' => 'شروع مجدد',
        'pause' => 'توقف',
        'resume' => 'ادامه',
        'completed' => 'تکمیل شد!',
        'excellent' => 'عالی!',
        'good' => 'خوب!',
        'keep_trying' => 'ادامه بده!',
        'try_again' => 'تلاش مجدد',
        'next_lesson' => 'درس بعدی',
        'best_score' => 'بهترین امتیاز',
        'your_score' => 'امتیاز شما',
        'keyboard' => 'کیبورد',
        'sound' => 'صدا',
        'welcome_title' => 'آماده برای تمرین؟',
        'welcome_subtitle' => 'در این درس شما یاد می‌گیرید:',
        'tip_1' => 'دقت خود را بالای 85% نگه دارید',
        'tip_2' => 'سرعت را به تدریج افزایش دهید',
        'tip_3' => 'از وضعیت صحیح دست استفاده کنید',
        'tip_4' => 'استراحت‌های کوتاه داشته باشید',
        'start_practice' => 'شروع تمرین',
        'wpm_unit' => 'کلمه/دقیقه'
    ],
    'en' => [
        'practice' => 'Practice',
        'lesson' => 'Lesson',
        'back' => 'Back',
        'speed' => 'Speed',
        'accuracy' => 'Accuracy',
        'errors' => 'Errors',
        'time' => 'Time',
        'progress' => 'Progress',
        'type_here' => 'Type here...',
        'restart' => 'Restart',
        'pause' => 'Pause',
        'resume' => 'Resume',
        'completed' => 'Completed!',
        'excellent' => 'Excellent!',
        'good' => 'Good!',
        'keep_trying' => 'Keep Trying!',
        'try_again' => 'Try Again',
        'next_lesson' => 'Next Lesson',
        'best_score' => 'Best Score',
        'your_score' => 'Your Score',
        'keyboard' => 'Keyboard',
        'sound' => 'Sound',
        'welcome_title' => 'Ready to Practice?',
        'welcome_subtitle' => 'In this lesson you will learn:',
        'tip_1' => 'Keep accuracy above 85%',
        'tip_2' => 'Gradually increase speed',
        'tip_3' => 'Use correct hand position',
        'tip_4' => 'Take short breaks',
        'start_practice' => 'Start Practice',
        'wpm_unit' => 'WPM'
    ]
];

$tr = $t[$currentLang];
$title = $currentLang === 'fa' ? $lesson['title_fa'] : $lesson['title_en'];
$description = $currentLang === 'fa' ? $lesson['description_fa'] : $lesson['description_en'];
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>" dir="<?= $currentLang === 'fa' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tr['practice'] ?> - <?= $title ?> | <?= SITE_NAME ?></title>
    
    <!-- Styles -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/themes.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/keyboard.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/practice.css">
</head>
<body class="theme-<?= $currentTheme ?> lang-<?= $currentLang ?> practice-page">
    
    <!-- Main Container - بدون اسکرول -->
    <div class="practice-wrapper">
        
        <!-- Header -->
        <header class="practice-header">
            <div class="practice-header-left">
                <a href="lessons.php" class="btn btn-secondary btn-sm">
                    <?= $currentLang === 'fa' ? '→' : '←' ?> <?= $tr['back'] ?>
                </a>
                <div class="lesson-info">
                    <h1 class="practice-title">
                        <?= $tr['lesson'] ?> <?= $lesson['order_index'] ?>
                    </h1>
                    <p class="practice-subtitle"><?= htmlspecialchars($title) ?></p>
                </div>
            </div>
            <div class="practice-header-right">
                <button class="btn-icon" id="toggleKeyboard" title="<?= $tr['keyboard'] ?>">⌨️</button>
                <button class="btn-icon" id="toggleSound" title="<?= $tr['sound'] ?>">🔊</button>
                <button class="btn-icon" id="themeToggle" title="Theme">🌙</button>
            </div>
        </header>
        
        <!-- Instructions Panel - صفحه خوش‌آمدگویی -->
        <div class="welcome-screen" id="welcomeScreen">
            <div class="welcome-content">
                <div class="welcome-icon">📚</div>
                <h2 class="welcome-title"><?= $tr['welcome_title'] ?></h2>
                <p class="welcome-subtitle"><?= $tr['welcome_subtitle'] ?></p>
                
                <div class="lesson-preview">
                    <h3><?= htmlspecialchars($title) ?></h3>
                    <p class="lesson-desc"><?= htmlspecialchars($description) ?></p>
                </div>
                
                <div class="tips-grid">
                    <div class="tip-card">
                        <div class="tip-icon">🎯</div>
                        <p><?= $tr['tip_1'] ?></p>
                    </div>
                    <div class="tip-card">
                        <div class="tip-icon">⚡</div>
                        <p><?= $tr['tip_2'] ?></p>
                    </div>
                    <div class="tip-card">
                        <div class="tip-icon">✋</div>
                        <p><?= $tr['tip_3'] ?></p>
                    </div>
                    <div class="tip-card">
                        <div class="tip-icon">☕</div>
                        <p><?= $tr['tip_4'] ?></p>
                    </div>
                </div>
                
                <?php if ($progress && $progress['best_wpm'] > 0): ?>
                <div class="previous-best">
                    <p><?= $tr['best_score'] ?>:</p>
                    <div class="best-stats">
                        <span><strong><?= $progress['best_wpm'] ?></strong> <?= $tr['wpm_unit'] ?></span>
                        <span><strong><?= $progress['best_accuracy'] ?>%</strong> <?= $tr['accuracy'] ?></span>
                        <span><strong><?= $progress['stars'] ?></strong> ⭐</span>
                    </div>
                </div>
                <?php endif; ?>
                
                <button class="btn btn-primary btn-lg" id="startPracticeBtn">
                    🚀 <?= $tr['start_practice'] ?>
                </button>
            </div>
        </div>
        
        <!-- Practice Area -->
        <div class="practice-container" id="practiceArea" style="display: none;">
            
            <!-- Compact Stats Bar -->
            <div class="stats-compact">
                <div class="stat-item">
                    <span class="stat-icon">⚡</span>
                    <span class="stat-value" id="wpmDisplay">0</span>
                    <span class="stat-label"><?= $tr['wpm_unit'] ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-icon">🎯</span>
                    <span class="stat-value" id="accuracyDisplay">100</span>
                    <span class="stat-label">%</span>
                </div>
                <div class="stat-item">
                    <span class="stat-icon">❌</span>
                    <span class="stat-value" id="errorsDisplay">0</span>
                    <span class="stat-label"><?= $tr['errors'] ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-icon">⏱️</span>
                    <span class="stat-value" id="timeDisplay">00:00</span>
                    <span class="stat-label"><?= $tr['time'] ?></span>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="progress-wrapper">
                <div class="progress-bar">
                    <div class="progress-fill" id="progressBar"></div>
                </div>
                <span class="progress-label" id="progressText">0%</span>
            </div>
            
            <!-- Typing Area - بدون اسکرول -->
            <div class="typing-zone">
                <div class="typing-text" id="typingText"></div>
            </div>
            
            <!-- Input Area -->
            <div class="input-zone">
                <input 
                    type="text" 
                    id="typingInput" 
                    class="typing-input" 
                    placeholder="<?= $tr['type_here'] ?>"
                    autocomplete="off"
                    autocorrect="off"
                    autocapitalize="off"
                    spellcheck="false"
                >
            </div>
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <button class="btn btn-sm" id="pauseBtn">⏸️</button>
                <button class="btn btn-sm" id="restartBtn">🔄</button>
            </div>
            
            <!-- Virtual Keyboard -->
            <div class="keyboard-zone" id="keyboardZone">
                <div id="virtualKeyboard"></div>
            </div>
        </div>
        
    </div>
    
    <!-- Result Modal -->
    <div class="modal" id="resultModal">
        <div class="modal-overlay" id="modalOverlay"></div>
        <div class="modal-content">
            <div class="modal-header">
                <div class="result-icon" id="resultIcon">🎉</div>
                <h2 class="result-title" id="resultTitle"><?= $tr['completed'] ?></h2>
            </div>
            
            <div class="modal-body">
                <!-- Stars -->
                <div class="stars-display" id="starsDisplay">
                    <span class="star">⭐</span>
                    <span class="star">⭐</span>
                    <span class="star">⭐</span>
                </div>
                
                <!-- Stats Grid -->
                <div class="result-stats">
                    <div class="result-stat">
                        <div class="result-stat-icon">⚡</div>
                        <div class="result-stat-value" id="finalWpm">0</div>
                        <div class="result-stat-label"><?= $tr['wpm_unit'] ?></div>
                    </div>
                    <div class="result-stat">
                        <div class="result-stat-icon">🎯</div>
                        <div class="result-stat-value" id="finalAccuracy">0%</div>
                        <div class="result-stat-label"><?= $tr['accuracy'] ?></div>
                    </div>
                    <div class="result-stat">
                        <div class="result-stat-icon">❌</div>
                        <div class="result-stat-value" id="finalErrors">0</div>
                        <div class="result-stat-label"><?= $tr['errors'] ?></div>
                    </div>
                    <div class="result-stat">
                        <div class="result-stat-icon">⏱️</div>
                        <div class="result-stat-value" id="finalTime">0s</div>
                        <div class="result-stat-label"><?= $tr['time'] ?></div>
                    </div>
                </div>
                
                <!-- Best Score -->
                <?php if ($progress && $progress['best_wpm'] > 0): ?>
                <div class="score-comparison">
                    <div class="score-item">
                        <span class="score-label"><?= $tr['best_score'] ?></span>
                        <span class="score-value"><?= $progress['best_wpm'] ?> <?= $tr['wpm_unit'] ?></span>
                    </div>
                    <div class="score-item highlight">
                        <span class="score-label"><?= $tr['your_score'] ?></span>
                        <span class="score-value" id="yourScore">0 <?= $tr['wpm_unit'] ?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="modal-footer">
                <button class="btn btn-secondary" id="tryAgainBtn">
                    🔄 <?= $tr['try_again'] ?>
                </button>
                <button class="btn btn-primary" id="nextLessonBtn">
                    <?= $tr['next_lesson'] ?> <?= $currentLang === 'fa' ? '←' : '→' ?>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="<?= SITE_URL ?>/assets/js/typing-engine.js"></script>
    <script src="<?= SITE_URL ?>/assets/js/keyboard.js"></script>
    <script src="<?= SITE_URL ?>/assets/js/timer.js"></script>
    <script src="<?= SITE_URL ?>/assets/js/practice.js"></script>
    
    <script>
        // Configuration
        window.PRACTICE_CONFIG = {
            siteUrl: '<?= SITE_URL ?>',
            lessonId: <?= $lessonId ?>,
            userId: <?= $user['id'] ?>,
            language: '<?= $lesson['language'] ?>',
            theme: '<?= $currentTheme ?>',
            currentLang: '<?= $currentLang ?>',
            lessonText: <?= json_encode($lesson['content'], JSON_UNESCAPED_UNICODE) ?>,
            soundEnabled: <?= $user['sound_enabled'] ? 'true' : 'false' ?>,
            translations: <?= json_encode($tr, JSON_UNESCAPED_UNICODE) ?>,
            previousBest: {
                wpm: <?= $progress ? $progress['best_wpm'] : 0 ?>,
                accuracy: <?= $progress ? $progress['best_accuracy'] : 0 ?>
            }
        };
    </script>
</body>
</html>