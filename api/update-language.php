<?php
/**
 * ====================================
 * Update Language API
 * API تغییر زبان
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

// چک کردن لاگین
if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

// دریافت داده‌ها
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$language = $input['language'] ?? '';

// اعتبارسنجی
if (!in_array($language, AVAILABLE_LANGUAGES)) {
    jsonResponse([
        'success' => false,
        'message' => 'زبان نامعتبر است'
    ], 400);
}

// به‌روزرسانی زبان
$userId = $_SESSION['user_id'];
$result = updateUserLanguage($userId, $language);

if ($result) {
    jsonResponse([
        'success' => true,
        'message' => 'زبان با موفقیت تغییر یافت',
        'language' => $language
    ]);
} else {
    jsonResponse([
        'success' => false,
        'message' => 'خطا در تغییر زبان'
    ], 500);
}
?>