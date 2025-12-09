/**
 * ====================================
 * Virtual Keyboard
 * کیبورد مجازی
 * ====================================
 */

class KeyboardVisualizer {
    constructor(options = {}) {
        this.container = options.container || document.getElementById('keyboard');
        this.language = options.language || 'fa';
        this.showFingers = options.showFingers !== false;
        
        // لیست کلیدها
        this.layouts = {
            fa: this.getPersianLayout(),
            en: this.getEnglishLayout()
        };
        
        this.currentLayout = this.layouts[this.language];
        
        this.init();
    }
    
    /**
     * راه‌اندازی
     */
    init() {
        if (!this.container) return;
        
        this.render();
        this.attachEventListeners();
    }
    
    /**
     * رندر کردن کیبورد
     */
    render() {
        this.container.innerHTML = '';
        this.container.classList.add('keyboard');
        
        this.currentLayout.forEach(row => {
            const rowElement = document.createElement('div');
            rowElement.classList.add('keyboard-row');
            
            row.forEach(key => {
                const keyElement = this.createKey(key);
                rowElement.appendChild(keyElement);
            });
            
            this.container.appendChild(rowElement);
        });
    }
    
    /**
     * ایجاد کلید
     */
    createKey(keyData) {
        const keyElement = document.createElement('div');
        keyElement.classList.add('key');
        
        // کلاس‌های اضافی
        if (keyData.width) {
            keyElement.classList.add(`key-${keyData.width}`);
        }
        
        if (keyData.finger) {
            keyElement.classList.add(`finger-${keyData.finger}`);
            keyElement.dataset.finger = keyData.finger;
        }
        
        // محتوای کلید
        const keyContent = document.createElement('div');
        keyContent.classList.add('key-content');
        keyContent.textContent = keyData.label;
        
        // نمایش کاراکتر اصلی
        if (keyData.char) {
            keyElement.dataset.char = keyData.char;
        }
        
        keyElement.appendChild(keyContent);
        
        return keyElement;
    }
    
    /**
     * اتصال Event Listener ها
     */
    attachEventListeners() {
        // هایلایت کلید با keydown
        document.addEventListener('keydown', (e) => {
            this.highlightKey(e.key, true);
        });
        
        // حذف هایلایت با keyup
        document.addEventListener('keyup', (e) => {
            this.highlightKey(e.key, false);
        });
    }
    
    /**
     * هایلایت کردن کلید
     */
    highlightKey(char, active) {
        // نرمال‌سازی کاراکتر
        const normalizedChar = this.normalizeChar(char);
        
        // پیدا کردن کلید
        const keyElements = this.container.querySelectorAll('.key');
        keyElements.forEach(key => {
            if (key.dataset.char === normalizedChar) {
                if (active) {
                    key.classList.add('active');
                    
                    // نمایش انگشت
                    if (this.showFingers && key.dataset.finger) {
                        this.showFinger(key.dataset.finger);
                    }
                } else {
                    key.classList.remove('active');
                }
            }
        });
    }
    
    /**
     * نمایش انگشت
     */
    showFinger(finger) {
        // حذف هایلایت انگشت قبلی
        this.container.querySelectorAll('.key').forEach(key => {
            key.classList.remove('finger-highlight');
        });
        
        // هایلایت کلیدهای این انگشت
        this.container.querySelectorAll(`[data-finger="${finger}"]`).forEach(key => {
            key.classList.add('finger-highlight');
        });
        
        // حذف بعد از 500ms
        setTimeout(() => {
            this.container.querySelectorAll('.key').forEach(key => {
                key.classList.remove('finger-highlight');
            });
        }, 500);
    }
    
    /**
     * نرمال‌سازی کاراکتر
     */
    normalizeChar(char) {
        if (char === ' ') return 'space';
        if (char === 'Enter') return 'enter';
        if (char === 'Backspace') return 'backspace';
        if (char === 'Shift') return 'shift';
        if (char === 'Tab') return 'tab';
        return char.toLowerCase();
    }
    
