/**
 * ====================================
 * Speed Race Game - Racing Edition
 * منطق بازی مسابقه با رقبا
 * ====================================
 */

class SpeedRaceGame {
    constructor() {
        this.config = window.GAME_CONFIG;
        
        // Game State
        this.textToType = this.config.texts[Math.floor(Math.random() * this.config.texts.length)];
        this.currentIndex = 0;
        this.errors = 0;
        this.startTime = null;
        this.timerInterval = null;
        this.botInterval1 = null;
        this.botInterval2 = null;
        this.gameFinished = false;
        
        // Bot Progress
        this.bot1Progress = 0;
        this.bot2Progress = 0;
        
        // Elements
        this.startScreen = document.getElementById('startScreen');
        this.startBtn = document.getElementById('startBtn');
        this.gameOverModal = document.getElementById('gameOverModal');
        this.textDisplay = document.getElementById('textDisplay');
        this.input = document.getElementById('typingInput');
        
        // Stats Elements
        this.wpmDisplay = document.getElementById('wpmDisplay');
        this.accuracyDisplay = document.getElementById('accuracyDisplay');
        this.timeDisplay = document.getElementById('timeDisplay');
        this.playerProgress = document.getElementById('playerProgress');
        this.bot1ProgressEl = document.getElementById('bot1Progress');
        this.bot2ProgressEl = document.getElementById('bot2Progress');
        
        this.init();
    }
    
    init() {
        console.log('🏎️ Speed Race initialized');
        console.log('📝 Text:', this.textToType);
        
        // Event Listeners
        this.startBtn.addEventListener('click', () => this.startCountdown());
        this.input.addEventListener('input', (e) => this.handleInput(e));
    }
    
    startCountdown() {
        const content = this.startScreen.querySelector('.start-content');
        
        let count = 3;
        content.innerHTML = `<div class="countdown">${count}</div>`;
        
        const countInterval = setInterval(() => {
            count--;
            if (count > 0) {
                content.innerHTML = `<div class="countdown">${count}</div>`;
            } else {
                content.innerHTML = `<div class="countdown">GO!</div>`;
                setTimeout(() => {
                    this.startScreen.style.display = 'none';
                    this.startRace();
                }, 500);
                clearInterval(countInterval);
            }
        }, 1000);
    }
    
    startRace() {
        console.log('🚀 Race Started!');
        
        this.displayText();
        this.input.disabled = false;
        this.input.focus();
        
        this.startTime = Date.now();
        this.timerInterval = setInterval(() => this.updateTimer(), 100);
        
        // Start Bots
        this.startBot1();
        this.startBot2();
    }
    
    displayText() {
        this.textDisplay.innerHTML = '';
        
        this.textToType.split('').forEach((char, index) => {
            const span = document.createElement('span');
            span.textContent = char;
            span.id = `char-${index}`;
            if (index === 0) span.classList.add('current');
            this.textDisplay.appendChild(span);
        });
    }
    
    startBot1() {
        this.botInterval1 = setInterval(() => {
            if (this.gameFinished) {
                clearInterval(this.botInterval1);
                return;
            }
            
            this.bot1Progress += Math.random() * 2; // سرعت متوسط
            if (this.bot1Progress >= 100) {
                this.bot1Progress = 100;
                this.checkRaceEnd();
            }
            this.bot1ProgressEl.style.width = this.bot1Progress + '%';
        }, 100);
    }
    
    startBot2() {
        this.botInterval2 = setInterval(() => {
            if (this.gameFinished) {
                clearInterval(this.botInterval2);
                return;
            }
            
            this.bot2Progress += Math.random() * 1.5; // یکم کندتر
            if (this.bot2Progress >= 100) {
                this.bot2Progress = 100;
                this.checkRaceEnd();
            }
            this.bot2ProgressEl.style.width = this.bot2Progress + '%';
        }, 100);
    }
    
    handleInput(e) {
        if (this.gameFinished) return;
        
        const input = e.target.value;
        const char = input[input.length - 1];
        const currentChar = this.textToType[this.currentIndex];
        const span = document.getElementById(`char-${this.currentIndex}`);
        
        if (char === currentChar) {
            span.classList.add('correct');
            span.classList.remove('current');
            this.currentIndex++;
            
            const progress = (this.currentIndex / this.textToType.length) * 100;
            this.playerProgress.style.width = progress + '%';
            
            if (this.currentIndex < this.textToType.length) {
                document.getElementById(`char-${this.currentIndex}`).classList.add('current');
            } else {
                this.finishRace();
            }
        } else {
            span.classList.add('incorrect');
            this.errors++;
        }
        
        e.target.value = '';
        this.updateStats();
    }
    
