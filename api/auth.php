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

// تنظیم header برای JSON
header('Content-Type: application/json; charset=utf-8');

// Session در auth-check شروع می‌شود

// فقط درخواست POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
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
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
}

/**
 * هندل کردن ورود
 */
function handleLogin($data) {
    $username = cleanInput($data['username'] ?? '');
    $password = $data['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'لطفاً نام کاربری و رمز عبور را وارد کنید'
        ]);
        exit;
    }
    
    $result = login($username, $password);
    
    if ($result['success']) {
        http_response_code(200);
        echo json_encode([
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
            'redirect' => './dashboard.php'
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => $result['message']
        ]);
    }
    exit;
}

/**
 * هندل کردن ثبت‌نام
 */
function handleRegister($data) {
    $username = cleanInput($data['username'] ?? '');
    $email = cleanInput($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $passwordConfirm = $data['password_confirm'] ?? '';
    
    // اعتبارسنجی
    if (empty($username) || empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'لطفاً تمام فیلدها را پر کنید'
        ]);
        exit;
    }
    
    if ($password !== $passwordConfirm) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'رمز عبور و تکرار آن مطابقت ندارند'
        ]);
        exit;
    }
    
    $result = register($username, $email, $password, $passwordConfirm);
    
    if ($result['success']) {
        http_response_code(200);
        echo json_encode([
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
            'redirect' => './dashboard.php'
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $result['message']
        ]);
    }
    exit;
}
?>