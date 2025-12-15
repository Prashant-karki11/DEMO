// AI Guidance functionality
document.addEventListener('DOMContentLoaded', function() {
    const chatWithAI = document.getElementById('chatWithAI');
    const closeChat = document.querySelector('.close-chat');
    const chatSection = document.querySelector('.ai-chat-section');
    const chatInput = document.getElementById('chatInput');
    const sendMessage = document.getElementById('sendMessage');
    const chatMessages = document.getElementById('chatMessages');
    const refreshBtn = document.getElementById('refreshRecommendations');
    const generateReportBtn = document.getElementById('generateReport');
    const reportModal = document.getElementById('reportModal');
    const generateReportBtns = document.querySelectorAll('.generate-report');
    const saveRecBtns = document.querySelectorAll('.save-rec');
    
    // Toggle chat section
    if (chatWithAI) {
        chatWithAI.addEventListener('click', function() {
            chatSection.style.display = 'block';
            this.style.display = 'none';
        });
    }
    
    if (closeChat) {
        closeChat.addEventListener('click', function() {
            chatSection.style.display = 'none';
            if (chatWithAI) chatWithAI.style.display = 'block';
        });
    }
    
    // Chat functionality
    if (sendMessage) {
        sendMessage.addEventListener('click', sendChatMessage);
    }
    
    if (chatInput) {
        chatInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendChatMessage();
            }
        });
    }
    
    function sendChatMessage() {
        const message = chatInput.value.trim();
        if (!message) return;
        
        // Add user message
        addMessage(message, 'user');
        chatInput.value = '';
        
        // Simulate AI response (replace with actual API call in production)
        setTimeout(() => {
            const responses = [
                "That's a great question! Based on your profile, I'd recommend focusing on improving your PHP skills first.",
                "I suggest looking into online courses on platforms like Coursera or Udemy to build those skills.",
                "The job market for PHP developers is growing, with many remote opportunities available.",
                "Consider contributing to open-source projects to build your portfolio and gain practical experience.",
                "Networking is key! Join developer communities and attend tech meetups to connect with professionals."
            ];
            const randomResponse = responses[Math.floor(Math.random() * responses.length)];
            addMessage(randomResponse, 'ai');
        }, 1000);
    }
    
    function addMessage(text, sender) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}-message`;
        
        const avatar = sender === 'user' ? 
            '<i class="fas fa-user"></i>' : 
            '<i class="fas fa-robot"></i>';
        
        messageDiv.innerHTML = `
            <div class="message-avatar">
                ${avatar}
            </div>
            <div class="message-content">
                <p>${text}</p>
            </div>
        `;
        
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // Refresh recommendations
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
            this.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh Recommendations';
                this.disabled = false;
                
                // Show success message
                const alert = document.createElement('div');
                alert.className = 'alert alert-success';
                alert.textContent = 'Recommendations refreshed successfully!';
                alert.style.marginBottom = '1rem';
                
                const container = document.querySelector('.ai-content');
                container.insertBefore(alert, container.firstChild);
                
                // Auto-remove alert
                setTimeout(() => {
                    alert.remove();
                }, 3000);
            }, 1500);
        });
    }
    
    // Generate report modal
    if (generateReportBtn) {
        generateReportBtn.addEventListener('click', function() {
            reportModal.classList.add('active');
        });
    }
    
    // Generate report
    generateReportBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const type = this.dataset.type;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
            this.disabled = true;
            
            // Simulate report generation
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-file-pdf"></i> Generate PDF';
                this.disabled = false;
                
                // Show download link (in real app, this would be an actual file download)
                const alert = document.createElement('div');
                alert.className = 'alert alert-success';
                alert.innerHTML = `
                    <i class="fas fa-check-circle"></i>
                    Report generated successfully! 
                    <a href="#" onclick="downloadReport('${type}')" style="color: inherit; text-decoration: underline;">
                        Click here to download
                    </a>
                `;
                
                reportModal.querySelector('.modal-body').appendChild(alert);
                
                // Auto-remove alert after 5 seconds
                setTimeout(() => {
                    alert.remove();
                }, 5000);
            }, 2000);
        });
    });
    
    // Save recommendations
    saveRecBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const recId = this.dataset.rec;
            const icon = this.querySelector('i');
            
            if (icon.classList.contains('far')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                this.innerHTML = '<i class="fas fa-bookmark"></i> Saved';
                this.style.backgroundColor = '#10b981';
                this.style.color = 'white';
                
                // Show notification
                showNotification('Recommendation saved to your bookmarks!');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                this.innerHTML = '<i class="far fa-bookmark"></i> Save';
                this.style.backgroundColor = '';
                this.style.color = '';
                
                showNotification('Recommendation removed from bookmarks.');
            }
        });
    });
    
    // Close modal when clicking outside
    if (reportModal) {
        reportModal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
            }
        });
        
        // Close modal with close button
        const closeModal = reportModal.querySelector('.close-modal');
        if (closeModal) {
            closeModal.addEventListener('click', function() {
                reportModal.classList.remove('active');
            });
        }
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
    `;
    document.head.appendChild(style);
});