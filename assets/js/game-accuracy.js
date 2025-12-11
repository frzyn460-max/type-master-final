/**
 * ====================================
 * Accuracy Challenge Game Logic
 * منطق بازی چالش دقت
 * ====================================
 */

class AccuracyGame {
    constructor() {
        this.config = window.GAME_CONFIG;
        
        // Game State
        this.words = this.shuffleArray([...this.config.words]).slice(0, 20);
        this.currentWordIndex = 0;
        this.totalWords = 20;
        this.mistakes = 0;
        this.perfectWords = 0;
        this.wordMistakes = 0; // اشتباهات کلمه فعلی
        
        // Elements
        this.startScreen = document.getElementById('startScreen');
        this.gameoverScreen = document.getElementById('gameoverScreen');
        this.wordDisplay = document.getElementById('wordDisplay');
        this.wordNumber = document.getElementById('wordNumber');
        this.input = document.getElementById('typingInput');
        
        // Stats Elements
        this.accuracyDisplay = document.getElementById('accuracyDisplay');
        this.mistakesDisplay = document.getElementById('mistakesDisplay');
        this.wordsLeftDisplay = document.getElementById('wordsLeftDisplay');
        this.perfectDisplay = document.getElementById('perfectDisplay');
        this.progressBar = document.getElementById('progressBar');
        this.progressText = document.getElementById('progressText');
        
        this.init();
    }
    
    init() {
        console.log('🎯 Accuracy Challenge initialized');
        console.log('📝 Words:', this.words);
        
        // Event Listeners
        document.getElementById('startBtn').addEventListener('click', () => this.startGame());
        this.input.addEventListener('input', (e) => this.handleInput(e));
    }
    
    shuffleArray(array) {
        for (let i = array.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [array[i], array[j]] = [array[j], array[i]];
        }
        return array;
    }
    
    startGame() {
        console.log('🚀 Game Started');
        
        this.startScreen.style.display = 'none';
        this.input.disabled = false;
        this.input.focus();
        
        this.showWord();
    }
    
    showWord() {
        if (this.currentWordIndex >= this.totalWords) {
            this.gameOver();
            return;
        }
        
        const word = this.words[this.currentWordIndex];
        this.wordDisplay.textContent = word;
        this.wordDisplay.classList.remove('correct', 'wrong');
        
        this.wordNumber.textContent = `${this.config.translations.current_word} ${this.currentWordIndex + 1}/20`;
        this.wordsLeftDisplay.textContent = this.totalWords - this.currentWordIndex;
        
        this.wordMistakes = 0;
        this.input.value = '';
        this.input.classList.remove('error');
    }
    
    handleInput(e) {
        const typed = e.target.value;
        const currentWord = this.words[this.currentWordIndex];
        
        // بررسی هر کاراکتر
        let isCorrect = true;
        for (let i = 0; i < typed.length; i++) {
            if (typed[i] !== currentWord[i]) {
                isCorrect = false;
                this.wordMistakes++;
                this.mistakes++;
                this.input.classList.add('error');
                this.updateStats();
                
                // صدای خطا (اختیاری)
                this.input.value = typed.slice(0, -1); // حذف کاراکتر اشتباه
                return;
            }
        }
        
        // اگه کلمه کامل شد
        if (typed === currentWord) {
            this.wordComplete();
        }
    }
    
    wordComplete() {
        // اگه هیچ اشتباهی نداشته
        if (this.wordMistakes === 0) {
            this.perfectWords++;
            this.wordDisplay.classList.add('correct');
            this.perfectDisplay.textContent = this.perfectWords;
        } else {
            this.wordDisplay.classList.add('wrong');
        }
        
        // کلمه بعدی
        setTimeout(() => {
            this.currentWordIndex++;
            this.updateProgress();
            this.showWord();
        }, 500);
    }
    
    updateStats() {
        // محاسبه دقت
        const totalChars = this.words.slice(0, this.currentWordIndex + 1).join('').length + this.input.value.length;
        const correctChars = totalChars - this.mistakes;
        const accuracy = totalChars > 0 ? Math.round((correctChars / totalChars) * 100) : 100;
        
        this.accuracyDisplay.textContent = accuracy + '%';
        this.mistakesDisplay.textContent = this.mistakes;
    }
    
    updateProgress() {
        const progress = (this.currentWordIndex / this.totalWords) * 100;
        this.progressBar.style.width = progress + '%';
        this.progressText.textContent = `${this.currentWordIndex} / ${this.totalWords}`;
    }
    
    gameOver() {
        console.log('🏁 Game Over');
        
        // محاسبه آمار نهایی
        const totalChars = this.words.join('').length;
        const correctChars = totalChars - this.mistakes;
        const accuracy = Math.round((correctChars / totalChars) * 100);
        
        // محاسبه امتیاز
        const baseScore = 1000;
        const accuracyBonus = accuracy * 10;
        const perfectBonus = this.perfectWords * 50;
        const score = baseScore + accuracyBonus + perfectBonus;
        
        // نمایش نتایج
        document.getElementById('finalAccuracy').textContent = accuracy + '%';
        document.getElementById('finalPerfect').textContent = this.perfectWords;
        document.getElementById('finalScore').textContent = score;
        
        // تعیین عنوان و آیکون
        const resultIcon = document.getElementById('resultIcon');
        const resultTitle = document.getElementById('resultTitle');
        const resultBadge = document.getElementById('resultBadge');
        const tr = this.config.translations;
        
        if (accuracy === 100) {
            resultIcon.textContent = '💎';
            resultTitle.textContent = tr.perfect_score;
            resultBadge.innerHTML = `<div class="badge badge-gold">🏆 ${tr.perfect_score}</div>`;
        } else if (accuracy >= 95) {
            resultIcon.textContent = '⭐';
            resultTitle.textContent = tr.excellent;
            resultBadge.innerHTML = `<div class="badge badge-gold">${tr.almost_perfect}</div>`;
        } else if (accuracy >= 85) {
            resultIcon.textContent = '✨';
            resultTitle.textContent = tr.good;
            resultBadge.innerHTML = `<div class="badge badge-silver">${tr.good}</div>`;
        } else {
            resultIcon.textContent = '💪';
            resultTitle.textContent = tr.try_again;
            resultBadge.innerHTML = `<div class="badge badge-silver">${tr.need_practice}</div>`;
        }
        
        // چک رکورد جدید
        if (accuracy > this.config.bestAccuracy) {
            resultBadge.innerHTML += `<div class="badge badge-gold" style="margin-top: 1rem;">🎉 ${tr.new_record}</div>`;
        }
        
        this.gameoverScreen.style.display = 'flex';
        
        // ذخیره امتیاز
        this.saveScore(score, accuracy);
    }
    
    async saveScore(score, accuracy) {
        try {
            const response = await fetch(`${this.config.siteUrl}/api/save-game-score.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    game_type: 'accuracy',
                    score: score,
                    level_reached: 0,
                    wpm: 0,
                    accuracy: accuracy,
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
    window.game = new AccuracyGame();
    console.log('✅ Accuracy Challenge ready!');
});