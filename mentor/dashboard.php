<?php
session_start();
require_once '../config/database.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'mentor') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get mentor stats
$stats_stmt = $conn->prepare("
    SELECT 
        (SELECT COUNT(*) FROM mentorship WHERE mentor_id = ? AND status = 'active') as active_mentees,
        (SELECT COUNT(*) FROM mentorship WHERE mentor_id = ? AND status = 'completed') as completed_sessions,
        (SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0) as unread_messages
");
$stats_stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

// Get upcoming sessions
$sessions_stmt = $conn->prepare("
    SELECT m.*, u.name as mentee_name, u.email as mentee_email 
    FROM mentorship m 
    JOIN users u ON m.mentee_id = u.id 
    WHERE m.mentor_id = ? 
    AND m.status = 'active' 
    ORDER BY m.start_date 
    LIMIT 5
");
$sessions_stmt->bind_param("i", $user_id);
$sessions_stmt->execute();
$upcoming_sessions = $sessions_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentor Dashboard - CareerPath</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/navigation.php'; ?>
    
    <main class="dashboard-main">
        <div class="container">
            <div class="dashboard-header">
                <h1><i class="fas fa-chalkboard-teacher"></i> Mentor Dashboard</h1>
                <p>Guide the next generation of professionals</p>
            </div>
            
            <div class="mentor-stats">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #3b82f6;">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-number"><?php echo $stats['active_mentees'] ?? 0; ?></span>
                        <span class="stat-label">Active Mentees</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #10b981;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-number"><?php echo $stats['completed_sessions'] ?? 0; ?></span>
                        <span class="stat-label">Completed Sessions</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #f59e0b;">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-number"><?php echo $stats['unread_messages'] ?? 0; ?></span>
                        <span class="stat-label">Unread Messages</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #8b5cf6;">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-number">4.8</span>
                        <span class="stat-label">Average Rating</span>
                    </div>
                </div>
            </div>
            
            <div class="mentor-content">
                <div class="mentor-left">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3><i class="fas fa-calendar-alt"></i> Upcoming Sessions</h3>
                        </div>
                        <div class="card-body">
                            <?php if(empty($upcoming_sessions)): ?>
                                <div class="no-sessions">
                                    <i class="fas fa-calendar-times fa-2x"></i>
                                    <p>No upcoming sessions scheduled</p>
                                    <a href="mentees.php" class="btn btn-primary">Find Mentees</a>
                                </div>
                            <?php else: ?>
                                <div class="sessions-list">
                                    <?php foreach($upcoming_sessions as $session): ?>
                                    <div class="session-item">
                                        <div class="session-date">
                                            <strong><?php echo date('M d', strtotime($session['start_date'])); ?></strong>
                                            <small><?php echo date('D', strtotime($session['start_date'])); ?></small>
                                        </div>
                                        <div class="session-details">
                                            <h4><?php echo htmlspecialchars($session['mentee_name']); ?></h4>
                                            <p><?php echo htmlspecialchars($session['mentee_email']); ?></p>
                                        </div>
                                        <div class="session-actions">
                                            <button class="btn-icon" title="Reschedule">
                                                <i class="fas fa-calendar-alt"></i>
                                            </button>
                                            <button class="btn-icon" title="Start Session">
                                                <i class="fas fa-video"></i>
                                            </button>
                                            <button class="btn-icon" title="Message">
                                                <i class="fas fa-comment"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3><i class="fas fa-tasks"></i> Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <div class="quick-actions">
                                <a href="mentees.php" class="action-btn">
                                    <i class="fas fa-user-plus"></i>
                                    <span>Find New Mentees</span>
                                </a>
                                <a href="schedule.php" class="action-btn">
                                    <i class="fas fa-calendar-plus"></i>
                                    <span>Set Availability</span>
                                </a>
                                <a href="resources.php" class="action-btn">
                                    <i class="fas fa-book"></i>
                                    <span>Share Resources</span>
                                </a>
                                <a href="forum.php" class="action-btn">
                                    <i class="fas fa-comments"></i>
                                    <span>Join Discussions</span>
                                </a>
                                <a href="ai_tools.php" class="action-btn">
                                    <i class="fas fa-robot"></i>
                                    <span>AI Tools</span>
                                </a>
                                <a href="reports.php" class="action-btn">
                                    <i class="fas fa-chart-bar"></i>
                                    <span>Progress Reports</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mentor-right">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3><i class="fas fa-comments"></i> Recent Messages</h3>
                        </div>
                        <div class="card-body">
                            <div class="messages-list">
                                <div class="message-item unread">
                                    <div class="message-avatar">
                                        <i class="fas fa-user-circle"></i>
                                    </div>
                                    <div class="message-content">
                                        <h4>John Doe</h4>
                                        <p>Thanks for the advice on the interview...</p>
                                        <span class="message-time">10 min ago</span>
                                    </div>
                                </div>
                                <div class="message-item">
                                    <div class="message-avatar">
                                        <i class="fas fa-user-circle"></i>
                                    </div>
                                    <div class="message-content">
                                        <h4>Jane Smith</h4>
                                        <p>Can we reschedule our session to Friday?</p>
                                        <span class="message-time">2 hours ago</span>
                                    </div>
                                </div>
                                <div class="message-item">
                                    <div class="message-avatar">
                                        <i class="fas fa-user-circle"></i>
                                    </div>
                                    <div class="message-content">
                                        <h4>Mike Johnson</h4>
                                        <p>The resume template you shared was perfect!</p>
                                        <span class="message-time">1 day ago</span>
                                    </div>
                                </div>
                            </div>
                            <a href="messages.php" class="btn btn-outline btn-block">View All Messages</a>
                        </div>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3><i class="fas fa-lightbulb"></i> Mentorship Tips</h3>
                        </div>
                        <div class="card-body">
                            <div class="tips-list">
                                <div class="tip-item">
                                    <i class="fas fa-check-circle"></i>
                                    <p>Set clear expectations with your mentees from day one</p>
                                </div>
                                <div class="tip-item">
                                    <i class="fas fa-check-circle"></i>
                                    <p>Use AI tools to personalize career advice</p>
                                </div>
                                <div class="tip-item">
                                    <i class="fas fa-check-circle"></i>
                                    <p>Schedule regular check-ins, not just formal sessions</p>
                                </div>
                                <div class="tip-item">
                                    <i class="fas fa-check-circle"></i>
                                    <p>Share real-world examples and stories</p>
                                </div>
                                <div class="tip-item">
                                    <i class="fas fa-check-circle"></i>
                                    <p>Encourage mentees to set SMART goals</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="recent-activity">
                <h2 class="section-title">Recent Activity</h2>
                <div class="activity-list">
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="activity-content">
                            <p>New mentorship request from Sarah Johnson</p>
                            <span class="activity-time">30 minutes ago</span>
                        </div>
                        <div class="activity-actions">
                            <button class="btn btn-sm btn-primary">Accept</button>
                            <button class="btn btn-sm btn-outline">View</button>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="activity-content">
                            <p>Received a 5-star rating from your mentee John</p>
                            <span class="activity-time">2 hours ago</span>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-video"></i>
                        </div>
                        <div class="activity-content">
                            <p>Upcoming video session with Mike in 1 hour</p>
                            <span class="activity-time">Tomorrow, 10:00 AM</span>
                        </div>
                        <div class="activity-actions">
                            <button class="btn btn-sm btn-primary">Join Session</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/mentor.js"></script>
</body>
</html>