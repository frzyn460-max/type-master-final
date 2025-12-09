<?php
/**
 * ====================================
 * Save Progress API
 * API ذخیره پیشرفت درس
 * ====================================
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-check.php';

// فقط درخواست POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

// چک کردن لاگین
if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

// دریافت داده‌ها
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    jsonResponse(['success' => false, 'message' => 'Invalid JSON'], 400);
}

// اعتبارسنجی
$lessonId = isset($input['lesson_id']) ? (int)$input['lesson_id'] : null;
$wpm = isset($input['wpm']) ? (float)$input['wpm'] : 0;
$accuracy = isset($input['accuracy']) ? (float)$input['accuracy'] : 0;
$timeSpent = isset($input['time_spent']) ? (int)$input['time_spent'] : 0;

if (!$lessonId) {
    jsonResponse(['success' => false, 'message' => 'Lesson ID is required'], 400);
}

$userId = $_SESSION['user_id'];

// چک کردن وجود درس
$lesson = getLessonById($lessonId);
if (!$lesson) {
    jsonResponse(['success' => false, 'message' => 'Lesson not found'], 404);
}

try {
    // ذخیره پیشرفت
    $stars = saveProgress($userId, $lessonId, $wpm, $accuracy, $timeSpent);
    
    // دریافت پیشرفت بروزرسانی شده
    $progress = getUserLessonProgress($userId, $lessonId);
    
    // چک کردن تکمیل برای اولین بار
    $isFirstCompletion = false;
    $xpEarned = 0;
    
    if ($stars > 0 && !isset($progress['previously_completed'])) {
        $isFirstCompletion = true;
        $xpEarned = $lesson['xp_reward'];
        
        // اعطای نشان‌ها
        checkAndAwardAchievements($userId);
    }
    
    // دریافت اطلاعات کاربر بروزرسانی شده
    $user = getUserById($userId);
    
    jsonResponse([
        'success' => true,
        'message' => 'پیشرفت با موفقیت ذخیره شد',
        'stars' => $stars,
        'is_first_completion' => $isFirstCompletion,
        'xp_earned' => $xpEarned,
        'total_xp' => $user['total_xp'],
        'level' => $user['level'],
        'progress' => [
            'wpm' => $progress['wpm'],
            'accuracy' => $progress['accuracy'],
            'best_wpm' => $progress['best_wpm'],
            'best_accuracy' => $progress['best_accuracy'],
            'attempts' => $progress['attempts']
        ]
    ]);
    
} catch (Exception $e) {
    error_log('Save Progress Error: ' . $e->getMessage());
    jsonResponse([
        'success' => false,
        'message' => 'خطا در ذخیره پیشرفت'
    ], 500);
}

/**
 * بررسی و اعطای نشان‌ها
 */
function checkAndAwardAchievements($userId) {
    $stats = getUserStats($userId);
    
    // نشان اولین درس
    if ($stats['lessons_completed'] >= 1) {
        awardAchievement($userId, 'first_lesson');
    }
    
    // نشان استاد سرعت
    if ($stats['avg_wpm'] >= 60) {
        awardAchievement($userId, 'speed_master');
    }
    
    // نشان پادشاه دقت
    if ($stats['avg_accuracy'] >= 95) {
        awardAchievement($userId, 'accuracy_king');
    }
    
    // نشان تکمیل 10 درس
    if ($stats['lessons_completed'] >= 10) {
        awardAchievement($userId, 'complete_10');
    }
    
    // نشان تکمیل 25 درس
    if ($stats['lessons_completed'] >= 25) {
        awardAchievement($userId, 'complete_25');
    }
    
    // نشان تکمیل همه درس‌ها
    $totalLessons = dbCount('lessons', 'is_active = 1');
    if ($stats['lessons_completed'] >= $totalLessons) {
        awardAchievement($userId, 'complete_all');
    }
}
?>