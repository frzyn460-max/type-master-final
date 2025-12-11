<?php
/**
 * ====================================
 * Save Progress API
 * API ذخیره پیشرفت درس
 * ====================================
 */

header('Content-Type: application/json');

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
    
    // دریافت پیشرفت قبلی
    $progress = getUserLessonProgress($userId, $lessonId);
    
    // محاسبه is_completed (اگر accuracy >= 85)
    $isCompleted = ($accuracy >= 85) ? 1 : 0;
    
    // آماده‌سازی داده‌ها
    $data = [
        'wpm' => $wpm,
        'accuracy' => $accuracy,
        'time_spent' => $timeSpent,
        'is_completed' => $isCompleted,
        'stars' => $stars
    ];
    
    // اگر پیشرفت قبلی وجود داره
    if ($progress) {
        // به‌روزرسانی best scores
        $bestWpm = max($wpm, $progress['best_wpm']);
        $bestAccuracy = max($accuracy, $progress['best_accuracy']);
        $attempts = $progress['attempts'] + 1;
        
        $data['best_wpm'] = $bestWpm;
        $data['best_accuracy'] = $bestAccuracy;
        $data['attempts'] = $attempts;
        
        // اگر تکمیل شد، زمان تکمیل رو ثبت کن
        if ($isCompleted && !$progress['is_completed']) {
            $data['completed_at'] = date('Y-m-d H:i:s');
        }
        
        // Update
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $fields = [];
        foreach ($data as $key => $value) {
            if ($key === 'completed_at') {
                $fields[] = "$key = '$value'";
            } else {
                $fields[] = "$key = " . (is_numeric($value) ? $value : "'$value'");
            }
        }
        
        $sql = "UPDATE user_progress SET " . implode(', ', $fields) . " WHERE user_id = ? AND lesson_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $userId, $lessonId);
        $stmt->execute();
        
        $isNewRecord = false;
        
    } else {
        // Insert جدید
        $data['user_id'] = $userId;
        $data['lesson_id'] = $lessonId;
        $data['best_wpm'] = $wpm;
        $data['best_accuracy'] = $accuracy;
        $data['attempts'] = 1;
        
        if ($isCompleted) {
            $data['completed_at'] = date('Y-m-d H:i:s');
        }
        
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $types = str_repeat('s', count($data));
        
        $sql = "INSERT INTO user_progress ($columns) VALUES ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...array_values($data));
        $stmt->execute();
        
        $isNewRecord = true;
    }
    
    // اضافه کردن XP به کاربر (فقط اولین بار)
    if ($isCompleted && $isNewRecord) {
        $xpReward = $lesson['xp_reward'];
        
        // Update user XP
        $conn = $db->getConnection();
        $sql = "UPDATE users SET total_xp = total_xp + ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $xpReward, $userId);
        $stmt->execute();
        
        // محاسبه سطح جدید
        $user = getCurrentUser();
        $newXp = $user['total_xp'] + $xpReward;
        $newLevel = calculateLevel($newXp);
        
        // به‌روزرسانی سطح
        if ($newLevel > $user['level']) {
            $sql = "UPDATE users SET level = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ii', $newLevel, $userId);
            $stmt->execute();
            
            $leveledUp = true;
        } else {
            $leveledUp = false;
        }
        
        $_SESSION['total_xp'] = $newXp;
        $_SESSION['level'] = $newLevel;
    } else {
        $xpReward = 0;
        $leveledUp = false;
    }
    
    // بررسی دستاوردها (Achievements)
    $newAchievements = [];
    
    // اولین درس
    if ($isNewRecord && $isCompleted) {
        $hasFirstLesson = checkAchievement($userId, 'first_lesson');
        if (!$hasFirstLesson) {
            grantAchievement($userId, 'first_lesson');
            $newAchievements[] = 'first_lesson';
        }
    }
    
    // پادشاه دقت (Accuracy >= 95)
    if ($accuracy >= 95) {
        $hasAccuracyKing = checkAchievement($userId, 'accuracy_king');
        if (!$hasAccuracyKing) {
            grantAchievement($userId, 'accuracy_king');
            $newAchievements[] = 'accuracy_king';
        }
    }
    
    // سرعت رعد و برق (WPM >= 60)
    if ($wpm >= 60) {
        $hasSpeedDemon = checkAchievement($userId, 'speed_demon');
        if (!$hasSpeedDemon) {
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
            'xp_earned' => $xpReward,
            'leveled_up' => $leveledUp,
            'new_achievements' => $newAchievements,
            'is_new_record' => $isNewRecord,
            'best_wpm' => isset($bestWpm) ? $bestWpm : $wpm,
            'best_accuracy' => isset($bestAccuracy) ? $bestAccuracy : $accuracy
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * محاسبه سطح بر اساس XP
 */
function calculateLevel($xp) {
    // هر 1000 XP = 1 سطح
    return floor($xp / 1000) + 1;
}

/**
 * بررسی دستاورد
 */
function checkAchievement($userId, $badgeType) {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $sql = "SELECT id FROM achievements WHERE user_id = ? AND badge_type = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('is', $userId, $badgeType);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0;
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
        ],
        'perfect_lesson' => [
            'title_fa' => 'کامل',
            'title_en' => 'Perfect',
            'icon' => '💯'
        ],
        'marathon' => [
            'title_fa' => 'ماراتن',
            'title_en' => 'Marathon',
            'icon' => '🏃'
        ]
    ];
    
    if (!isset($badges[$badgeType])) {
        return false;
    }
    
    $badge = $badges[$badgeType];
    
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $sql = "INSERT INTO achievements (user_id, badge_type, title_fa, title_en, icon) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('issss', $userId, $badgeType, $badge['title_fa'], $badge['title_en'], $badge['icon']);
    
    return $stmt->execute();
}