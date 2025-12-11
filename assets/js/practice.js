/**
 * ====================================
 * Practice Page Manager
 * مدیریت صفحه تمرین
 * ====================================
 */

class PracticeManager {
    constructor() {
        // Configuration
        this.config = window.PRACTICE_CONFIG;
        
        // Elements
        this.elements = {
            welcomeScreen: document.getElementById('welcomeScreen'),
            practiceArea: document.getElementById('practiceArea'),
            startPracticeBtn: document.getElementById('startPracticeBtn'),
            typingText: document.getElementById('typingText'),
            typingInput: document.getElementById('typingInput'),
            virtualKeyboard: document.getElementById('virtualKeyboard'),
            keyboardZone: document.getElementById('keyboardZone'),
            toggleKeyboard: document.getElementById('toggleKeyboard'),
            toggleSound: document.getElementById('toggleSound'),
            pauseBtn: document.getElementById('pauseBtn'),
            restartBtn: document.getElementById('restartBtn'),
            resultModal: document.getElementById('resultModal'),
            modalOverlay: document.getElementById('modalOverlay'),
            tryAgainBtn: document.getElementById('tryAgainBtn'),
            nextLessonBtn: document.getElementById('nextLessonBtn'),
            // Stats
            wpmDisplay: document.getElementById('wpmDisplay'),
            accuracyDisplay: document.getElementById('accuracyDisplay'),
            errorsDisplay: document.getElementById('errorsDisplay'),
            timeDisplay: document.getElementById('timeDisplay'),
            progressBar: document.getElementById('progressBar'),
            progressText: document.getElementById('progressText'),
            // Result
            resultIcon: document.getElementById('resultIcon'),
            resultTitle: document.getElementById('resultTitle'),
            starsDisplay: document.getElementById('starsDisplay'),
            finalWpm: document.getElementById('finalWpm'),
            finalAccuracy: document.getElementById('finalAccuracy'),
            finalErrors: document.getElementById('finalErrors'),
            finalTime: document.getElementById('finalTime'),
            yourScore: document.getElementById('yourScore')
        };
        
        // Instances
        this.typingEngine = null;
        this.keyboard = null;
        this.timer = null;
        
        // State
        this.isPaused = false;
        this.isCompleted = false;
        this.keyboardVisible = true;
        this.soundEnabled = this.config.soundEnabled;
        
        this.init();
    }
    
    /**
     * راه‌اندازی
     */
    init() {
        console.log('🚀 Practice Manager initialized');
        
        this.setupEventListeners();
        this.setupThemeToggle();
    }
    
