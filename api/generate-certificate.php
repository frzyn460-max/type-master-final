<?php
/**
 * ====================================
 * Generate Certificate API
 * API صدور و دانلود گواهینامه
 * ====================================
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// بررسی لاگین
if (!isset($_SESSION['user_id'])) {
    die('Unauthorized');
}

$code = $_GET['code'] ?? null;
$action = $_GET['action'] ?? 'view';

if (!$code) {
    die('Certificate code is required');
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // دریافت اطلاعات گواهینامه
    $sql = "SELECT c.*, u.username 
            FROM certificates c
            JOIN users u ON c.user_id = u.id
            WHERE c.certificate_code = :code AND c.user_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'code' => $code,
        'user_id' => $_SESSION['user_id']
    ]);
    
    $cert = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cert) {
        die('Certificate not found');
    }
    
    // ====================================
    // ساخت گواهینامه به صورت HTML/CSS
    // ====================================
    
    $issueDate = date('Y/m/d', strtotime($cert['issued_at']));
    
    ?>
    <!DOCTYPE html>
    <html lang="fa" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>گواهینامه - <?= htmlspecialchars($cert['username']) ?></title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: 'Tahoma', 'Arial', sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 2rem;
            }

            .certificate {
                width: 1000px;
                max-width: 100%;
                background: white;
                padding: 4rem;
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                position: relative;
                overflow: hidden;
            }

            .certificate::before {
                content: '';
                position: absolute;
                inset: 2rem;
                border: 4px solid #8b5cf6;
                border-radius: 10px;
                pointer-events: none;
            }

            .certificate::after {
                content: '';
                position: absolute;
                inset: 2.5rem;
                border: 2px solid #6366f1;
                border-radius: 8px;
                pointer-events: none;
            }

            .cert-header {
                text-align: center;
                margin-bottom: 3rem;
                position: relative;
                z-index: 1;
            }

            .cert-logo {
                font-size: 5rem;
                margin-bottom: 1rem;
                animation: float 3s ease-in-out infinite;
            }

            @keyframes float {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-10px); }
            }

            .cert-title {
                font-size: 3rem;
                font-weight: 900;
                color: #8b5cf6;
                margin-bottom: 0.5rem;
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
            }

            .cert-subtitle {
                font-size: 1.5rem;
                color: #6366f1;
                font-weight: 600;
            }

            .cert-body {
                text-align: center;
                margin: 3rem 0;
                position: relative;
                z-index: 1;
            }

            .cert-text {
                font-size: 1.3rem;
                color: #475569;
                margin-bottom: 1.5rem;
                line-height: 1.8;
            }

            .cert-name {
                font-size: 3.5rem;
                font-weight: 900;
                color: #1e293b;
                margin: 2rem 0;
                padding: 1rem 2rem;
                background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(99, 102, 241, 0.1));
                border-radius: 15px;
                display: inline-block;
            }

            .cert-achievement {
                font-size: 1.3rem;
                color: #475569;
                margin-bottom: 2rem;
            }

            .cert-level {
                display: inline-block;
                padding: 0.75rem 2rem;
                background: linear-gradient(135deg, #8b5cf6, #6366f1);
                color: white;
                font-size: 2rem;
                font-weight: 900;
                border-radius: 50px;
                margin: 1rem 0;
                box-shadow: 0 8px 20px rgba(139, 92, 246, 0.4);
            }

            .cert-stats {
                display: flex;
                justify-content: center;
                gap: 3rem;
                margin: 2rem 0;
                padding: 2rem;
                background: #f8fafc;
                border-radius: 15px;
            }

            .cert-stat {
                text-align: center;
            }

            .cert-stat-value {
                font-size: 2.5rem;
                font-weight: 900;
                color: #8b5cf6;
                display: block;
            }

            .cert-stat-label {
                font-size: 1rem;
                color: #64748b;
                margin-top: 0.5rem;
            }

            .cert-footer {
                display: flex;
                justify-content: space-between;
                align-items: flex-end;
                margin-top: 3rem;
                padding-top: 2rem;
                border-top: 2px solid #e2e8f0;
                position: relative;
                z-index: 1;
            }

            .cert-date,
            .cert-signature {
                text-align: center;
            }

            .cert-date-label,
            .cert-signature-label {
                font-size: 1rem;
                color: #64748b;
                margin-bottom: 0.5rem;
            }

            .cert-date-value {
                font-size: 1.3rem;
                font-weight: 700;
                color: #1e293b;
            }

            .cert-signature-line {
                width: 200px;
                border-top: 2px solid #1e293b;
                margin-bottom: 0.5rem;
            }

            .cert-signature-name {
                font-size: 1.3rem;
                font-weight: 700;
                color: #1e293b;
            }

            .cert-code {
                position: absolute;
                bottom: 2rem;
                right: 2rem;
                font-size: 0.9rem;
                color: #94a3b8;
                font-family: 'Courier New', monospace;
            }

            .cert-watermark {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%) rotate(-45deg);
                font-size: 15rem;
                color: rgba(139, 92, 246, 0.03);
                font-weight: 900;
                pointer-events: none;
                z-index: 0;
            }

            @media print {
                body {
                    background: white;
                    padding: 0;
                }
                
                .certificate {
                    box-shadow: none;
                    page-break-after: always;
                }
            }

            @media (max-width: 768px) {
                .certificate {
                    padding: 2rem;
                }

                .cert-title {
                    font-size: 2rem;
                }

                .cert-name {
                    font-size: 2rem;
                }

                .cert-stats {
                    flex-direction: column;
                    gap: 1.5rem;
                }
            }
        </style>
    </head>
    <body>
        <div class="certificate">
            <div class="cert-watermark">⌨️</div>
            
            <div class="cert-header">
                <div class="cert-logo">🏆</div>
                <h1 class="cert-title">گواهینامه تکمیل</h1>
                <p class="cert-subtitle">Typing Master Pro</p>
            </div>

            <div class="cert-body">
                <p class="cert-text">
                    این گواهی می‌کند که
                </p>

                <div class="cert-name">
                    <?= htmlspecialchars($cert['username']) ?>
                </div>

                <p class="cert-achievement">
                    با موفقیت دوره تایپینگ را در سطح
                </p>

                <div class="cert-level">
                    <?php
                    $levels = [
                        'beginner' => 'مبتدی',
                        'intermediate' => 'متوسط',
                        'advanced' => 'پیشرفته'
                    ];
                    echo $levels[strtolower($cert['level_name'])] ?? $cert['level_name'];
                    ?>
                </div>

                <p class="cert-achievement">
                    تکمیل نموده است
                </p>

                <div class="cert-stats">
                    <div class="cert-stat">
                        <span class="cert-stat-value"><?= round($cert['avg_wpm']) ?></span>
                        <span class="cert-stat-label">میانگین سرعت (WPM)</span>
                    </div>
                    <div class="cert-stat">
                        <span class="cert-stat-value"><?= round($cert['avg_accuracy']) ?>%</span>
                        <span class="cert-stat-label">میانگین دقت</span>
                    </div>
                    <div class="cert-stat">
                        <span class="cert-stat-value"><?= $cert['lessons_completed'] ?></span>
                        <span class="cert-stat-label">درس تکمیل شده</span>
                    </div>
                </div>
            </div>

            <div class="cert-footer">
                <div class="cert-date">
                    <div class="cert-date-label">تاریخ صدور</div>
                    <div class="cert-date-value"><?= $issueDate ?></div>
                </div>

                <div class="cert-signature">
                    <div class="cert-signature-line"></div>
                    <div class="cert-signature-label">امضا</div>
                    <div class="cert-signature-name">تیم Typing Master</div>
                </div>
            </div>

            <div class="cert-code">
                #<?= $cert['certificate_code'] ?>
            </div>
        </div>

        <?php if ($action === 'download'): ?>
        <script>
            window.onload = function() {
                window.print();
            };
        </script>
        <?php endif; ?>
    </body>
    </html>
    <?php
    
} catch (Exception $e) {
    die('Error generating certificate: ' . $e->getMessage());
}
?>