<?php
/**
 * ====================================
 * Authentication API
 * API ورود و ثبت‌نام
 * ====================================
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-check.php';

// Session در auth-check شروع می‌شود

// فقط درخواست POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

// دریافت داده‌های JSON
$input = json_decode(file_get_contents('php://input'), true);

// اگر داده JSON نیست، از POST معمولی استفاده کن
if (!$input) {
    $input = $_POST;
}

// چک کردن نوع عملیات
$action = $input['action'] ?? '';

switch ($action) {
    case 'login':
        handleLogin($input);
        break;
        
    case 'register':
        handleRegister($input);
        break;
        
    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}

/**
 * هندل کردن ورود
 */
function handleLogin($data) {
    $username = cleanInput($data['username'] ?? '');
    $password = $data['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        jsonResponse([
            'success' => false,
            'message' => 'لطفاً نام کاربری و رمز عبور را وارد کنید'
        ]);
    }
    
    $result = login($username, $password);
    
    if ($result['success']) {
        jsonResponse([
            'success' => true,
            'message' => 'خوش آمدید!',
            'user' => [
                'id' => $result['user']['id'],
                'username' => $result['user']['username'],
                'email' => $result['user']['email'],
                'avatar' => $result['user']['avatar'],
                'level' => $result['user']['level'],
                'total_xp' => $result['user']['total_xp'],
                'language' => $result['user']['language'],
                'theme' => $result['user']['theme']
            ],
            'redirect' => SITE_URL . '/dashboard.php'
        ]);
    } else {
        jsonResponse([
            'success' => false,
            'message' => $result['message']
        ], 401);
    }
}

/**
 * هندل کردن ثبت‌نام
 */
function handleRegister($data) {
    $username = cleanInput($data['username'] ?? '');
    $email = cleanInput($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $passwordConfirm = $data['password_confirm'] ?? '';
    
    $result = register($username, $email, $password, $passwordConfirm);
    
    if ($result['success']) {
        jsonResponse([
            'success' => true,
            'message' => 'ثبت‌نام با موفقیت انجام شد!',
            'user' => [
                'id' => $result['user']['id'],
                'username' => $result['user']['username'],
                'email' => $result['user']['email'],
                'avatar' => $result['user']['avatar'],
                'level' => $result['user']['level'],
                'total_xp' => $result['user']['total_xp'],
                'language' => $result['user']['language'],
                'theme' => $result['user']['theme']
            ],
            'redirect' => SITE_URL . '/dashboard.php'
        ]);
    } else {
        jsonResponse([
            'success' => false,
            'message' => $result['message']
        ], 400);
    }
}
?>