    /**
     * لایه کیبورد فارسی
     */
    getPersianLayout() {
        return [
            // ردیف اول
            [
                { label: '‍', char: '‍', width: 'small', finger: 'pinky-left' },
                { label: '۱', char: '۱', finger: 'pinky-left' },
                { label: '۲', char: '۲', finger: 'ring-left' },
                { label: '۳', char: '۳', finger: 'middle-left' },
                { label: '۴', char: '۴', finger: 'index-left' },
                { label: '۵', char: '۵', finger: 'index-left' },
                { label: '۶', char: '۶', finger: 'index-right' },
                { label: '۷', char: '۷', finger: 'index-right' },
                { label: '۸', char: '۸', finger: 'middle-right' },
                { label: '۹', char: '۹', finger: 'ring-right' },
                { label: '۰', char: '۰', finger: 'pinky-right' },
                { label: '-', char: '-', finger: 'pinky-right' },
                { label: '=', char: '=', finger: 'pinky-right' },
                { label: '⌫', char: 'backspace', width: 'large', finger: 'pinky-right' }
            ],
            // ردیف دوم
            [
                { label: 'Tab ↹', char: 'tab', width: 'medium', finger: 'pinky-left' },
                { label: 'ض', char: 'ض', finger: 'pinky-left' },
                { label: 'ص', char: 'ص', finger: 'ring-left' },
                { label: 'ث', char: 'ث', finger: 'middle-left' },
                { label: 'ق', char: 'ق', finger: 'index-left' },
                { label: 'ف', char: 'ف', finger: 'index-left' },
                { label: 'غ', char: 'غ', finger: 'index-right' },
                { label: 'ع', char: 'ع', finger: 'index-right' },
                { label: 'ه', char: 'ه', finger: 'middle-right' },
                { label: 'خ', char: 'خ', finger: 'ring-right' },
                { label: 'ح', char: 'ح', finger: 'pinky-right' },
                { label: 'ج', char: 'ج', finger: 'pinky-right' },
                { label: 'چ', char: 'چ', finger: 'pinky-right' }
            ],
            // ردیف سوم
            [
                { label: 'Caps ⇪', char: 'caps', width: 'medium', finger: 'pinky-left' },
                { label: 'ش', char: 'ش', finger: 'pinky-left' },
                { label: 'س', char: 'س', finger: 'ring-left' },
                { label: 'ی', char: 'ی', finger: 'middle-left' },
                { label: 'ب', char: 'ب', finger: 'index-left' },
                { label: 'ل', char: 'ل', finger: 'index-left' },
                { label: 'ا', char: 'ا', finger: 'index-right' },
                { label: 'ت', char: 'ت', finger: 'index-right' },
                { label: 'ن', char: 'ن', finger: 'middle-right' },
                { label: 'م', char: 'م', finger: 'ring-right' },
                { label: 'ک', char: 'ک', finger: 'pinky-right' },
                { label: 'گ', char: 'گ', finger: 'pinky-right' },
                { label: '↵ Enter', char: 'enter', width: 'large', finger: 'pinky-right' }
            ],
            // ردیف چهارم
            [
                { label: '⇧ Shift', char: 'shift', width: 'xlarge', finger: 'pinky-left' },
                { label: 'ظ', char: 'ظ', finger: 'pinky-left' },
                { label: 'ط', char: 'ط', finger: 'ring-left' },
                { label: 'ز', char: 'ز', finger: 'middle-left' },
                { label: 'ر', char: 'ر', finger: 'index-left' },
                { label: 'ذ', char: 'ذ', finger: 'index-left' },
                { label: 'د', char: 'د', finger: 'index-right' },
                { label: 'پ', char: 'پ', finger: 'index-right' },
                { label: 'و', char: 'و', finger: 'middle-right' },
                { label: '.', char: '.', finger: 'ring-right' },
                { label: '/', char: '/', finger: 'pinky-right' },
                { label: '⇧ Shift', char: 'shift', width: 'xlarge', finger: 'pinky-right' }
            ],
            // ردیف پنجم
            [
                { label: 'Space فاصله', char: 'space', width: 'space', finger: 'thumb' }
            ]
        ];
    }
    
