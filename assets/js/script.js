// ==========================================
// CareerPath - Enhanced JavaScript Module
// ==========================================

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeMobileNavigation();
    initializeFormValidation();
    initializePasswordToggle();
    initializeUserDropdown();
    initializeTooltips();
    initializeAnimations();
    initializeNotifications();
});

// Mobile Navigation Toggle
function initializeMobileNavigation() {
    const mobileToggle = document.querySelector('.mobile-toggle');
    const navLinks = document.querySelector('.nav-links');
    
    if (mobileToggle) {
        mobileToggle.addEventListener('click', function() {
            navLinks.style.display = navLinks.style.display === 'flex' ? 'none' : 'flex';
            this.classList.toggle('active');
        });
        
        // Close menu when clicking on a link
        if (navLinks) {
            navLinks.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', function() {
                    navLinks.style.display = 'none';
                    mobileToggle.classList.remove('active');
                });
            });
        }
    }
}

// Enhanced Form Validation
function initializeFormValidation() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            const errors = [];
            
            requiredFields.forEach(field => {
                const value = field.value.trim();
                
                if (!value) {
                    isValid = false;
                    field.style.borderColor = '#ef4444';
                    field.classList.add('error');
                    errors.push(`${field.labels[0]?.textContent || field.name} is required`);
                } else {
                    field.style.borderColor = '#d1d5db';
                    field.classList.remove('error');
                    
                    // Email validation
                    if (field.type === 'email' && !isValidEmail(value)) {
                        isValid = false;
                        field.style.borderColor = '#ef4444';
                        field.classList.add('error');
                        errors.push('Please enter a valid email address');
                    }
                    
                    // Password validation
                    if (field.name === 'password' && value.length < 6) {
                        isValid = false;
                        field.style.borderColor = '#ef4444';
                        field.classList.add('error');
                        errors.push('Password must be at least 6 characters');
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showNotification('Please fix the errors in the form', 'error');
            }
        });
        
        // Real-time field validation
        form.querySelectorAll('input, select, textarea').forEach(field => {
            field.addEventListener('blur', function() {
                validateField(this);
            });
            
            field.addEventListener('focus', function() {
                this.classList.remove('error');
                this.style.borderColor = '#d1d5db';
            });
        });
    });
}

// Validate individual field
function validateField(field) {
    const value = field.value.trim();
    
    if (field.hasAttribute('required') && !value) {
        field.classList.add('error');
        field.style.borderColor = '#ef4444';
        return false;
    }
    
    if (field.type === 'email' && value && !isValidEmail(value)) {
        field.classList.add('error');
        field.style.borderColor = '#ef4444';
        return false;
    }
    
    field.classList.remove('error');
    field.style.borderColor = '#d1d5db';
    return true;
}

// Email validation helper
function isValidEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

// Password Visibility Toggle
function initializePasswordToggle() {
    const passwordToggles = document.querySelectorAll('.password-toggle');
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const passwordInput = this.previousElementSibling;
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
    });
}

// User Dropdown Menu
function initializeUserDropdown() {
    const userBtn = document.querySelector('.user-btn');
    const dropdownMenu = document.querySelector('.dropdown-menu');
    
    if (userBtn && dropdownMenu) {
        userBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.user-dropdown')) {
                dropdownMenu.classList.remove('show');
            }
        });
    }
}

// Initialize Tooltips
function initializeTooltips() {
    const tooltips = document.querySelectorAll('[title]');
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', function() {
            createTooltip(this);
        });
    });
}

// Create tooltip element
function createTooltip(element) {
    const title = element.getAttribute('title');
    if (!title || element.querySelector('.tooltip')) return;
    
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = title;
    tooltip.style.cssText = `
        position: absolute;
        background: rgba(0,0,0,0.8);
        color: white;
        padding: 0.5rem 0.75rem;
        border-radius: 0.4rem;
        font-size: 0.875rem;
        pointer-events: none;
        z-index: 1000;
        white-space: nowrap;
        bottom: calc(100% + 0.5rem);
        left: 50%;
        transform: translateX(-50%);
    `;
    element.style.position = 'relative';
    element.appendChild(tooltip);
    
    setTimeout(() => tooltip.remove(), 2000);
}

// Initialize Animations on Scroll
function initializeAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.feature-card, .role-card, .dashboard-card').forEach(el => {
        observer.observe(el);
    });
}

// Notification System
function initializeNotifications() {
    // Auto-close alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            closeAlert(alert);
        }, 5000);
    });
    
    // Add close button to alerts
    alerts.forEach(alert => {
        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '<i class="fas fa-times"></i>';
        closeBtn.className = 'alert-close';
        closeBtn.style.cssText = `
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: none;
            border: none;
            cursor: pointer;
            color: inherit;
            font-size: 1.25rem;
        `;
        closeBtn.addEventListener('click', function() {
            closeAlert(alert);
        });
        alert.style.position = 'relative';
        alert.appendChild(closeBtn);
    });
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'error' ? 'exclamation-circle' : type === 'success' ? 'check-circle' : 'info-circle'}"></i>
        <span>${message}</span>
    `;
    notification.style.cssText = `
        position: fixed;
        top: 2rem;
        right: 2rem;
        z-index: 2000;
        animation: slideInDown 0.4s ease-out;
        max-width: 400px;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => closeAlert(notification), 4000);
}

// Close alert
function closeAlert(alert) {
    alert.style.animation = 'slideOutUp 0.3s ease-out';
    setTimeout(() => alert.remove(), 300);
}

// ==========================================
// Utility Functions
// ==========================================

// Smooth scroll to element
function smoothScroll(target) {
    if (typeof target === 'string') {
        target = document.querySelector(target);
    }
    if (target) {
        target.scrollIntoView({ behavior: 'smooth' });
    }
}

// Debounce function
function debounce(func, delay) {
    let timeoutId;
    return function(...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func(...args), delay);
    };
}

// Throttle function  
function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}
            const dropdown = this.nextElementSibling;
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.user-dropdown')) {
                const dropdown = document.querySelector('.dropdown-menu');
                if (dropdown) {
                    dropdown.style.display = 'none';
                }
            }
        });
    }
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 100,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Alert auto-dismiss
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 300);
        }, 5000);
    });
});

// ==========================================
// Resume Builder Functionality
// ==========================================

function initializeResumeBuilder() {
    const resumeForm = document.getElementById('resumeForm');
    
    if (resumeForm) {
        resumeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading state
            const submitBtn = resumeForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            submitBtn.disabled = true;
            
            // Collect form data
            const formData = new FormData(resumeForm);
            
            // Send via AJAX
            fetch('profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Resume saved successfully!', 'success');
                    // Update button to show download option
                    location.reload();
                } else {
                    showNotification(data.message || 'Error saving resume', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred while saving', 'error');
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
    
    // Template selection preview
    const templateRadios = document.querySelectorAll('input[name="template"]');
    templateRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            console.log('Selected template:', this.value);
        });
    });
}

// Show notification message
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = type === 'success' ? 'success-message' : 'error-message';
    
    const icon = type === 'success' 
        ? '<i class="fas fa-check-circle"></i>' 
        : '<i class="fas fa-exclamation-circle"></i>';
    
    notification.innerHTML = `${icon} <span>${message}</span>`;
    
    // Add to the beginning of resume form
    const resumeForm = document.getElementById('resumeForm');
    if (resumeForm) {
        resumeForm.insertBefore(notification, resumeForm.firstChild);
        
        // Remove after 4 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transition = 'opacity 0.3s ease';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 4000);
    }
}

// Initialize resume builder when profile page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeResumeBuilder();
});
