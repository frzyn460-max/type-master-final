<?php
/**
 * ====================================
 * Save Progress API - FIXED VERSION
 * API ذخیره پیشرفت درس - نسخه اصلاح شده
 * ====================================
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// بررسی ورود کاربر
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit;
}

// فقط POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

try {
    // دریافت داده‌های JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    // اعتبارسنجی
    $lessonId = isset($input['lesson_id']) ? (int)$input['lesson_id'] : 0;
    $wpm = isset($input['wpm']) ? (float)$input['wpm'] : 0;
    $accuracy = isset($input['accuracy']) ? (float)$input['accuracy'] : 0;
    $timeSpent = isset($input['time_spent']) ? (int)$input['time_spent'] : 0;
    $errors = isset($input['errors']) ? (int)$input['errors'] : 0;
    $stars = isset($input['stars']) ? (int)$input['stars'] : 0;
    
    if ($lessonId <= 0) {
        throw new Exception('Invalid lesson ID');
    }
    
    if ($wpm < 0 || $accuracy < 0 || $accuracy > 100) {
        throw new Exception('Invalid stats values');
    }
    
    $userId = $_SESSION['user_id'];
    
    // دریافت اطلاعات درس
    $lesson = getLessonById($lessonId);
    
    if (!$lesson) {
        throw new Exception('Lesson not found');
    }
    
    // دریافت دیتابیس
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // دریافت پیشرفت قبلی
    $sql = "SELECT * FROM user_progress WHERE user_id = :user_id AND lesson_id = :lesson_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['user_id' => $userId, 'lesson_id' => $lessonId]);
    $progress = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // محاسبه is_completed (اگر accuracy >= 85)
    $isCompleted = ($accuracy >= 85) ? 1 : 0;
    
    $isNewRecord = false;
    $xpEarned = 0;
    $leveledUp = false;
    
    if ($progress) {
        // به‌روزرسانی رکورد موجود
        $bestWpm = max($wpm, $progress['best_wpm']);
        $bestAccuracy = max($accuracy, $progress['best_accuracy']);
        $attempts = $progress['attempts'] + 1;
        
        $updateData = [
            'wpm' => $wpm,
            'accuracy' => $accuracy,
            'time_spent' => $progress['time_spent'] + $timeSpent,
            'is_completed' => $isCompleted,
            'stars' => $stars,
            'best_wpm' => $bestWpm,
            'best_accuracy' => $bestAccuracy,
            'attempts' => $attempts
        ];
        
        // اگر الان تکمیل شد و قبلاً تکمیل نشده بود
        if ($isCompleted && !$progress['is_completed']) {
            $updateData['completed_at'] = date('Y-m-d H:i:s');
        }
        
        // Update
        $sql = "UPDATE user_progress SET 
                wpm = :wpm,
                accuracy = :accuracy,
                time_spent = :time_spent,
                is_completed = :is_completed,
                stars = :stars,
                best_wpm = :best_wpm,
                best_accuracy = :best_accuracy,
                attempts = :attempts" .
                ($isCompleted && !$progress['is_completed'] ? ", completed_at = :completed_at" : "") .
                " WHERE user_id = :user_id AND lesson_id = :lesson_id";
        
        $stmt = $conn->prepare($sql);
        
        $params = [
            'wpm' => $wpm,
            'accuracy' => $accuracy,
            'time_spent' => $updateData['time_spent'],
            'is_completed' => $isCompleted,
            'stars' => $stars,
            'best_wpm' => $bestWpm,
            'best_accuracy' => $bestAccuracy,
            'attempts' => $attempts,
            'user_id' => $userId,
            'lesson_id' => $lessonId
        ];
        
        if ($isCompleted && !$progress['is_completed']) {
            $params['completed_at'] = $updateData['completed_at'];
        }
        
        $stmt->execute($params);
        
    } else {
        // Insert جدید
        $isNewRecord = true;
        
        $sql = "INSERT INTO user_progress 
                (user_id, lesson_id, wpm, accuracy, time_spent, is_completed, stars, best_wpm, best_accuracy, attempts, completed_at) 
                VALUES 
                (:user_id, :lesson_id, :wpm, :accuracy, :time_spent, :is_completed, :stars, :best_wpm, :best_accuracy, 1, " .
                ($isCompleted ? ":completed_at" : "NULL") . ")";
        
        $stmt = $conn->prepare($sql);
        
        $params = [
            'user_id' => $userId,
            'lesson_id' => $lessonId,
            'wpm' => $wpm,
            'accuracy' => $accuracy,
            'time_spent' => $timeSpent,
            'is_completed' => $isCompleted,
            'stars' => $stars,
            'best_wpm' => $wpm,
            'best_accuracy' => $accuracy
        ];
        
        if ($isCompleted) {
            $params['completed_at'] = date('Y-m-d H:i:s');
        }
        
        $stmt->execute($params);
    }
    
    // اضافه کردن XP به کاربر (فقط اولین بار که تکمیل میشه)
    if ($isCompleted && ($isNewRecord || !$progress['is_completed'])) {
        $xpEarned = $lesson['xp_reward'];
        
        // Update user XP
        $sql = "UPDATE users SET total_xp = total_xp + :xp WHERE id = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['xp' => $xpEarned, 'user_id' => $userId]);
        
        // محاسبه سطح جدید
        $sql = "SELECT total_xp, level FROM users WHERE id = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $newXp = $user['total_xp'];
        $newLevel = floor($newXp / 1000) + 1; // هر 1000 XP = 1 سطح
        
        // به‌روزرسانی سطح
        if ($newLevel > $user['level']) {
            $sql = "UPDATE users SET level = :level WHERE id = :user_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['level' => $newLevel, 'user_id' => $userId]);
            
            $leveledUp = true;
        }
        
        $_SESSION['total_xp'] = $newXp;
        $_SESSION['level'] = $newLevel;
    }
    
    // بررسی دستاوردها (Achievements)
    $newAchievements = [];
    
    // اولین درس
    if ($isNewRecord && $isCompleted) {
        if (!checkAchievement($userId, 'first_lesson')) {
            grantAchievement($userId, 'first_lesson');
            $newAchievements[] = 'first_lesson';
        }
    }
    
    // پادشاه دقت (Accuracy >= 95)
    if ($accuracy >= 95) {
        if (!checkAchievement($userId, 'accuracy_king')) {
            grantAchievement($userId, 'accuracy_king');
            $newAchievements[] = 'accuracy_king';
        }
    }
    
    // سرعت رعد و برق (WPM >= 60)
    if ($wpm >= 60) {
        if (!checkAchievement($userId, 'speed_demon')) {
            grantAchievement($userId, 'speed_demon');
            $newAchievements[] = 'speed_demon';
        }
    }
    
    // پاسخ موفق
    echo json_encode([
        'success' => true,
        'message' => 'Progress saved successfully',
        'data' => [
            'is_completed' => $isCompleted,
            'stars' => $stars,
            'xp_earned' => $xpEarned,
            'leveled_up' => $leveledUp,
            'new_achievements' => $newAchievements,
            'is_new_record' => $isNewRecord,
            'best_wpm' => isset($bestWpm) ? $bestWpm : $wpm,
            'best_accuracy' => isset($bestAccuracy) ? $bestAccuracy : $accuracy
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * بررسی دستاورد
 */
