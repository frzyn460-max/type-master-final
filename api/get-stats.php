<?php
/**
 * ====================================
 * Get Stats API
 * API دریافت آمار کاربر
 * ====================================
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// بررسی لاگین
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // ====================================
    // 1. آمار کلی
    // ====================================
    
    // زمان تمرین
    $stmt = $conn->prepare("
        SELECT SUM(time_spent) as total_time 
        FROM user_progress 
        WHERE user_id = :user_id
    ");
    $stmt->execute(['user_id' => $user_id]);
    $total_time = $stmt->fetch(PDO::FETCH_ASSOC)['total_time'] ?? 0;
    
    // تعداد درس‌های تکمیل شده
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total_lessons 
        FROM user_progress 
        WHERE user_id = :user_id AND is_completed = 1
    ");
    $stmt->execute(['user_id' => $user_id]);
    $total_lessons = $stmt->fetch(PDO::FETCH_ASSOC)['total_lessons'];
    
    // تعداد بازی‌ها
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total_games 
        FROM game_scores 
        WHERE user_id = :user_id
    ");
    $stmt->execute(['user_id' => $user_id]);
    $total_games = $stmt->fetch(PDO::FETCH_ASSOC)['total_games'];
    
    // میانگین WPM
    $stmt = $conn->prepare("
        SELECT AVG(wpm) as avg_wpm 
        FROM (
            SELECT wpm FROM user_progress WHERE user_id = :user_id AND wpm > 0
            UNION ALL
            SELECT wpm FROM game_scores WHERE user_id = :user_id AND wpm > 0
        ) as combined
    ");
    $stmt->execute(['user_id' => $user_id]);
    $avg_wpm = $stmt->fetch(PDO::FETCH_ASSOC)['avg_wpm'] ?? 0;
    
    // میانگین دقت
    $stmt = $conn->prepare("
        SELECT AVG(accuracy) as avg_accuracy 
        FROM (
            SELECT accuracy FROM user_progress WHERE user_id = :user_id AND accuracy > 0
            UNION ALL
            SELECT accuracy FROM game_scores WHERE user_id = :user_id AND accuracy > 0
        ) as combined
    ");
    $stmt->execute(['user_id' => $user_id]);
    $avg_accuracy = $stmt->fetch(PDO::FETCH_ASSOC)['avg_accuracy'] ?? 0;
    
    // مجموع XP
    $stmt = $conn->prepare("SELECT total_xp FROM users WHERE id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $total_xp = $stmt->fetch(PDO::FETCH_ASSOC)['total_xp'];
    
    // ====================================
    // 2. پیشرفت WPM (7 روز اخیر)
    // ====================================
    $stmt = $conn->prepare("
        SELECT 
            DATE(updated_at) as date,
            AVG(wpm) as wpm
        FROM (
            SELECT updated_at, wpm FROM user_progress 
            WHERE user_id = :user_id AND wpm > 0 AND updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            UNION ALL
            SELECT created_at as updated_at, wpm FROM game_scores 
            WHERE user_id = :user_id AND wpm > 0 AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ) as combined
        GROUP BY DATE(updated_at)
        ORDER BY date ASC
    ");
    $stmt->execute(['user_id' => $user_id]);
    $wpm_progress = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // ====================================
    // 3. پیشرفت دقت (7 روز اخیر)
    // ====================================
    $stmt = $conn->prepare("
        SELECT 
            DATE(updated_at) as date,
            AVG(accuracy) as accuracy
        FROM (
            SELECT updated_at, accuracy FROM user_progress 
            WHERE user_id = :user_id AND accuracy > 0 AND updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            UNION ALL
            SELECT created_at as updated_at, accuracy FROM game_scores 
            WHERE user_id = :user_id AND accuracy > 0 AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ) as combined
        GROUP BY DATE(updated_at)
        ORDER BY date ASC
    ");
    $stmt->execute(['user_id' => $user_id]);
    $accuracy_progress = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // ====================================
    // 4. عملکرد درس‌ها (10 درس برتر)
    // ====================================
    $stmt = $conn->prepare("
        SELECT 
            l.title_fa as lesson_name,
            up.best_wpm as wpm,
            up.best_accuracy as accuracy
        FROM user_progress up
        JOIN lessons l ON up.lesson_id = l.id
        WHERE up.user_id = :user_id AND up.is_completed = 1
        ORDER BY up.best_wpm DESC
        LIMIT 10
    ");
    $stmt->execute(['user_id' => $user_id]);
    $lessons_performance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // ====================================
    // 5. عملکرد بازی‌ها
    // ====================================
    $stmt = $conn->prepare("
        SELECT 
            game_type,
            CASE game_type
                WHEN 'word-rain' THEN 'بارش کلمات'
                WHEN 'speed-race' THEN 'مسابقه سرعت'
                WHEN 'accuracy' THEN 'چالش دقت'
                WHEN 'survival' THEN 'حالت بقا'
                WHEN 'code-master' THEN 'استاد کدنویسی'
            END as game_name,
            MAX(score) as best_score,
            MAX(wpm) as best_wpm,
            MAX(accuracy) as best_accuracy
        FROM game_scores
        WHERE user_id = :user_id
        GROUP BY game_type
        ORDER BY best_score DESC
    ");
    $stmt->execute(['user_id' => $user_id]);
    $games_performance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // ====================================
    // خروجی نهایی
    // ====================================
    echo json_encode([
        'success' => true,
        'data' => [
            'overview' => [
                'total_time' => (float)$total_time,
                'total_lessons' => (int)$total_lessons,
                'total_games' => (int)$total_games,
                'avg_wpm' => round((float)$avg_wpm, 2),
                'avg_accuracy' => round((float)$avg_accuracy, 2),
                'total_xp' => (int)$total_xp
            ],
            'wpm_progress' => $wpm_progress,
            'accuracy_progress' => $accuracy_progress,
            'lessons_performance' => $lessons_performance,
            'games_performance' => $games_performance
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'خطا در دریافت آمار: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>