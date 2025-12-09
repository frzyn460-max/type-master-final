<?php
/**
 * ====================================
 * Helper Functions
 * توابع کمکی و کاربردی پروژه
 * ====================================
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// ====================================
// 🔒 توابع امنیتی
// ====================================

/**
 * رمزنگاری پسورد
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * تطبیق پسورد
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * تولید توکن تصادفی
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * پاکسازی ورودی
 */
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * اعتبارسنجی ایمیل
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * اعتبارسنجی نام کاربری
 */
function isValidUsername($username) {
    $length = mb_strlen($username);
    return $length >= USERNAME_MIN_LENGTH && 
           $length <= USERNAME_MAX_LENGTH && 
           preg_match('/^[a-zA-Z0-9_]+$/', $username);
}

/**
 * اعتبارسنجی پسورد
 */
function isValidPassword($password) {
    return strlen($password) >= PASSWORD_MIN_LENGTH;
}

// ====================================
// 👤 توابع کاربر
// ====================================

/**
 * دریافت اطلاعات کاربر با ID
 */
function getUserById($userId) {
    return dbGetById('users', $userId);
}

/**
 * دریافت اطلاعات کاربر با نام کاربری
 */
function getUserByUsername($username) {
    $sql = "SELECT * FROM users WHERE username = ? LIMIT 1";
    return dbQueryOne($sql, [$username]);
}

/**
 * دریافت اطلاعات کاربر با ایمیل
 */
function getUserByEmail($email) {
    $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
    return dbQueryOne($sql, [$email]);
}

/**
 * چک کردن وجود نام کاربری
 */
function usernameExists($username) {
    return dbExists('users', 'username = ?', [$username]);
}

/**
 * چک کردن وجود ایمیل
 */
function emailExists($email) {
    return dbExists('users', 'email = ?', [$email]);
}

/**
 * ایجاد کاربر جدید
 */
function createUser($username, $email, $password) {
    $data = [
        'username' => $username,
        'email' => $email,
        'password' => hashPassword($password),
        'language' => DEFAULT_LANGUAGE,
        'theme' => DEFAULT_THEME,
        'total_xp' => 0,
        'level' => 1
    ];
    
    return dbInsert('users', $data);
}

/**
 * به‌روزرسانی آخرین زمان ورود
 */
function updateLastLogin($userId) {
    $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
    return dbExecute($sql, [$userId]);
}

/**
 * محاسبه سطح از روی XP
 */
function calculateLevel($xp) {
    return floor($xp / XP_PER_LEVEL) + 1;
}

/**
 * محاسبه XP برای سطح بعدی
 */
function getXpForNextLevel($currentXp) {
    $currentLevel = calculateLevel($currentXp);
    $nextLevelXp = $currentLevel * XP_PER_LEVEL;
    return $nextLevelXp - $currentXp;
}

/**
 * اضافه کردن XP به کاربر
 */
function addXpToUser($userId, $xp) {
    $user = getUserById($userId);
    $newXp = $user['total_xp'] + $xp;
    $newLevel = calculateLevel($newXp);
    
    $sql = "UPDATE users SET total_xp = ?, level = ? WHERE id = ?";
    dbExecute($sql, [$newXp, $newLevel, $userId]);
    
    // چک کردن ارتقا سطح
    if ($newLevel > $user['level']) {
        awardAchievement($userId, 'level_up_' . $newLevel);
        return ['leveled_up' => true, 'new_level' => $newLevel];
    }
    
    return ['leveled_up' => false];
}

// ====================================
// 🎓 توابع درس‌ها
// ====================================

/**
 * دریافت تمام درس‌ها
 */
function getLessons($language = null, $level = null) {
    $where = "is_active = 1";
    $params = [];
    
    if ($language) {
        $where .= " AND language = ?";
        $params[] = $language;
    }
    
    if ($level) {
        $where .= " AND level = ?";
        $params[] = $level;
    }
    
    $sql = "SELECT * FROM lessons WHERE {$where} ORDER BY order_index ASC";
    return dbQuery($sql, $params);
}

