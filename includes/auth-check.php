<?php
/**
 * ====================================
 * Authentication Check
 * بررسی احراز هویت کاربر
 * ====================================
 */

// بارگذاری فایل‌های مورد نیاز
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// شروع Session (فقط یک بار)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * چک کردن لاگین بودن کاربر
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * دریافت کاربر فعلی
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    // اگر اطلاعات کاربر در Session هست
    if (isset($_SESSION['user_data'])) {
        return $_SESSION['user_data'];
    }
    
    // دریافت از دیتابیس
    $user = getUserById($_SESSION['user_id']);
    
    if ($user) {
        // ذخیره در Session برای بار بعد
        $_SESSION['user_data'] = $user;
        return $user;
    }
    
    // اگر کاربر پیدا نشد، Session را پاک کن
    logout();
    return null;
}

/**
 * ورود کاربر
 */
function login($username, $password) {
    // دریافت کاربر
    $user = getUserByUsername($username);
    
    if (!$user) {
        return ['success' => false, 'message' => 'نام کاربری یا رمز عبور اشتباه است'];
    }
    
    // چک کردن پسورد
    if (!verifyPassword($password, $user['password'])) {
        return ['success' => false, 'message' => 'نام کاربری یا رمز عبور اشتباه است'];
    }
    
    // ذخیره در Session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_data'] = $user;
    $_SESSION['language'] = $user['language'];
    $_SESSION['theme'] = $user['theme'];
    
    // به‌روزرسانی آخرین زمان ورود
    updateLastLogin($user['id']);
    
    return ['success' => true, 'message' => 'ورود موفقیت‌آمیز بود', 'user' => $user];
}

/**
 * ثبت‌نام کاربر جدید
 */
function register($username, $email, $password, $passwordConfirm) {
    // اعتبارسنجی ورودی‌ها
    if (empty($username) || empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'لطفاً تمام فیلدها را پر کنید'];
    }
    
    if (!isValidUsername($username)) {
        return ['success' => false, 'message' => 'نام کاربری نامعتبر است'];
    }
    
    if (!isValidEmail($email)) {
        return ['success' => false, 'message' => 'ایمیل نامعتبر است'];
    }
    
    if (!isValidPassword($password)) {
        return ['success' => false, 'message' => 'رمز عبور باید حداقل ' . PASSWORD_MIN_LENGTH . ' کاراکتر باشد'];
    }
    
    if ($password !== $passwordConfirm) {
        return ['success' => false, 'message' => 'رمز عبور و تکرار آن مطابقت ندارند'];
    }
    
    // چک کردن وجود نام کاربری
    if (usernameExists($username)) {
        return ['success' => false, 'message' => 'این نام کاربری قبلاً ثبت شده است'];
    }
    
    // چک کردن وجود ایمیل
    if (emailExists($email)) {
        return ['success' => false, 'message' => 'این ایمیل قبلاً ثبت شده است'];
    }
    
    // ایجاد کاربر
    $userId = createUser($username, $email, $password);
    
    if ($userId) {
        // ورود خودکار
        $user = getUserById($userId);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_data'] = $user;
        $_SESSION['language'] = $user['language'];
        $_SESSION['theme'] = $user['theme'];
        
        // اعطای نشان تازه‌وارد
        awardAchievement($userId, 'first_lesson');
        
        return ['success' => true, 'message' => 'ثبت‌نام با موفقیت انجام شد', 'user' => $user];
    }
    
    return ['success' => false, 'message' => 'خطا در ثبت‌نام. لطفاً دوباره تلاش کنید'];
}

/**
 * خروج کاربر
 */
function logout() {
    // پاک کردن Session
    $_SESSION = [];
    
    // حذف کوکی Session
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // نابود کردن Session
    session_destroy();
    
    return true;
}

/**
 * محافظت صفحات - فقط کاربران لاگین شده
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect(SITE_URL . '/index.php?action=login');
        exit;
    }
}

/**
 * محافظت صفحات - فقط کاربران لاگین نشده
 */
function requireGuest() {
    if (isLoggedIn()) {
        redirect(SITE_URL . '/dashboard.php');
        exit;
    }
}

/**
 * به‌روزرسانی زبان کاربر
 */
function updateUserLanguage($userId, $language) {
    if (!in_array($language, AVAILABLE_LANGUAGES)) {
        return false;
    }
    
    $sql = "UPDATE users SET language = ? WHERE id = ?";
    dbExecute($sql, [$language, $userId]);
    
    $_SESSION['language'] = $language;
    
    // به‌روزرسانی در Session
    if (isset($_SESSION['user_data'])) {
        $_SESSION['user_data']['language'] = $language;
    }
    
    return true;
}

/**
 * به‌روزرسانی تم کاربر
 */
function updateUserTheme($userId, $theme) {
    if (!in_array($theme, AVAILABLE_THEMES)) {
        return false;
    }
    
    $sql = "UPDATE users SET theme = ? WHERE id = ?";
    dbExecute($sql, [$theme, $userId]);
    
    $_SESSION['theme'] = $theme;
    
    // به‌روزرسانی در Session
    if (isset($_SESSION['user_data'])) {
        $_SESSION['user_data']['theme'] = $theme;
    }
    
    return true;
}

/**
 * به‌روزرسانی صدا
 */
function updateUserSound($userId, $soundEnabled) {
    $sql = "UPDATE users SET sound_enabled = ? WHERE id = ?";
    dbExecute($sql, [$soundEnabled ? 1 : 0, $userId]);
    
    // به‌روزرسانی در Session
    if (isset($_SESSION['user_data'])) {
        $_SESSION['user_data']['sound_enabled'] = $soundEnabled;
    }
    
    return true;
}

/**
 * به‌روزرسانی آواتار
 */
function updateUserAvatar($userId, $avatarPath) {
    $sql = "UPDATE users SET avatar = ? WHERE id = ?";
    dbExecute($sql, [$avatarPath, $userId]);
    
    // به‌روزرسانی در Session
    if (isset($_SESSION['user_data'])) {
        $_SESSION['user_data']['avatar'] = $avatarPath;
    }
    
    return true;
}

/**
 * آپلود آواتار
 */
function uploadAvatar($file, $userId) {
    // چک کردن خطا
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'خطا در آپلود فایل'];
    }
    
    // چک کردن سایز
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'message' => 'حجم فایل نباید بیشتر از 5MB باشد'];
    }
    
    // چک کردن نوع فایل
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'message' => 'فرمت فایل مجاز نیست'];
    }
    
    // ایجاد پوشه در صورت عدم وجود
    if (!file_exists(AVATARS_PATH)) {
        mkdir(AVATARS_PATH, 0755, true);
    }
    
    // نام فایل یونیک
    $filename = 'avatar_' . $userId . '_' . time() . '.' . $extension;
    $destination = AVATARS_PATH . $filename;
    
    // انتقال فایل
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        updateUserAvatar($userId, $filename);
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['success' => false, 'message' => 'خطا در ذخیره فایل'];
}

// ====================================
// ✅ فایل احراز هویت آماده است
// ====================================
?>