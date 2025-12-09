<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

$lessonId = isset($_GET['lesson']) ? (int)$_GET['lesson'] : null;

if (!$lessonId) {
    redirect(SITE_URL . '/lessons.php');
}

$lesson = getLessonById($lessonId);

if (!$lesson) {
    die("درس یافت نشد!");
}

$progress = getUserLessonProgress($user['id'], $lessonId);
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>" dir="<?= $currentLang === 'fa' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تمرین - <?= $currentLang === 'fa' ? $lesson['title_fa'] : $lesson['title_en'] ?></title>
    
    <!-- استایل‌های Inline -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary: #6366f1;
            --secondary: #8b5cf6;
            --success: #10b981;
            --danger: #ef4444;
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --bg-tertiary: #334155;
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --border-color: #334155;
        }
        
        body {
            font-family: 'Tahoma', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: var(--bg-secondary);
            border-radius: 15px;
        }
        
        .btn {
            padding: 10px 20px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .btn-secondary {
            background: var(--bg-tertiary);
        }
        
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            padding: 20px;
            background: var(--bg-secondary);
            border: 2px solid var(--border-color);
            border-radius: 12px;
            text-align: center;
        }
        
        .stat-label {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 5px;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: var(--primary);
        }
        
        .typing-area {
            background: var(--bg-secondary);
            padding: 40px;
            border-radius: 15px;
            border: 2px solid var(--border-color);
            margin-bottom: 30px;
            min-height: 300px;
            font-size: 28px;
            line-height: 2.5;
            text-align: justify;
        }
        
        .typing-area .char {
            transition: all 0.1s;
            position: relative;
        }
        
        .typing-area .char.current {
            background: rgba(99, 102, 241, 0.3);
            border-radius: 4px;
            animation: blink 1s infinite;
        }
        
        .typing-area .char.correct {
            color: var(--success);
        }
        
        .typing-area .char.incorrect {
            color: var(--danger);
            background: rgba(239, 68, 68, 0.1);
            border-radius: 4px;
        }
        
        @keyframes blink {
            0%, 50%, 100% { opacity: 1; }
            25%, 75% { opacity: 0.5; }
        }
        
        .typing-input {
            width: 100%;
            padding: 15px;
            font-size: 20px;
            background: var(--bg-tertiary);
            border: 2px solid var(--border-color);
            border-radius: 10px;
            color: var(--text-primary);
            margin-bottom: 20px;
        }
        
        .typing-input:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .progress-bar {
            height: 12px;
            background: var(--bg-tertiary);
            border-radius: 999px;
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            width: 0%;
            transition: width 0.3s;
        }
        
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.8);
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: var(--bg-secondary);
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            max-width: 500px;
            border: 2px solid var(--border-color);
        }
        
        .result-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        
        .result-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin: 30px 0;
        }
        
        .result-stat {
            padding: 15px;
            background: var(--bg-tertiary);
            border-radius: 10px;
        }
        
        .result-stat-value {
            font-size: 32px;
            font-weight: bold;
            color: var(--primary);
        }
        
        .stars {
            font-size: 48px;
            margin: 20px 0;
        }
        
        .actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>درس <?= $lesson['order_index'] ?>: <?= $currentLang === 'fa' ? $lesson['title_fa'] : $lesson['title_en'] ?></h1>
            <a href="lessons.php" class="btn btn-secondary">← بازگشت</a>
        </div>
        
        <!-- Stats -->
        <div class="stats-bar">
            <div class="stat-card">
                <div class="stat-label">سرعت (WPM)</div>
                <div class="stat-value" id="wpm">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">دقت</div>
                <div class="stat-value" id="accuracy">100%</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">خطاها</div>
                <div class="stat-value" id="errors">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">زمان</div>
                <div class="stat-value" id="time">00:00</div>
            </div>
        </div>
        
        <!-- Progress -->
        <div class="progress-bar">
            <div class="progress-fill" id="progress"></div>
        </div>
        
        <!-- Typing Area -->
        <div class="typing-area" id="typingArea"></div>
        
        <!-- Input -->
        <input type="text" id="typingInput" class="typing-input" placeholder="اینجا تایپ کنید..." autocomplete="off">
        
        <!-- Actions -->
        <div class="actions">
            <button class="btn" onclick="location.reload()">🔄 شروع مجدد</button>
        </div>
    </div>
    
    <!-- Result Modal -->
    <div class="modal" id="resultModal">
        <div class="modal-content">
            <div class="result-icon" id="resultIcon">🎉</div>
            <h2 id="resultTitle">تکمیل شد!</h2>
            <div class="stars" id="stars"></div>
            <div class="result-stats">
                <div class="result-stat">
                    <div class="stat-label">سرعت</div>
                    <div class="result-stat-value" id="finalWpm">0</div>
                </div>
                <div class="result-stat">
                    <div class="stat-label">دقت</div>
                    <div class="result-stat-value" id="finalAccuracy">0%</div>
                </div>
                <div class="result-stat">
                    <div class="stat-label">خطاها</div>
                    <div class="result-stat-value" id="finalErrors">0</div>
                </div>
                <div class="result-stat">
                    <div class="stat-label">زمان</div>
                    <div class="result-stat-value" id="finalTime">0s</div>
                </div>
            </div>
            <div class="actions">
                <button class="btn" onclick="location.reload()">تلاش مجدد</button>
                <button class="btn" onclick="location.href='lessons.php'">درس بعدی</button>
            </div>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script>
        // متن درس
        const TEXT = <?= json_encode($lesson['content'], JSON_UNESCAPED_UNICODE) ?>;
        const LESSON_ID = <?= $lessonId ?>;
        const USER_ID = <?= $user['id'] ?>;
        
        console.log('✅ Text loaded:', TEXT);
        
        // متغیرها
        let currentIndex = 0;
        let errors = 0;
        let startTime = null;
        let timerInterval = null;
        
        const typingArea = document.getElementById('typingArea');
        const typingInput = document.getElementById('typingInput');
        
        // رندر متن
        function renderText() {
            typingArea.innerHTML = '';
            TEXT.split('').forEach((char, i) => {
                const span = document.createElement('span');
                span.textContent = char;
                span.className = 'char';
                if (i === 0) span.classList.add('current');
                typingArea.appendChild(span);
            });
        }
        
        // شروع تایمر
        function startTimer() {
            startTime = Date.now();
            timerInterval = setInterval(() => {
                const elapsed = Math.floor((Date.now() - startTime) / 1000);
                const minutes = Math.floor(elapsed / 60);
                const seconds = elapsed % 60;
                document.getElementById('time').textContent = 
                    `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            }, 1000);
        }
        
        // محاسبه آمار
        function updateStats() {
            if (!startTime) return;
            
            const timeElapsed = (Date.now() - startTime) / 1000 / 60;
            const words = currentIndex / 5;
            const wpm = Math.round(words / timeElapsed);
            const accuracy = Math.round((currentIndex - errors) / currentIndex * 100);
            const progress = Math.round((currentIndex / TEXT.length) * 100);
            
            document.getElementById('wpm').textContent = wpm;
            document.getElementById('accuracy').textContent = accuracy + '%';
            document.getElementById('errors').textContent = errors;
            document.getElementById('progress').style.width = progress + '%';
        }
        
        // مدیریت تایپ
        typingInput.addEventListener('input', (e) => {
            if (!startTime) startTimer();
            
            const typed = e.target.value;
            const char = typed[typed.length - 1];
            
            if (currentIndex < TEXT.length) {
                const expected = TEXT[currentIndex];
                const isCorrect = char === expected;
                
                const spans = typingArea.querySelectorAll('.char');
                spans[currentIndex].classList.remove('current');
                spans[currentIndex].classList.add(isCorrect ? 'correct' : 'incorrect');
                
                if (!isCorrect) errors++;
                
                currentIndex++;
                
                if (currentIndex < TEXT.length) {
                    spans[currentIndex].classList.add('current');
                }
                
                updateStats();
                
                if (currentIndex >= TEXT.length) {
                    finish();
                }
            }
        });
        
        // اتمام
        function finish() {
            clearInterval(timerInterval);
            typingInput.disabled = true;
            
            const timeElapsed = Math.floor((Date.now() - startTime) / 1000);
            const wpm = Math.round((TEXT.length / 5) / (timeElapsed / 60));
            const accuracy = Math.round((currentIndex - errors) / currentIndex * 100);
            const stars = calculateStars(wpm, accuracy);
            
            // نمایش نتیجه
            document.getElementById('resultIcon').textContent = stars === 3 ? '🏆' : stars === 2 ? '⭐' : '👍';
            document.getElementById('resultTitle').textContent = stars === 3 ? 'عالی!' : stars === 2 ? 'خوب!' : 'ادامه بده!';
            document.getElementById('stars').textContent = '⭐'.repeat(stars) + '☆'.repeat(3 - stars);
            document.getElementById('finalWpm').textContent = wpm;
            document.getElementById('finalAccuracy').textContent = accuracy + '%';
            document.getElementById('finalErrors').textContent = errors;
            document.getElementById('finalTime').textContent = timeElapsed + 's';
            
            document.getElementById('resultModal').classList.add('active');
            
            // ذخیره پیشرفت
            saveProgress(wpm, accuracy, timeElapsed);
        }
        
        // محاسبه ستاره
        function calculateStars(wpm, accuracy) {
            if (accuracy < 85) return 0;
            if (wpm >= 60) return 3;
            if (wpm >= 40) return 2;
            if (wpm >= 20) return 1;
            return 0;
        }
        
        // ذخیره پیشرفت
        async function saveProgress(wpm, accuracy, time) {
            try {
                const response = await fetch('<?= SITE_URL ?>/api/save-progress.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        lesson_id: LESSON_ID,
                        wpm: wpm,
                        accuracy: accuracy,
                        time_spent: time
                    })
                });
                const result = await response.json();
                console.log('✅ Progress saved:', result);
            } catch (error) {
                console.error('❌ Save error:', error);
            }
        }
        
        // شروع
        renderText();
        typingInput.focus();
        
        console.log('✅ Typing system ready!');
    </script>
</body>
</html>