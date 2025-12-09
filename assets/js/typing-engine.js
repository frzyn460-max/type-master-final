/**
 * ====================================
 * Typing Engine
 * موتور اصلی تایپینگ
 * ====================================
 */

class TypingEngine {
    constructor(options = {}) {
        this.text = options.text || '';
        this.container = options.container || document.getElementById('typingArea');
        this.inputElement = options.input || document.getElementById('typingInput');
        
        // تنظیمات
        this.language = options.language || 'fa';
        this.soundEnabled = options.soundEnabled !== false;
        this.highlightErrors = options.highlightErrors !== false;
        
        // وضعیت
        this.currentIndex = 0;
        this.errors = 0;
        this.startTime = null;
        this.endTime = null;
        this.isStarted = false;
        this.isFinished = false;
        
        // آمار
        this.stats = {
            correctChars: 0,
            incorrectChars: 0,
            totalChars: 0,
            wpm: 0,
            accuracy: 0,
            timeElapsed: 0
        };
        
        // Callback ها
        this.onStart = options.onStart || null;
        this.onProgress = options.onProgress || null;
        this.onComplete = options.onComplete || null;
        this.onError = options.onError || null;
        
        this.init();
    }
    
    /**
     * راه‌اندازی اولیه
     */
    init() {
        if (!this.container || !this.inputElement) {
            console.error('❌ Container or input element not found!');
            return;
        }
        
        if (!this.text || this.text.trim() === '') {
            console.error('❌ Text is empty!');
            alert('خطا: متن خالی است!');
            return;
        }
        
        console.log('✅ Typing Engine initialized with text:', this.text.substring(0, 50) + '...');
        
        this.renderText();
        this.attachEventListeners();
        this.focusInput();
    }
    
    /**
     * رندر کردن متن
     */
    renderText() {
        this.container.innerHTML = '';
        
        const chars = this.text.split('');
        chars.forEach((char, index) => {
            const span = document.createElement('span');
            span.textContent = char;
            span.classList.add('char');
            span.dataset.index = index;
            
            if (index === 0) {
                span.classList.add('current');
            }
            
            this.container.appendChild(span);
        });
    }
    
    /**
     * اتصال Event Listener ها
     */
    attachEventListeners() {
        // تایپ کردن
        this.inputElement.addEventListener('input', (e) => {
            this.handleInput(e);
        });
        
        // جلوگیری از Paste
        this.inputElement.addEventListener('paste', (e) => {
            e.preventDefault();
        });
        
        // جلوگیری از Context Menu
        this.inputElement.addEventListener('contextmenu', (e) => {
            e.preventDefault();
        });
        
        // فوکوس مجدد
        this.container.addEventListener('click', () => {
            this.focusInput();
        });
    }
    
    /**
     * مدیریت ورودی
     */
    handleInput(e) {
        const typedText = e.target.value;
        const typedChar = typedText[typedText.length - 1];
        
        // شروع تایپینگ
        if (!this.isStarted) {
            this.start();
        }
        
        // چک کردن کاراکتر
        if (this.currentIndex < this.text.length) {
            const expectedChar = this.text[this.currentIndex];
            const isCorrect = typedChar === expectedChar;
            
            // به‌روزرسانی نمایش
            this.updateCharDisplay(this.currentIndex, isCorrect);
            
            // به‌روزرسانی آمار
            this.stats.totalChars++;
            if (isCorrect) {
                this.stats.correctChars++;
                this.playSound('correct');
            } else {
                this.stats.incorrectChars++;
                this.errors++;
                this.playSound('wrong');
                
                if (this.onError) {
                    this.onError(this.currentIndex, expectedChar, typedChar);
                }
            }
            
            // حرکت به کاراکتر بعدی
            this.currentIndex++;
            
            // به‌روزرسانی کاراکتر فعلی
            if (this.currentIndex < this.text.length) {
                this.updateCurrentChar();
            }
            
            // محاسبه WPM و Accuracy
            this.calculateStats();
            
            // Callback پیشرفت
            if (this.onProgress) {
                this.onProgress(this.getProgress());
            }
            
            // چک اتمام
            if (this.currentIndex >= this.text.length) {
                this.finish();
            }
        }
    }
    
    /**
     * شروع تایپینگ
     */
    start() {
        this.isStarted = true;
        this.startTime = Date.now();
        
        if (this.onStart) {
            this.onStart();
        }
    }
    
    /**
     * اتمام تایپینگ
     */
    finish() {
        this.isFinished = true;
        this.endTime = Date.now();
        this.inputElement.disabled = true;
        
        this.calculateFinalStats();
        this.playSound('complete');
        
        if (this.onComplete) {
            this.onComplete(this.getFinalStats());
        }
    }
    
    /**
     * به‌روزرسانی نمایش کاراکتر
     */
    updateCharDisplay(index, isCorrect) {
        const charElement = this.container.querySelector(`[data-index="${index}"]`);
        
        if (charElement) {
            charElement.classList.remove('current');
            
            if (isCorrect) {
                charElement.classList.add('correct');
            } else {
                charElement.classList.add('incorrect');
            }
        }
    }
    
