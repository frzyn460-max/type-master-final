<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth-check.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

requireLogin();

$user = getCurrentUser();
$achievements = getUserAchievements($user['id']);
$stats = getUserStats($user['id']);

$currentLang = getCurrentLanguage();
$currentTheme = getCurrentTheme();

// ترجمه‌ها
$t = [
    'fa' => [
        'profile' => 'پروفایل',
        'edit_profile' => 'ویرایش پروفایل',
        'account_info' => 'اطلاعات حساب',
        'username' => 'نام کاربری',
        'email' => 'ایمیل',
        'member_since' => 'عضو از تاریخ',
        'level' => 'سطح',
        'total_xp' => 'مجموع امتیاز',
        'settings' => 'تنظیمات',
        'theme' => 'تم',
        'language' => 'زبان',
        'sound' => 'صدا',
        'enabled' => 'فعال',
        'disabled' => 'غیرفعال',
        'dark' => 'تیره',
        'light' => 'روشن',
        'persian' => 'فارسی',
        'english' => 'English',
        'change_avatar' => 'تغییر آواتار',
        'upload' => 'آپلود',
        'achievements' => 'نشان‌ها',
        'all_achievements' => 'تمام نشان‌ها',
        'earned' => 'کسب شده',
        'statistics' => 'آمار',
        'lessons_completed' => 'درس تکمیل شده',
        'avg_wpm' => 'میانگین سرعت',
        'avg_accuracy' => 'میانگین دقت',
        'total_time' => 'زمان تمرین',
        'games_played' => 'بازی انجام شده',
        'save_changes' => 'ذخیره تغییرات',
        'password' => 'رمز عبور',
        'change_password' => 'تغییر رمز عبور',
        'current_password' => 'رمز عبور فعلی',
        'new_password' => 'رمز عبور جدید',
        'confirm_password' => 'تکرار رمز عبور'
    ],
    'en' => [
        'profile' => 'Profile',
        'edit_profile' => 'Edit Profile',
        'account_info' => 'Account Information',
        'username' => 'Username',
        'email' => 'Email',
        'member_since' => 'Member Since',
        'level' => 'Level',
        'total_xp' => 'Total XP',
        'settings' => 'Settings',
        'theme' => 'Theme',
        'language' => 'Language',
        'sound' => 'Sound',
        'enabled' => 'Enabled',
        'disabled' => 'Disabled',
        'dark' => 'Dark',
        'light' => 'Light',
        'persian' => 'فارسی',
        'english' => 'English',
        'change_avatar' => 'Change Avatar',
        'upload' => 'Upload',
        'achievements' => 'Achievements',
        'all_achievements' => 'All Achievements',
        'earned' => 'Earned',
        'statistics' => 'Statistics',
        'lessons_completed' => 'Lessons Completed',
        'avg_wpm' => 'Average WPM',
        'avg_accuracy' => 'Average Accuracy',
        'total_time' => 'Practice Time',
        'games_played' => 'Games Played',
        'save_changes' => 'Save Changes',
        'password' => 'Password',
        'change_password' => 'Change Password',
        'current_password' => 'Current Password',
        'new_password' => 'New Password',
        'confirm_password' => 'Confirm Password'
    ]
];