/**
 * دریافت درس با ID
 */
function getLessonById($lessonId) {
    return dbGetById('lessons', $lessonId);
}

/**
 * دریافت پیشرفت کاربر در درس
 */
function getUserLessonProgress($userId, $lessonId) {
    $sql = "SELECT * FROM user_progress WHERE user_id = ? AND lesson_id = ? LIMIT 1";
    return dbQueryOne($sql, [$userId, $lessonId]);
}

/**
 * ذخیره پیشرفت درس
 */
function saveProgress($userId, $lessonId, $wpm, $accuracy, $timeSpent) {
    $progress = getUserLessonProgress($userId, $lessonId);
    $stars = calculateStars($wpm, $accuracy);
    
    $data = [
        'wpm' => $wpm,
        'accuracy' => $accuracy,
        'time_spent' => $timeSpent,
        'stars' => $stars,
        'is_completed' => ($stars > 0) ? 1 : 0
    ];
    
    if ($progress) {
        // به‌روزرسانی رکورد موجود
        $data['attempts'] = $progress['attempts'] + 1;
        $data['best_wpm'] = max($progress['best_wpm'], $wpm);
        $data['best_accuracy'] = max($progress['best_accuracy'], $accuracy);
        
        if ($data['is_completed'] && !$progress['is_completed']) {
            $data['completed_at'] = date('Y-m-d H:i:s');
        }
        
        dbUpdate('user_progress', $data, 'user_id = ? AND lesson_id = ?', [$userId, $lessonId]);
    } else {
        // رکورد جدید
        $data['user_id'] = $userId;
        $data['lesson_id'] = $lessonId;
        $data['attempts'] = 1;
        $data['best_wpm'] = $wpm;
        $data['best_accuracy'] = $accuracy;
        
        if ($data['is_completed']) {
            $data['completed_at'] = date('Y-m-d H:i:s');
        }
        
        dbInsert('user_progress', $data);
    }
    
    // اضافه کردن XP
    if ($data['is_completed'] && (!$progress || !$progress['is_completed'])) {
        $lesson = getLessonById($lessonId);
        addXpToUser($userId, $lesson['xp_reward']);
    }
    
    return $stars;
}

/**
 * محاسبه ستاره‌ها
 */
function calculateStars($wpm, $accuracy) {
    if ($accuracy < STARS_ACCURACY_MIN) {
        return 0;
    }
    
    if ($wpm >= STARS_WPM_THRESHOLD_3) {
        return 3;
    } elseif ($wpm >= STARS_WPM_THRESHOLD_2) {
        return 2;
    } elseif ($wpm >= STARS_WPM_THRESHOLD_1) {
        return 1;
    }
    
    return 0;
}

// ====================================
// 🎮 توابع بازی
// ====================================

/**
 * ذخیره امتیاز بازی
 */
function saveGameScore($userId, $gameType, $score, $wpm, $accuracy, $level, $duration) {
    $data = [
        'user_id' => $userId,
        'game_type' => $gameType,
        'score' => $score,
        'wpm' => $wpm,
        'accuracy' => $accuracy,
        'level_reached' => $level,
        'duration' => $duration
    ];
    
    return dbInsert('game_scores', $data);
}

/**
 * دریافت بهترین امتیازات کاربر
 */
function getUserBestScores($userId, $gameType = null) {
    $where = "user_id = ?";
    $params = [$userId];
    
    if ($gameType) {
        $where .= " AND game_type = ?";
        $params[] = $gameType;
    }
    
    $sql = "SELECT game_type, MAX(score) as best_score, MAX(wpm) as best_wpm 
            FROM game_scores 
            WHERE {$where} 
            GROUP BY game_type";
    
    return dbQuery($sql, $params);
}

// ====================================
// 🏆 توابع نشان‌ها
// ====================================

/**
 * اعطای نشان به کاربر
 */
