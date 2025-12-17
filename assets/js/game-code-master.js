/**
 * ====================================
 * Code Master Game Logic
 * منطق بازی استاد کدنویسی
 * ====================================
 */

class CodeMasterGame {
    constructor() {
        this.config = window.GAME_CONFIG;

        // Game Settings
        this.selectedLanguage = null;
        this.selectedLevel = 'easy';

        // Game State
        this.codeLines = [];
        this.currentLineIndex = 0;
        this.totalLines = 10;
        this.mistakes = 0;
        this.perfectLines = 0;
        this.startTime = null;
        this.totalChars = 0;
        this.correctChars = 0;

        // Elements
        this.setupScreen = document.getElementById('setupScreen');
        this.gameoverScreen = document.getElementById('gameoverScreen');
        this.codeDisplay = document.getElementById('codeDisplay');
        this.codeDescription = document.getElementById('codeDescription');
        this.codeLangBadge = document.getElementById('codeLangBadge');
        this.lineNumber = document.getElementById('lineNumber');
        this.input = document.getElementById('typingInput');

        // Stats Elements
        this.wpmDisplay = document.getElementById('wpmDisplay');
        this.accuracyDisplay = document.getElementById('accuracyDisplay');
        this.scoreDisplay = document.getElementById('scoreDisplay');
        this.linesLeftDisplay = document.getElementById('linesLeftDisplay');
        this.progressBar = document.getElementById('progressBar');
        this.progressText = document.getElementById('progressText');

        this.init();
    }

