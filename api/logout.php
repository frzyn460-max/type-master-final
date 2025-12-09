<?php
/**
 * ====================================
 * Logout API
 * API خروج از سیستم
 * ====================================
 */

session_start();

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth-check.php';

// خروج از سیستم
logout();

// ریدایرکت به صفحه اصلی
redirect(SITE_URL . '/index.php');
?>