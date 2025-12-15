<?php
session_start();
require_once 'config/database.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get unread message count
$unread_stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM messages WHERE receiver_id = ? AND is_read = 0");
$unread_stmt->bind_param("i", $user_id);
$unread_stmt->execute();
$unread_count = $unread_stmt->get_result()->fetch_assoc()['unread_count'];

// Get recent conversations
$conversations_stmt = $conn->prepare("
    SELECT 
        u.id as other_user_id,
        u.name as other_user_name,
        u.user_type as other_user_type,
        m.message as last_message,
        MAX(m.created_at) as last_message_time,
        SUM(CASE WHEN m.is_read = 0 AND m.receiver_id = ? THEN 1 ELSE 0 END) as unread
    FROM (
        SELECT sender_id as user_id FROM messages WHERE receiver_id = ?
        UNION
        SELECT receiver_id as user_id FROM messages WHERE sender_id = ?
    ) as conversations
    JOIN users u ON conversations.user_id = u.id
    LEFT JOIN messages m ON (
        (m.sender_id = ? AND m.receiver_id = u.id) OR 
        (m.sender_id = u.id AND m.receiver_id = ?)
    )
    WHERE u.id != ?
    GROUP BY u.id, u.name, u.user_type
    ORDER BY MAX(m.created_at) DESC
    LIMIT 10
");
$conversations_stmt->bind_param("iiiiii", $user_id, $user_id, $user_id, $user_id, $user_id, $user_id);
$conversations_stmt->execute();
$conversations = $conversations_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!-- Messages Dropdown in Header -->
<div class="messages-dropdown">
    <button class="messages-btn" id="messagesToggle">
        <i class="fas fa-envelope"></i>
        <?php if($unread_count > 0): ?>
            <span class="notification-badge"><?php echo $unread_count; ?></span>
        <?php endif; ?>
    </button>
    
    <div class="messages-dropdown-menu" id="messagesDropdown">
        <div class="messages-header">
            <h4>Messages</h4>
            <a href="messages.php" class="view-all">View All</a>
        </div>
        
        <div class="messages-list">
            <?php if(empty($conversations)): ?>
                <div class="no-messages">
                    <p>No messages yet</p>
                </div>
            <?php else: ?>
                <?php foreach($conversations as $conv): ?>
                <a href="messages.php?user=<?php echo $conv['other_user_id']; ?>" class="message-item <?php echo $conv['unread'] > 0 ? 'unread' : ''; ?>">
                    <div class="message-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="message-content">
                        <h5><?php echo htmlspecialchars($conv['other_user_name']); ?></h5>
                        <p><?php echo htmlspecialchars(substr($conv['last_message'] ?? 'No messages yet', 0, 30)); ?>...</p>
                        <span class="message-time">
                            <?php echo $conv['last_message_time'] ? date('H:i', strtotime($conv['last_message_time'])) : ''; ?>
                        </span>
                        <?php if($conv['unread'] > 0): ?>
                            <span class="unread-count"><?php echo $conv['unread']; ?></span>
                        <?php endif; ?>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="messages-footer">
            <a href="messages.php" class="btn btn-outline btn-sm">New Message</a>
        </div>
    </div>
</div>

<!-- Add to Header Navigation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const messagesToggle = document.getElementById('messagesToggle');
    const messagesDropdown = document.getElementById('messagesDropdown');
    
    if(messagesToggle) {
        messagesToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            messagesDropdown.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if(!messagesDropdown.contains(e.target) && !messagesToggle.contains(e.target)) {
                messagesDropdown.classList.remove('show');
            }
        });
    }
});
</script>

<style>
.messages-dropdown {
    position: relative;
}

.messages-btn {
    background: none;
    border: none;
    color: var(--gray-700);
    cursor: pointer;
    position: relative;
    padding: 0.5rem;
    border-radius: 0.5rem;
    transition: background-color 0.3s;
}

.messages-btn:hover {
    background-color: var(--gray-100);
}

.notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: var(--error);
    color: white;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    font-size: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.messages-dropdown-menu {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    width: 320px;
    z-index: 1000;
    margin-top: 0.5rem;
}

.messages-dropdown-menu.show {
    display: block;
    animation: fadeIn 0.2s ease-out;
}

.messages-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid var(--gray-200);
}

.messages-header h4 {
    margin: 0;
    color: var(--gray-800);
}

.view-all {
    color: var(--primary-blue);
    text-decoration: none;
    font-size: 0.875rem;
}

.messages-list {
    max-height: 400px;
    overflow-y: auto;
}

.message-item {
    display: flex;
    align-items: flex-start;
    padding: 1rem;
    border-bottom: 1px solid var(--gray-200);
    text-decoration: none;
    color: inherit;
    transition: background-color 0.3s;
}

.message-item:hover {
    background-color: var(--gray-50);
}

.message-item.unread {
    background-color: #f0f9ff;
}

.message-avatar {
    margin-right: 0.75rem;
    flex-shrink: 0;
}

.message-avatar i {
    color: var(--gray-500);
    font-size: 1.5rem;
}

.message-content {
    flex: 1;
    min-width: 0;
}

.message-content h5 {
    margin: 0 0 0.25rem;
    color: var(--gray-800);
    font-size: 0.875rem;
    font-weight: 600;
}

.message-content p {
    margin: 0 0 0.25rem;
    color: var(--gray-600);
    font-size: 0.875rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.message-time {
    color: var(--gray-500);
    font-size: 0.75rem;
}

.unread-count {
    display: inline-block;
    background: var(--primary-blue);
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 0.75rem;
    text-align: center;
    line-height: 20px;
    margin-left: 0.5rem;
}

.no-messages {
    padding: 2rem 1rem;
    text-align: center;
    color: var(--gray-500);
}

.messages-footer {
    padding: 1rem;
    border-top: 1px solid var(--gray-200);
    text-align: center;
}
</style>