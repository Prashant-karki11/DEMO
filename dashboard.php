<?php
session_start();
require_once 'config/database.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_type = $_SESSION['user_type'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CareerPath</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/navigation.php'; ?>
    
    <main class="dashboard-main">
        <div class="container">
            <div class="dashboard-header">
                <h1>Welcome back, <?php echo $_SESSION['user_name']; ?>!</h1>
                <p>Here's what's happening with your career journey</p>
            </div>
            
            <?php if($user_type == 'job_seeker'): ?>
                <!-- Job Seeker Dashboard -->
                <div class="dashboard-grid">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3><i class="fas fa-bullseye"></i> Resume Builder</h3>
                        </div>
                        <div class="card-body">
                            <p>Set and track your resume.</p>
                            <a href="job_seeker/profile.php" class="btn btn-outline">Manage </a>
                        </div>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3><i class="fas fa-briefcase"></i> Job Matches</h3>
                        </div>
                        <div class="card-body">
                            <p>Find jobs matching your profile</p>
                            <a href="job_seeker/jobs.php" class="btn btn-outline">View Jobs</a>
                        </div>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3><i class="fas fa-robot"></i> AI Guidance</h3>
                        </div>
                        <div class="card-body">
                            <p>Get personalized career advice</p>
                            <a href="job_seeker/ai_guidance.php" class="btn btn-outline">Get Advice</a>
                        </div>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3><i class="fas fa-tasks"></i> Applications</h3>
                        </div>
                        <div class="card-body">
                            <p>Track your job applications</p>
                            <a href="job_seeker/tracker.php" class="btn btn-outline">View Tracker</a>
                        </div>
                    </div>
                </div>
                
            <?php elseif($user_type == 'recruiter'): ?>
                <!-- Recruiter Dashboard -->
                <div class="dashboard-grid">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3><i class="fas fa-plus-circle"></i> Post New Job</h3>
                        </div>
                        <div class="card-body">
                            <p>Create a new job listing</p>
                            <a href="recruiter/post_job.php" class="btn btn-outline">Post Job</a>
                        </div>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3><i class="fas fa-users"></i> Applicants</h3>
                        </div>
                        <div class="card-body">
                            <p>Manage job applications</p>
                            <a href="recruiter/manage_applications.php" class="btn btn-outline">View Applicants</a>
                        </div>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3><i class="fas fa-chart-bar"></i> Analytics</h3>
                        </div>
                        <div class="card-body">
                            <p>View job performance metrics</p>
                            <a href="recruiter/analytics.php" class="btn btn-outline">View Analytics</a>
                        </div>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3><i class="fas fa-building"></i> Company Profile</h3>
                        </div>
                        <div class="card-body">
                            <p>Update company information</p>
                            <a href="recruiter/profile.php" class="btn btn-outline">Edit Profile</a>
                        </div>
                    </div>
                </div>
                
            <?php elseif($user_type == 'mentor'): ?>
                <!-- Mentor Dashboard -->
                <div class="dashboard-grid">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3><i class="fas fa-user-graduate"></i> My Mentees</h3>
                        </div>
                        <div class="card-body">
                            <p>View and guide your mentees</p>
                            <a href="mentor/mentees.php" class="btn btn-outline">View Mentees</a>
                        </div>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3><i class="fas fa-calendar-alt"></i> Schedule</h3>
                        </div>
                        <div class="card-body">
                            <p>Manage your mentorship sessions</p>
                            <a href="mentor/schedule.php" class="btn btn-outline">View Schedule</a>
                        </div>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3><i class="fas fa-comments"></i> Messages</h3>
                        </div>
                        <div class="card-body">
                            <p>Communicate with mentees</p>
                            <a href="mentor/messages.php" class="btn btn-outline">View Messages</a>
                        </div>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3><i class="fas fa-star"></i> Ratings</h3>
                        </div>
                        <div class="card-body">
                            <p>View your mentorship ratings</p>
                            <a href="mentor/ratings.php" class="btn btn-outline">View Ratings</a>
                        </div>
                    </div>
                </div>
                
            <?php else: ?>
                <div class="alert alert-info">
                    <p>Your dashboard is being prepared. Please check back later.</p>
                </div>
            <?php endif; ?>
            
            <!-- Recent Activity Section -->
            <div class="recent-activity">
                <h2 class="section-title">Recent Activity</h2>
                <div class="activity-list">
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="activity-content">
                            <p>Your account was successfully verified</p>
                            <span class="activity-time">2 hours ago</span>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div class="activity-content">
                            <p>New job recommendations available</p>
                            <span class="activity-time">1 day ago</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/script.js"></script>
</body>
</html>