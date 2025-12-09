<?php
/**
 * ====================================
 * Update Profile API
 * API بروزرسانی پروفایل و تنظیمات
 * ====================================
 */

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

$userId = $_SESSION['user_id'];

// ====================================
// 1. آپلود آواتار
// ====================================
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    handleAvatarUpload($userId);
}

// ====================================
// 2. بروزرسانی تنظیمات
// ====================================
$input = json_decode(file_get_contents('php://input'), true);

if ($input && isset($input['theme'])) {
    handleSettingsUpdate($userId, $input);
}

// اگر هیچ درخواستی نبود
jsonResponse(['success' => false, 'message' => 'No action specified'], 400);

// ====================================
// توابع کمکی
// ====================================

/**
 * مدیریت آپلود آواتار
 */
function handleAvatarUpload($userId) {
    $file = $_FILES['avatar'];
    
    // اعتبارسنجی
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    // چک نوع فایل
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        jsonResponse([
            'success' => false,
            'message' => 'فرمت فایل مجاز نیست. فقط JPG, PNG, GIF, WEBP'
        ], 400);
    }
    
    // چک حجم
    if ($file['size'] > $maxSize) {
        jsonResponse([
            'success' => false,
            'message' => 'حجم فایل نباید بیشتر از 5MB باشد'
        ], 400);
    }
    
    // ایجاد پوشه در صورت عدم وجود
    $uploadDir = __DIR__ . '/../uploads/avatars/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // نام یونیک برای فایل
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'avatar_' . $userId . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // حذف آواتار قبلی (اگر وجود داشته باشد)
    $user = getUserById($userId);
    if ($user['avatar'] && $user['avatar'] !== 'default-avatar.png') {
        $oldAvatar = $uploadDir . $user['avatar'];
        if (file_exists($oldAvatar)) {
            unlink($oldAvatar);
        }
    }
    
    // انتقال فایل
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // کوچک کردن تصویر (اختیاری)
        resizeImage($filepath, 300, 300);
        
        // بروزرسانی در دیتابیس
        updateUserAvatar($userId, $filename);
        
        jsonResponse([
            'success' => true,
            'message' => 'آواتار با موفقیت تغییر یافت',
            'filename' => $filename
        ]);
    } else {
        jsonResponse([
            'success' => false,
            'message' => 'خطا در آپلود فایل'
        ], 500);
    }
}

/**
 * مدیریت بروزرسانی تنظیمات
 */
function handleSettingsUpdate($userId, $data) {
    $updateData = [];
    $changes = [];
    
    // تم
    if (isset($data['theme']) && in_array($data['theme'], AVAILABLE_THEMES)) {
        $updateData['theme'] = $data['theme'];
        $_SESSION['theme'] = $data['theme'];
        $changes[] = 'theme';
    }
    
    // زبان
    if (isset($data['language']) && in_array($data['language'], AVAILABLE_LANGUAGES)) {
        $updateData['language'] = $data['language'];
        $_SESSION['language'] = $data['language'];
        $changes[] = 'language';
    }
    
    // صدا
    if (isset($data['sound'])) {
        $updateData['sound_enabled'] = (int)$data['sound'];
        $changes[] = 'sound';
    }
    
    // بروزرسانی در دیتابیس
    if (!empty($updateData)) {
        dbUpdate('users', $updateData, 'id = ?', [$userId]);
        
        // بروزرسانی Session
        if (isset($_SESSION['user_data'])) {
            foreach ($updateData as $key => $value) {
                $_SESSION['user_data'][$key] = $value;
            }
        }
        
        jsonResponse([
            'success' => true,
            'message' => 'تنظیمات با موفقیت ذخیره شد',
            'changes' => $changes
        ]);
    }
    
    jsonResponse([
        'success' => false,
        'message' => 'هیچ تغییری اعمال نشد'
    ], 400);
}

/**
 * کوچک کردن تصویر
 */
function resizeImage($filepath, $maxWidth, $maxHeight) {
    // دریافت اطلاعات تصویر
    $imageInfo = getimagesize($filepath);
    if (!$imageInfo) return false;
    
    list($width, $height, $type) = $imageInfo;
    
    // اگر تصویر کوچک‌تر از حد مجاز است، نیازی به تغییر اندازه نیست
    if ($width <= $maxWidth && $height <= $maxHeight) {
        return true;
    }
    
    // محاسبه نسبت
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = (int)($width * $ratio);
    $newHeight = (int)($height * $ratio);
    
    // ایجاد تصویر منبع
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($filepath);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($filepath);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($filepath);
            break;
        case IMAGETYPE_WEBP:
            $source = imagecreatefromwebp($filepath);
            break;
        default:
            return false;
    }
    
    // ایجاد تصویر جدید
    $newImage = imagecreatetruecolor($newWidth, $newHeight);
    
    // حفظ شفافیت برای PNG و GIF
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
        imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
    }
    
    // تغییر اندازه
    imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // ذخیره تصویر
    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($newImage, $filepath, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($newImage, $filepath, 9);
            break;
        case IMAGETYPE_GIF:
            imagegif($newImage, $filepath);
            break;
        case IMAGETYPE_WEBP:
            imagewebp($newImage, $filepath, 90);
            break;
    }
    
    // آزادسازی حافظه
    imagedestroy($source);
    imagedestroy($newImage);
    
    return true;
}

/**
 * حذف آواتار قدیمی
 */
function deleteOldAvatar($userId) {
    $user = getUserById($userId);
    if ($user && $user['avatar'] && $user['avatar'] !== 'default-avatar.png') {
        $oldAvatarPath = __DIR__ . '/../uploads/avatars/' . $user['avatar'];
        if (file_exists($oldAvatarPath)) {
            unlink($oldAvatarPath);
        }
    }
}
?>