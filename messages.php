<?php
session_start();
require_once 'config/database.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$other_user_id = $_GET['user'] ?? null;

// Get user info
$user_stmt = $conn->prepare("SELECT name, user_type FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// Get conversations list
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
");
$conversations_stmt->bind_param("iiiiii", $user_id, $user_id, $user_id, $user_id, $user_id, $user_id);
$conversations_stmt->execute();
$conversations = $conversations_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get messages with specific user
$messages = [];
$other_user = null;

if($other_user_id) {
    // Mark messages as read
    $read_stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE receiver_id = ? AND sender_id = ? AND is_read = 0");
    $read_stmt->bind_param("ii", $user_id, $other_user_id);
    $read_stmt->execute();
    
    // Get other user info
    $other_stmt = $conn->prepare("SELECT id, name, user_type, email FROM users WHERE id = ?");
    $other_stmt->bind_param("i", $other_user_id);
    $other_stmt->execute();
    $other_user = $other_stmt->get_result()->fetch_assoc();
    
    // Get messages
    $msg_stmt = $conn->prepare("
        SELECT m.*, u.name as sender_name 
        FROM messages m 
        JOIN users u ON m.sender_id = u.id 
        WHERE (m.sender_id = ? AND m.receiver_id = ?) 
        OR (m.sender_id = ? AND m.receiver_id = ?) 
        ORDER BY m.created_at ASC
    ");
    $msg_stmt->bind_param("iiii", $user_id, $other_user_id, $other_user_id, $user_id);
    $msg_stmt->execute();
    $messages = $msg_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Send message
if(isset($_POST['send_message'])) {
    $receiver_id = $_POST['receiver_id'];
    $message = trim($_POST['message']);
    
    if(!empty($message)) {
        $insert_stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $insert_stmt->bind_param("iis", $user_id, $receiver_id, $message);
        
        if($insert_stmt->execute()) {
            header("Location: messages.php?user=" . $receiver_id);
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - CareerPath</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="dashboard-main">
        <div class="container">
            <div class="dashboard-header">
                <h1><i class="fas fa-comments"></i> Messages</h1>
                <p>Communicate with recruiters, job seekers, and mentors</p>
            </div>
            
            <div class="messages-container">
                <div class="conversations-sidebar">
                    <div class="conversations-header">
                        <h3>Conversations</h3>
                        <button class="btn btn-sm btn-primary" id="newMessageBtn">
                            <i class="fas fa-plus"></i> New
                        </button>
                    </div>
                    
                    <div class="conversations-list">
                        <?php if(empty($conversations)): ?>
                            <div class="no-conversations">
                                <i class="fas fa-comments fa-2x"></i>
                                <p>No conversations yet</p>
                            </div>
                        <?php else: ?>
                            <?php foreach($conversations as $conv): ?>
                            <a href="messages.php?user=<?php echo $conv['other_user_id']; ?>" class="conversation-item <?php echo $conv['other_user_id'] == $other_user_id ? 'active' : ''; ?> <?php echo $conv['unread'] > 0 ? 'unread' : ''; ?>">
                                <div class="conversation-avatar">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <div class="conversation-info">
                                    <h4><?php echo htmlspecialchars($conv['other_user_name']); ?></h4>
                                    <p class="last-message">
                                        <?php echo htmlspecialchars(substr($conv['last_message'] ?? 'No messages yet', 0, 25)); ?>...
                                    </p>
                                    <span class="conversation-time">
                                        <?php echo $conv['last_message_time'] ? date('M d', strtotime($conv['last_message_time'])) : ''; ?>
                                    </span>
                                </div>
                                <?php if($conv['unread'] > 0): ?>
                                    <span class="unread-badge"><?php echo $conv['unread']; ?></span>
                                <?php endif; ?>
                            </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="messages-content">
                    <?php if($other_user): ?>
                        <div class="chat-header">
                            <div class="chat-user-info">
                                <div class="chat-avatar">
                                    <i class="fas fa-user-circle fa-2x"></i>
                                </div>
                                <div>
                                    <h3><?php echo htmlspecialchars($other_user['name']); ?></h3>
                                    <p class="user-type"><?php echo ucfirst(str_replace('_', ' ', $other_user['user_type'])); ?></p>
                                </div>
                            </div>
                            <div class="chat-actions">
                                <button class="btn-icon" title="Video Call">
                                    <i class="fas fa-video"></i>
                                </button>
                                <button class="btn-icon" title="Profile">
                                    <i class="fas fa-user"></i>
                                </button>
                                <button class="btn-icon" title="More">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="chat-messages" id="chatMessages">
                            <?php foreach($messages as $msg): ?>
                            <div class="message-item <?php echo $msg['sender_id'] == $user_id ? 'sent' : 'received'; ?>">
                                <div class="message-avatar">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <div class="message-bubble">
                                    <p><?php echo htmlspecialchars($msg['message']); ?></p>
                                    <span class="message-time">
                                        <?php echo date('H:i', strtotime($msg['created_at'])); ?>
                                        <?php if($msg['sender_id'] == $user_id && $msg['is_read']): ?>
                                            <i class="fas fa-check-double read-icon"></i>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="chat-input">
                            <form method="POST" class="message-form">
                                <input type="hidden" name="receiver_id" value="<?php echo $other_user_id; ?>">
                                <div class="input-group">
                                    <input type="text" name="message" placeholder="Type your message here..." required autocomplete="off">
                                    <button type="submit" name="send_message" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i> Send
                                    </button>
                                </div>
                                <div class="input-actions">
                                    <button type="button" class="btn-icon" title="Attach File">
                                        <i class="fas fa-paperclip"></i>
                                    </button>
                                    <button type="button" class="btn-icon" title="Emoji">
                                        <i class="far fa-smile"></i>
                                    </button>
                                    <button type="button" class="btn-icon" title="Schedule">
                                        <i class="far fa-calendar-alt"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="no-chat-selected">
                            <i class="fas fa-comments fa-4x"></i>
                            <h3>Select a conversation</h3>
                            <p>Choose a conversation from the sidebar or start a new one</p>
                            <button class="btn btn-primary" id="startNewChat">
                                <i class="fas fa-plus"></i> Start New Chat
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <!-- New Message Modal -->
    <div id="newMessageModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>New Message</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="search-users">
                    <input type="text" id="searchUsersInput" placeholder="Search by name or email...">
                    <div class="users-list" id="usersList">
                        <!-- Users will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/script.js"></script>
    <script src="assets/js/messages.js"></script>
</body>
</html>     