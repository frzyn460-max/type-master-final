<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth-check.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

requireLogin();

$lessonId = 1; // درس اول
$lesson = getLessonById($lessonId);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تست ساده</title>
    <style>
        body {
            font-family: Tahoma;
            padding: 20px;
            background: #0f172a;
            color: white;
        }
        .typing-area {
            background: #1e293b;
            padding: 20px;
            border-radius: 10px;
            font-size: 24px;
            line-height: 2;
            margin: 20px 0;
        }
        .char {
            transition: all 0.1s;
        }
        .char.current {
            background: rgba(99, 102, 241, 0.3);
            border-radius: 4px;
        }
        .char.correct {
            color: #10b981;
        }
        .char.incorrect {
            color: #ef4444;
            background: rgba(239, 68, 68, 0.1);
        }
        input {
            width: 100%;
            padding: 10px;
            font-size: 18px;
            margin: 20px 0;
        }
        .stats {
            display: flex;
            gap: 20px;
            margin: 20px 0;
        }
        .stat {
            padding: 10px 20px;
            background: #1e293b;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <h1>🧪 تست ساده تایپینگ</h1>
    
    <div class="stats">
        <div class="stat">WPM: <span id="wpm">0</span></div>
        <div class="stat">دقت: <span id="accuracy">100</span>%</div>
        <div class="stat">خطا: <span id="errors">0</span></div>
    </div>
    
    <div class="typing-area" id="typingArea"></div>
    
    <input type="text" id="typingInput" placeholder="اینجا تایپ کن..." autocomplete="off">
    
    <div style="margin-top: 20px;">
        <button onclick="location.reload()" style="padding: 10px 20px; background: #6366f1; color: white; border: none; border-radius: 5px; cursor: pointer;">🔄 شروع مجدد</button>
        <a href="lessons.php" style="padding: 10px 20px; background: #10b981; color: white; text-decoration: none; border-radius: 5px; margin-left: 10px;">← بازگشت</a>
    </div>
    
    <script>
        // متن درس
        const TEXT = <?= json_encode($lesson['content'], JSON_UNESCAPED_UNICODE) ?>;
        
        console.log('📚 Text loaded:', TEXT);
        console.log('📏 Text length:', TEXT.length);
        
        let currentIndex = 0;
        let errors = 0;
        let startTime = null;
        
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
        
        // مدیریت تایپ
        typingInput.addEventListener('input', (e) => {
            if (!startTime) startTime = Date.now();
            
            const typed = e.target.value;
            const char = typed[typed.length - 1];
            
            if (currentIndex < TEXT.length) {
                const expected = TEXT[currentIndex];
                const isCorrect = char === expected;
                
                // به‌روزرسانی نمایش
                const spans = typingArea.querySelectorAll('.char');
                spans[currentIndex].classList.remove('current');
                spans[currentIndex].classList.add(isCorrect ? 'correct' : 'incorrect');
                
                if (!isCorrect) errors++;
                
                currentIndex++;
                
                if (currentIndex < TEXT.length) {
                    spans[currentIndex].classList.add('current');
                }
                
                // محاسبه آمار
                const timeElapsed = (Date.now() - startTime) / 1000 / 60;
                const words = currentIndex / 5;
                const wpm = Math.round(words / timeElapsed);
                const accuracy = Math.round((currentIndex - errors) / currentIndex * 100);
                
                document.getElementById('wpm').textContent = wpm;
                document.getElementById('accuracy').textContent = accuracy;
                document.getElementById('errors').textContent = errors;
                
                // اتمام
                if (currentIndex >= TEXT.length) {
                    typingInput.disabled = true;
                    alert('✅ تمام شد!\n\nWPM: ' + wpm + '\nدقت: ' + accuracy + '%');
                }
            }
        });
        
        // شروع
        renderText();
        typingInput.focus();
    </script>
</body>
</html>