    init() {
        console.log('💻 Code Master initialized');

        // Language Selection
        document.querySelectorAll('.lang-option').forEach(option => {
            option.addEventListener('click', (e) => {
                document.querySelectorAll('.lang-option').forEach(o => o.classList.remove('selected'));
                option.classList.add('selected');
                this.selectedLanguage = option.dataset.lang;
                this.checkStartButton();
            });
        });

        // Level Selection
        document.querySelectorAll('.level-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('.level-btn').forEach(b => b.classList.remove('selected'));
                btn.classList.add('selected');
                this.selectedLevel = btn.dataset.level;
            });
        });

        // Start Button
        document.getElementById('startBtn').addEventListener('click', () => this.startGame());

        // Typing Input
        this.input.addEventListener('input', (e) => this.handleInput(e));
    }

    checkStartButton() {
        const startBtn = document.getElementById('startBtn');
        startBtn.disabled = !this.selectedLanguage;
    }

    startGame() {
        if (!this.selectedLanguage) return;

        console.log('🚀 Game Started');
        console.log('Language:', this.selectedLanguage);
        console.log('Level:', this.selectedLevel);

        // دریافت کدها
        const codes = this.config.codeSamples[this.selectedLanguage][this.selectedLevel];
        this.codeLines = this.shuffleArray([...codes]).slice(0, this.totalLines);

        console.log('Code Lines:', this.codeLines);

        // شروع بازی
        this.setupScreen.style.display = 'none';
        this.input.disabled = false;
        this.input.focus();

        // نمایش زبان
        const langNames = {
            'javascript': 'JavaScript',
            'html': 'HTML',
            'css': 'CSS',
            'php': 'PHP',
            'python': 'Python',
            'java': 'Java',
            'cpp': 'C++'
        };
        this.codeLangBadge.textContent = langNames[this.selectedLanguage];

        this.startTime = Date.now();
        this.showLine();
    }

    shuffleArray(array) {
        for (let i = array.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [array[i], array[j]] = [array[j], array[i]];
        }
        return array;
    }

    showLine() {
        if (this.currentLineIndex >= this.totalLines) {
            this.gameOver();
            return;
        }

        const lineData = this.codeLines[this.currentLineIndex];
        const code = lineData.code;
        const description = this.config.language === 'fa' ? lineData.desc_fa : lineData.desc_en;

        this.codeDisplay.textContent = code;
        this.codeDescription.textContent = '📝 ' + description;

        this.lineNumber.textContent = `Line ${this.currentLineIndex + 1}/${this.totalLines}`;
        this.linesLeftDisplay.textContent = this.totalLines - this.currentLineIndex;

        this.input.value = '';
        this.input.classList.remove('correct', 'error');
        this.lineMistakes = 0;
    }

    handleInput(e) {
        const typed = e.target.value;
        const currentLineData = this.codeLines[this.currentLineIndex];
        const currentLine = currentLineData.code;

        // بررسی هر کاراکتر
        let isCorrect = true;
        for (let i = 0; i < typed.length; i++) {
            if (typed[i] !== currentLine[i]) {
                isCorrect = false;
                this.mistakes++;
                this.lineMistakes++;

                // حذف کاراکتر اشتباه
                this.input.value = typed.slice(0, -1);
                this.input.classList.add('error');

                setTimeout(() => {
                    this.input.classList.remove('error');
                }, 300);

                this.updateStats();
                return;
            }
        }

        // محاسبه کاراکترها
        this.totalChars++;
        this.correctChars++;

        // اگه خط کامل شد
        if (typed === currentLine) {
            this.lineComplete();
        }

        this.updateStats();
    }

    lineComplete() {
        console.log('✅ Line complete!');

        // اگه بدون اشتباه
        if (this.lineMistakes === 0) {
            this.perfectLines++;
            this.input.classList.add('correct');
        }

        // خط بعدی
        setTimeout(() => {
            this.currentLineIndex++;
            this.updateProgress();
            this.showLine();
        }, 500);
    }

    updateStats() {
        // محاسبه WPM
        const timeElapsed = (Date.now() - this.startTime) / 1000 / 60;
        const wordsTyped = this.correctChars / 5;
        const wpm = timeElapsed > 0 ? Math.round(wordsTyped / timeElapsed) : 0;

        // محاسبه دقت
        const accuracy = this.totalChars > 0 ? Math.round((this.correctChars / this.totalChars) * 100) : 100;

        // محاسبه امتیاز
        const score = (wpm * 10) + (accuracy * 5) + (this.perfectLines * 100);

        this.wpmDisplay.textContent = wpm;
        this.accuracyDisplay.textContent = accuracy + '%';
        this.scoreDisplay.textContent = Math.round(score);
    }

    updateProgress() {
        const progress = (this.currentLineIndex / this.totalLines) * 100;
        this.progressBar.style.width = progress + '%';
        this.progressText.textContent = `${this.currentLineIndex} / ${this.totalLines}`;
    }

    gameOver() {
        console.log('🏁 Game Over');

        // محاسبه آمار نهایی
        const timeElapsed = (Date.now() - this.startTime) / 1000 / 60;
        const wordsTyped = this.correctChars / 5;
        const wpm = timeElapsed > 0 ? Math.round(wordsTyped / timeElapsed) : 0;
        const accuracy = this.totalChars > 0 ? Math.round((this.correctChars / this.totalChars) * 100) : 100;
        const score = (wpm * 10) + (accuracy * 5) + (this.perfectLines * 100);

        // نمایش نتایج
        document.getElementById('finalWPM').textContent = wpm;
        document.getElementById('finalAccuracy').textContent = accuracy + '%';
        document.getElementById('finalScore').textContent = Math.round(score);

        // تعیین عنوان
        const resultIcon = document.getElementById('resultIcon');
        const resultTitle = document.getElementById('resultTitle');
        const tr = this.config.translations;

        if (accuracy === 100 && this.perfectLines === this.totalLines) {
            resultIcon.textContent = '🏆';
            resultTitle.textContent = tr.coding_master;
        } else if (accuracy >= 95) {
            resultIcon.textContent = '⭐';
            resultTitle.textContent = tr.great_job;
        } else {
            resultIcon.textContent = '💪';
            resultTitle.textContent = tr.keep_practicing;
        }

        this.gameoverScreen.style.display = 'flex';

        // ذخیره امتیاز
        this.saveScore(Math.round(score), wpm, accuracy);
    }

    async saveScore(score, wpm, accuracy) {
        try {
            const response = await fetch(`${this.config.siteUrl}/api/save-game-score.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    game_type: 'code-master',
                    score: score,
                    level_reached: 0,
                    wpm: wpm,
                    accuracy: accuracy,
                    duration: Math.round((Date.now() - this.startTime) / 1000)
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
    window.game = new CodeMasterGame();
    console.log('✅ Code Master ready!');
});