/**
 * ====================================
 * Profile JavaScript
 * اسکریپت مدیریت پروفایل
 * ====================================
 */

document.addEventListener('DOMContentLoaded', () => {
    initAvatarUpload();
    initSettingsForm();
});

// ====================================
// 1. آپلود آواتار
// ====================================
function initAvatarUpload() {
    const avatarUploadBtn = document.getElementById('avatarUploadBtn');
    const avatarInput = document.getElementById('avatarInput');
    const profileAvatar = document.getElementById('profileAvatar');
    
    if (!avatarUploadBtn || !avatarInput) return;
    
    // کلیک روی دکمه آپلود
    avatarUploadBtn.addEventListener('click', () => {
        avatarInput.click();
    });
    
    // انتخاب فایل
    avatarInput.addEventListener('change', async (e) => {
        const file = e.target.files[0];
        
        if (!file) return;
        
        // بررسی نوع فایل
        if (!file.type.startsWith('image/')) {
            showToast(APP_CONFIG.language === 'fa' ? 'لطفاً یک تصویر انتخاب کنید' : 'Please select an image', 'error');
            return;
        }
        
        // بررسی حجم فایل (حداکثر 5MB)
        if (file.size > 5 * 1024 * 1024) {
            showToast(APP_CONFIG.language === 'fa' ? 'حجم فایل نباید بیشتر از 5MB باشد' : 'File size must not exceed 5MB', 'error');
            return;
        }
        
        // نمایش پیش‌نمایش
        const reader = new FileReader();
        reader.onload = (e) => {
            profileAvatar.src = e.target.result;
        };
        reader.readAsDataURL(file);
        
        // آپلود فایل
        await uploadAvatar(file);
    });
}

async function uploadAvatar(file) {
    const formData = new FormData();
    formData.append('avatar', file);
    
    try {
        const response = await fetch(`${APP_CONFIG.siteUrl}/api/update-profile.php`, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message || (APP_CONFIG.language === 'fa' ? 'آواتار با موفقیت تغییر یافت' : 'Avatar updated successfully'), 'success');
            
            // بروزرسانی آواتار در header
            const headerAvatar = document.querySelector('.user-avatar');
            if (headerAvatar) {
                headerAvatar.src = `./uploads/avatars/${result.filename}`;
            }
        } else {
            showToast(result.message || (APP_CONFIG.language === 'fa' ? 'خطا در آپلود آواتار' : 'Error uploading avatar'), 'error');
        }
    } catch (error) {
        console.error('خطا در آپلود:', error);
        showToast(APP_CONFIG.language === 'fa' ? 'خطا در ارتباط با سرور' : 'Server connection error', 'error');
    }
}

// ====================================
// 2. فرم تنظیمات
// ====================================
function initSettingsForm() {
    const settingsForm = document.getElementById('settingsForm');
    
    if (!settingsForm) return;
    
    settingsForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(settingsForm);
        const data = {
            theme: formData.get('theme'),
            language: formData.get('language'),
            sound: formData.get('sound') ? 1 : 0
        };
        
        // غیرفعال کردن دکمه
        const submitBtn = settingsForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = APP_CONFIG.language === 'fa' ? 'در حال ذخیره...' : 'Saving...';
        
        try {
            const response = await fetch(`${APP_CONFIG.siteUrl}/api/update-profile.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast(result.message || (APP_CONFIG.language === 'fa' ? 'تنظیمات با موفقیت ذخیره شد' : 'Settings saved successfully'), 'success');
                
                // اعمال تغییرات
                if (data.theme !== APP_CONFIG.theme) {
                    document.body.classList.remove(`theme-${APP_CONFIG.theme}`);
                    document.body.classList.add(`theme-${data.theme}`);
                    APP_CONFIG.theme = data.theme;
                }
                
                if (data.language !== APP_CONFIG.language) {
                    // ریلود برای تغییر زبان
                    localStorage.setItem('language', data.language);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                    return;
                }
            } else {
                showToast(result.message || (APP_CONFIG.language === 'fa' ? 'خطا در ذخیره تنظیمات' : 'Error saving settings'), 'error');
            }
        } catch (error) {
            console.error('خطا در ذخیره:', error);
            showToast(APP_CONFIG.language === 'fa' ? 'خطا در ارتباط با سرور' : 'Server connection error', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });
}

// ====================================
// 3. انیمیشن صفحه
// ====================================
function animateProfileCards() {
    const cards = document.querySelectorAll('.card, .profile-card');
    
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

// اجرای انیمیشن
setTimeout(animateProfileCards, 100);

// ====================================
// 4. پیش‌نمایش تصویر قبل از آپلود
// ====================================
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const preview = document.getElementById('profileAvatar');
            if (preview) {
                preview.src = e.target.result;
                
                // انیمیشن تغییر تصویر
                preview.style.opacity = '0';
                setTimeout(() => {
                    preview.style.transition = 'opacity 0.3s ease';
                    preview.style.opacity = '1';
                }, 50);
            }
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

// ====================================
// 5. Drag & Drop برای آواتار
// ====================================
function initDragDropAvatar() {
    const avatarContainer = document.querySelector('.profile-avatar-container');
    const avatarInput = document.getElementById('avatarInput');
    
    if (!avatarContainer || !avatarInput) return;
    
    // جلوگیری از رفتار پیش‌فرض
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        avatarContainer.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    // هایلایت در هنگام drag
    ['dragenter', 'dragover'].forEach(eventName => {
        avatarContainer.addEventListener(eventName, () => {
            avatarContainer.style.borderColor = 'var(--primary)';
            avatarContainer.style.transform = 'scale(1.05)';
        });
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        avatarContainer.addEventListener(eventName, () => {
            avatarContainer.style.borderColor = '';
            avatarContainer.style.transform = '';
        });
    });
    
    // مدیریت drop
    avatarContainer.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (files.length > 0) {
            avatarInput.files = files;
            avatarInput.dispatchEvent(new Event('change'));
        }
    });
}

// فعال‌سازی drag & drop
initDragDropAvatar();

// ====================================
// 6. تایید قبل از خروج با تغییرات ذخیره نشده
// ====================================
let hasUnsavedChanges = false;

document.querySelectorAll('#settingsForm input, #settingsForm select').forEach(input => {
    input.addEventListener('change', () => {
        hasUnsavedChanges = true;
    });
});

document.getElementById('settingsForm')?.addEventListener('submit', () => {
    hasUnsavedChanges = false;
});

window.addEventListener('beforeunload', (e) => {
    if (hasUnsavedChanges) {
        e.preventDefault();
        e.returnValue = '';
    }
});

// ====================================
// 7. نمایش/پنهان کردن بخش‌های مختلف
// ====================================
function toggleSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.classList.toggle('collapsed');
    }
}

// ====================================
// 8. کپی لینک پروفایل
// ====================================
function copyProfileLink() {
    const profileUrl = `${window.location.origin}/profile.php?user=${APP_CONFIG.username}`;
    
    navigator.clipboard.writeText(profileUrl).then(() => {
        showToast(APP_CONFIG.language === 'fa' ? 'لینک پروفایل کپی شد' : 'Profile link copied', 'success');
    }).catch(err => {
        console.error('خطا در کپی:', err);
    });
}

// ====================================
// 9. Export
// ====================================
window.ProfileManager = {
    uploadAvatar,
    previewImage,
    toggleSection,
    copyProfileLink
};