    /**
     * لایه کیبورد انگلیسی
     */
    getEnglishLayout() {
        return [
            // Row 1
            [
                { label: '`', char: '`', finger: 'pinky-left' },
                { label: '1', char: '1', finger: 'pinky-left' },
                { label: '2', char: '2', finger: 'ring-left' },
                { label: '3', char: '3', finger: 'middle-left' },
                { label: '4', char: '4', finger: 'index-left' },
                { label: '5', char: '5', finger: 'index-left' },
                { label: '6', char: '6', finger: 'index-right' },
                { label: '7', char: '7', finger: 'index-right' },
                { label: '8', char: '8', finger: 'middle-right' },
                { label: '9', char: '9', finger: 'ring-right' },
                { label: '0', char: '0', finger: 'pinky-right' },
                { label: '-', char: '-', finger: 'pinky-right' },
                { label: '=', char: '=', finger: 'pinky-right' },
                { label: '⌫', char: 'backspace', width: 'large', finger: 'pinky-right' }
            ],
            // Row 2
            [
                { label: 'Tab ↹', char: 'tab', width: 'medium', finger: 'pinky-left' },
                { label: 'Q', char: 'q', finger: 'pinky-left' },
                { label: 'W', char: 'w', finger: 'ring-left' },
                { label: 'E', char: 'e', finger: 'middle-left' },
                { label: 'R', char: 'r', finger: 'index-left' },
                { label: 'T', char: 't', finger: 'index-left' },
                { label: 'Y', char: 'y', finger: 'index-right' },
                { label: 'U', char: 'u', finger: 'index-right' },
                { label: 'I', char: 'i', finger: 'middle-right' },
                { label: 'O', char: 'o', finger: 'ring-right' },
                { label: 'P', char: 'p', finger: 'pinky-right' },
                { label: '[', char: '[', finger: 'pinky-right' },
                { label: ']', char: ']', finger: 'pinky-right' }
            ],
            // Row 3
            [
                { label: 'Caps ⇪', char: 'caps', width: 'medium', finger: 'pinky-left' },
                { label: 'A', char: 'a', finger: 'pinky-left' },
                { label: 'S', char: 's', finger: 'ring-left' },
                { label: 'D', char: 'd', finger: 'middle-left' },
                { label: 'F', char: 'f', finger: 'index-left' },
                { label: 'G', char: 'g', finger: 'index-left' },
                { label: 'H', char: 'h', finger: 'index-right' },
                { label: 'J', char: 'j', finger: 'index-right' },
                { label: 'K', char: 'k', finger: 'middle-right' },
                { label: 'L', char: 'l', finger: 'ring-right' },
                { label: ';', char: ';', finger: 'pinky-right' },
                { label: "'", char: "'", finger: 'pinky-right' },
                { label: '↵ Enter', char: 'enter', width: 'large', finger: 'pinky-right' }
            ],
            // Row 4
            [
                { label: '⇧ Shift', char: 'shift', width: 'xlarge', finger: 'pinky-left' },
                { label: 'Z', char: 'z', finger: 'pinky-left' },
                { label: 'X', char: 'x', finger: 'ring-left' },
                { label: 'C', char: 'c', finger: 'middle-left' },
                { label: 'V', char: 'v', finger: 'index-left' },
                { label: 'B', char: 'b', finger: 'index-left' },
                { label: 'N', char: 'n', finger: 'index-right' },
                { label: 'M', char: 'm', finger: 'index-right' },
                { label: ',', char: ',', finger: 'middle-right' },
                { label: '.', char: '.', finger: 'ring-right' },
                { label: '/', char: '/', finger: 'pinky-right' },
                { label: '⇧ Shift', char: 'shift', width: 'xlarge', finger: 'pinky-right' }
            ],
            // Row 5
            [
                { label: 'Space', char: 'space', width: 'space', finger: 'thumb' }
            ]
        ];
    }
    
    /**
     * تغییر زبان
     */
    changeLanguage(language) {
        if (this.layouts[language]) {
            this.language = language;
            this.currentLayout = this.layouts[language];
            this.render();
            this.attachEventListeners();
        }
    }
    
    /**
     * نابودی
     */
    destroy() {
        this.container.innerHTML = '';
    }
}

// ====================================
// Export
// ====================================
window.KeyboardVisualizer = KeyboardVisualizer;