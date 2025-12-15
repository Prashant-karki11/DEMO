// Mentor Dashboard functionality
document.addEventListener('DOMContentLoaded', function() {
    // Session actions
    const sessionButtons = document.querySelectorAll('.session-actions .btn-icon');
    sessionButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.querySelector('i').className;
            
            if(action.includes('video')) {
                // Start video session
                window.open('/video_session.html', '_blank');
            } else if(action.includes('calendar')) {
                // Reschedule session
                openRescheduleModal();
            } else if(action.includes('comment')) {
                // Open chat
                window.location.href = 'messages.php';
            }
        });
    });
    
    // Quick action buttons
    const actionBtns = document.querySelectorAll('.action-btn');
    actionBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const action = this.querySelector('span').textContent.toLowerCase();
            
            switch(action) {
                case 'find new mentees':
                    window.location.href = 'mentees.php';
                    break;
                case 'set availability':
                    openCalendarModal();
                    break;
                case 'share resources':
                    openResourcesModal();
                    break;
                case 'join discussions':
                    window.location.href = '../forum.php';
                    break;
                case 'ai tools':
                    window.location.href = 'ai_tools.php';
                    break;
                case 'progress reports':
                    window.location.href = 'reports.php';
                    break;
            }
        });
    });
    
    // Activity actions
    const activityActions = document.querySelectorAll('.activity-actions button');
    activityActions.forEach(btn => {
        btn.addEventListener('click', function() {
            const text = this.textContent.toLowerCase();
            
            if(text.includes('accept')) {
                // Accept mentorship request
                const activityItem = this.closest('.activity-item');
                const activityContent = activityItem.querySelector('.activity-content p').textContent;
                const menteeName = activityContent.match(/from (.+)/)[1];
                
                if(confirm(`Accept mentorship request from ${menteeName}?`)) {
                    activityItem.style.opacity = '0.5';
                    setTimeout(() => {
                        activityItem.remove();
                        showNotification('Mentorship request accepted!');
                    }, 500);
                }
            } else if(text.includes('join')) {
                // Join video session
                window.open('/video_session.html', '_blank');
            }
        });
    });
    
    function openRescheduleModal() {
        // Create reschedule modal
        const modal = document.createElement('div');
        modal.className = 'modal active';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Reschedule Session</h2>
                    <button class="close-modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="newDate">New Date</label>
                        <input type="date" id="newDate" min="${new Date().toISOString().split('T')[0]}">
                    </div>
                    <div class="form-group">
                        <label for="newTime">New Time</label>
                        <input type="time" id="newTime">
                    </div>
                    <div class="modal-actions">
                        <button class="btn btn-secondary close-modal">Cancel</button>
                        <button class="btn btn-primary" id="confirmReschedule">Reschedule</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Add event listeners
        modal.querySelector('.close-modal').addEventListener('click', () => modal.remove());
        modal.querySelector('#confirmReschedule').addEventListener('click', () => {
            const date = modal.querySelector('#newDate').value;
            const time = modal.querySelector('#newTime').value;
            
            if(date && time) {
                showNotification('Session rescheduled successfully!');
                modal.remove();
            } else {
                alert('Please select both date and time');
            }
        });
        
        // Close on background click
        modal.addEventListener('click', (e) => {
            if(e.target === modal) modal.remove();
        });
    }
    
    function openCalendarModal() {
        // Create calendar modal
        const modal = document.createElement('div');
        modal.className = 'modal active';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Set Your Availability</h2>
                    <button class="close-modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="availability-calendar">
                        <h3>Select Available Time Slots</h3>
                        <div class="week-days">
                            <div class="day">Mon</div>
                            <div class="day">Tue</div>
                            <div class="day">Wed</div>
                            <div class="day">Thu</div>
                            <div class="day">Fri</div>
                            <div class="day">Sat</div>
                            <div class="day">Sun</div>
                        </div>
                        <div class="time-slots">
                            <div class="time-slot">
                                <span>9:00 AM - 10:00 AM</span>
                                <div class="day-slots">
                                    <input type="checkbox" id="mon-9">
                                    <input type="checkbox" id="tue-9">
                                    <input type="checkbox" id="wed-9">
                                    <input type="checkbox" id="thu-9">
                                    <input type="checkbox" id="fri-9">
                                    <input type="checkbox" id="sat-9">
                                    <input type="checkbox" id="sun-9">
                                </div>
                            </div>
                            <!-- Add more time slots as needed -->
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button class="btn btn-secondary close-modal">Cancel</button>
                        <button class="btn btn-primary" id="saveAvailability">Save Availability</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Add event listeners
        modal.querySelector('.close-modal').addEventListener('click', () => modal.remove());
        modal.querySelector('#saveAvailability').addEventListener('click', () => {
            showNotification('Availability saved successfully!');
            modal.remove();
        });
        
        // Close on background click
        modal.addEventListener('click', (e) => {
            if(e.target === modal) modal.remove();
        });
    }
    
    function openResourcesModal() {
        // Create resources modal
        const modal = document.createElement('div');
        modal.className = 'modal active';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Share Resources</h2>
                    <button class="close-modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="resourceTitle">Resource Title</label>
                        <input type="text" id="resourceTitle" placeholder="e.g., Resume Template 2024">
                    </div>
                    <div class="form-group">
                        <label for="resourceType">Resource Type</label>
                        <select id="resourceType">
                            <option value="">Select type</option>
                            <option value="template">Template</option>
                            <option value="article">Article</option>
                            <option value="video">Video</option>
                            <option value="course">Course</option>
                            <option value="tool">Tool</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="resourceLink">Link</label>
                        <input type="url" id="resourceLink" placeholder="https://example.com/resource">
                    </div>
                    <div class="form-group">
                        <label for="resourceDescription">Description</label>
                        <textarea id="resourceDescription" rows="3" placeholder="Describe this resource..."></textarea>
                    </div>
                    <div class="form-group">
                        <label for="shareWith">Share With</label>
                        <select id="shareWith" multiple>
                            <option value="all">All Mentees</option>
                            <!-- Mentee options would be loaded dynamically -->
                        </select>
                    </div>
                    <div class="modal-actions">
                        <button class="btn btn-secondary close-modal">Cancel</button>
                        <button class="btn btn-primary" id="shareResource">Share Resource</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Add event listeners
        modal.querySelector('.close-modal').addEventListener('click', () => modal.remove());
        modal.querySelector('#shareResource').addEventListener('click', () => {
            const title = modal.querySelector('#resourceTitle').value;
            if(title) {
                showNotification('Resource shared successfully!');
                modal.remove();
            } else {
                alert('Please enter a resource title');
            }
        });
        
        // Close on background click
        modal.addEventListener('click', (e) => {
            if(e.target === modal) modal.remove();
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