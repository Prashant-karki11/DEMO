<?php
session_start();
require_once '../config/database.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'recruiter') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$job_id = $_GET['job_id'] ?? null;

// Get recruiter's jobs
$jobs_stmt = $conn->prepare("SELECT j.id, j.title, COUNT(a.id) as application_count 
                            FROM jobs j 
                            LEFT JOIN applications a ON j.id = a.job_id 
                            WHERE j.recruiter_id = ? 
                            GROUP BY j.id 
                            ORDER BY j.posted_date DESC");
$jobs_stmt->bind_param("i", $user_id);
$jobs_stmt->execute();
$jobs = $jobs_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get applications for specific job or all jobs
if($job_id) {
    $app_stmt = $conn->prepare("SELECT a.*, u.name, u.email, u.skills, u.experience, j.title as job_title 
                               FROM applications a 
                               JOIN users u ON a.job_seeker_id = u.id 
                               JOIN jobs j ON a.job_id = j.id 
                               WHERE a.job_id = ? AND j.recruiter_id = ?
                               ORDER BY a.applied_date DESC");
    $app_stmt->bind_param("ii", $job_id, $user_id);
} else {
    $app_stmt = $conn->prepare("SELECT a.*, u.name, u.email, u.skills, u.experience, j.title as job_title 
                               FROM applications a 
                               JOIN users u ON a.job_seeker_id = u.id 
                               JOIN jobs j ON a.job_id = j.id 
                               WHERE j.recruiter_id = ?
                               ORDER BY a.applied_date DESC");
    $app_stmt->bind_param("i", $user_id);
}
$app_stmt->execute();
$applications = $app_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Update application status
if(isset($_POST['update_status'])) {
    $app_id = $_POST['application_id'];
    $status = $_POST['status'];
    $feedback = $_POST['feedback'] ?? '';
    
    $update_stmt = $conn->prepare("UPDATE applications SET status = ?, feedback = ? WHERE id = ?");
    $update_stmt->bind_param("ssi", $status, $feedback, $app_id);
    
    if($update_stmt->execute()) {
        $success = "Application status updated!";
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
    <title>Manage Applications - CareerPath</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/navigation.php'; ?>
    
    <main class="dashboard-main">
        <div class="container">
            <div class="dashboard-header">
                <h1>Manage Applications</h1>
                <p>Review and manage job applications from candidates</p>
            </div>
            
            <?php if(isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="applications-container">
                <div class="applications-sidebar">
                    <h3>Your Job Listings</h3>
                    <div class="job-list">
                        <a href="manage_applications.php" class="job-item <?php echo !$job_id ? 'active' : ''; ?>">
                            <span class="job-title">All Applications</span>
                            <span class="app-count"><?php echo count($applications); ?></span>
                        </a>
                        <?php foreach($jobs as $job): ?>
                        <a href="manage_applications.php?job_id=<?php echo $job['id']; ?>" class="job-item <?php echo $job_id == $job['id'] ? 'active' : ''; ?>">
                            <span class="job-title"><?php echo htmlspecialchars($job['title']); ?></span>
                            <span class="app-count"><?php echo $job['application_count']; ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="sidebar-stats">
                        <h4>Quick Stats</h4>
                        <div class="stat-item">
                            <span>Total Applications:</span>
                            <strong><?php echo count($applications); ?></strong>
                        </div>
                        <div class="stat-item">
                            <span>Pending Review:</span>
                            <strong><?php echo count(array_filter($applications, function($app) { return $app['status'] == 'pending'; })); ?></strong>
                        </div>
                        <div class="stat-item">
                            <span>Shortlisted:</span>
                            <strong><?php echo count(array_filter($applications, function($app) { return $app['status'] == 'shortlisted'; })); ?></strong>
                        </div>
                    </div>
                </div>
                
                <div class="applications-content">
                    <div class="applications-header">
                        <h3>
                            <?php if($job_id): ?>
                                Applications for: <?php echo htmlspecialchars($applications[0]['job_title'] ?? 'Job'); ?>
                            <?php else: ?>
                                All Applications
                            <?php endif; ?>
                        </h3>
                        <div class="applications-filters">
                            <select id="statusFilter" class="filter-select">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="reviewed">Reviewed</option>
                                <option value="shortlisted">Shortlisted</option>
                                <option value="rejected">Rejected</option>
                                <option value="hired">Hired</option>
                            </select>
                            <input type="text" id="searchCandidates" placeholder="Search candidates..." class="search-input">
                        </div>
                    </div>
                    
                    <?php if(empty($applications)): ?>
                        <div class="no-applications">
                            <i class="fas fa-users fa-3x"></i>
                            <h3>No applications yet</h3>
                            <p>Applications will appear here when candidates apply to your jobs.</p>
                            <a href="post_job.php" class="btn btn-primary">Post a Job</a>
                        </div>
                    <?php else: ?>
                        <div class="applications-list">
                            <?php foreach($applications as $app): ?>
                            <div class="application-card" data-status="<?php echo $app['status']; ?>">
                                <div class="application-header">
                                    <div class="candidate-info">
                                        <div class="candidate-avatar">
                                            <i class="fas fa-user-circle"></i>
                                        </div>
                                        <div>
                                            <h4><?php echo htmlspecialchars($app['name']); ?></h4>
                                            <p class="candidate-email"><?php echo htmlspecialchars($app['email']); ?></p>
                                            <p class="job-applied">Applied for: <?php echo htmlspecialchars($app['job_title']); ?></p>
                                        </div>
                                    </div>
                                    <div class="application-status">
                                        <span class="status-badge status-<?php echo $app['status']; ?>">
                                            <?php echo ucfirst($app['status']); ?>
                                        </span>
                                        <span class="applied-date">
                                            <?php echo date('M d, Y', strtotime($app['applied_date'])); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="application-body">
                                    <div class="candidate-skills">
                                        <strong>Skills:</strong>
                                        <div class="skill-tags">
                                            <?php 
                                            $skills = explode(',', $app['skills'] ?? '');
                                            foreach(array_slice($skills, 0, 5) as $skill): 
                                                if(trim($skill)): ?>
                                                    <span class="skill-tag"><?php echo trim($skill); ?></span>
                                            <?php endif; endforeach; ?>
                                            <?php if(count($skills) > 5): ?>
                                                <span class="skill-tag">+<?php echo count($skills) - 5; ?> more</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <?php if($app['cover_letter']): ?>
                                    <div class="cover-letter">
                                        <strong>Cover Letter:</strong>
                                        <p><?php echo substr(htmlspecialchars($app['cover_letter']), 0, 200); ?>...</p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="application-actions">
                                    <button class="btn btn-outline view-profile" data-candidate="<?php echo $app['job_seeker_id']; ?>">
                                        <i class="fas fa-user"></i> View Profile
                                    </button>
                                    <button class="btn btn-outline message-candidate" data-candidate="<?php echo $app['job_seeker_id']; ?>">
                                        <i class="fas fa-envelope"></i> Message
                                    </button>
                                    <div class="status-dropdown">
                                        <button class="btn btn-secondary dropdown-toggle">
                                            <i class="fas fa-edit"></i> Change Status
                                        </button>
                                        <div class="dropdown-menu">
                                            <form method="POST" class="status-form">
                                                <input type="hidden" name="application_id" value="<?php echo $app['id']; ?>">
                                                <select name="status" class="status-select">
                                                    <option value="pending" <?php echo $app['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="reviewed" <?php echo $app['status'] == 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                                                    <option value="shortlisted" <?php echo $app['status'] == 'shortlisted' ? 'selected' : ''; ?>>Shortlisted</option>
                                                    <option value="rejected" <?php echo $app['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                                    <option value="hired" <?php echo $app['status'] == 'hired' ? 'selected' : ''; ?>>Hired</option>
                                                </select>
                                                <textarea name="feedback" placeholder="Feedback (optional)"></textarea>
                                                <button type="submit" name="update_status" class="btn btn-primary btn-sm">Update</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Candidate Profile Modal -->
    <div id="candidateModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Candidate Profile</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body" id="candidateProfile">
                <!-- Dynamic content -->
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/manage_applications.js"></script>
</body>
</html>