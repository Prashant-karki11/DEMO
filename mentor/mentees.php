<?php
session_start();
require_once '../config/database.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'mentor') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Get mentor's expertise from user profile
$mentor_stmt = $conn->prepare("SELECT skills FROM users WHERE id = ?");
$mentor_stmt->bind_param("i", $user_id);
$mentor_stmt->execute();
$mentor = $mentor_stmt->get_result()->fetch_assoc();
$mentor_skills = $mentor['skills'] ?? '';

// Get potential mentees based on skill matching
$mentees = [];
if($mentor_skills) {
    $mentee_skills = explode(',', $mentor_skills);
    $placeholders = implode(',', array_fill(0, count($mentee_skills), '?'));
    $types = str_repeat('s', count($mentee_skills));
    
    // Find job seekers whose skills match mentor's expertise
    $query = "SELECT u.* FROM users u 
              WHERE u.user_type = 'job_seeker' 
              AND u.id NOT IN (
                  SELECT mentee_id FROM mentorship WHERE mentor_id = ?
              )
              AND (
                  u.skills LIKE CONCAT('%', ?, '%')";
    
    for($i = 1; $i < count($mentee_skills); $i++) {
        $query .= " OR u.skills LIKE CONCAT('%', ?, '%')";
    }
    
    $query .= ") LIMIT 20";
    
    $params = array_merge([$user_id], $mentee_skills);
    $types = 'i' . $types;
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $mentees = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Get current mentees
$current_stmt = $conn->prepare("
    SELECT m.*, u.name, u.email, u.skills, u.experience 
    FROM mentorship m 
    JOIN users u ON m.mentee_id = u.id 
    WHERE m.mentor_id = ? 
    AND m.status IN ('active', 'requested')
    ORDER BY m.created_at DESC
");
$current_stmt->bind_param("i", $user_id);
$current_stmt->execute();
$current_mentees = $current_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Send mentorship request
if(isset($_POST['send_request'])) {
    $mentee_id = $_POST['mentee_id'];
    $message = $_POST['message'];
    
    $check_stmt = $conn->prepare("SELECT id FROM mentorship WHERE mentor_id = ? AND mentee_id = ?");
    $check_stmt->bind_param("ii", $user_id, $mentee_id);
    $check_stmt->execute();
    
    if($check_stmt->get_result()->num_rows == 0) {
        $insert_stmt = $conn->prepare("INSERT INTO mentorship (mentor_id, mentee_id, status) VALUES (?, ?, 'requested')");
        $insert_stmt->bind_param("ii", $user_id, $mentee_id);
        
        if($insert_stmt->execute()) {
            // Send notification message
            $msg_stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
            $msg_stmt->bind_param("iis", $user_id, $mentee_id, $message);
            $msg_stmt->execute();
            
            $success = "Mentorship request sent successfully!";
        } else {
            $error = "Error sending request. Please try again.";
        }
    } else {
        $error = "You have already sent a request to this mentee.";
    }
}

// Update mentorship status
if(isset($_POST['update_status'])) {
    $mentorship_id = $_POST['mentorship_id'];
    $status = $_POST['status'];
    
    $update_stmt = $conn->prepare("UPDATE mentorship SET status = ? WHERE id = ?");
    $update_stmt->bind_param("si", $status, $mentorship_id);
    
    if($update_stmt->execute()) {
        $success = "Mentorship status updated!";
    } else {
        $error = "Error updating status.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Mentees - CareerPath</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/navigation.php'; ?>
    
    <main class="dashboard-main">
        <div class="container">
            <div class="dashboard-header">
                <h1><i class="fas fa-user-graduate"></i> Manage Mentees</h1>
                <p>Connect with and guide aspiring professionals</p>
            </div>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="mentees-container">
                <div class="mentees-tabs">
                    <button class="tab-btn active" data-tab="current">Current Mentees (<?php echo count($current_mentees); ?>)</button>
                    <button class="tab-btn" data-tab="potential">Potential Mentees (<?php echo count($mentees); ?>)</button>
                    <button class="tab-btn" data-tab="requests">Pending Requests</button>
                </div>
                
                <div class="tab-content active" id="current">
                    <div class="mentees-grid">
                        <?php if(empty($current_mentees)): ?>
                            <div class="no-mentees">
                                <i class="fas fa-users fa-3x"></i>
                                <h3>No current mentees</h3>
                                <p>Start by finding potential mentees who match your expertise.</p>
                                <button class="btn btn-primary switch-tab" data-tab="potential">
                                    <i class="fas fa-search"></i> Find Mentees
                                </button>
                            </div>
                        <?php else: ?>
                            <?php foreach($current_mentees as $mentee): ?>
                            <div class="mentee-card">
                                <div class="mentee-header">
                                    <div class="mentee-avatar">
                                        <i class="fas fa-user-circle fa-3x"></i>
                                    </div>
                                    <div class="mentee-info">
                                        <h3><?php echo htmlspecialchars($mentee['name']); ?></h3>
                                        <p class="mentee-email"><?php echo htmlspecialchars($mentee['email']); ?></p>
                                        <span class="mentee-status status-<?php echo $mentee['status']; ?>">
                                            <?php echo ucfirst($mentee['status']); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="mentee-skills">
                                    <h4>Skills</h4>
                                    <div class="skill-tags">
                                        <?php 
                                        $skills = explode(',', $mentee['skills'] ?? '');
                                        foreach(array_slice($skills, 0, 5) as $skill): 
                                            if(trim($skill)): ?>
                                                <span class="skill-tag"><?php echo trim($skill); ?></span>
                                        <?php endif; endforeach; ?>
                                    </div>
                                </div>
                                
                                <div class="mentee-experience">
                                    <h4>Experience</h4>
                                    <p><?php echo substr(htmlspecialchars($mentee['experience'] ?? 'Not specified'), 0, 100); ?>...</p>
                                </div>
                                
                                <div class="mentee-actions">
                                    <button class="btn btn-outline message-mentee" data-mentee="<?php echo $mentee['mentee_id']; ?>">
                                        <i class="fas fa-envelope"></i> Message
                                    </button>
                                    <button class="btn btn-primary schedule-session" data-mentee="<?php echo $mentee['mentee_id']; ?>">
                                        <i class="fas fa-video"></i> Schedule Session
                                    </button>
                                    <?php if($mentee['status'] == 'requested'): ?>
                                        <form method="POST" class="status-form">
                                            <input type="hidden" name="mentorship_id" value="<?php echo $mentee['id']; ?>">
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" name="update_status" class="btn btn-success">
                                                <i class="fas fa-check"></i> Accept
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="tab-content" id="potential">
                    <div class="search-filters">
                        <input type="text" id="searchMentees" placeholder="Search by name, skills, or experience..." class="search-input">
                        <select id="experienceFilter" class="filter-select">
                            <option value="">All Experience Levels</option>
                            <option value="entry">Entry Level</option>
                            <option value="mid">Mid Level</option>
                            <option value="senior">Senior Level</option>
                        </select>
                    </div>
                    
                    <div class="mentees-grid">
                        <?php if(empty($mentees)): ?>
                            <div class="no-mentees">
                                <i class="fas fa-search fa-3x"></i>
                                <h3>No potential mentees found</h3>
                                <p>Update your skills profile to get better matches.</p>
                                <a href="profile.php" class="btn btn-primary">Update Profile</a>
                            </div>
                        <?php else: ?>
                            <?php foreach($mentees as $mentee): ?>
                            <div class="mentee-card potential">
                                <div class="mentee-header">
                                    <div class="mentee-avatar">
                                        <i class="fas fa-user-circle fa-3x"></i>
                                    </div>
                                    <div class="mentee-info">
                                        <h3><?php echo htmlspecialchars($mentee['name']); ?></h3>
                                        <p class="mentee-email"><?php echo htmlspecialchars($mentee['email']); ?></p>
                                        <div class="match-score">
                                            <i class="fas fa-bullseye"></i>
                                            <span>High Match</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mentee-skills">
                                    <h4>Skills Looking For Guidance</h4>
                                    <div class="skill-tags">
                                        <?php 
                                        $mentee_skills = explode(',', $mentee['skills'] ?? '');
                                        $common_skills = array_intersect(
                                            array_map('trim', $mentee_skills),
                                            array_map('trim', explode(',', $mentor_skills))
                                        );
                                        foreach(array_slice($common_skills, 0, 5) as $skill): ?>
                                            <span class="skill-tag highlight"><?php echo $skill; ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <div class="mentee-goals">
                                    <h4>Career Goals</h4>
                                    <p>Looking for guidance in career advancement and skill development...</p>
                                </div>
                                
                                <div class="mentee-actions">
                                    <button class="btn btn-outline view-profile" data-user="<?php echo $mentee['id']; ?>">
                                        <i class="fas fa-eye"></i> View Profile
                                    </button>
                                    <button class="btn btn-primary send-request" data-mentee="<?php echo $mentee['id']; ?>">
                                        <i class="fas fa-user-plus"></i> Send Request
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="tab-content" id="requests">
                    <div class="requests-list">
                        <div class="no-mentees">
                            <i class="fas fa-inbox fa-3x"></i>
                            <h3>No pending requests</h3>
                            <p>Mentees will appear here when they send you mentorship requests.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Send Request Modal -->
    <div id="requestModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Send Mentorship Request</h2>
                <button class="close-modal">&times;</button>
            </div>
            <form method="POST" class="modal-form">
                <input type="hidden" name="mentee_id" id="menteeId">
                <div class="form-group">
                    <label for="message">Personalized Message</label>
                    <textarea id="message" name="message" rows="4" required placeholder="Introduce yourself and explain how you can help...">
Hello! I noticed we share similar skills and I'd love to offer my guidance. With my experience in [your expertise], I can help you [specific help]. Let's connect and discuss your career goals!
                    </textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                    <button type="submit" name="send_request" class="btn btn-primary">Send Request</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Mentee Profile Modal -->
    <div id="profileModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Mentee Profile</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body" id="profileContent">
                <!-- Dynamic content -->
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/mentees.js"></script>
</body>
</html>