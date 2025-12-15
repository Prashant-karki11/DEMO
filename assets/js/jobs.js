// Job filtering and modal functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchJobs');
    const jobTypeFilter = document.getElementById('jobType');
    const experienceFilter = document.getElementById('experienceLevel');
    const jobCards = document.querySelectorAll('.job-card');
    const viewButtons = document.querySelectorAll('.view-details');
    const applyButtons = document.querySelectorAll('.apply-now');
    const modals = document.querySelectorAll('.modal');
    const closeButtons = document.querySelectorAll('.close-modal');
    
    // Filter jobs
    function filterJobs() {
        const searchTerm = searchInput.value.toLowerCase();
        const jobType = jobTypeFilter.value;
        const experienceLevel = experienceFilter.value;
        
        jobCards.forEach(card => {
            const title = card.querySelector('h3').textContent.toLowerCase();
            const company = card.querySelector('.company-name').textContent.toLowerCase();
            const type = card.dataset.type;
            const level = card.dataset.level;
            
            const matchesSearch = title.includes(searchTerm) || company.includes(searchTerm);
            const matchesType = !jobType || type === jobType;
            const matchesLevel = !experienceLevel || level === experienceLevel;
            
            if (matchesSearch && matchesType && matchesLevel) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
    
    if (searchInput) searchInput.addEventListener('input', filterJobs);
    if (jobTypeFilter) jobTypeFilter.addEventListener('change', filterJobs);
    if (experienceFilter) experienceFilter.addEventListener('change', filterJobs);
    
    // View job details
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const jobId = this.dataset.job;
            // In a real application, you would fetch job details via AJAX
            const jobModal = document.getElementById('jobModal');
            const content = document.getElementById('jobDetailsContent');
            
            // Mock content - replace with actual AJAX call
            content.innerHTML = `
                <h3>Senior PHP Developer</h3>
                <p><strong>Company:</strong> Tech Solutions Inc.</p>
                <p><strong>Location:</strong> Kathmandu, Remote</p>
                <p><strong>Type:</strong> Full Time</p>
                <p><strong>Experience:</strong> Senior Level</p>
                <p><strong>Salary:</strong> $50,000 - $70,000</p>
                
                <div class="job-description">
                    <h4>Job Description</h4>
                    <p>We are looking for an experienced PHP Developer to join our team...</p>
                    
                    <h4>Requirements</h4>
                    <ul>
                        <li>3+ years of PHP experience</li>
                        <li>Strong knowledge of MySQL</li>
                        <li>Experience with Laravel framework</li>
                        <li>Understanding of REST APIs</li>
                    </ul>
                    
                    <h4>Skills Required</h4>
                    <div class="skill-tags">
                        <span class="skill-tag">PHP</span>
                        <span class="skill-tag">MySQL</span>
                        <span class="skill-tag">Laravel</span>
                        <span class="skill-tag">JavaScript</span>
                        <span class="skill-tag">REST API</span>
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button class="btn btn-secondary close-modal">Close</button>
                    <button class="btn btn-primary apply-from-modal" data-job="${jobId}">Apply Now</button>
                </div>
            `;
            
            jobModal.classList.add('active');
        });
    });
    
    // Apply for job
    applyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const jobId = this.dataset.job;
            document.getElementById('applyJobId').value = jobId;
            document.getElementById('applyModal').classList.add('active');
        });
    });
    
    // Close modals
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            modal.classList.remove('active');
        });
    });
    
    // Close modal when clicking outside
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
            }
        });
    });
    
    // Apply from modal
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('apply-from-modal')) {
            const jobModal = document.getElementById('jobModal');
            const applyModal = document.getElementById('applyModal');
            
            jobModal.classList.remove('active');
            applyModal.classList.add('active');
        }
    });
});