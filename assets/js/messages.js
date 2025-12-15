// Messaging System functionality
document.addEventListener('DOMContentLoaded', function() {
    const newMessageBtn = document.getElementById('newMessageBtn');
    const startNewChatBtn = document.getElementById('startNewChat');
    const newMessageModal = document.getElementById('newMessageModal');
    const searchUsersInput = document.getElementById('searchUsersInput');
    const usersList = document.getElementById('usersList');
    const chatMessages = document.getElementById('chatMessages');
    
    // Open new message modal
    if(newMessageBtn) {
        newMessageBtn.addEventListener('click', function() {
            newMessageModal.classList.add('active');
            loadUsersList();
        });
    }
    
    if(startNewChatBtn) {
        startNewChatBtn.addEventListener('click', function() {
            newMessageModal.classList.add('active');
            loadUsersList();
        });
    }
    
    // Load users for new message
    function loadUsersList(search = '') {
        // Mock data - replace with AJAX call
        const users = [
            { id: 1, name: 'John Doe', email: 'john@example.com', type: 'job_seeker' },
            { id: 2, name: 'Jane Smith', email: 'jane@example.com', type: 'recruiter' },
            { id: 3, name: 'Mike Johnson', email: 'mike@example.com', type: 'mentor' },
            { id: 4, name: 'Sarah Williams', email: 'sarah@example.com', type: 'job_seeker' },
            { id: 5, name: 'David Brown', email: 'david@example.com', type: 'recruiter' }
        ];
        
        // Filter users based on search
        const filteredUsers = users.filter(user => 
            user.name.toLowerCase().includes(search.toLowerCase()) ||
            user.email.toLowerCase().includes(search.toLowerCase())
        );
        
        // Update users list
        usersList.innerHTML = filteredUsers.map(user => `
            <div class="user-item" data-user="${user.id}">
                <div class="user-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="user-info">
                    <h4>${user.name}</h4>
                    <p>${user.email}</p>
                    <span class="user-type">${user.type.replace('_', ' ')}</span>
                </div>
                <button class="btn btn-outline btn-sm start-chat" data-user="${user.id}">
                    Message
                </button>
            </div>
        `).join('');
        
        // Add event listeners to start chat buttons
        document.querySelectorAll('.start-chat').forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = this.dataset.user;
                window.location.href = `messages.php?user=${userId}`;
            });
        });
    }
    
    // Search users
    if(searchUsersInput) {
        searchUsersInput.addEventListener('input', function() {
            loadUsersList(this.value);
        });
        
        // Load initial users
        loadUsersList();
    }
    
    // Scroll to bottom of chat
    if(chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // Close modals
    const closeButtons = document.querySelectorAll('.close-modal');
    closeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.modal');
            modal.classList.remove('active');
        });
    });
    
    // Close modal when clicking outside
    if(newMessageModal) {
        newMessageModal.addEventListener('click', function(e) {
            if(e.target === this) {
                this.classList.remove('active');
            }
        });
    }
    
    // Message form submission
    const messageForm = document.querySelector('.message-form');
    if(messageForm) {
        messageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const messageInput = this.querySelector('input[name="message"]');
            const message = messageInput.value.trim();
            
            if(message) {
                // Add message to chat (in production, this would be via AJAX)
                const messageItem = document.createElement('div');
                messageItem.className = 'message-item sent';
                messageItem.innerHTML = `
                    <div class="message-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="message-bubble">
                        <p>${message}</p>
                        <span class="message-time">
                            ${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                            <i class="fas fa-check-double read-icon"></i>
                        </span>
                    </div>
                `;
                
                chatMessages.appendChild(messageItem);
                messageInput.value = '';
                
                // Scroll to bottom
                chatMessages.scrollTop = chatMessages.scrollHeight;
                
                // Simulate reply (in production, this would be via WebSocket)
                setTimeout(() => {
                    const replyItem = document.createElement('div');
                    replyItem.className = 'message-item received';
                    replyItem.innerHTML = `
                        <div class="message-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="message-bubble">
                            <p>Thanks for your message! I'll get back to you soon.</p>
                            <span class="message-time">
                                ${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                            </span>
                        </div>
                    `;
                    
                    chatMessages.appendChild(replyItem);
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }, 1000);
            }
        });
    }
    
    // Input actions
    const inputActions = document.querySelectorAll('.input-actions .btn-icon');
    inputActions.forEach(btn => {
        btn.addEventListener('click', function() {
            const icon = this.querySelector('i').className;
            
            if(icon.includes('paperclip')) {
                // Attach file
                const input = document.createElement('input');
                input.type = 'file';
                input.click();
                
                input.addEventListener('change', function() {
                    if(this.files.length > 0) {
                        alert(`File "${this.files[0].name}" selected. In production, this would upload the file.`);
                    }
                });
            } else if(icon.includes('smile')) {
                // Emoji picker
                alert('Emoji picker would open here');
            } else if(icon.includes('calendar')) {
                // Schedule message
                alert('Schedule message feature');
            }
        });
    });
    
    // Chat actions
    const chatActions = document.querySelectorAll('.chat-actions .btn-icon');
    chatActions.forEach(btn => {
        btn.addEventListener('click', function() {
            const icon = this.querySelector('i').className;
            
            if(icon.includes('video')) {
                // Start video call
                window.open('/video_call.html', '_blank');
            } else if(icon.includes('user')) {
                // View profile
                const userName = document.querySelector('.chat-user-info h3').textContent;
                alert(`Viewing profile of ${userName}`);
            } else if(icon.includes('ellipsis')) {
                // More options
                alert('More options menu');
            }
        });
    });
});