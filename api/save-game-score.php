<?php
/**
 * ====================================
 * Save Game Score API
 * API ذخیره امتیاز بازی
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
    $gameType = isset($input['game_type']) ? $input['game_type'] : '';
    $score = isset($input['score']) ? (int)$input['score'] : 0;
    $levelReached = isset($input['level_reached']) ? (int)$input['level_reached'] : 1;
    $wpm = isset($input['wpm']) ? (float)$input['wpm'] : 0;
    $accuracy = isset($input['accuracy']) ? (float)$input['accuracy'] : 0;
    $duration = isset($input['duration']) ? (int)$input['duration'] : 0;
    
    // بررسی نوع بازی
    $validGameTypes = ['word-rain', 'speed-race', 'accuracy', 'survival'];
    if (!in_array($gameType, $validGameTypes)) {
        throw new Exception('Invalid game type');
    }
    
    if ($score < 0) {
        throw new Exception('Invalid score');
    }
    
    $userId = $_SESSION['user_id'];
    
    // دریافت بهترین امتیاز قبلی
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $sql = "SELECT MAX(score) as best_score FROM game_scores 
            WHERE user_id = :user_id AND game_type = :game_type";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'user_id' => $userId,
        'game_type' => $gameType
    ]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $previousBest = $result['best_score'] ?? 0;
    
    // ذخیره امتیاز جدید
    $sql = "INSERT INTO game_scores 
            (user_id, game_type, score, wpm, accuracy, level_reached, duration) 
            VALUES (:user_id, :game_type, :score, :wpm, :accuracy, :level_reached, :duration)";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'user_id' => $userId,
        'game_type' => $gameType,
        'score' => $score,
        'wpm' => $wpm,
        'accuracy' => $accuracy,
        'level_reached' => $levelReached,
        'duration' => $duration
    ]);
    
    // بررسی رکورد جدید
    $isNewRecord = ($score > $previousBest);
    
    // اعطای XP (10% از امتیاز)
    $xpEarned = floor($score * 0.1);
    
    if ($xpEarned > 0) {
        $sql = "UPDATE users SET total_xp = total_xp + :xp WHERE id = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'xp' => $xpEarned,
            'user_id' => $userId
        ]);
        
        // محاسبه سطح جدید
        $user = getCurrentUser();
        $newXp = $user['total_xp'] + $xpEarned;
        $newLevel = calculateLevel($newXp);
        
        // به‌روزرسانی سطح
        if ($newLevel > $user['level']) {
            $sql = "UPDATE users SET level = :level WHERE id = :user_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'level' => $newLevel,
                'user_id' => $userId
            ]);
            
            $leveledUp = true;
        } else {
            $leveledUp = false;
        }
        
        $_SESSION['total_xp'] = $newXp;
        $_SESSION['level'] = $newLevel;
    } else {
        $leveledUp = false;
    }
    
    // بررسی دستاوردها
    $newAchievements = [];
    
    // اولین بازی
    $sql = "SELECT COUNT(*) as count FROM game_scores WHERE user_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['user_id' => $userId]);
    $gameCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($gameCount == 1) {
        if (!checkAchievement($userId, 'first_game')) {
            grantAchievement($userId, 'first_game');
            $newAchievements[] = 'first_game';
        }
    }
    
    // امتیاز بالا (1000+)
    if ($score >= 1000) {
        if (!checkAchievement($userId, 'score_1000')) {
            grantAchievement($userId, 'score_1000');
            $newAchievements[] = 'score_1000';
        }
    }
    
    // پاسخ موفق
    echo json_encode([
        'success' => true,
        'message' => 'Score saved successfully',
        'data' => [
            'score' => $score,
            'previous_best' => $previousBest,
            'is_new_record' => $isNewRecord,
            'xp_earned' => $xpEarned,
            'leveled_up' => $leveledUp,
            'new_achievements' => $newAchievements
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
    return floor($xp / 1000) + 1;
}

/**
 * بررسی دستاورد
 */
function checkAchievement($userId, $badgeType) {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $sql = "SELECT id FROM achievements WHERE user_id = :user_id AND badge_type = :badge_type";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'user_id' => $userId,
        'badge_type' => $badgeType
    ]);
    
    return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
}

/**
 * اعطای دستاورد
 */
function grantAchievement($userId, $badgeType) {
    $badges = [
        'first_game' => [
            'title_fa' => 'اولین بازی',
            'title_en' => 'First Game',
            'icon' => '🎮'
        ],
        'score_1000' => [
            'title_fa' => 'هزار امتیاز',
            'title_en' => 'Score 1000',
            'icon' => '🏆'
        ],
        'game_master' => [
            'title_fa' => 'استاد بازی',
            'title_en' => 'Game Master',
            'icon' => '👑'
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