    /**
     * تنظیم Event Listeners
     */
    setupEventListeners() {
        // شروع تمرین
        if (this.elements.startPracticeBtn) {
            this.elements.startPracticeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                console.log('🚀 Start button clicked!');
                this.startPractice();
            });
        } else {
            console.error('❌ Start practice button not found!');
        }
        
        // توقف موقت
        this.elements.pauseBtn?.addEventListener('click', () => {
            this.togglePause();
        });
        
        // شروع مجدد
        this.elements.restartBtn?.addEventListener('click', () => {
            this.restart();
        });
        
        // تلاش مجدد
        this.elements.tryAgainBtn?.addEventListener('click', () => {
            this.restart();
            this.hideModal();
        });
        
        // درس بعدی
        this.elements.nextLessonBtn?.addEventListener('click', () => {
            window.location.href = `${this.config.siteUrl}/lessons.php`;
        });
        
        // نمایش/مخفی کیبورد
        this.elements.toggleKeyboard?.addEventListener('click', () => {
            this.toggleKeyboard();
        });
        
        // صدا
        this.elements.toggleSound?.addEventListener('click', () => {
            this.toggleSound();
        });
        
        // بستن Modal با کلیک روی overlay
        this.elements.modalOverlay?.addEventListener('click', () => {
            this.hideModal();
        });
        
        // Escape برای بستن Modal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.elements.resultModal?.classList.contains('active')) {
                this.hideModal();
            }
        });
    }
    
    /**
     * شروع تمرین
     */
    startPractice() {
        console.log('▶️ Starting practice...');
        console.log('📝 Lesson text:', this.config.lessonText);
        
        if (!this.config.lessonText) {
            console.error('❌ Lesson text is empty!');
            alert('خطا: متن درس خالی است!');
            return;
        }
        
        // مخفی کردن صفحه راهنما
        if (this.elements.welcomeScreen) {
            this.elements.welcomeScreen.style.display = 'none';
            console.log('✅ Welcome screen hidden');
        }
        
        // نمایش صفحه تمرین
        if (this.elements.practiceArea) {
            this.elements.practiceArea.style.display = 'flex';
            console.log('✅ Practice area shown');
        }
        
        // راه‌اندازی موتور تایپینگ
        this.initTypingEngine();
        
        // راه‌اندازی کیبورد مجازی
        this.initKeyboard();
        
        // راه‌اندازی تایمر
        this.initTimer();
        
        // فوکوس روی input
        setTimeout(() => {
            this.elements.typingInput?.focus();
            console.log('✅ Input focused');
        }, 100);
    }
    
    /**
     * راه‌اندازی موتور تایپینگ
     */
    initTypingEngine() {
        this.typingEngine = new TypingEngine({
            text: this.config.lessonText,
            container: this.elements.typingText,
            input: this.elements.typingInput,
            language: this.config.language,
            soundEnabled: this.soundEnabled,
            
            onStart: () => {
                console.log('✅ Typing started');
                this.timer?.start();
            },
            
            onProgress: (progress) => {
                this.updateStats(progress);
            },
            
            onComplete: (stats) => {
                console.log('🏁 Typing completed', stats);
                this.handleCompletion(stats);
            },
            
            onError: (index, expected, typed) => {
                console.log('❌ Error:', { index, expected, typed });
            }
        });
    }
    
    /**
     * راه‌اندازی کیبورد مجازی
     */
    initKeyboard() {
        if (!this.elements.virtualKeyboard) return;
        
        this.keyboard = new KeyboardVisualizer({
            container: this.elements.virtualKeyboard,
            language: this.config.language,
            showFingers: true
        });
        
        console.log('⌨️ Keyboard initialized');
    }
    
    /**
     * راه‌اندازی تایمر
     */
    initTimer() {
        this.timer = new Timer({
            displayElement: this.elements.timeDisplay,
            format: 'mm:ss',
            
            onTick: (elapsed) => {
                // می‌تونیم کارهای اضافی انجام بدیم
            }
        });
        
        console.log('⏱️ Timer initialized');
    }
    
    /**
     * به‌روزرسانی آمار
     */
    updateStats(progress) {
        // WPM
        if (this.elements.wpmDisplay) {
            this.elements.wpmDisplay.textContent = progress.wpm;
        }
        
        // Accuracy
        if (this.elements.accuracyDisplay) {
            this.elements.accuracyDisplay.textContent = `${progress.accuracy}%`;
        }
        
        // Errors
        if (this.elements.errorsDisplay) {
            this.elements.errorsDisplay.textContent = progress.errors;
        }
        
        // Progress
        if (this.elements.progressBar) {
            this.elements.progressBar.style.width = `${progress.percentage}%`;
        }
        
        if (this.elements.progressText) {
            this.elements.progressText.textContent = `${progress.percentage}%`;
        }
    }
    
    /**
     * مدیریت تکمیل تمرین
     */
    async handleCompletion(stats) {
        this.isCompleted = true;
        this.timer?.stop();
        
        // محاسبه ستاره‌ها
        const stars = this.calculateStars(stats.wpm, stats.accuracy);
        
        // نمایش نتیجه
        this.showResult(stats, stars);
        
        // ذخیره پیشرفت
        await this.saveProgress(stats, stars);
    }
    
    /**
     * محاسبه ستاره‌ها
     */
    calculateStars(wpm, accuracy) {
        if (accuracy < 85) return 0;
        
        let stars = 0;
        
        if (wpm >= 60 && accuracy >= 95) {
            stars = 3;
        } else if (wpm >= 40 && accuracy >= 90) {
            stars = 2;
        } else if (wpm >= 20 && accuracy >= 85) {
            stars = 1;
        }
        
        return stars;
    }
    
    /**
     * نمایش نتیجه
     */
    showResult(stats, stars) {
        // آیکون و عنوان
        const messages = {
            3: { icon: '🏆', title: this.config.translations.excellent },
            2: { icon: '⭐', title: this.config.translations.good },
            1: { icon: '👍', title: this.config.translations.keep_trying },
            0: { icon: '💪', title: this.config.translations.keep_trying }
        };
        
        const message = messages[stars] || messages[0];
        
        this.elements.resultIcon.textContent = message.icon;
        this.elements.resultTitle.textContent = message.title;
        
        // ستاره‌ها
        this.updateStarsDisplay(stars);
        
        // آمار
        this.elements.finalWpm.textContent = stats.wpm;
        this.elements.finalAccuracy.textContent = `${stats.accuracy}%`;
        this.elements.finalErrors.textContent = stats.errors;
        this.elements.finalTime.textContent = `${stats.timeElapsed}s`;
        
        // مقایسه با بهترین امتیاز
        if (this.elements.yourScore) {
            this.elements.yourScore.textContent = `${stats.wpm} WPM`;
            
            // بررسی رکورد جدید
            if (stats.wpm > this.config.previousBest.wpm) {
                this.elements.yourScore.classList.add('pulse');
                this.elements.yourScore.style.color = 'var(--success)';
            }
        }
        
        // نمایش Modal
        this.showModal();
    }
    
    /**
     * به‌روزرسانی نمایش ستاره‌ها
     */
    updateStarsDisplay(earnedStars) {
        const stars = this.elements.starsDisplay?.querySelectorAll('.star');
        
        if (stars) {
            stars.forEach((star, index) => {
                if (index < earnedStars) {
                    star.classList.remove('empty');
                    star.textContent = '⭐';
                } else {
                    star.classList.add('empty');
                    star.textContent = '☆';
                }
            });
        }
    }
    
    /**
     * ذخیره پیشرفت
     */
    async saveProgress(stats, stars) {
        try {
            const response = await fetch(`${this.config.siteUrl}/api/save-progress.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    lesson_id: this.config.lessonId,
                    wpm: stats.wpm,
                    accuracy: stats.accuracy,
                    time_spent: stats.timeElapsed,
                    errors: stats.errors,
                    stars: stars
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                console.log('✅ Progress saved successfully');
            } else {
                console.error('❌ Failed to save progress:', result.message);
            }
        } catch (error) {
            console.error('❌ Error saving progress:', error);
        }
    }
    
    /**
     * توقف موقت / ادامه
     */
    togglePause() {
        if (!this.typingEngine || this.isCompleted) return;
        
        this.isPaused = !this.isPaused;
        
        if (this.isPaused) {
            this.typingEngine.pause();
            this.timer?.pause();
            this.elements.pauseBtn.textContent = `▶️ ${this.config.translations.resume}`;
        } else {
            this.typingEngine.resume();
            this.timer?.resume();
            this.elements.pauseBtn.textContent = `⏸️ ${this.config.translations.pause}`;
        }
    }
    
    /**
     * شروع مجدد
     */
    restart() {
        // ریست موتور تایپینگ
        if (this.typingEngine) {
            this.typingEngine.reset();
        }
        
        // ریست تایمر
        if (this.timer) {
            this.timer.reset();
        }
        
        // ریست وضعیت
        this.isPaused = false;
        this.isCompleted = false;
        
        // ریست نمایش
        this.updateStats({
            wpm: 0,
            accuracy: 100,
            errors: 0,
            percentage: 0
        });
        
        // فوکوس
        this.elements.typingInput?.focus();
        
        console.log('🔄 Practice restarted');
    }
    
    /**
     * نمایش/مخفی کیبورد
     */
    toggleKeyboard() {
        this.keyboardVisible = !this.keyboardVisible;
        
        if (this.keyboardVisible) {
            this.elements.keyboardZone?.classList.remove('hidden');
        } else {
            this.elements.keyboardZone?.classList.add('hidden');
        }
    }
    
    /**
     * فعال/غیرفعال صدا
     */
    toggleSound() {
        this.soundEnabled = !this.soundEnabled;
        
        if (this.typingEngine) {
            this.typingEngine.toggleSound(this.soundEnabled);
        }
        
        this.elements.toggleSound.textContent = this.soundEnabled ? '🔊' : '🔇';
    }
    
    /**
     * تنظیم Toggle تم
     */
    setupThemeToggle() {
        const themeToggle = document.getElementById('themeToggle');
        
        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                const currentTheme = document.body.classList.contains('theme-dark') ? 'dark' : 'light';
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                
                document.body.classList.remove('theme-dark', 'theme-light');
                document.body.classList.add(`theme-${newTheme}`);
                
                themeToggle.textContent = newTheme === 'dark' ? '☀️' : '🌙';
                
                // ذخیره در localStorage
                localStorage.setItem('theme', newTheme);
            });
        }
    }
    
    /**
     * نمایش Modal
     */
    showModal() {
        this.elements.resultModal?.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    /**
     * مخفی کردن Modal
     */
    hideModal() {
        this.elements.resultModal?.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// ====================================
// Initialize
// ====================================
document.addEventListener('DOMContentLoaded', () => {
    console.log('📄 DOM Content Loaded');
    
    // چک کردن وجود CONFIG
    if (!window.PRACTICE_CONFIG) {
        console.error('❌ PRACTICE_CONFIG not found!');
        return;
    }
    
    console.log('✅ Config loaded:', window.PRACTICE_CONFIG);
    
    // راه‌اندازی Practice Manager
    try {
        window.practiceManager = new PracticeManager();
        console.log('✅ Practice Manager initialized successfully!');
    } catch (error) {
        console.error('❌ Error initializing Practice Manager:', error);
    }
});