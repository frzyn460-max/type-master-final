/**
 * ====================================
 * Word Rain Game Logic
 * منطق بازی بارش کلمات
 * ====================================
 */

class WordRainGame {
    constructor() {
        this.config = window.GAME_CONFIG;
        
        // Game State
        this.score = 0;
        this.level = 1;
        this.lives = 3;
        this.combo = 0;
        this.isPlaying = false;
        this.isPaused = false;
        
        // Game Settings
        this.words = this.getWords();
        this.fallingWords = [];
        this.spawnInterval = null;
        this.updateInterval = null;
        this.baseSpeed = 3000; // 3 ثانیه برای رسیدن به پایین
        
        // Elements
        this.container = document.getElementById('gameContainer');
        this.input = document.getElementById('gameInput');
        this.welcomeScreen = document.getElementById('welcomeScreen');
        this.gameoverScreen = document.getElementById('gameoverScreen');
        this.startBtn = document.getElementById('startBtn');
        this.restartBtn = document.getElementById('restartBtn');
        
        // Stats Elements
        this.scoreDisplay = document.getElementById('scoreDisplay');
        this.levelDisplay = document.getElementById('levelDisplay');
        this.livesDisplay = document.getElementById('livesDisplay');
        this.comboDisplay = document.getElementById('comboDisplay');
        
        this.init();
    }
    
