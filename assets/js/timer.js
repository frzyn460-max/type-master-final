/**
 * ====================================
 * Timer Class
 * کلاس تایمر حرفه‌ای
 * ====================================
 */

class Timer {
    constructor(options = {}) {
        // تنظیمات
        this.displayElement = options.displayElement || null;
        this.format = options.format || 'mm:ss'; // mm:ss یا ss یا hh:mm:ss
        this.countDown = options.countDown || false;
        this.startTime = options.startTime || 0;
        this.endTime = options.endTime || null;
        
        // وضعیت
        this.isRunning = false;
        this.isPaused = false;
        this.elapsedTime = 0;
        this.startTimestamp = null;
        this.pauseTimestamp = null;
        this.intervalId = null;
        
        // Callbacks
        this.onTick = options.onTick || null;
        this.onStart = options.onStart || null;
        this.onPause = options.onPause || null;
        this.onResume = options.onResume || null;
        this.onStop = options.onStop || null;
        this.onComplete = options.onComplete || null;
    }
    
    /**
     * شروع تایمر
     */
    start() {
        if (this.isRunning) return;
        
        this.isRunning = true;
        this.isPaused = false;
        this.startTimestamp = Date.now() - (this.elapsedTime * 1000);
        
        this.intervalId = setInterval(() => {
            this.tick();
        }, 100); // هر 100ms به‌روزرسانی می‌شه
        
        if (this.onStart) {
            this.onStart();
        }
    }
    
    /**
     * توقف موقت
     */
    pause() {
        if (!this.isRunning || this.isPaused) return;
        
        this.isPaused = true;
        this.pauseTimestamp = Date.now();
        
        clearInterval(this.intervalId);
        this.intervalId = null;
        
        if (this.onPause) {
            this.onPause();
        }
    }
    
    /**
     * ادامه
     */
    resume() {
        if (!this.isPaused) return;
        
        this.isPaused = false;
        
        // محاسبه زمان توقف
        const pauseDuration = Date.now() - this.pauseTimestamp;
        this.startTimestamp += pauseDuration;
        
        this.intervalId = setInterval(() => {
            this.tick();
        }, 100);
        
        if (this.onResume) {
            this.onResume();
        }
    }
    
    /**
     * توقف کامل
     */
    stop() {
        if (!this.isRunning) return;
        
        this.isRunning = false;
        this.isPaused = false;
        
        clearInterval(this.intervalId);
        this.intervalId = null;
        
        if (this.onStop) {
            this.onStop(this.elapsedTime);
        }
    }
    
    /**
     * ریست
     */
    reset() {
        this.stop();
        this.elapsedTime = 0;
        this.startTimestamp = null;
        this.pauseTimestamp = null;
        this.updateDisplay();
    }
    
    /**
     * تیک (به‌روزرسانی)
     */
    tick() {
        if (!this.isRunning || this.isPaused) return;
        
        const now = Date.now();
        this.elapsedTime = (now - this.startTimestamp) / 1000; // ثانیه
        
        // چک countdown
        if (this.countDown) {
            this.elapsedTime = this.startTime - this.elapsedTime;
            
            if (this.elapsedTime <= 0) {
                this.elapsedTime = 0;
                this.stop();
                
                if (this.onComplete) {
                    this.onComplete();
                }
            }
        }
        
        // چک end time
        if (this.endTime && this.elapsedTime >= this.endTime) {
            this.elapsedTime = this.endTime;
            this.stop();
            
            if (this.onComplete) {
                this.onComplete();
            }
        }
        
        this.updateDisplay();
        
        if (this.onTick) {
            this.onTick(this.elapsedTime);
        }
    }
    
    /**
     * به‌روزرسانی نمایش
     */
    updateDisplay() {
        if (!this.displayElement) return;
        
        const formatted = this.formatTime(this.elapsedTime);
        
        if (this.displayElement instanceof HTMLElement) {
            this.displayElement.textContent = formatted;
        }
    }
    
    /**
     * فرمت کردن زمان
     */
    formatTime(seconds) {
        const totalSeconds = Math.floor(Math.abs(seconds));
        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const secs = totalSeconds % 60;
        const ms = Math.floor((Math.abs(seconds) % 1) * 10);
        
        switch (this.format) {
            case 'hh:mm:ss':
                return `${this.pad(hours)}:${this.pad(minutes)}:${this.pad(secs)}`;
            
            case 'mm:ss':
                return `${this.pad(minutes)}:${this.pad(secs)}`;
            
            case 'mm:ss.ms':
                return `${this.pad(minutes)}:${this.pad(secs)}.${ms}`;
            
            case 'ss':
                return `${totalSeconds}s`;
            
            case 'ss.ms':
                return `${totalSeconds}.${ms}s`;
            
            default:
                return `${this.pad(minutes)}:${this.pad(secs)}`;
        }
    }
    
    /**
     * اضافه کردن صفر
     */
    pad(num) {
        return String(num).padStart(2, '0');
    }
    
    /**
     * دریافت زمان فعلی
     */
    getElapsedTime() {
        return this.elapsedTime;
    }
    
    /**
     * دریافت زمان به صورت فرمت شده
     */
    getFormattedTime() {
        return this.formatTime(this.elapsedTime);
    }
    
    /**
     * تنظیم زمان
     */
    setTime(seconds) {
        this.elapsedTime = seconds;
        this.updateDisplay();
    }
    
    /**
     * تنظیم فرمت
     */
    setFormat(format) {
        this.format = format;
        this.updateDisplay();
    }
    
    /**
     * نابودی
     */
    destroy() {
        this.stop();
        this.displayElement = null;
        this.onTick = null;
        this.onStart = null;
        this.onPause = null;
        this.onResume = null;
        this.onStop = null;
        this.onComplete = null;
    }
}

/**
 * ====================================
 * Countdown Timer
 * تایمر شمارش معکوس
 * ====================================
 */
class CountdownTimer extends Timer {
    constructor(options = {}) {
        super({
            ...options,
            countDown: true,
            startTime: options.duration || 60 // پیش‌فرض 60 ثانیه
        });
    }
}

/**
 * ====================================
 * Stopwatch
 * کرنومتر
 * ====================================
 */
class Stopwatch extends Timer {
    constructor(options = {}) {
        super({
            ...options,
            countDown: false
        });
    }
    
    /**
     * ثبت لپ
     */
    lap() {
        if (!this.isRunning) return null;
        
        return {
            time: this.elapsedTime,
            formatted: this.getFormattedTime(),
            timestamp: Date.now()
        };
    }
}

// ====================================
// Export
// ====================================
window.Timer = Timer;
window.CountdownTimer = CountdownTimer;
window.Stopwatch = Stopwatch;