function awardAchievement($userId, $badgeType) {
    // چک کردن وجود نشان
    if (dbExists('achievements', 'user_id = ? AND badge_type = ?', [$userId, $badgeType])) {
        return false;
    }
    
    $badges = $GLOBALS['badges'];
    
    if (isset($badges[$badgeType])) {
        $badge = $badges[$badgeType];
        
        $data = [
            'user_id' => $userId,
            'badge_type' => $badgeType,
            'title_fa' => $badge['title_fa'],
            'title_en' => $badge['title_en'],
            'icon' => $badge['icon']
        ];
        
        return dbInsert('achievements', $data);
    }
    
    return false;
}

/**
 * دریافت نشان‌های کاربر
 */
function getUserAchievements($userId) {
    $sql = "SELECT * FROM achievements WHERE user_id = ? ORDER BY earned_at DESC";
    return dbQuery($sql, [$userId]);
}

// ====================================
// 📊 توابع آمار
// ====================================

/**
 * دریافت آمار کلی کاربر
 */
function getUserStats($userId) {
    $stats = [];
    
    // تعداد درس‌های تکمیل شده
    $stats['lessons_completed'] = dbCount('user_progress', 'user_id = ? AND is_completed = 1', [$userId]);
    
    // میانگین WPM
    $sql = "SELECT AVG(wpm) as avg_wpm FROM user_progress WHERE user_id = ? AND is_completed = 1";
    $result = dbQueryOne($sql, [$userId]);
    $stats['avg_wpm'] = round($result['avg_wpm'] ?? 0, 2);
    
    // میانگین دقت
    $sql = "SELECT AVG(accuracy) as avg_accuracy FROM user_progress WHERE user_id = ? AND is_completed = 1";
    $result = dbQueryOne($sql, [$userId]);
    $stats['avg_accuracy'] = round($result['avg_accuracy'] ?? 0, 2);
    
    // مجموع زمان تمرین
    $sql = "SELECT SUM(time_spent) as total_time FROM user_progress WHERE user_id = ?";
    $result = dbQueryOne($sql, [$userId]);
    $stats['total_time'] = $result['total_time'] ?? 0;
    
    // تعداد بازی‌ها
    $stats['games_played'] = dbCount('game_scores', 'user_id = ?', [$userId]);
    
    // تعداد نشان‌ها
    $stats['achievements_count'] = dbCount('achievements', 'user_id = ?', [$userId]);
    
    return $stats;
}

// ====================================
// 🌍 توابع زبان
// ====================================

/**
 * دریافت زبان فعلی
 */
function getCurrentLanguage() {
    if (isset($_SESSION['language'])) {
        return $_SESSION['language'];
    }
    return DEFAULT_LANGUAGE;
}

/**
 * تغییر زبان
 */
function setLanguage($lang) {
    if (in_array($lang, AVAILABLE_LANGUAGES)) {
        $_SESSION['language'] = $lang;
        return true;
    }
    return false;
}

// ====================================
// 🎨 توابع تم
// ====================================

/**
 * دریافت تم فعلی
 */
function getCurrentTheme() {
    if (isset($_SESSION['theme'])) {
        return $_SESSION['theme'];
    }
    return DEFAULT_THEME;
}

/**
 * تغییر تم
 */
function setTheme($theme) {
    if (in_array($theme, AVAILABLE_THEMES)) {
        $_SESSION['theme'] = $theme;
        return true;
    }
    return false;
}

// ====================================
// 🔧 توابع کمکی عمومی
// ====================================

/**
 * فرمت کردن تاریخ فارسی
 */
function formatPersianDate($timestamp) {
    return jdate('Y/m/d H:i', strtotime($timestamp));
}

/**
 * تبدیل ثانیه به فرمت قابل خواندن
 */
function formatDuration($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    
    if ($hours > 0) {
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }
    return sprintf('%02d:%02d', $minutes, $secs);
}

/**
 * JSON Response
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Redirect
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}

// ====================================
// ✅ فایل توابع آماده است
// ====================================
?>