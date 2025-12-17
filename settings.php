<?php
/**
 * ====================================
 * Settings Page
 * صفحه تنظیمات پیشرفته
 * ====================================
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth-check.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

requireLogin();

$user = getCurrentUser();
$currentLang = getCurrentLanguage();
$currentTheme = getCurrentTheme();

// ترجمه‌ها
$t = [
    'fa' => [
        'settings' => 'تنظیمات',
        'general_settings' => 'تنظیمات عمومی',
        'manage_preferences' => 'تنظیمات و ترجیحات خود را مدیریت کنید',
        'appearance' => 'ظاهر',
        'theme' => 'تم',
        'dark_mode' => 'حالت تاریک',
        'light_mode' => 'حالت روشن',
        'language' => 'زبان',
        'persian' => 'فارسی',
        'english' => 'انگلیسی',
        'practice_settings' => 'تنظیمات تمرین',
        'sound_effects' => 'افکت صوتی',
        'enable_sounds' => 'فعال‌سازی صداها',
        'keyboard_sounds' => 'صدای کیبورد',
        'show_keyboard' => 'نمایش کیبورد مجازی',
        'show_hints' => 'نمایش راهنماها',
        'auto_next_lesson' => 'رفتن خودکار به درس بعدی',
        'notifications' => 'اعلان‌ها',
        'achievement_notifications' => 'اعلان دستاوردها',
        'daily_reminder' => 'یادآوری روزانه',
        'progress_notifications' => 'اعلان پیشرفت',
        'account' => 'حساب کاربری',
        'username' => 'نام کاربری',
        'email' => 'ایمیل',
        'change_password' => 'تغییر رمز عبور',
        'current_password' => 'رمز عبور فعلی',
        'new_password' => 'رمز عبور جدید',
        'confirm_password' => 'تکرار رمز عبور',
        'privacy' => 'حریم خصوصی',
        'show_in_leaderboard' => 'نمایش در رتبه‌بندی',
        'show_achievements' => 'نمایش عمومی دستاوردها',
        'data_management' => 'مدیریت داده‌ها',
        'export_data' => 'دریافت داده‌های من',
        'delete_account' => 'حذف حساب کاربری',
        'danger_zone' => 'منطقه خطرناک',
        'delete_warning' => 'حذف حساب کاربری غیرقابل بازگشت است!',
        'save_changes' => 'ذخیره تغییرات',
        'cancel' => 'انصراف',
        'saved_successfully' => 'تغییرات با موفقیت ذخیره شد',
        'error_saving' => 'خطا در ذخیره تغییرات',
        'on' => 'روشن',
        'off' => 'خاموش'
    ],
    'en' => [
        'settings' => 'Settings',
        'general_settings' => 'General Settings',
        'manage_preferences' => 'Manage your settings and preferences',
        'appearance' => 'Appearance',
        'theme' => 'Theme',
        'dark_mode' => 'Dark Mode',
        'light_mode' => 'Light Mode',
        'language' => 'Language',
        'persian' => 'Persian',
        'english' => 'English',
        'practice_settings' => 'Practice Settings',
        'sound_effects' => 'Sound Effects',
        'enable_sounds' => 'Enable Sounds',
        'keyboard_sounds' => 'Keyboard Sounds',
        'show_keyboard' => 'Show Virtual Keyboard',
        'show_hints' => 'Show Hints',
        'auto_next_lesson' => 'Auto Next Lesson',
        'notifications' => 'Notifications',
        'achievement_notifications' => 'Achievement Notifications',
        'daily_reminder' => 'Daily Reminder',
        'progress_notifications' => 'Progress Notifications',
        'account' => 'Account',
        'username' => 'Username',
        'email' => 'Email',
        'change_password' => 'Change Password',
        'current_password' => 'Current Password',
        'new_password' => 'New Password',
        'confirm_password' => 'Confirm Password',
        'privacy' => 'Privacy',
        'show_in_leaderboard' => 'Show in Leaderboard',
        'show_achievements' => 'Public Achievements',
        'data_management' => 'Data Management',
        'export_data' => 'Export My Data',
        'delete_account' => 'Delete Account',
        'danger_zone' => 'Danger Zone',
        'delete_warning' => 'Account deletion is permanent!',
        'save_changes' => 'Save Changes',
        'cancel' => 'Cancel',
        'saved_successfully' => 'Changes saved successfully',
        'error_saving' => 'Error saving changes',
        'on' => 'On',
        'off' => 'Off'
    ]
];

$tr = $t[$currentLang];
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>" dir="<?= $currentLang === 'fa' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tr['settings'] ?> - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/themes.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/stats.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/settings.css">
</head>
<body class="theme-<?= $currentTheme ?> lang-<?= $currentLang ?>">
    
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>
    
    <main class="main-content">
        <header class="top-bar">
            <div class="top-bar-left">
                <button class="btn-icon mobile-menu-btn" id="mobileMenuBtn">
                    <span>☰</span>
                </button>
                <h1 class="page-title">⚙️ <?= $tr['settings'] ?></h1>
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
        
        <div class="dashboard-container">
            <!-- Hero -->
            <div class="settings-hero">
                <div class="settings-hero-content">
                    <h1><?= $tr['general_settings'] ?></h1>
                    <p><?= $tr['manage_preferences'] ?></p>
                </div>
                <div class="settings-hero-icon">⚙️</div>
            </div>

            <div class="settings-layout">
                <!-- Appearance Section -->
                <div class="settings-section">
                    <div class="section-header">
                        <div class="section-icon">🎨</div>
                        <h2 class="section-title"><?= $tr['appearance'] ?></h2>
                    </div>
                    
                    <div class="settings-group">
                        <div class="setting-item">
                            <div class="setting-info">
                                <div class="setting-label">🌓 <?= $tr['theme'] ?></div>
                                <div class="setting-desc"><?= $currentTheme === 'dark' ? $tr['dark_mode'] : $tr['light_mode'] ?></div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="themeSwitch" <?= $currentTheme === 'dark' ? 'checked' : '' ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <div class="setting-label">🌍 <?= $tr['language'] ?></div>
                                <div class="setting-desc"><?= $currentLang === 'fa' ? $tr['persian'] : $tr['english'] ?></div>
                            </div>
                            <select class="setting-select" id="languageSelect">
                                <option value="fa" <?= $currentLang === 'fa' ? 'selected' : '' ?>><?= $tr['persian'] ?></option>
                                <option value="en" <?= $currentLang === 'en' ? 'selected' : '' ?>><?= $tr['english'] ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Practice Settings -->
                <div class="settings-section">
                    <div class="section-header">
                        <div class="section-icon">📝</div>
                        <h2 class="section-title"><?= $tr['practice_settings'] ?></h2>
                    </div>
                    
                    <div class="settings-group">
                        <div class="setting-item">
                            <div class="setting-info">
                                <div class="setting-label">🔊 <?= $tr['sound_effects'] ?></div>
                                <div class="setting-desc"><?= $tr['enable_sounds'] ?></div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="soundSwitch" <?= $user['sound_enabled'] ? 'checked' : '' ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <div class="setting-label">⌨️ <?= $tr['show_keyboard'] ?></div>
                                <div class="setting-desc"><?= $tr['show_keyboard'] ?></div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="keyboardSwitch" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <div class="setting-label">💡 <?= $tr['show_hints'] ?></div>
                                <div class="setting-desc"><?= $tr['show_hints'] ?></div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="hintsSwitch" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <div class="setting-label">➡️ <?= $tr['auto_next_lesson'] ?></div>
                                <div class="setting-desc"><?= $tr['auto_next_lesson'] ?></div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="autoNextSwitch">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Notifications -->
                <div class="settings-section">
                    <div class="section-header">
                        <div class="section-icon">🔔</div>
                        <h2 class="section-title"><?= $tr['notifications'] ?></h2>
                    </div>
                    
                    <div class="settings-group">
                        <div class="setting-item">
                            <div class="setting-info">
                                <div class="setting-label">🏆 <?= $tr['achievement_notifications'] ?></div>
                                <div class="setting-desc"><?= $tr['achievement_notifications'] ?></div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="achievementNotif" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <div class="setting-label">📅 <?= $tr['daily_reminder'] ?></div>
                                <div class="setting-desc"><?= $tr['daily_reminder'] ?></div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="dailyReminder">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <div class="setting-label">📈 <?= $tr['progress_notifications'] ?></div>
                                <div class="setting-desc"><?= $tr['progress_notifications'] ?></div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="progressNotif" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Privacy -->
                <div class="settings-section">
                    <div class="section-header">
                        <div class="section-icon">🔒</div>
                        <h2 class="section-title"><?= $tr['privacy'] ?></h2>
                    </div>
                    
                    <div class="settings-group">
                        <div class="setting-item">
                            <div class="setting-info">
                                <div class="setting-label">🏅 <?= $tr['show_in_leaderboard'] ?></div>
                                <div class="setting-desc"><?= $tr['show_in_leaderboard'] ?></div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="showLeaderboard" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <div class="setting-label">🎖️ <?= $tr['show_achievements'] ?></div>
                                <div class="setting-desc"><?= $tr['show_achievements'] ?></div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="showAchievements" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Data Management -->
                <div class="settings-section">
                    <div class="section-header">
                        <div class="section-icon">💾</div>
                        <h2 class="section-title"><?= $tr['data_management'] ?></h2>
                    </div>
                    
                    <div class="settings-group">
                        <div class="setting-item">
                            <div class="setting-info">
                                <div class="setting-label">📥 <?= $tr['export_data'] ?></div>
                                <div class="setting-desc"><?= $tr['export_data'] ?></div>
                            </div>
                            <button class="btn-action" onclick="exportData()">
                                📥 <?= $currentLang === 'fa' ? 'دریافت' : 'Export' ?>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="settings-section danger-section">
                    <div class="section-header">
                        <div class="section-icon">⚠️</div>
                        <h2 class="section-title"><?= $tr['danger_zone'] ?></h2>
                    </div>
                    
                    <div class="settings-group">
                        <div class="setting-item">
                            <div class="setting-info">
                                <div class="setting-label">🗑️ <?= $tr['delete_account'] ?></div>
                                <div class="setting-desc danger-text"><?= $tr['delete_warning'] ?></div>
                            </div>
                            <button class="btn-danger" onclick="confirmDelete()">
                                🗑️ <?= $currentLang === 'fa' ? 'حذف حساب' : 'Delete' ?>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="settings-actions">
                    <button class="btn-save" id="saveBtn">
                        ✓ <?= $tr['save_changes'] ?>
                    </button>
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
    <script>
        const APP_CONFIG = {
            siteUrl: '<?= SITE_URL ?>',
            language: '<?= $currentLang ?>',
            theme: '<?= $currentTheme ?>',
            userId: <?= $user['id'] ?>
        };

        // Theme Toggle
        document.getElementById('themeSwitch').addEventListener('change', function() {
            document.getElementById('themeToggle').click();
        });

        // Language Select
        document.getElementById('languageSelect').addEventListener('change', function() {
            if (this.value !== APP_CONFIG.language) {
                document.getElementById('langToggle').click();
            }
        });

        // Save Settings
        document.getElementById('saveBtn').addEventListener('click', async function() {
            const settings = {
                sound_enabled: document.getElementById('soundSwitch').checked,
                show_keyboard: document.getElementById('keyboardSwitch').checked,
                show_hints: document.getElementById('hintsSwitch').checked,
                auto_next: document.getElementById('autoNextSwitch').checked,
                achievement_notif: document.getElementById('achievementNotif').checked,
                daily_reminder: document.getElementById('dailyReminder').checked,
                progress_notif: document.getElementById('progressNotif').checked,
                show_leaderboard: document.getElementById('showLeaderboard').checked,
                show_achievements: document.getElementById('showAchievements').checked
            };

            try {
                // Save to localStorage
                localStorage.setItem('typing_settings', JSON.stringify(settings));
                
                // Update sound in database
                await fetch(`${APP_CONFIG.siteUrl}/api/update-profile.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ sound_enabled: settings.sound_enabled })
                });

                showToast('<?= $tr['saved_successfully'] ?>', 'success');
            } catch (error) {
                showToast('<?= $tr['error_saving'] ?>', 'error');
            }
        });

        function exportData() {
            window.location.href = `${APP_CONFIG.siteUrl}/api/export-data.php`;
        }

        function confirmDelete() {
            if (confirm('<?= $currentLang === 'fa' ? 'آیا مطمئن هستید؟ این عمل غیرقابل بازگشت است!' : 'Are you sure? This action is permanent!' ?>')) {
                if (confirm('<?= $currentLang === 'fa' ? 'تمام داده‌های شما حذف خواهد شد. آیا ادامه می‌دهید؟' : 'All your data will be deleted. Continue?' ?>')) {
                    window.location.href = `${APP_CONFIG.siteUrl}/api/delete-account.php`;
                }
            }
        }
    </script>
</body>
</html>