    init() {
        console.log('🎮 Word Rain Game initialized');
        
        // Event Listeners
        this.startBtn.addEventListener('click', () => this.startGame());
        this.restartBtn.addEventListener('click', () => this.restart());
        this.input.addEventListener('input', (e) => this.handleInput(e));
        
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isPlaying) {
                this.togglePause();
            }
        });
    }
    
    getWords() {
        // کلمات فارسی و انگلیسی
        const persianWords = [
            'کتاب', 'خانه', 'مدرسه', 'دانشگاه', 'کامپیوتر',
            'تایپ', 'بازی', 'سرعت', 'دقت', 'تمرین',
            'درس', 'آموزش', 'یادگیری', 'پیشرفت', 'موفقیت',
            'صفحه', 'کلید', 'کیبورد', 'ماوس', 'نمایشگر',
            'برنامه', 'سیستم', 'فایل', 'پوشه', 'داده',
            'شبکه', 'اینترنت', 'وب', 'سایت', 'صفحه',
            'نرم‌افزار', 'سخت‌افزار', 'پردازنده', 'حافظه', 'دیسک',
            'ویندوز', 'لینوکس', 'اپل', 'گوگل', 'مایکروسافت'
        ];
        
        const englishWords = [
            'book', 'house', 'school', 'university', 'computer',
            'type', 'game', 'speed', 'accuracy', 'practice',
            'lesson', 'education', 'learning', 'progress', 'success',
            'screen', 'key', 'keyboard', 'mouse', 'display',
            'program', 'system', 'file', 'folder', 'data',
            'network', 'internet', 'web', 'site', 'page',
            'software', 'hardware', 'processor', 'memory', 'disk',
            'windows', 'linux', 'apple', 'google', 'microsoft'
        ];
        
        return this.config.language === 'fa' ? persianWords : englishWords;
    }
    
    startGame() {
        console.log('🚀 Game Started');
        
        this.welcomeScreen.style.display = 'none';
        this.input.disabled = false;
        this.input.focus();
        this.isPlaying = true;
        
        // شروع spawn کلمات
        this.spawnWord();
        this.spawnInterval = setInterval(() => {
            if (!this.isPaused) {
                this.spawnWord();
            }
        }, 2000 - (this.level * 100)); // هر سطح سریع‌تر
        
        // آپدیت بازی
        this.updateInterval = setInterval(() => {
            if (!this.isPaused) {
                this.update();
            }
        }, 50);
    }
    
    spawnWord() {
        const word = this.words[Math.floor(Math.random() * this.words.length)];
        const containerWidth = this.container.clientWidth;
        
        const wordElement = document.createElement('div');
        wordElement.className = 'falling-word';
        wordElement.textContent = word;
        wordElement.dataset.word = word;
        wordElement.style.left = Math.random() * (containerWidth - 150) + 'px';
        wordElement.style.animationDuration = (this.baseSpeed - (this.level * 200)) + 'ms';
        
        this.container.appendChild(wordElement);
        this.fallingWords.push({
            element: wordElement,
            word: word,
            startTime: Date.now()
        });
    }
    
    update() {
        const currentTime = Date.now();
        
        this.fallingWords.forEach((fw, index) => {
            const elapsed = currentTime - fw.startTime;
            const duration = this.baseSpeed - (this.level * 200);
            
            // اگر به پایین رسید
            if (elapsed >= duration) {
                this.missWord(fw, index);
            }
        });
    }
    
    handleInput(e) {
        const typed = e.target.value.trim();
        
        if (!typed) return;
        
        // پیدا کردن کلمه منطبق
        const matchIndex = this.fallingWords.findIndex(fw => fw.word === typed);
        
        if (matchIndex !== -1) {
            this.catchWord(this.fallingWords[matchIndex], matchIndex);
            this.input.value = '';
        }
    }
    
    catchWord(fallingWord, index) {
        // محاسبه امتیاز
        const timeBonus = Math.floor((this.baseSpeed - (Date.now() - fallingWord.startTime)) / 100);
        const comboBonus = this.combo * 10;
        const points = 10 + timeBonus + comboBonus;
        
        this.score += points;
        this.combo++;
        
        // افکت
        fallingWord.element.classList.add('matched', 'explode');
        
        // حذف بعد از انیمیشن
        setTimeout(() => {
            fallingWord.element.remove();
        }, 500);
        
        this.fallingWords.splice(index, 1);
        
        // نمایش کمبو
        if (this.combo >= 5) {
            this.showCombo();
        }
        
        // چک سطح بعدی
        if (this.score >= this.level * 100) {
            this.levelUp();
        }
        
        this.updateUI();
    }
    
    missWord(fallingWord, index) {
        this.lives--;
        this.combo = 0;
        
        // افکت
        fallingWord.element.style.borderColor = 'var(--danger)';
        fallingWord.element.style.animation = 'shake 0.5s';
        
        setTimeout(() => {
            fallingWord.element.remove();
        }, 500);
        
        this.fallingWords.splice(index, 1);
        
        this.updateUI();
        
        // بررسی Game Over
        if (this.lives <= 0) {
            this.gameOver();
        }
    }
    
    showCombo() {
        const comboEl = document.createElement('div');
        comboEl.className = 'combo-display';
        comboEl.textContent = `🔥 ${this.combo}x COMBO!`;
        this.container.appendChild(comboEl);
        
        setTimeout(() => {
            comboEl.remove();
        }, 1000);
    }
    
    levelUp() {
        this.level++;
        
        // نمایش پیام
        const levelUpEl = document.createElement('div');
        levelUpEl.className = 'combo-display';
        levelUpEl.textContent = `⚡ LEVEL ${this.level}!`;
        this.container.appendChild(levelUpEl);
        
        setTimeout(() => {
            levelUpEl.remove();
        }, 1000);
        
        // افزایش سرعت spawn
        clearInterval(this.spawnInterval);
        this.spawnInterval = setInterval(() => {
            if (!this.isPaused) {
                this.spawnWord();
            }
        }, Math.max(800, 2000 - (this.level * 100)));
    }
    
    updateUI() {
        this.scoreDisplay.textContent = this.score.toLocaleString();
        this.levelDisplay.textContent = this.level;
        this.comboDisplay.textContent = this.combo + 'x';
        
        // Lives
        const hearts = '❤️'.repeat(this.lives) + '🖤'.repeat(3 - this.lives);
        this.livesDisplay.textContent = hearts;
    }
    
    togglePause() {
        this.isPaused = !this.isPaused;
        
        if (this.isPaused) {
            this.input.disabled = true;
        } else {
            this.input.disabled = false;
            this.input.focus();
        }
    }
    
    async gameOver() {
        console.log('💀 Game Over');
        
        this.isPlaying = false;
        this.input.disabled = true;
        
        // توقف intervals
        clearInterval(this.spawnInterval);
        clearInterval(this.updateInterval);
        
        // حذف کلمات باقی‌مانده
        this.fallingWords.forEach(fw => fw.element.remove());
        this.fallingWords = [];
        
        // نمایش صفحه Game Over
        document.getElementById('finalScore').textContent = this.score.toLocaleString();
        
        // چک رکورد جدید
        if (this.score > this.config.bestScore) {
            document.getElementById('newRecordBadge').style.display = 'block';
            document.getElementById('gameoverIcon').textContent = '🏆';
        }
        
        this.gameoverScreen.style.display = 'flex';
        
        // ذخیره امتیاز
        await this.saveScore();
    }
    
    async saveScore() {
        try {
            const response = await fetch(`${this.config.siteUrl}/api/save-game-score.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    game_type: 'word-rain',
                    score: this.score,
                    level_reached: this.level,
                    wpm: 0,
                    accuracy: 0,
                    duration: 0
                })
            });
            
            const result = await response.json();
            console.log('✅ Score saved:', result);
        } catch (error) {
            console.error('❌ Error saving score:', error);
        }
    }
    
    restart() {
        // Reset state
        this.score = 0;
        this.level = 1;
        this.lives = 3;
        this.combo = 0;
        this.fallingWords = [];
        
        this.updateUI();
        
        // مخفی کردن Game Over
        this.gameoverScreen.style.display = 'none';
        document.getElementById('newRecordBadge').style.display = 'none';
        document.getElementById('gameoverIcon').textContent = '💀';
        
        // شروع مجدد
        this.startGame();
    }
}

// ====================================
// Initialize
// ====================================
document.addEventListener('DOMContentLoaded', () => {
    window.game = new WordRainGame();
    console.log('✅ Word Rain ready!');
});