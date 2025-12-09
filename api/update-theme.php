<?php
/**
 * ====================================
 * Update Theme API
 * API تغییر تم
 * ====================================
 */

session_start();

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
    $input = $_POST;
}

$theme = $input['theme'] ?? '';

// اعتبارسنجی
if (!in_array($theme, AVAILABLE_THEMES)) {
    jsonResponse([
        'success' => false,
        'message' => 'تم نامعتبر است'
    ], 400);
}

// به‌روزرسانی تم
$userId = $_SESSION['user_id'];
$result = updateUserTheme($userId, $theme);

if ($result) {
    jsonResponse([
        'success' => true,
        'message' => 'تم با موفقیت تغییر یافت',
        'theme' => $theme
    ]);
} else {
    jsonResponse([
        'success' => false,
        'message' => 'خطا در تغییر تم'
    ], 500);
}
?>