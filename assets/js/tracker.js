// Application Tracker functionality
document.addEventListener('DOMContentLoaded', function() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const applicationRows = document.querySelectorAll('.application-row');
    const viewButtons = document.querySelectorAll('.view-application');
    const deleteButtons = document.querySelectorAll('.delete-application');
    const applicationModal = document.getElementById('applicationModal');
    const applicationDetails = document.getElementById('applicationDetails');
    
    // Filter applications
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const filter = this.dataset.filter;
            
            // Update active button
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Filter rows
            applicationRows.forEach(row => {
                if (filter === 'all' || row.dataset.status === filter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
    
    // View application details
    viewButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const appId = this.dataset.id;
            
            // In a real app, fetch application details via AJAX
            // For demo, we'll show mock data
            fetchApplicationDetails(appId);
        });
    });
    
    // Delete application
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const appId = this.dataset.id;
            
            if (confirm('Are you sure you want to withdraw this application?')) {
                // In a real app, make AJAX request to delete
                const row = this.closest('tr');
                row.style.opacity = '0.5';
                row.style.pointerEvents = 'none';
                
                // Simulate API call
                setTimeout(() => {
                    row.remove();
                    showNotification('Application withdrawn successfully!');
                    
                    // Update stats if needed
                    updateApplicationStats();
                }, 500);
            }
        });
    });
    
    function fetchApplicationDetails(appId) {
        // Mock data - replace with actual AJAX call
        const mockData = {
            title: 'Senior PHP Developer',
            company: 'Tech Solutions Inc.',
            appliedDate: '2024-01-15',
            status: 'reviewed',
            coverLetter: 'I am writing to express my interest in the Senior PHP Developer position...',
            skills: 'PHP, MySQL, JavaScript, Laravel, REST API',
            experience: '5 years of experience in web development...'
        };
        
        applicationDetails.innerHTML = `
            <div class="application-detail">
                <h3>${mockData.title}</h3>
                <p><strong>Company:</strong> ${mockData.company}</p>
                <p><strong>Applied Date:</strong> ${mockData.appliedDate}</p>
                <p><strong>Status:</strong> <span class="status-badge status-${mockData.status}">${mockData.status}</span></p>
                
                <div class="detail-section">
                    <h4>Cover Letter</h4>
                    <p>${mockData.coverLetter}</p>
                </div>
                
                <div class="detail-section">
                    <h4>Skills Mentioned</h4>
                    <div class="skill-tags">
                        ${mockData.skills.split(', ').map(skill => 
                            `<span class="skill-tag">${skill}</span>`
                        ).join('')}
                    </div>
                </div>
                
                <div class="detail-section">
                    <h4>Experience Summary</h4>
                    <p>${mockData.experience}</p>
                </div>
                
                <div class="modal-actions">
                    <button class="btn btn-secondary close-modal">Close</button>
                    <button class="btn btn-primary">Message Recruiter</button>
                </div>
            </div>
        `;
        
        applicationModal.classList.add('active');
        
        // Re-attach event listeners for buttons inside modal
        setTimeout(() => {
            const closeBtn = applicationModal.querySelector('.close-modal');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    applicationModal.classList.remove('active');
                });
            }
        }, 100);
    }
    
    function updateApplicationStats() {
        const rows = document.querySelectorAll('.application-row:not([style*="display: none"])');
        const stats = {
            total: rows.length,
            pending: 0,
            reviewed: 0,
            shortlisted: 0,
            rejected: 0,
            hired: 0
        };
        
        rows.forEach(row => {
            const status = row.dataset.status;
            if (stats[status] !== undefined) {
                stats[status]++;
            }
        });
        
        // Update filter buttons
        filterBtns.forEach(btn => {
            const filter = btn.dataset.filter;
            if (filter !== 'all') {
                const count = stats[filter];
                btn.textContent = `${filter.charAt(0).toUpperCase() + filter.slice(1)} (${count})`;
            }
        });
    }
    
    function showNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'notification';
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--success);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 10000;
            animation: slideIn 0.3s ease-out;
        `;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }
});