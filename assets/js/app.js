/**
 * ====================================
 * Typing Master - Main JavaScript
 * اسکریپت اصلی پروژه
 * ====================================
 */

// ====================================
// 1. متغیرهای اصلی
// ====================================
const APP = {
    currentTheme: document.body.classList.contains('theme-dark') ? 'dark' : 'light',
    currentLang: document.documentElement.lang,
    modals: {},
    toast: null
};

// ====================================
// 2. راه‌اندازی اولیه
// ====================================
document.addEventListener('DOMContentLoaded', () => {
    initModals();
    initThemeToggle();
    initLangToggle();
    initForms();
    initToast();
    
    // بستن modal با کلیک روی overlay
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', () => {
            closeAllModals();
        });
    });
    
    // بستن modal با دکمه close
    document.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', () => {
            closeAllModals();
        });
    });
    
    // بستن modal با ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeAllModals();
        }
    });
});

// ====================================
// 3. مدیریت Modal ها
// ====================================
function initModals() {
    APP.modals.login = document.getElementById('loginModal');
    APP.modals.register = document.getElementById('registerModal');
    
    // دکمه‌های باز کردن modal
    document.getElementById('showLogin')?.addEventListener('click', () => {
        openModal('login');
    });
    
    document.getElementById('showRegister')?.addEventListener('click', () => {
        openModal('register');
    });
    
    // تعویض بین login و register
    document.getElementById('switchToRegister')?.addEventListener('click', (e) => {
        e.preventDefault();
        closeAllModals();
        openModal('register');
    });
    
    document.getElementById('switchToLogin')?.addEventListener('click', (e) => {
        e.preventDefault();
        closeAllModals();
        openModal('login');
    });
}

function openModal(modalName) {
    const modal = APP.modals[modalName];
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeAllModals() {
    Object.values(APP.modals).forEach(modal => {
        modal.classList.remove('active');
    });
    document.body.style.overflow = '';
}

// ====================================
// 4. تغییر تم
// ====================================
function initThemeToggle() {
    const themeBtn = document.getElementById('themeToggle');
    
    themeBtn?.addEventListener('click', async () => {
        const newTheme = APP.currentTheme === 'dark' ? 'light' : 'dark';
        
        // تغییر کلاس body
        document.body.classList.remove(`theme-${APP.currentTheme}`);
        document.body.classList.add(`theme-${newTheme}`);
        
        APP.currentTheme = newTheme;
        
        // ذخیره در سرور (اگر لاگین است)
        if (window.location.pathname !== '/index.php') {
            try {
                await fetch(`${APP_CONFIG.siteUrl}/api/update-theme.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ theme: newTheme })
                });
            } catch (error) {
                console.error('خطا در ذخیره تم:', error);
            }
        }
    });
}

// ====================================
// 5. تغییر زبان
// ====================================
function initLangToggle() {
    const langBtn = document.getElementById('langToggle');
    
    langBtn?.addEventListener('click', async () => {
        const newLang = APP.currentLang === 'fa' ? 'en' : 'fa';
        
        // ذخیره در سرور (اگر لاگین است)
        if (window.location.pathname !== '/index.php') {
            try {
                await fetch(`${APP_CONFIG.siteUrl}/api/update-language.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ language: newLang })
                });
            } catch (error) {
                console.error('خطا در ذخیره زبان:', error);
            }
        }
        
        // ذخیره در localStorage برای صفحات عمومی
        localStorage.setItem('language', newLang);
        
        // ریلود صفحه
        window.location.reload();
    });
}

// ====================================
// 6. فرم‌ها
// ====================================
function initForms() {
    // فرم ورود
    const loginForm = document.getElementById('loginForm');
    loginForm?.addEventListener('submit', handleLogin);
    
    // فرم ثبت‌نام
    const registerForm = document.getElementById('registerForm');
    registerForm?.addEventListener('submit', handleRegister);
}

async function handleLogin(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const data = {
        action: 'login',
        username: formData.get('username'),
        password: formData.get('password')
    };
    
    // غیرفعال کردن دکمه submit
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = APP.currentLang === 'fa' ? 'در حال ورود...' : 'Logging in...';
    
    try {
        const response = await fetch(`${APP_CONFIG.siteUrl}/api/auth.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message, 'success');
            setTimeout(() => {
                window.location.href = result.redirect;
            }, 1000);
        } else {
            showError('loginError', result.message);
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    } catch (error) {
        showError('loginError', APP.currentLang === 'fa' ? 'خطا در ارتباط با سرور' : 'Server connection error');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
}

async function handleRegister(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const data = {
        action: 'register',
        username: formData.get('username'),
        email: formData.get('email'),
        password: formData.get('password'),
        password_confirm: formData.get('password_confirm')
    };
    
    // چک کردن تطابق پسوردها
    if (data.password !== data.password_confirm) {
        showError('registerError', APP.currentLang === 'fa' ? 'رمز عبور و تکرار آن مطابقت ندارند' : 'Passwords do not match');
        return;
    }
    
    // غیرفعال کردن دکمه submit
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = APP.currentLang === 'fa' ? 'در حال ثبت‌نام...' : 'Registering...';
    
    try {
        const response = await fetch(`${APP_CONFIG.siteUrl}/api/auth.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message, 'success');
            setTimeout(() => {
                window.location.href = result.redirect;
            }, 1000);
        } else {
            showError('registerError', result.message);
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    } catch (error) {
        showError('registerError', APP.currentLang === 'fa' ? 'خطا در ارتباط با سرور' : 'Server connection error');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
}

// ====================================
// 7. نمایش خطا
// ====================================
function showError(elementId, message) {
    const errorElement = document.getElementById(elementId);
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.classList.add('active');
        
        // پاک کردن خطا بعد از 5 ثانیه
        setTimeout(() => {
            errorElement.classList.remove('active');
        }, 5000);
    }
}

// ====================================
// 8. Toast Notification
// ====================================
function initToast() {
    APP.toast = document.getElementById('toast');
}

function showToast(message, type = 'success') {
    if (!APP.toast) return;
    
    const toast = APP.toast;
    const icon = toast.querySelector('.toast-icon');
    const messageEl = toast.querySelector('.toast-message');
    
    // تنظیم محتوا
    messageEl.textContent = message;
    
    // تنظیم ایکون
    if (type === 'success') {
        icon.textContent = '✓';
        toast.classList.remove('error');
    } else {
        icon.textContent = '✕';
        toast.classList.add('error');
    }
    
    // نمایش
    toast.classList.add('active');
    
    // مخفی کردن بعد از 3 ثانیه
    setTimeout(() => {
        toast.classList.remove('active');
    }, 3000);
}

// ====================================
// 9. توابع کمکی
// ====================================

// انیمیشن اسکرول
function smoothScrollTo(element) {
    element.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
    });
}

// فرمت کردن اعداد فارسی
function toPersianNumber(num) {
    const persianDigits = '۰۱۲۳۴۵۶۷۸۹';
    return num.toString().replace(/\d/g, x => persianDigits[x]);
}

// کپی کردن متن
async function copyToClipboard(text) {
    try {
        await navigator.clipboard.writeText(text);
        showToast(APP.currentLang === 'fa' ? 'کپی شد!' : 'Copied!', 'success');
    } catch (error) {
        console.error('خطا در کپی:', error);
    }
}

// ====================================
// 10. Export
// ====================================
window.APP = APP;
window.showToast = showToast;
window.copyToClipboard = copyToClipboard;
window.toPersianNumber = toPersianNumber;