/**
 * ====================================
 * Dashboard JavaScript
 * اسکریپت داشبورد
 * ====================================
 */

document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
    animateStats();
    animateProgressBar();
});

// ====================================
// 1. مدیریت Sidebar
// ====================================
function initSidebar() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mainContent = document.querySelector('.main-content');
    
    // دکمه موبایل
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            
            // بستن با کلیک بیرون از sidebar
            if (sidebar.classList.contains('active')) {
                document.addEventListener('click', closeSidebarOnClickOutside);
            } else {
                document.removeEventListener('click', closeSidebarOnClickOutside);
            }
        });
    }
    
    // بستن sidebar با کلیک بیرون
    function closeSidebarOnClickOutside(e) {
        if (!sidebar.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
            sidebar.classList.remove('active');
            document.removeEventListener('click', closeSidebarOnClickOutside);
        }
    }
    
    // بستن sidebar با ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
            document.removeEventListener('click', closeSidebarOnClickOutside);
        }
    });
}

// ====================================
// 2. انیمیشن آمار
// ====================================
function animateStats() {
    const statValues = document.querySelectorAll('.stat-value');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const target = entry.target;
                const finalValue = target.textContent.replace(/[^0-9.]/g, '');
                
                if (finalValue) {
                    animateValue(target, 0, parseFloat(finalValue), 1500);
                }
                
                observer.unobserve(target);
            }
        });
    }, { threshold: 0.5 });
    
    statValues.forEach(stat => {
        observer.observe(stat);
    });
}

function animateValue(element, start, end, duration) {
    const startTime = performance.now();
    const originalText = element.textContent;
    const hasPercent = originalText.includes('%');
    const hasComma = originalText.includes(',');
    
    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Easing function
        const easeOutQuad = progress * (2 - progress);
        const current = start + (end - start) * easeOutQuad;
        
        let displayValue = Math.floor(current);
        
        // فرمت کردن عدد
        if (hasComma) {
            displayValue = displayValue.toLocaleString();
        }
        
        element.textContent = displayValue + (hasPercent ? '%' : '');
        
        if (progress < 1) {
            requestAnimationFrame(update);
        } else {
            element.textContent = originalText; // بازگشت به مقدار اصلی
        }
    }
    
    requestAnimationFrame(update);
}

// ====================================
// 3. انیمیشن Progress Bar
// ====================================
function animateProgressBar() {
    const progressBar = document.querySelector('.xp-progress-fill');
    
    if (progressBar) {
        const targetWidth = progressBar.style.width;
        progressBar.style.width = '0%';
        
        // شروع انیمیشن بعد از کمی تاخیر
        setTimeout(() => {
            progressBar.style.width = targetWidth;
        }, 500);
    }
}

// ====================================
// 4. انیمیشن ورود صفحه
// ====================================
function animatePageLoad() {
    const cards = document.querySelectorAll('.stat-card, .card, .achievement-item');
    
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
}

// اجرای انیمیشن ورود
setTimeout(animatePageLoad, 100);

// ====================================
// 5. بروزرسانی زمان واقعی
// ====================================
function updateRealTimeData() {
    // این تابع می‌تواند از API داده‌های جدید دریافت کند
    // و آمار را به‌روزرسانی کند
    
    // مثال:
    // fetch('/api/get-stats.php')
    //     .then(res => res.json())
    //     .then(data => {
    //         // بروزرسانی UI
    //     });
}

// بروزرسانی هر 30 ثانیه
// setInterval(updateRealTimeData, 30000);

// ====================================
// 6. اکسپورت توابع
// ====================================
window.Dashboard = {
    animateStats,
    animateProgressBar,
    updateRealTimeData
};