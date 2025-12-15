<?php
session_start();
require_once '../config/database.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'job_seeker') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get applications
$stmt = $conn->prepare("SELECT a.*, j.title, j.company_id, c.company_name, j.location, j.job_type 
                        FROM applications a 
                        JOIN jobs j ON a.job_id = j.id 
                        LEFT JOIN companies c ON j.company_id = c.id 
                        WHERE a.job_seeker_id = ? 
                        ORDER BY a.applied_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$applications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get statistics
$stats = [
    'total' => count($applications),
    'pending' => 0,
    'reviewed' => 0,
    'shortlisted' => 0,
    'rejected' => 0,
    'hired' => 0
];

foreach($applications as $app) {
    $stats[$app['status']]++;
}

// Calculate success rate
$success_rate = 0;
if($stats['total'] > 0) {
    $success_rate = (($stats['shortlisted'] + $stats['hired']) / $stats['total']) * 100;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Tracker - CareerPath</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/navigation.php'; ?>
    
    <main class="dashboard-main">
        <div class="container">
            <div class="dashboard-header">
                <h1>Application Tracker</h1>
                <p>Track all your job applications in one place</p>
            </div>
            
            <div class="tracker-stats">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #3b82f6;">
                        <i class="fas fa-paper-plane"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-number"><?php echo $stats['total']; ?></span>
                        <span class="stat-label">Total Applications</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #10b981;">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-number"><?php echo $stats['reviewed']; ?></span>
                        <span class="stat-label">Viewed</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #f59e0b;">
                        <i class="fas fa-list-alt"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-number"><?php echo $stats['shortlisted']; ?></span>
                        <span class="stat-label">Shortlisted</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #ef4444;">
                        <i class="fas fa-times"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-number"><?php echo $stats['rejected']; ?></span>
                        <span class="stat-label">Rejected</span>
                    </div>
                </div>
            </div>
            
            <div class="tracker-filters">
                <button class="filter-btn active" data-filter="all">All (<?php echo $stats['total']; ?>)</button>
                <button class="filter-btn" data-filter="pending">Pending (<?php echo $stats['pending']; ?>)</button>
                <button class="filter-btn" data-filter="reviewed">Viewed (<?php echo $stats['reviewed']; ?>)</button>
                <button class="filter-btn" data-filter="shortlisted">Shortlisted (<?php echo $stats['shortlisted']; ?>)</button>
                <button class="filter-btn" data-filter="rejected">Rejected (<?php echo $stats['rejected']; ?>)</button>
                <button class="filter-btn" data-filter="hired">Hired (<?php echo $stats['hired']; ?>)</button>
            </div>
            
            <div class="applications-table">
                <table>
                    <thead>
                        <tr>
                            <th>Position</th>
                            <th>Company</th>
                            <th>Applied Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($applications)): ?>
                            <tr>
                                <td colspan="5" class="no-data">
                                    <i class="fas fa-inbox fa-2x"></i>
                                    <p>No applications yet. Start applying for jobs!</p>
                                    <a href="jobs.php" class="btn btn-primary">Browse Jobs</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($applications as $app): ?>
                            <tr class="application-row" data-status="<?php echo $app['status']; ?>">
                                <td>
                                    <strong><?php echo htmlspecialchars($app['title']); ?></strong>
                                    <small><?php echo ucfirst(str_replace('_', ' ', $app['job_type'])); ?> • <?php echo htmlspecialchars($app['location']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($app['company_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($app['applied_date'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $app['status']; ?>">
                                        <?php echo ucfirst($app['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-icon view-application" data-id="<?php echo $app['id']; ?>" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-icon message-recruiter" title="Message Recruiter">
                                            <i class="fas fa-envelope"></i>
                                        </button>
                                        <button class="btn-icon delete-application" data-id="<?php echo $app['id']; ?>" title="Withdraw">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="tracker-insights">
                <h3><i class="fas fa-lightbulb"></i> AI Insights</h3>
                <div class="insights-grid">
                    <div class="insight-card">
                        <h4>Application Rate</h4>
                        <p>You've applied to <?php echo $stats['total']; ?> jobs this month.</p>
                        <?php if($stats['total'] < 5): ?>
                            <div class="insight-tip">
                                <i class="fas fa-info-circle"></i>
                                <strong>Tip:</strong> Try applying to at least 10-15 jobs per month for better results.
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="insight-card">
                        <h4>Success Rate</h4>
                        <?php if($stats['total'] > 0): ?>
                            <div class="success-rate">
                                <div class="rate-circle" style="background: conic-gradient(var(--success) <?php echo round($success_rate); ?>%, var(--gray-200) 0);">
                                    <span><?php echo round($success_rate); ?>%</span>
                                </div>
                                <p>Shortlisting rate</p>
                            </div>
                        <?php else: ?>
                            <p>No applications yet</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="insight-card">
                        <h4>Next Steps</h4>
                        <?php if($stats['pending'] > 0): ?>
                            <p>You have <?php echo $stats['pending']; ?> pending applications.</p>
                            <ul>
                                <li>Follow up on applications after 7 days</li>
                                <li>Prepare for potential interviews</li>
                                <li>Update your resume regularly</li>
                            </ul>
                        <?php else: ?>
                            <p>All applications have been processed.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Application Details Modal -->
    <div id="applicationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Application Details</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body" id="applicationDetails">
                <!-- Dynamic content -->
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/tracker.js"></script>
</body>
</html>