    /**
     * به‌روزرسانی کاراکتر فعلی
     */
    updateCurrentChar() {
        // حذف current از همه
        this.container.querySelectorAll('.char').forEach(el => {
            el.classList.remove('current');
        });
        
        // اضافه کردن به کاراکتر فعلی
        const currentChar = this.container.querySelector(`[data-index="${this.currentIndex}"]`);
        if (currentChar) {
            currentChar.classList.add('current');
            
            // اسکرول خودکار
            this.scrollToChar(currentChar);
        }
    }
    
    /**
     * اسکرول به کاراکتر
     */
    scrollToChar(element) {
        const containerRect = this.container.getBoundingClientRect();
        const elementRect = element.getBoundingClientRect();
        
        if (elementRect.bottom > containerRect.bottom || elementRect.top < containerRect.top) {
            element.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
    
    /**
     * محاسبه آمار
     */
    calculateStats() {
        if (!this.startTime) return;
        
        const timeElapsed = (Date.now() - this.startTime) / 1000; // ثانیه
        this.stats.timeElapsed = timeElapsed;
        
        // محاسبه WPM (کلمه در دقیقه)
        // فرض: 5 کاراکتر = 1 کلمه
        const words = this.stats.correctChars / 5;
        const minutes = timeElapsed / 60;
        this.stats.wpm = minutes > 0 ? Math.round(words / minutes) : 0;
        
        // محاسبه دقت
        this.stats.accuracy = this.stats.totalChars > 0
            ? Math.round((this.stats.correctChars / this.stats.totalChars) * 100)
            : 0;
    }
    
    /**
     * محاسبه آمار نهایی
     */
    calculateFinalStats() {
        this.calculateStats();
        
        // زمان کل
        const totalTime = (this.endTime - this.startTime) / 1000;
        this.stats.timeElapsed = totalTime;
        
        // WPM نهایی
        const totalWords = this.text.length / 5;
        const minutes = totalTime / 60;
        this.stats.wpm = Math.round(totalWords / minutes);
    }
    
    /**
     * دریافت پیشرفت
     */
    getProgress() {
        return {
            current: this.currentIndex,
            total: this.text.length,
            percentage: Math.round((this.currentIndex / this.text.length) * 100),
            wpm: this.stats.wpm,
            accuracy: this.stats.accuracy,
            errors: this.errors,
            timeElapsed: this.stats.timeElapsed
        };
    }
    
    /**
     * دریافت آمار نهایی
     */
    getFinalStats() {
        return {
            wpm: this.stats.wpm,
            accuracy: this.stats.accuracy,
            errors: this.errors,
            correctChars: this.stats.correctChars,
            incorrectChars: this.stats.incorrectChars,
            totalChars: this.stats.totalChars,
            timeElapsed: Math.round(this.stats.timeElapsed),
            text: this.text
        };
    }
    
    /**
     * پخش صدا
     */
    playSound(type) {
        if (!this.soundEnabled) return;
        
        const sounds = {
            correct: './assets/sounds/correct.mp3',
            wrong: './assets/sounds/wrong.mp3',
            complete: './assets/sounds/complete.mp3'
        };
        
        const soundPath = sounds[type];
        if (soundPath) {
            const audio = new Audio(soundPath);
            audio.volume = 0.3;
            audio.play().catch(() => {}); // سایلنت fail
        }
    }
    
    /**
     * ریست کردن
     */
    reset(newText = null) {
        // ریست وضعیت
        this.currentIndex = 0;
        this.errors = 0;
        this.startTime = null;
        this.endTime = null;
        this.isStarted = false;
        this.isFinished = false;
        
        // ریست آمار
        this.stats = {
            correctChars: 0,
            incorrectChars: 0,
            totalChars: 0,
            wpm: 0,
            accuracy: 0,
            timeElapsed: 0
        };
        
        // متن جدید
        if (newText) {
            this.text = newText;
        }
        
        // رندر مجدد
        this.renderText();
        
        // فعال کردن input
        this.inputElement.disabled = false;
        this.inputElement.value = '';
        this.focusInput();
    }
    
    /**
     * فوکوس روی input
     */
    focusInput() {
        this.inputElement.focus();
    }
    
    /**
     * توقف موقت
     */
    pause() {
        this.inputElement.disabled = true;
    }
    
    /**
     * ادامه
     */
    resume() {
        this.inputElement.disabled = false;
        this.focusInput();
    }
    
    /**
     * تغییر متن
     */
    setText(newText) {
        this.text = newText;
        this.reset();
    }
    
    /**
     * فعال/غیرفعال کردن صدا
     */
    toggleSound(enabled) {
        this.soundEnabled = enabled;
    }
    
    /**
     * نابودی
     */
    destroy() {
        this.inputElement.removeEventListener('input', this.handleInput);
        this.container.innerHTML = '';
    }
}

// ====================================
// Export
// ====================================
window.TypingEngine = TypingEngine;