$tr = $t[$currentLang];
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>" dir="<?= $currentLang === 'fa' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tr['profile'] ?> - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/themes.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/dashboard.css">
</head>
<body class="theme-<?= $currentTheme ?> lang-<?= $currentLang ?>">
    
    <!-- Sidebar -->
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Bar -->
        <header class="top-bar">
            <div class="top-bar-left">
                <button class="btn-icon mobile-menu-btn" id="mobileMenuBtn">
                    <span>☰</span>
                </button>
                <h1 class="page-title"><?= $tr['profile'] ?></h1>
            </div>
            <div class="top-bar-right">
                <button class="btn-icon" id="themeToggle">
                    <span class="icon"></span>
                </button>
                <button class="btn-icon" id="langToggle">
                    <span class="icon"><?= $currentLang === 'fa' ? '🇬🇧' : '🇮🇷' ?></span>
                </button>
                <div class="user-menu">
                    <img src="./uploads/avatars/<?= $user['avatar'] ?>" alt="Avatar" class="user-avatar" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($user['username']) ?>&background=6366f1&color=fff'">
                    <span class="user-name"><?= htmlspecialchars($user['username']) ?></span>
                </div>
            </div>
        </header>
        
        <!-- Profile Content -->
        <div class="dashboard-container">
            <div class="profile-layout">
                <!-- Profile Sidebar -->
                <div class="profile-sidebar">
                    <div class="profile-card">
                        <div class="profile-avatar-container">
                            <img src="./uploads/avatars/<?= $user['avatar'] ?>" alt="Avatar" class="profile-avatar" id="profileAvatar" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($user['username']) ?>&background=6366f1&color=fff&size=200'">
                            <button class="avatar-upload-btn" id="avatarUploadBtn">
                                📷
                            </button>
                            <input type="file" id="avatarInput" accept="image/*" style="display: none;">
                        </div>
                        
                        <h2 class="profile-name"><?= htmlspecialchars($user['username']) ?></h2>
                        <p class="profile-email"><?= htmlspecialchars($user['email']) ?></p>
                        
                        <div class="profile-stats">
                            <div class="profile-stat">
                                <div class="profile-stat-value"><?= $user['level'] ?></div>
                                <div class="profile-stat-label"><?= $tr['level'] ?></div>
                            </div>
                            <div class="profile-stat">
                                <div class="profile-stat-value"><?= number_format($user['total_xp']) ?></div>
                                <div class="profile-stat-label"><?= $tr['total_xp'] ?></div>
                            </div>
                        </div>
                        
                        <div class="profile-info">
                            <p><strong><?= $tr['member_since'] ?>:</strong> <?= date('Y/m/d', strtotime($user['created_at'])) ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Profile Content -->
                <div class="profile-content">
                    <!-- Statistics Card -->
                    <div class="card">
                        <div class="card-header">
                            <h3><?= $tr['statistics'] ?></h3>
                        </div>
                        <div class="card-body">
                            <div class="stats-grid">
                                <div class="stat-item-simple">
                                    <div class="stat-icon-simple">📚</div>
                                    <div class="stat-text-simple">
                                        <div class="stat-value-simple"><?= $stats['lessons_completed'] ?></div>
                                        <div class="stat-label-simple"><?= $tr['lessons_completed'] ?></div>
                                    </div>
                                </div>
                                <div class="stat-item-simple">
                                    <div class="stat-icon-simple">⚡</div>
                                    <div class="stat-text-simple">
                                        <div class="stat-value-simple"><?= $stats['avg_wpm'] ?></div>
                                        <div class="stat-label-simple"><?= $tr['avg_wpm'] ?></div>
                                    </div>
                                </div>
                                <div class="stat-item-simple">
                                    <div class="stat-icon-simple">🎯</div>
                                    <div class="stat-text-simple">
                                        <div class="stat-value-simple"><?= $stats['avg_accuracy'] ?>%</div>
                                        <div class="stat-label-simple"><?= $tr['avg_accuracy'] ?></div>
                                    </div>
                                </div>
                                <div class="stat-item-simple">
                                    <div class="stat-icon-simple">⏱️</div>
                                    <div class="stat-text-simple">
                                        <div class="stat-value-simple"><?= round($stats['total_time'] / 60) ?></div>
                                        <div class="stat-label-simple"><?= $tr['total_time'] ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Settings Card -->
                    <div class="card">
                        <div class="card-header">
                            <h3><?= $tr['settings'] ?></h3>
                        </div>
                        <div class="card-body">
                            <form id="settingsForm">
                                <div class="settings-row">
                                    <div class="settings-label">
                                        <span class="settings-icon">🎨</span>
                                        <span><?= $tr['theme'] ?></span>
                                    </div>
                                    <div class="settings-control">
                                        <select name="theme" class="form-select">
                                            <option value="dark" <?= $currentTheme === 'dark' ? 'selected' : '' ?>><?= $tr['dark'] ?></option>
                                            <option value="light" <?= $currentTheme === 'light' ? 'selected' : '' ?>><?= $tr['light'] ?></option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="settings-row">
                                    <div class="settings-label">
                                        <span class="settings-icon">🌍</span>
                                        <span><?= $tr['language'] ?></span>
                                    </div>
                                    <div class="settings-control">
                                        <select name="language" class="form-select">
                                            <option value="fa" <?= $currentLang === 'fa' ? 'selected' : '' ?>><?= $tr['persian'] ?></option>
                                            <option value="en" <?= $currentLang === 'en' ? 'selected' : '' ?>><?= $tr['english'] ?></option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="settings-row">
                                    <div class="settings-label">
                                        <span class="settings-icon">🔊</span>
                                        <span><?= $tr['sound'] ?></span>
                                    </div>
                                    <div class="settings-control">
                                        <label class="toggle-switch">
                                            <input type="checkbox" name="sound" <?= $user['sound_enabled'] ? 'checked' : '' ?>>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <?= $tr['save_changes'] ?>
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Achievements Card -->
                    <div class="card" id="achievements">
                        <div class="card-header">
                            <h3><?= $tr['achievements'] ?></h3>
                            <span class="badge"><?= count($achievements) ?> <?= $tr['earned'] ?></span>
                        </div>
                        <div class="card-body">
                            <?php if (count($achievements) > 0): ?>
                                <div class="achievements-grid">
                                    <?php foreach ($achievements as $achievement): ?>
                                        <div class="achievement-badge">
                                            <div class="achievement-badge-icon"><?= $achievement['icon'] ?></div>
                                            <div class="achievement-badge-title">
                                                <?= $currentLang === 'fa' ? $achievement['title_fa'] : $achievement['title_en'] ?>
                                            </div>
                                            <div class="achievement-badge-date">
                                                <?= date('Y/m/d', strtotime($achievement['earned_at'])) ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <div class="empty-icon">🏆</div>
                                    <p><?= $currentLang === 'fa' ? 'هنوز نشانی کسب نکرده‌اید' : 'No achievements yet' ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <div class="toast" id="toast">
        <span class="toast-icon">✓</span>
        <span class="toast-message"></span>
    </div>
    
    <script src="<?= SITE_URL ?>/assets/js/app.js"></script>
    <script src="<?= SITE_URL ?>/assets/js/dashboard.js"></script>
    <script src="<?= SITE_URL ?>/assets/js/profile.js"></script>
    <script>
        const APP_CONFIG = {
            siteUrl: '<?= SITE_URL ?>',
            language: '<?= $currentLang ?>',
            theme: '<?= $currentTheme ?>'
        };
    </script>
</body>
</html>