function checkAchievement($userId, $badgeType) {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $sql = "SELECT id FROM achievements WHERE user_id = :user_id AND badge_type = :badge_type";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['user_id' => $userId, 'badge_type' => $badgeType]);
    
    return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
}

/**
 * اعطای دستاورد
 */
function grantAchievement($userId, $badgeType) {
    $badges = [
        'first_lesson' => [
            'title_fa' => 'تازه‌وارد',
            'title_en' => 'Beginner',
            'icon' => '🔰'
        ],
        'accuracy_king' => [
            'title_fa' => 'پادشاه دقت',
            'title_en' => 'Accuracy King',
            'icon' => '🎯'
        ],
        'speed_demon' => [
            'title_fa' => 'سرعت رعد و برق',
            'title_en' => 'Speed Demon',
            'icon' => '⚡'
        ]
    ];
    
    if (!isset($badges[$badgeType])) {
        return false;
    }
    
    $badge = $badges[$badgeType];
    
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $sql = "INSERT INTO achievements (user_id, badge_type, title_fa, title_en, icon) 
            VALUES (:user_id, :badge_type, :title_fa, :title_en, :icon)";
    
    $stmt = $conn->prepare($sql);
    return $stmt->execute([
        'user_id' => $userId,
        'badge_type' => $badgeType,
        'title_fa' => $badge['title_fa'],
        'title_en' => $badge['title_en'],
        'icon' => $badge['icon']
    ]);
}
?>