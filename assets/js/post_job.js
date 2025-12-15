// Post Job Wizard Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Wizard Navigation
    const formSteps = document.querySelectorAll('.form-step');
    const wizardSteps = document.querySelectorAll('.wizard-steps .step');
    const progressSteps = document.querySelectorAll('.progress-step');
    const progressRing = document.getElementById('progress-ring');
    const progressPercent = document.getElementById('progressPercent');
    const nextButtons = document.querySelectorAll('.next-step');
    const prevButtons = document.querySelectorAll('.prev-step');
    const aiAnalyzeBtn = document.getElementById('aiAnalyze');
    const aiModal = document.getElementById('aiModal');
    const aiScoreRing = document.getElementById('ai-score-ring');
    const aiScore = document.getElementById('aiScore');
    
    // Initialize progress
    updateProgress(1);
    
    // Next Step
    nextButtons.forEach(button => {
        button.addEventListener('click', function() {
            const currentStep = document.querySelector('.form-step.active');
            const nextStepNum = parseInt(this.dataset.next);
            
            // Validate current step before proceeding
            if (validateStep(currentStep.dataset.step)) {
                goToStep(nextStepNum);
            }
        });
    });
    
    // Previous Step
    prevButtons.forEach(button => {
        button.addEventListener('click', function() {
            const prevStepNum = parseInt(this.dataset.prev);
            goToStep(prevStepNum);
        });
    });
    
    function goToStep(stepNum) {
        // Update form steps
        formSteps.forEach(step => {
            step.classList.remove('active');
            if (parseInt(step.dataset.step) === stepNum) {
                step.classList.add('active');
            }
        });
        
        // Update wizard steps
        wizardSteps.forEach(step => {
            step.classList.remove('active');
            if (parseInt(step.querySelector('.step-number').textContent) === stepNum) {
                step.classList.add('active');
            }
        });
        
        // Update progress steps
        progressSteps.forEach(step => {
            step.classList.remove('active');
            if (step.textContent.includes(getStepName(stepNum))) {
                step.classList.add('active');
            }
        });
        
        // Update progress
        updateProgress(stepNum);
        
        // Update live preview
        if (stepNum === 3) {
            updateLivePreview();
        }
    }
    
    function getStepName(stepNum) {
        switch(stepNum) {
            case 1: return 'Job Details';
            case 2: return 'Requirements';
            case 3: return 'Preview';
            default: return '';
        }
    }
    
    function updateProgress(currentStep) {
        const progress = ((currentStep - 1) / 2) * 100;
        const circumference = 2 * Math.PI * 54;
        const offset = circumference - (progress / 100) * circumference;
        
        progressRing.style.strokeDashoffset = offset;
        progressPercent.textContent = Math.round(progress) + '%';
    }
    
    function validateStep(stepNum) {
        const step = document.querySelector(`.form-step[data-step="${stepNum}"]`);
        const requiredInputs = step.querySelectorAll('[required]');
        let isValid = true;
        
        requiredInputs.forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                input.classList.add('error');
                
                // Add error message
                if (!input.nextElementSibling || !input.nextElementSibling.classList.contains('error-message')) {
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'error-message';
                    errorMsg.textContent = 'This field is required';
                    errorMsg.style.color = '#ef4444';
                    errorMsg.style.fontSize = '0.875rem';
                    errorMsg.style.marginTop = '0.25rem';
                    input.parentNode.insertBefore(errorMsg, input.nextSibling);
                }
            } else {
                input.classList.remove('error');
                const errorMsg = input.nextElementSibling;
                if (errorMsg && errorMsg.classList.contains('error-message')) {
                    errorMsg.remove();
                }
            }
        });
        
        if (!isValid) {
            // Scroll to first error
            const firstError = step.querySelector('.error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
            
            // Show error alert
            showAlert('Please fill in all required fields before proceeding.', 'error');
        }
        
        return isValid;
    }
    
    // Live Preview Update
    function updateLivePreview() {
        const title = document.getElementById('title').value || 'Job Title';
        const company = document.querySelector('.company-display span').textContent;
        const location = document.getElementById('location').value || 'Location';
        const jobType = document.getElementById('job_type').value || 'Job Type';
        const experienceLevel = document.getElementById('experience_level').value || 'Experience Level';
        const salaryRange = document.getElementById('salary_range').value || 'Not specified';
        const deadline = document.getElementById('deadline').value || 'Not set';
        const description = document.getElementById('description').value || 'Job description will appear here...';
        const requirements = document.getElementById('requirements').value || 'Requirements will appear here...';
        const skills = document.getElementById('skills_required').value || 'No specific skills listed';
        
        const preview = document.getElementById('livePreview');
        preview.innerHTML = `
            <div class="preview-job-title">${title}</div>
            <div class="preview-company">
                <i class="fas fa-building"></i> ${company}
            </div>
            <div class="preview-tags">
                <span class="tag">${location}</span>
                <span class="tag">${formatJobType(jobType)}</span>
                <span class="tag">${formatExperienceLevel(experienceLevel)}</span>
            </div>
            <div class="preview-description">
                <p>${truncateText(description, 200)}</p>
            </div>
            <div class="preview-details">
                <div class="detail-item">
                    <span>Salary:</span>
                    <strong>${salaryRange}</strong>
                </div>
                <div class="detail-item">
                    <span>Deadline:</span>
                    <strong>${formatDate(deadline)}</strong>
                </div>
                <div class="detail-item">
                    <span>Requirements:</span>
                    <span>${truncateText(requirements, 50)}</span>
                </div>
                <div class="detail-item">
                    <span>Skills:</span>
                    <span>${truncateText(skills, 50)}</span>
                </div>
            </div>
        `;
    }
    
    function formatJobType(type) {
        const types = {
            'full_time': 'Full Time',
            'part_time': 'Part Time',
            'contract': 'Contract',
            'internship': 'Internship',
            'temporary': 'Temporary'
        };
        return types[type] || type;
    }
    
    function formatExperienceLevel(level) {
        const levels = {
            'entry': 'Entry Level',
            'mid': 'Mid Level',
            'senior': 'Senior Level',
            'executive': 'Executive'
        };
        return levels[level] || level;
    }
    
    function formatDate(dateString) {
        if (!dateString || dateString === 'Not set') return 'Not set';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
    }
    
    function truncateText(text, maxLength) {
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength) + '...';
    }
    
    // Real-time preview updates
    const previewInputs = [
        'title', 'location', 'job_type', 'experience_level', 
        'salary_range', 'deadline', 'description', 'requirements', 'skills_required'
    ];
    
    previewInputs.forEach(inputId => {
        const input = document.getElementById(inputId);
        if (input) {
            input.addEventListener('input', updateLivePreview);
            input.addEventListener('change', updateLivePreview);
        }
    });
    
    // AI Analysis
    if (aiAnalyzeBtn) {
        aiAnalyzeBtn.addEventListener('click', function() {
            analyzeWithAI();
        });
    }
    
    function analyzeWithAI() {
        // Show loading
        aiAnalyzeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Analyzing...';
        aiAnalyzeBtn.disabled = true;
        
        // Simulate AI analysis
        setTimeout(() => {
            // Calculate AI score based on form completeness
            let score = 50;
            
            const title = document.getElementById('title').value;
            const description = document.getElementById('description').value;
            const requirements = document.getElementById('requirements').value;
            const skills = document.getElementById('skills_required').value;
            const salary = document.getElementById('salary_range').value;
            
            if (title && title.length > 10) score += 10;
            if (description && description.length > 100) score += 15;
            if (requirements && requirements.length > 50) score += 10;
            if (skills && skills.split(',').length >= 3) score += 10;
            if (salary) score += 15;
            
            // Update AI score display
            const circumference = 2 * Math.PI * 45;
            const offset = circumference - (score / 100) * circumference;
            aiScoreRing.style.strokeDashoffset = offset;
            aiScore.textContent = score;
            
            // Show modal
            aiModal.classList.add('active');
            
            // Reset button
            aiAnalyzeBtn.innerHTML = '<i class="fas fa-magic"></i> Analyze with AI';
            aiAnalyzeBtn.disabled = false;
        }, 1500);
    }
    
    // Close AI modal
    const closeModal = aiModal.querySelector('.close-modal');
    if (closeModal) {
        closeModal.addEventListener('click', () => {
            aiModal.classList.remove('active');
        });
    }
    
    aiModal.addEventListener('click', (e) => {
        if (e.target === aiModal) {
            aiModal.classList.remove('active');
        }
    });
    
    // Form submission
    const jobForm = document.getElementById('jobForm');
    if (jobForm) {
        jobForm.addEventListener('submit', function(e) {
            if (!validateStep(3)) {
                e.preventDefault();
                showAlert('Please complete all required fields before submitting.', 'error');
                return;
            }
            
            // Show loading
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Posting...';
            submitBtn.disabled = true;
            
            // Form will submit normally
        });
    }
    
    // Helper functions
    function showAlert(message, type = 'info') {
        // Remove existing alerts
        const existingAlert = document.querySelector('.flash-alert');
        if (existingAlert) existingAlert.remove();
        
        // Create alert
        const alert = document.createElement('div');
        alert.className = `flash-alert alert-${type}`;
        alert.innerHTML = `
            <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
            <button class="close-alert">&times;</button>
        `;
        
        // Style alert
        alert.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            background: ${type === 'error' ? '#fee2e2' : '#dbeafe'};
            color: ${type === 'error' ? '#991b1b' : '#1e40af'};
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 10000;
            animation: slideIn 0.3s ease-out;
            max-width: 400px;
        `;
        
        document.body.appendChild(alert);
        
        // Close button
        alert.querySelector('.close-alert').addEventListener('click', () => {
            alert.remove();
        });
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => alert.remove(), 300);
            }
        }, 5000);
    }
    
    // Add CSS animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes slideOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(100%);
            }
        }
        
        input.error {
            border-color: #ef4444 !important;
        }
        
        .error-message {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        .tag {
            display: inline-block;
            background: var(--gray-100);
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            color: var(--gray-700);
        }
    `;
    document.head.appendChild(style);
});