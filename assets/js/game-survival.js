/**
 * ====================================
 * Survival Mode Game Logic
 * منطق بازی حالت بقا
 * ====================================
 */

class SurvivalGame {
    constructor() {
        this.config = window.GAME_CONFIG;
        
        // Game State
        this.lives = 3;
        this.level = 1;
        this.score = 0;
        this.wordsTyped = 0;
        this.currentWord = '';
        this.words = [...this.config.words];
        
        // Level Settings
        this.wordsPerLevel = 10;
        this.wordsInCurrentLevel = 0;
        
        // Elements
        this.startScreen = document.getElementById('startScreen');
        this.gameoverScreen = document.getElementById('gameoverScreen');
        this.wordDisplay = document.getElementById('wordDisplay');
        this.input = document.getElementById('typingInput');
        
        // Stats Elements
        this.livesDisplay = document.getElementById('livesDisplay');
        this.levelDisplay = document.getElementById('levelDisplay');
        this.scoreDisplay = document.getElementById('scoreDisplay');
        this.wordsDisplay = document.getElementById('wordsDisplay');
        
        this.init();
    }
    
    init() {
        console.log('❤️ Survival Mode initialized');
        
        // Event Listeners
        document.getElementById('startBtn').addEventListener('click', () => this.startGame());
        this.input.addEventListener('input', (e) => this.handleInput(e));
    }
    
    startGame() {
        console.log('🚀 Game Started');
        
        this.startScreen.style.display = 'none';
        this.input.disabled = false;
        this.input.focus();
        
        this.nextWord();
    }
    
    nextWord() {
        // انتخاب کلمه تصادفی
        this.currentWord = this.words[Math.floor(Math.random() * this.words.length)];
        this.wordDisplay.textContent = this.currentWord;
        this.wordDisplay.classList.remove('correct', 'wrong');
        this.input.value = '';
    }
    
    handleInput(e) {
        const typed = e.target.value.trim();
        
        if (!typed) return;
        
        // چک کردن با Space یا Enter
        if (e.target.value.endsWith(' ') || typed === this.currentWord) {
            if (typed === this.currentWord) {
                this.correctWord();
            } else {
                this.wrongWord();
            }
        }
    }
    
    correctWord() {
        console.log('✅ Correct!');
        
        this.wordDisplay.classList.add('correct');
        
        // امتیازدهی
        const basePoints = 10;
        const levelBonus = this.level * 5;
        const points = basePoints + levelBonus;
        
        this.score += points;
        this.wordsTyped++;
        this.wordsInCurrentLevel++;
        
        // چک level up
        if (this.wordsInCurrentLevel >= this.wordsPerLevel) {
            this.levelUp();
        }
        
        this.updateStats();
        
        setTimeout(() => {
            this.nextWord();
        }, 300);
    }
    
    wrongWord() {
        console.log('❌ Wrong!');
        
        this.wordDisplay.classList.add('wrong');
        this.lives--;
        
        if (this.lives <= 0) {
            this.gameOver();
            return;
        }
        
        this.updateStats();
        
        setTimeout(() => {
            this.nextWord();
        }, 500);
    }
    
    levelUp() {
        this.level++;
        this.wordsInCurrentLevel = 0;
        
        console.log('⬆️ Level Up!', this.level);
        
        // نمایش نوتیفیکیشن
        const notification = document.createElement('div');
        notification.className = 'level-notification';
        notification.textContent = `${this.config.translations.level_up} ${this.level}!`;
        document.querySelector('.game-content').appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 2000);
        
        this.updateStats();
    }
    
    updateStats() {
        // Lives
        const hearts = '❤️'.repeat(this.lives) + '🖤'.repeat(3 - this.lives);
        this.livesDisplay.textContent = hearts;
        
        if (this.lives === 1) {
            this.livesDisplay.classList.add('critical');
        } else {
            this.livesDisplay.classList.remove('critical');
        }
        
        // Other Stats
        this.levelDisplay.textContent = this.level;
        this.scoreDisplay.textContent = this.score.toLocaleString();
        this.wordsDisplay.textContent = this.wordsTyped;
    }
    
    gameOver() {
        console.log('💀 Game Over');
        
        this.input.disabled = true;
        
        // نمایش نتایج
        document.getElementById('finalLevel').textContent = this.level;
        document.getElementById('finalWords').textContent = this.wordsTyped;
        document.getElementById('finalScore').textContent = this.score.toLocaleString();
        
        const resultIcon = document.getElementById('resultIcon');
        const resultTitle = document.getElementById('resultTitle');
        const resultBadge = document.getElementById('resultBadge');
        const tr = this.config.translations;
        
        // تعیین پیام بر اساس سطح
        if (this.level >= 10) {
            resultIcon.textContent = '👑';
            resultTitle.textContent = tr.you_survived;
            resultBadge.innerHTML = `<div class="badge">🏆 ${tr.you_survived}</div>`;
        } else if (this.level >= 5) {
            resultIcon.textContent = '💪';
            resultTitle.textContent = tr.game_over;
            resultBadge.innerHTML = `<div class="badge">⭐ ${this.config.language === 'fa' ? 'عالی!' : 'Great!'}</div>`;
        } else {
            resultIcon.textContent = '💀';
            resultTitle.textContent = tr.game_over;
            resultBadge.innerHTML = `<div class="badge">${tr.try_again}</div>`;
        }
        
        // چک رکورد جدید
        if (this.score > this.config.bestScore) {
            resultBadge.innerHTML += `<div class="badge" style="margin-top: 1rem;">🎉 ${tr.new_record}</div>`;
        }
        
        this.gameoverScreen.style.display = 'flex';
        
        // ذخیره امتیاز
        this.saveScore();
    }
    
    async saveScore() {
        try {
            const response = await fetch(`${this.config.siteUrl}/api/save-game-score.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    game_type: 'survival',
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
}

// ====================================
// Initialize
// ====================================
document.addEventListener('DOMContentLoaded', () => {
    window.game = new SurvivalGame();
    console.log('✅ Survival Mode ready!');
});