    updateTimer() {
        if (!this.startTime) return;
        
        const elapsed = Math.floor((Date.now() - this.startTime) / 1000);
        const minutes = Math.floor(elapsed / 60);
        const seconds = elapsed % 60;
        this.timeDisplay.textContent = `${minutes}:${String(seconds).padStart(2, '0')}`;
    }
    
    updateStats() {
        if (!this.startTime) return;
        
        const timeElapsed = (Date.now() - this.startTime) / 1000 / 60;
        const wordsTyped = this.currentIndex / 5;
        const wpm = Math.round(wordsTyped / timeElapsed) || 0;
        const accuracy = this.currentIndex > 0
            ? Math.round(((this.currentIndex - this.errors) / this.currentIndex) * 100)
            : 100;
        
        this.wpmDisplay.textContent = wpm;
        this.accuracyDisplay.textContent = accuracy + '%';
    }
    
    checkRaceEnd() {
        const playerProgress = parseFloat(this.playerProgress.style.width);
        
        if (this.bot1Progress >= 100 || this.bot2Progress >= 100) {
            this.finishRace();
        }
    }
    
    finishRace() {
        if (this.gameFinished) return;
        this.gameFinished = true;
        
        console.log('🏁 Race Finished!');
        
        clearInterval(this.timerInterval);
        clearInterval(this.botInterval1);
        clearInterval(this.botInterval2);
        
        const playerProgress = parseFloat(this.playerProgress.style.width) || 0;
        
        // محاسبه رتبه
        const positions = [
            { name: 'player', progress: playerProgress },
            { name: 'bot1', progress: this.bot1Progress },
            { name: 'bot2', progress: this.bot2Progress }
        ].sort((a, b) => b.progress - a.progress);
        
        const playerPosition = positions.findIndex(p => p.name === 'player') + 1;
        
        // علامت‌گذاری برنده
        if (playerPosition === 1) {
            document.querySelector('.racer.player').classList.add('winner');
        }
        
        this.showResults(playerPosition);
    }
    
    showResults(position) {
        const timeElapsed = (Date.now() - this.startTime) / 1000 / 60;
        const wordsTyped = this.currentIndex / 5;
        const wpm = Math.round(wordsTyped / timeElapsed) || 0;
        const accuracy = this.currentIndex > 0
            ? Math.round(((this.currentIndex - this.errors) / this.currentIndex) * 100)
            : 0;
        
        // محاسبه امتیاز
        const positionBonus = position === 1 ? 300 : position === 2 ? 200 : 100;
        const score = Math.round(positionBonus + (wpm * 2));
        
        // نمایش نتایج
        document.getElementById('finalPosition').textContent = position;
        document.getElementById('finalWPM').textContent = wpm;
        document.getElementById('finalScore').textContent = score;
        
        // تغییر پیام بر اساس رتبه
        const resultIcon = document.getElementById('resultIcon');
        const resultTitle = document.getElementById('resultTitle');
        const resultSubtitle = document.getElementById('resultSubtitle');
        const tr = this.config.translations;
        
        if (position === 1) {
            resultIcon.textContent = '🏆';
            resultTitle.textContent = tr.you_won;
            resultSubtitle.textContent = tr.excellent;
        } else if (position === 2) {
            resultIcon.textContent = '🥈';
            resultTitle.textContent = tr.second_place;
            resultSubtitle.textContent = tr.good_job;
        } else {
            resultIcon.textContent = '🥉';
            resultTitle.textContent = tr.third_place;
            resultSubtitle.textContent = tr.try_again;
        }
        
        this.gameOverModal.classList.add('show');
        
        // ذخیره امتیاز
        this.saveScore(score, wpm, accuracy);
    }
    
    async saveScore(score, wpm, accuracy) {
        try {
            const response = await fetch(`${this.config.siteUrl}/api/save-game-score.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    game_type: 'speed-race',
                    score: score,
                    level_reached: 0,
                    wpm: wpm,
                    accuracy: accuracy,
                    duration: Math.floor((Date.now() - this.startTime) / 1000)
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
    window.game = new SpeedRaceGame();
    console.log('✅ Speed Race ready!');
});