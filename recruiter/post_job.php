<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config/database.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'recruiter') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Get or create company
$company_stmt = $conn->prepare("SELECT id, company_name FROM companies WHERE user_id = ?");
$company_stmt->bind_param("i", $user_id);
$company_stmt->execute();
$company_result = $company_stmt->get_result();
$company = $company_result->fetch_assoc();

if(!$company) {
    // Create default company
    $company_name = $_SESSION['user_name'] . "'s Company";
    $create_stmt = $conn->prepare("INSERT INTO companies (user_id, company_name) VALUES (?, ?)");
    $create_stmt->bind_param("is", $user_id, $company_name);
    
    if($create_stmt->execute()) {
        $company_id = $create_stmt->insert_id;
        // Get the created company
        $company_stmt->execute();
        $company = $company_result->fetch_assoc();
    } else {
        $error = "Error creating company profile. Please contact support.";
    }
}

// Handle form submission
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_job'])) {
    // Collect form data
    $title = $conn->real_escape_string(trim($_POST['title'] ?? ''));
    $description = $conn->real_escape_string(trim($_POST['description'] ?? ''));
    $requirements = $conn->real_escape_string(trim($_POST['requirements'] ?? ''));
    $skills_required = $conn->real_escape_string(trim($_POST['skills_required'] ?? ''));
    $location = $conn->real_escape_string(trim($_POST['location'] ?? ''));
    $job_type = $conn->real_escape_string($_POST['job_type'] ?? '');
    $salary_range = $conn->real_escape_string(trim($_POST['salary_range'] ?? ''));
    $experience_level = $conn->real_escape_string($_POST['experience_level'] ?? '');
    $deadline = $conn->real_escape_string($_POST['deadline'] ?? '');
    
    // Simple validation
    if(empty($title) || empty($description) || empty($requirements) || empty($location) || 
       empty($job_type) || empty($experience_level) || empty($deadline)) {
        $error = "Please fill in all required fields marked with *";
    } else {
        // Insert job using simple query for testing
        $sql = "INSERT INTO jobs (recruiter_id, company_id, title, description, requirements, skills_required, location, job_type, salary_range, experience_level, deadline) 
                VALUES ('$user_id', '{$company['id']}', '$title', '$description', '$requirements', '$skills_required', '$location', '$job_type', '$salary_range', '$experience_level', '$deadline')";
        
        if($conn->query($sql)) {
            $job_id = $conn->insert_id;
            $success = "Job posted successfully! Job ID: $job_id";
            
            // Clear form
            echo '<script>document.getElementById("jobForm").reset();</script>';
        } else {
            $error = "Error posting job: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post New Job - CareerPath</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .simple-form {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .form-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e5e7eb;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
        }
        .form-group label.required::after {
            content: " *";
            color: #ef4444;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-size: 1.125rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .btn-submit:hover {
            background: #2563eb;
        }
        .btn-submit:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .char-count {
            font-size: 0.875rem;
            color: #6b7280;
            text-align: right;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/navigation.php'; ?>
    
    <main class="dashboard-main">
        <div class="container">
            <div class="form-header">
                <h1><i class="fas fa-plus-circle"></i> Post New Job</h1>
                <p>Fill in the details below to post your job listing</p>
            </div>
            
            <?php if($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                    <a href="manage_jobs.php" style="margin-left: auto; color: inherit; text-decoration: underline;">View Jobs</a>
                </div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="simple-form">
                <form method="POST" id="jobForm" onsubmit="return validateForm()">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                        
                        <div class="form-group">
                            <label for="title" class="required">Job Title</label>
                            <input type="text" id="title" name="title" class="form-control" 
                                   placeholder="e.g., Senior PHP Developer" required
                                   value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Company</label>
                            <div class="form-control" style="background: #f9fafb;">
                                <strong><?php echo htmlspecialchars($company['company_name'] ?? 'Your Company'); ?></strong>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Job Details -->
                    <div class="form-section">
                        <h3><i class="fas fa-map-marker-alt"></i> Job Details</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="location" class="required">Location</label>
                                <input type="text" id="location" name="location" class="form-control" 
                                       placeholder="e.g., Kathmandu, Remote" required
                                       value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="job_type" class="required">Job Type</label>
                                <select id="job_type" name="job_type" class="form-control" required>
                                    <option value="">Select type</option>
                                    <option value="full_time" <?php echo ($_POST['job_type'] ?? '') == 'full_time' ? 'selected' : ''; ?>>Full Time</option>
                                    <option value="part_time" <?php echo ($_POST['job_type'] ?? '') == 'part_time' ? 'selected' : ''; ?>>Part Time</option>
                                    <option value="contract" <?php echo ($_POST['job_type'] ?? '') == 'contract' ? 'selected' : ''; ?>>Contract</option>
                                    <option value="internship" <?php echo ($_POST['job_type'] ?? '') == 'internship' ? 'selected' : ''; ?>>Internship</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="experience_level" class="required">Experience Level</label>
                                <select id="experience_level" name="experience_level" class="form-control" required>
                                    <option value="">Select level</option>
                                    <option value="entry" <?php echo ($_POST['experience_level'] ?? '') == 'entry' ? 'selected' : ''; ?>>Entry Level</option>
                                    <option value="mid" <?php echo ($_POST['experience_level'] ?? '') == 'mid' ? 'selected' : ''; ?>>Mid Level</option>
                                    <option value="senior" <?php echo ($_POST['experience_level'] ?? '') == 'senior' ? 'selected' : ''; ?>>Senior Level</option>
                                    <option value="executive" <?php echo ($_POST['experience_level'] ?? '') == 'executive' ? 'selected' : ''; ?>>Executive</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="salary_range">Salary Range</label>
                                <input type="text" id="salary_range" name="salary_range" class="form-control" 
                                       placeholder="e.g., $50,000 - $70,000"
                                       value="<?php echo htmlspecialchars($_POST['salary_range'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="deadline" class="required">Application Deadline</label>
                            <input type="date" id="deadline" name="deadline" class="form-control" required
                                   value="<?php echo $_POST['deadline'] ?? ''; ?>"
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    
                    <!-- Job Description -->
                    <div class="form-section">
                        <h3><i class="fas fa-file-alt"></i> Job Description</h3>
                        
                        <div class="form-group">
                            <label for="description" class="required">Job Description</label>
                            <textarea id="description" name="description" class="form-control" rows="6" required
                                      placeholder="Describe the role, responsibilities, and what makes your company great..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            <div class="char-count">
                                <span id="descCount">0</span> characters
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="requirements" class="required">Requirements</label>
                            <textarea id="requirements" name="requirements" class="form-control" rows="4" required
                                      placeholder="List the required qualifications, skills, and experience..."><?php echo htmlspecialchars($_POST['requirements'] ?? ''); ?></textarea>
                            <div class="char-count">
                                <span id="reqCount">0</span> characters
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="skills_required">Skills Required</label>
                            <textarea id="skills_required" name="skills_required" class="form-control" rows="3"
                                      placeholder="e.g., PHP, MySQL, JavaScript, React, Communication"><?php echo htmlspecialchars($_POST['skills_required'] ?? ''); ?></textarea>
                            <small style="color: #6b7280; display: block; margin-top: 0.25rem;">
                                Separate skills with commas. Used for AI matching with candidates.
                            </small>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="form-group">
                        <button type="submit" name="post_job" class="btn-submit">
                            <i class="fas fa-paper-plane"></i> Post Job
                        </button>
                    </div>
                    
                    <div style="text-align: center; margin-top: 1rem; color: #6b7280; font-size: 0.875rem;">
                        <p><i class="fas fa-info-circle"></i> Your job will be reviewed and go live within 24 hours.</p>
                    </div>
                </form>
            </div>
        </div>
    </main>
    
    <script>
    // Character counters
    const descTextarea = document.getElementById('description');
    const reqTextarea = document.getElementById('requirements');
    const descCount = document.getElementById('descCount');
    const reqCount = document.getElementById('reqCount');
    
    function updateCount() {
        descCount.textContent = descTextarea.value.length;
        reqCount.textContent = reqTextarea.value.length;
    }
    
    descTextarea.addEventListener('input', updateCount);
    reqTextarea.addEventListener('input', updateCount);
    
    // Initialize counts
    updateCount();
    
    // Set default deadline to 30 days from now
    const today = new Date();
    const defaultDeadline = new Date(today);
    defaultDeadline.setDate(today.getDate() + 30);
    
    const deadlineInput = document.getElementById('deadline');
    if(deadlineInput && !deadlineInput.value) {
        deadlineInput.value = defaultDeadline.toISOString().split('T')[0];
    }
    
    // Form validation
    function validateForm() {
        let isValid = true;
        const requiredFields = document.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if(!field.value.trim()) {
                isValid = false;
                field.style.borderColor = '#ef4444';
                
                // Add error message if not exists
                if(!field.nextElementSibling || !field.nextElementSibling.classList.contains('error-msg')) {
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'error-msg';
                    errorMsg.textContent = 'This field is required';
                    errorMsg.style.color = '#ef4444';
                    errorMsg.style.fontSize = '0.875rem';
                    errorMsg.style.marginTop = '0.25rem';
                    field.parentNode.insertBefore(errorMsg, field.nextSibling);
                }
            } else {
                field.style.borderColor = '#d1d5db';
                
                // Remove error message
                const errorMsg = field.nextElementSibling;
                if(errorMsg && errorMsg.classList.contains('error-msg')) {
                    errorMsg.remove();
                }
            }
        });
        
        if(!isValid) {
            alert('Please fill in all required fields marked with *');
            return false;
        }
        
        // Show loading
        const submitBtn = document.querySelector('button[name="post_job"]');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Posting...';
        submitBtn.disabled = true;
        
        return true;
    }
    
    // Real-time validation
    document.querySelectorAll('[required]').forEach(field => {
        field.addEventListener('input', function() {
            if(this.value.trim()) {
                this.style.borderColor = '#d1d5db';
                const errorMsg = this.nextElementSibling;
                if(errorMsg && errorMsg.classList.contains('error-msg')) {
                    errorMsg.remove();
                }
            }
        });
    });
    </script>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>