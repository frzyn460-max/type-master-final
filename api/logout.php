<?php
/**
 * ====================================
 * Logout API
 * API خروج از سیستم
 * ====================================
 */

// شروع session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// پاک کردن تمام session ها
$_SESSION = array();

// حذف session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// نابود کردن session
session_destroy();

// ریدایرکت به صفحه اصلی
header('Location: ../index.php');
exit;
?>