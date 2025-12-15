<?php
session_start();
require_once '../config/database.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'job_seeker') {
    header("Location: ../login.php");
    exit();
}

// TF-IDF Job Matching Algorithm (Simplified)
function calculateTFIDFScore($jobDescription, $userSkills) {
    // Simple keyword matching for demo
    $jobWords = strtolower($jobDescription);
    $userWords = strtolower($userSkills);
    
    $jobKeywords = array_unique(array_filter(explode(' ', preg_replace('/[^a-z0-9]/', ' ', $jobWords))));
    $userKeywords = array_unique(array_filter(explode(' ', preg_replace('/[^a-z0-9]/', ' ', $userWords))));
    
    $matches = array_intersect($jobKeywords, $userKeywords);
    
    if(count($jobKeywords) == 0) return 0;
    
    return (count($matches) / count($jobKeywords)) * 100;
}

// Get user skills
$user_id = $_SESSION['user_id'];
$user_stmt = $conn->prepare("SELECT skills FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_skills = $user['skills'] ?? '';

// Get all active jobs
$jobs = [];
if($user_skills) {
    $job_stmt = $conn->prepare("SELECT j.*, c.company_name, c.logo FROM jobs j 
                                LEFT JOIN companies c ON j.company_id = c.id 
                                WHERE j.status = 'active' ORDER BY j.posted_date DESC");
    $job_stmt->execute();
    $job_result = $job_stmt->get_result();
    
    while($job = $job_result->fetch_assoc()) {
        // Calculate match score
        $job_text = $job['title'] . ' ' . $job['description'] . ' ' . $job['requirements'];
        $match_score = calculateTFIDFScore($job_text, $user_skills);
        $job['match_score'] = round($match_score);
        $jobs[] = $job;
    }
    
    // Sort by match score
    usort($jobs, function($a, $b) {
        return $b['match_score'] - $a['match_score'];
    });
}

// Apply for job
if(isset($_POST['apply_job'])) {
    $job_id = $_POST['job_id'];
    $cover_letter = $_POST['cover_letter'] ?? '';
    
    // Check if already applied
    $check_stmt = $conn->prepare("SELECT id FROM applications WHERE job_id = ? AND job_seeker_id = ?");
    $check_stmt->bind_param("ii", $job_id, $user_id);
    $check_stmt->execute();
    
    if($check_stmt->get_result()->num_rows == 0) {
        $apply_stmt = $conn->prepare("INSERT INTO applications (job_id, job_seeker_id, cover_letter) VALUES (?, ?, ?)");
        $apply_stmt->bind_param("iis", $job_id, $user_id, $cover_letter);
        
        if($apply_stmt->execute()) {
            $success = "Successfully applied for the job!";
        } else {
            $error = "Error applying for job. Please try again.";
        }
    } else {
        $error = "You have already applied for this job.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Recommendations - CareerPath</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/navigation.php'; ?>
    
    <main class="dashboard-main">
        <div class="container">
            <div class="dashboard-header">
                <h1>AI-Powered Job Recommendations</h1>
                <p>Jobs matched to your skills using TF-IDF algorithm</p>
            </div>
            
            <?php if(isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="filters">
                <div class="filter-group">
                    <input type="text" id="searchJobs" placeholder="Search jobs by title or company..." class="search-input">
                </div>
                <div class="filter-group">
                    <select id="jobType" class="filter-select">
                        <option value="">All Job Types</option>
                        <option value="full_time">Full Time</option>
                        <option value="part_time">Part Time</option>
                        <option value="contract">Contract</option>
                        <option value="internship">Internship</option>
                    </select>
                </div>
                <div class="filter-group">
                    <select id="experienceLevel" class="filter-select">
                        <option value="">All Experience Levels</option>
                        <option value="entry">Entry Level</option>
                        <option value="mid">Mid Level</option>
                        <option value="senior">Senior Level</option>
                    </select>
                </div>
            </div>
            
            <div class="jobs-grid">
                <?php if(empty($jobs)): ?>
                    <div class="no-jobs">
                        <i class="fas fa-briefcase fa-3x"></i>
                        <h3>No jobs available at the moment</h3>
                        <p>Try updating your skills profile to get better matches</p>
                        <a href="profile.php" class="btn btn-primary">Update Profile</a>
                    </div>
                <?php else: ?>
                    <?php foreach($jobs as $job): ?>
                    <div class="job-card" data-type="<?php echo $job['job_type']; ?>" data-level="<?php echo $job['experience_level']; ?>">
                        <div class="job-header">
                            <div class="company-logo">
                                <?php if($job['logo']): ?>
                                    <img src="<?php echo $job['logo']; ?>" alt="<?php echo $job['company_name']; ?>">
                                <?php else: ?>
                                    <i class="fas fa-building"></i>
                                <?php endif; ?>
                            </div>
                            <div class="job-title">
                                <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                                <p class="company-name"><?php echo htmlspecialchars($job['company_name']); ?></p>
                            </div>
                            <div class="match-badge" style="background: <?php echo $job['match_score'] > 80 ? '#10b981' : ($job['match_score'] > 60 ? '#f59e0b' : '#ef4444'); ?>">
                                <?php echo $job['match_score']; ?>% Match
                            </div>
                        </div>
                        
                        <div class="job-details">
                            <div class="job-tags">
                                <span class="tag"><i class="fas fa-clock"></i> <?php echo ucfirst(str_replace('_', ' ', $job['job_type'])); ?></span>
                                <span class="tag"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['location']); ?></span>
                                <span class="tag"><i class="fas fa-chart-line"></i> <?php echo ucfirst($job['experience_level']); ?> Level</span>
                                <span class="tag"><i class="fas fa-money-bill-wave"></i> <?php echo htmlspecialchars($job['salary_range']); ?></span>
                            </div>
                            
                            <p class="job-description">
                                <?php echo substr(htmlspecialchars($job['description']), 0, 150); ?>...
                            </p>
                            
                            <div class="job-footer">
                                <span class="posted-date">
                                    <i class="far fa-calendar"></i> 
                                    Posted <?php echo date('M d, Y', strtotime($job['posted_date'])); ?>
                                </span>
                                <div class="job-actions">
                                    <button class="btn btn-outline view-details" data-job="<?php echo $job['id']; ?>">
                                        <i class="fas fa-eye"></i> View Details
                                    </button>
                                    <button class="btn btn-primary apply-now" data-job="<?php echo $job['id']; ?>">
                                        <i class="fas fa-paper-plane"></i> Apply Now
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <!-- Job Details Modal -->
    <div id="jobModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Job Details</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body" id="jobDetailsContent">
                <!-- Dynamic content loaded via AJAX -->
            </div>
        </div>
    </div>
    
    <!-- Apply Modal -->
    <div id="applyModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Apply for Job</h2>
                <button class="close-modal">&times;</button>
            </div>
            <form method="POST" class="modal-form">
                <input type="hidden" name="job_id" id="applyJobId">
                <div class="form-group">
                    <label for="cover_letter">Cover Letter (Optional)</label>
                    <textarea id="cover_letter" name="cover_letter" rows="4" placeholder="Tell the employer why you're a good fit for this position..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                    <button type="submit" name="apply_job" class="btn btn-primary">Submit Application</button>
                </div>
            </form>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/jobs.js"></script>
</body>
</html>