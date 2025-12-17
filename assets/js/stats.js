/**
 * ====================================
 * Stats Page - Advanced Charts
 * نمودارهای پیشرفته و جذاب
 * ====================================
 */

// تنظیمات پیش‌فرض
Chart.defaults.font.family = "'Vazirmatn', 'Tahoma', sans-serif";
const isDark = APP_CONFIG.theme === 'dark';
const textColor = isDark ? '#cbd5e0' : '#475569';
const gridColor = isDark ? '#334155' : '#e2e8f0';

// رنگ‌ها
const colors = {
    primary: '#6366f1',
    secondary: '#8b5cf6',
    success: '#10b981',
    warning: '#f59e0b',
    danger: '#ef4444'
};

// نمودار پیشرفت ماهانه - نسخه جذاب
if (document.getElementById('progressChart') && CHART_DATA.progress.labels.length > 0) {
    const ctx = document.getElementById('progressChart').getContext('2d');

    // گرادیانت زیبا برای WPM
    const gradientWpm = ctx.createLinearGradient(0, 0, 0, 450);
    gradientWpm.addColorStop(0, `${colors.primary}60`);
    gradientWpm.addColorStop(0.5, `${colors.primary}30`);
    gradientWpm.addColorStop(1, `${colors.primary}00`);

    // گرادیانت زیبا برای Accuracy
    const gradientAccuracy = ctx.createLinearGradient(0, 0, 0, 450);
    gradientAccuracy.addColorStop(0, `${colors.success}60`);
    gradientAccuracy.addColorStop(0.5, `${colors.success}30`);
    gradientAccuracy.addColorStop(1, `${colors.success}00`);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: CHART_DATA.progress.labels,
            datasets: [
                {
                    label: CHART_DATA.translations.speed_wpm,
                    data: CHART_DATA.progress.wpm,
                    borderColor: colors.primary,
                    backgroundColor: gradientWpm,
                    borderWidth: 4,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 8,
                    pointHoverRadius: 12,
                    pointBackgroundColor: colors.primary,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 4,
                    pointHoverBorderWidth: 5,
                    pointHoverBackgroundColor: colors.primary,
                    pointShadowOffsetX: 0,
                    pointShadowOffsetY: 3,
                    pointShadowBlur: 10,
                    pointShadowColor: `${colors.primary}40`
                },
                {
                    label: CHART_DATA.translations.accuracy_percent,
                    data: CHART_DATA.progress.accuracy,
                    borderColor: colors.success,
                    backgroundColor: gradientAccuracy,
                    borderWidth: 4,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 8,
                    pointHoverRadius: 12,
                    pointBackgroundColor: colors.success,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 4,
                    pointHoverBorderWidth: 5,
                    pointHoverBackgroundColor: colors.success
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    align: 'end',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 20,
                        font: {
                            size: 15,
                            weight: '700'
                        },
                        color: textColor,
                        boxWidth: 12,
                        boxHeight: 12
                    }
                },
                tooltip: {
                    enabled: true,
                    backgroundColor: isDark ? '#1e293b' : '#fff',
                    titleColor: isDark ? '#fff' : '#1e293b',
                    bodyColor: isDark ? '#cbd5e0' : '#475569',
                    borderColor: isDark ? '#475569' : '#cbd5e0',
                    borderWidth: 2,
                    padding: 20,
                    cornerRadius: 15,
                    displayColors: true,
                    boxPadding: 10,
                    titleFont: {
                        size: 16,
                        weight: '800'
                    },
                    bodyFont: {
                        size: 15,
                        weight: '600'
                    },
                    callbacks: {
                        title: function (context) {
                            return context[0].label;
                        },
                        label: function (context) {
                            const label = context.dataset.label;
                            const value = context.parsed.y.toFixed(1);
                            return ` ${label}: ${value}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: gridColor,
                        drawBorder: false,
                        lineWidth: 1
                    },
                    ticks: {
                        font: {
                            size: 14,
                            weight: '700'
                        },
                        color: textColor,
                        padding: 15,
                        callback: function (value) {
                            return value.toFixed(0);
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 14,
                            weight: '700'
                        },
                        color: textColor,
                        padding: 15,
                        maxRotation: 45,
                        minRotation: 0
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            },
            elements: {
                line: {
                    borderJoinStyle: 'round'
                }
            }
        }
    });
}

// انیمیشن برای Progress Bars سطوح
setTimeout(() => {
    const levelBars = document.querySelectorAll('.level-progress-fill');
    levelBars.forEach((bar, index) => {
        setTimeout(() => {
            const targetWidth = bar.getAttribute('data-width');
            bar.style.width = targetWidth;
        }, index * 200 + 500);
    });
}, 300);

// انیمیشن برای کارت‌های عملکرد
const perfCards = document.querySelectorAll('.perf-card');
const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry, index) => {
        if (entry.isIntersecting) {
            setTimeout(() => {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }, index * 100);
        }
    });
}, {
    threshold: 0.1
});

perfCards.forEach(card => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    card.style.transition = 'all 0.5s ease';
    observer.observe(card);
});

// انیمیشن برای کارت‌های سطح
const levelCards = document.querySelectorAll('.level-progress-card');
levelCards.forEach((card, index) => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    card.style.transition = 'all 0.6s ease';

    setTimeout(() => {
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
    }, index * 150 + 300);
});

// انیمیشن برای XP bar
setTimeout(() => {
    const xpFill = document.querySelector('.xp-fill');
    if (xpFill) {
        const width = xpFill.style.width;
        xpFill.style.width = '0%';
        setTimeout(() => {
            xpFill.style.width = width;
        }, 100);
    }
}, 500);