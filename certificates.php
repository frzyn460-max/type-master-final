<?php
/**
 * ====================================
 * Certificates Page
 * صفحه گواهینامه‌ها
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

// دریافت گواهینامه‌ها
$db = Database::getInstance();
$conn = $db->getConnection();

$sql = "SELECT * FROM certificates WHERE user_id = :user_id ORDER BY issued_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute(['user_id' => $user['id']]);
$certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ترجمه‌ها
$t = [
    'fa' => [
        'certificates' => 'گواهینامه‌ها',
        'my_certificates' => 'گواهینامه‌های من',
        'celebrate_message' => 'دستاوردهای خود را جشن بگیرید!',
        'total_certificates' => 'مجموع گواهینامه‌ها',
        'certificate' => 'گواهینامه',
        'level_completed' => 'سطح تکمیل شده',
        'issued_date' => 'تاریخ صدور',
        'certificate_code' => 'کد گواهی',
        'download' => 'دانلود',
        'view' => 'مشاهده',
        'share' => 'اشتراک‌گذاری',
        'no_certificates' => 'هنوز گواهینامه‌ای دریافت نکرده‌اید',
        'start_learning_message' => 'درس‌ها را تکمیل کنید تا اولین گواهینامه خود را دریافت کنید!',
        'avg_wpm' => 'میانگین سرعت',
        'avg_accuracy' => 'میانگین دقت',
        'lessons_completed' => 'درس‌های تکمیل شده',
        'lesson' => 'درس',
        'beginner' => 'مبتدی',
        'intermediate' => 'متوسط',
        'advanced' => 'پیشرفته',
        'congratulations' => 'تبریک!',
        'certificate_of_completion' => 'گواهینامه تکمیل',
        'this_certifies_that' => 'این گواهی می‌کند که',
        'has_successfully_completed' => 'با موفقیت تکمیل کرده است',
        'typing_course' => 'دوره تایپینگ',
        'with_excellence' => 'با درجه عالی',
        'date' => 'تاریخ',
        'signature' => 'امضا',
        'typing_master_team' => 'تیم Typing Master'
    ],
    'en' => [
        'certificates' => 'Certificates',
        'my_certificates' => 'My Certificates',
        'celebrate_message' => 'Celebrate your achievements!',
        'total_certificates' => 'Total Certificates',
        'certificate' => 'Certificates',
        'level_completed' => 'Level Completed',
        'issued_date' => 'Issue Date',
        'certificate_code' => 'Certificate Code',
        'download' => 'Download',
        'view' => 'View',
        'share' => 'Share',
        'no_certificates' => 'No certificates yet',
        'start_learning_message' => 'Complete lessons to earn your first certificate!',
        'avg_wpm' => 'Average Speed',
        'avg_accuracy' => 'Average Accuracy',
        'lessons_completed' => 'Lessons Completed',
        'lesson' => 'Lessons',
        'beginner' => 'Beginner',
        'intermediate' => 'Intermediate',
        'advanced' => 'Advanced',
        'congratulations' => 'Congratulations!',
        'certificate_of_completion' => 'Certificate of Completion',
        'this_certifies_that' => 'This certifies that',
        'has_successfully_completed' => 'has successfully completed',
        'typing_course' => 'Typing Course',
        'with_excellence' => 'with excellence',
        'date' => 'Date',
        'signature' => 'Signature',
        'typing_master_team' => 'Typing Master Team'
    ]
];

$tr = $t[$currentLang];
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>" dir="<?= $currentLang === 'fa' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tr['certificates'] ?> - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/themes.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/stats.css">
    <style>
        /* Certificates Specific Styles */
        .certificates-hero {
            background: linear-gradient(135deg, #8b5cf6, #6366f1);
            border-radius: 20px;
            padding: 3rem;
            margin-bottom: 2rem;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .certificates-hero::before {
            content: '🏆';
            position: absolute;
            font-size: 20rem;
            opacity: 0.08;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-15deg);
        }

        .certificates-hero-content {
            position: relative;
            z-index: 1;
        }

        .certificates-hero h1 {
            font-size: 3rem;
            font-weight: 900;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .certificates-hero p {
            font-size: 1.3rem;
            opacity: 0.95;
        }

        .certificates-stats {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-lg);
            text-align: center;
        }

        .cert-stat {
            display: inline-block;
            margin: 0 2rem;
        }

        .cert-stat-value {
            font-size: 3rem;
            font-weight: 900;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1;
        }

        .cert-stat-label {
            color: var(--text-secondary);
            font-weight: 600;
            margin-top: 0.5rem;
        }

        .certificates-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }

        .certificate-card {
            background: var(--card-bg);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            transition: all 0.3s ease;
            border: 3px solid transparent;
            position: relative;
        }

        .certificate-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(99, 102, 241, 0.1));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .certificate-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-xl);
            border-color: var(--primary);
        }

        .certificate-card:hover::before {
            opacity: 1;
        }

        .certificate-preview {
            aspect-ratio: 4/3;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            position: relative;
            overflow: hidden;
            padding: 2rem;
            border-bottom: 3px solid var(--border-color);
        }

        .certificate-preview::before {
            content: '';
            position: absolute;
            inset: 1rem;
            border: 3px solid #8b5cf6;
            border-radius: 8px;
            box-shadow: inset 0 0 0 3px #6366f1;
        }

        .cert-preview-content {
            position: relative;
            z-index: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: #1e293b;
        }

        .cert-preview-badge {
            font-size: 3rem;
            margin-bottom: 1rem;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .cert-preview-title {
            font-size: 1.5rem;
            font-weight: 900;
            margin-bottom: 0.5rem;
            color: #8b5cf6;
        }

        .cert-preview-level {
            font-size: 2rem;
            font-weight: 900;
            margin-bottom: 0.5rem;
            color: #1e293b;
        }

        .cert-preview-code {
            font-size: 0.85rem;
            font-weight: 600;
            color: #64748b;
            font-family: 'Courier New', monospace;
        }

        .certificate-info {
            padding: 1.5rem;
        }

        .cert-level-badge {
            display: inline-block;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.85rem;
            margin-bottom: 1rem;
        }

        .cert-level-beginner {
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
        }

        .cert-level-intermediate {
            background: rgba(245, 158, 11, 0.15);
            color: #f59e0b;
        }

        .cert-level-advanced {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
        }

        .cert-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin: 1rem 0;
            padding: 1rem;
            background: var(--bg-secondary);
            border-radius: 12px;
        }

        .cert-stat-item {
            text-align: center;
        }

        .cert-stat-item-value {
            font-size: 1.5rem;
            font-weight: 900;
            color: var(--primary);
        }

        .cert-stat-item-label {
            font-size: 0.75rem;
            color: var(--text-secondary);
            margin-top: 0.25rem;
        }

        .cert-date {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .cert-actions {
            display: flex;
            gap: 0.75rem;
        }

        .cert-btn {
            flex: 1;
            padding: 0.75rem;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .cert-btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }

        .cert-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(99, 102, 241, 0.4);
        }

        .cert-btn-secondary {
            background: var(--bg-secondary);
            color: var(--text-primary);
            border: 2px solid var(--border-color);
        }

        .cert-btn-secondary:hover {
            background: var(--bg-tertiary);
            border-color: var(--primary);
        }

        .empty-certificates {
            text-align: center;
            padding: 5rem 2rem;
            background: var(--card-bg);
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
        }

        .empty-cert-icon {
            font-size: 8rem;
            margin-bottom: 2rem;
            opacity: 0.3;
        }

        .empty-cert-title {
            font-size: 2rem;
            font-weight: 900;
            margin-bottom: 1rem;
            color: var(--text-primary);
        }

        .empty-cert-text {
            color: var(--text-secondary);
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }

        .start-btn {
            display: inline-block;
            padding: 1rem 2.5rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 15px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .start-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.4);
        }

        @media (max-width: 768px) {
            .certificates-hero h1 {
                font-size: 2rem;
            }

            .certificates-grid {
                grid-template-columns: 1fr;
            }

            .cert-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="theme-<?= $currentTheme ?> lang-<?= $currentLang ?>">
    
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>
    
    <main class="main-content">
        <header class="top-bar">
            <div class="top-bar-left">
                <button class="btn-icon mobile-menu-btn" id="mobileMenuBtn">
                    <span>☰</span>
                </button>
                <h1 class="page-title">🏆 <?= $tr['certificates'] ?></h1>
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
            <div class="certificates-hero">
                <div class="certificates-hero-content">
                    <h1><?= $tr['my_certificates'] ?></h1>
                    <p><?= $tr['celebrate_message'] ?></p>
                </div>
            </div>

            <?php if (count($certificates) > 0): ?>
                <!-- Stats -->
                <div class="certificates-stats">
                    <div class="cert-stat">
                        <div class="cert-stat-value"><?= count($certificates) ?></div>
                        <div class="cert-stat-label"><?= $tr['total_certificates'] ?></div>
                    </div>
                </div>

                <!-- Certificates Grid -->
                <div class="certificates-grid">
                    <?php 
                    $levelColors = [
                        'beginner' => 'cert-level-beginner',
                        'intermediate' => 'cert-level-intermediate',
                        'advanced' => 'cert-level-advanced'
                    ];
                    
                    foreach ($certificates as $cert): 
                        $levelClass = $levelColors[strtolower($cert['level_name'])] ?? 'cert-level-beginner';
                    ?>
                        <div class="certificate-card">
                            <div class="certificate-preview">
                                <div class="cert-preview-content">
                                    <div class="cert-preview-badge">🏆</div>
                                    <div class="cert-preview-title"><?= $tr['certificate_of_completion'] ?></div>
                                    <div class="cert-preview-level"><?= $tr[strtolower($cert['level_name'])] ?? $cert['level_name'] ?></div>
                                    <div class="cert-preview-code">#<?= $cert['certificate_code'] ?></div>
                                </div>
                            </div>
                            
                            <div class="certificate-info">
                                <span class="cert-level-badge <?= $levelClass ?>">
                                    <?= $tr[strtolower($cert['level_name'])] ?? $cert['level_name'] ?>
                                </span>
                                
                                <div class="cert-stats">
                                    <div class="cert-stat-item">
                                        <div class="cert-stat-item-value"><?= round($cert['avg_wpm']) ?></div>
                                        <div class="cert-stat-item-label"><?= $tr['avg_wpm'] ?></div>
                                    </div>
                                    <div class="cert-stat-item">
                                        <div class="cert-stat-item-value"><?= round($cert['avg_accuracy']) ?>%</div>
                                        <div class="cert-stat-item-label"><?= $tr['avg_accuracy'] ?></div>
                                    </div>
                                    <div class="cert-stat-item">
                                        <div class="cert-stat-item-value"><?= $cert['lessons_completed'] ?></div>
                                        <div class="cert-stat-item-label"><?= $tr['lesson'] ?></div>
                                    </div>
                                </div>
                                
                                <div class="cert-date">
                                    <span>📅</span>
                                    <span><?= date($currentLang === 'fa' ? 'Y/m/d' : 'm/d/Y', strtotime($cert['issued_at'])) ?></span>
                                </div>
                                
                                <div class="cert-actions">
                                    <button class="cert-btn cert-btn-primary" onclick="viewCertificate('<?= $cert['certificate_code'] ?>')">
                                        <span>👁️</span>
                                        <span><?= $tr['view'] ?></span>
                                    </button>
                                    <button class="cert-btn cert-btn-secondary" onclick="downloadCertificate('<?= $cert['certificate_code'] ?>')">
                                        <span>📥</span>
                                        <span><?= $tr['download'] ?></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Empty State -->
                <div class="empty-certificates">
                    <div class="empty-cert-icon">🏆</div>
                    <h2 class="empty-cert-title"><?= $tr['no_certificates'] ?></h2>
                    <p class="empty-cert-text"><?= $tr['start_learning_message'] ?></p>
                    <a href="lessons.php" class="start-btn">
                        🚀 <?= $currentLang === 'fa' ? 'شروع یادگیری' : 'Start Learning' ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="<?= SITE_URL ?>/assets/js/app.js"></script>
    <script src="<?= SITE_URL ?>/assets/js/dashboard.js"></script>
    <script>
        const APP_CONFIG = {
            siteUrl: '<?= SITE_URL ?>',
            language: '<?= $currentLang ?>',
            theme: '<?= $currentTheme ?>'
        };

        function viewCertificate(code) {
            window.open(`${APP_CONFIG.siteUrl}/api/generate-certificate.php?code=${code}&action=view`, '_blank');
        }

        function downloadCertificate(code) {
            window.location.href = `${APP_CONFIG.siteUrl}/api/generate-certificate.php?code=${code}&action=download`;
        }
    </script>
</body>
</html>