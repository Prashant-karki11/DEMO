<?php
session_start();
require_once '../config/database.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'job_seeker') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Update profile
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $bio = trim($_POST['bio']);
    $skills = trim($_POST['skills']);
    $experience = trim($_POST['experience']);
    $education = trim($_POST['education']);
    $resume_file = $_FILES['resume']['name'];
    
    // Handle file upload
    if($resume_file) {
        $target_dir = "../uploads/resumes/";
        $target_file = $target_dir . basename($resume_file);
        move_uploaded_file($_FILES["resume"]["tmp_name"], $target_file);
    }
    $update_stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, bio = ?, skills = ?, experience = ?, education = ? WHERE id = ?");
    $update_stmt->bind_param("ssssssi", $name, $phone, $bio, $skills, $experience, $education, $user_id);
    
    if($update_stmt->execute()) {
        $_SESSION['user_name'] = $name;
        $success = "Profile updated successfully!";
        
        // Refresh user data
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
    } else {
        $error = "Error updating profile. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - CareerPath</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/navigation.php'; ?>
    
    <main class="dashboard-main">
        <div class="container">
            <div class="profile-header">
                <h1>My Profile</h1>
                <p>Manage your personal and professional information</p>
            </div>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="profile-container">
                <div class="profile-sidebar">
                    <div class="profile-card">
                        <div class="profile-avatar">
                            <i class="fas fa-user-circle fa-4x"></i>
                        </div>
                        <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                        <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
                        <p class="profile-type">Job Seeker</p>
                        
                        <div class="profile-stats">
                            <div class="stat-item">
                                <span class="stat-number">0</span>
                                <span class="stat-label">Applications</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number"><?php echo $user['skills'] ? count(explode(',', $user['skills'])) : 0; ?></span>
                                <span class="stat-label">Skills</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="profile-actions">
                        <a href="resume.php" class="btn btn-primary btn-block">
                            <i class="fas fa-file-pdf"></i> Generate Resume
                        </a>
                        <a href="#" class="btn btn-outline btn-block">
                            <i class="fas fa-download"></i> Download Profile
                        </a>
                    </div>
                </div>
                
                <div class="profile-content">
                    <div class="profile-tabs">
                        <button class="tab-btn active" data-tab="personal">Personal Info</button>
                        <button class="tab-btn" data-tab="professional">Professional</button>
                        <button class="tab-btn" data-tab="resume">Resume</button>
                        <button class="tab-btn" data-tab="settings">Settings</button>
                    </div>
                    
                    <div class="tab-content active" id="personal">
                        <form method="POST" class="profile-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name">Full Name</label>
                                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                    <small>Email cannot be changed</small>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="location">Location</label>
                                    <input type="text" id="location" name="location" placeholder="Enter your city">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="bio">Bio / Summary</label>
                                <textarea id="bio" name="bio" rows="3" placeholder="Write a brief summary about yourself..."><?php echo htmlspecialchars($user['bio']); ?></textarea>
                            </div>
                            
                            <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                    
                    <div class="tab-content" id="professional">
                        <form method="POST" class="profile-form">
                            <div class="form-group">
                                <label for="skills">Skills (comma separated)</label>
                                <textarea id="skills" name="skills" rows="3" placeholder="e.g., PHP, MySQL, JavaScript, React, Python"><?php echo htmlspecialchars($user['skills']); ?></textarea>
                                <small>List your skills separated by commas. This helps our AI match you with relevant jobs.</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="experience">Work Experience</label>
                                <textarea id="experience" name="experience" rows="5" placeholder="Describe your work experience..."><?php echo htmlspecialchars($user['experience']); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="education">Education</label>
                                <textarea id="education" name="education" rows="4" placeholder="Your educational background..."><?php echo htmlspecialchars($user['education']); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="career_goals">Career Goals</label>
                                <textarea id="career_goals" name="career_goals" rows="3" placeholder="Where do you see yourself in 5 years?"></textarea>
                            </div>
                            
                            <button type="submit" name="update_profile" class="btn btn-primary">Update Professional Info</button>
                        </form>
                    </div>
                    
                    <div class="tab-content" id="resume">
                        <div class="resume-section">
                            <h3>Resume Builder</h3>
                            <p>Create a professional resume using our templates</p>
                            
                            <div class="resume-templates">
                                <div class="template-card">
                                    <div class="template-preview">
                                        <div class="template-header" style="background: #2563eb;"></div>
                                        <div class="template-body">
                                            <div class="template-line"></div>
                                            <div class="template-line short"></div>
                                            <div class="template-line"></div>
                                        </div>
                                    </div>
                                    <h4>Modern Blue</h4>
                                    <button class="btn btn-outline use-template" data-template="modern_blue">
                                        <i class="fas fa-eye"></i> Preview
                                    </button>
                                </div>
                                
                                <div class="template-card">
                                    <div class="template-preview">
                                        <div class="template-header" style="background: #1e293b;"></div>
                                        <div class="template-body">
                                            <div class="template-line"></div>
                                            <div class="template-line short"></div>
                                            <div class="template-line"></div>
                                        </div>
                                    </div>
                                    <h4>Professional Dark</h4>
                                    <button class="btn btn-outline use-template" data-template="professional_dark">
                                        <i class="fas fa-eye"></i> Preview
                                    </button>
                                </div>
                                
                                <div class="template-card">
                                    <div class="template-preview">
                                        <div class="template-header" style="background: #0f766e;"></div>
                                        <div class="template-body">
                                            <div class="template-line"></div>
                                            <div class="template-line short"></div>
                                            <div class="template-line"></div>
                                        </div>
                                    </div>
                                    <h4>Clean Green</h4>
                                    <button class="btn btn-outline use-template" data-template="clean_green">
                                        <i class="fas fa-eye"></i> Preview
                                    </button>
                                </div>
                            </div>
                            
                            <div class="resume-actions">
                                <button class="btn btn-primary" id="generateResume">
                                    <i class="fas fa-file-pdf"></i> Generate PDF Resume
                                </button>
                                <button class="btn btn-secondary" id="uploadResume">
                                    <i class="fas fa-upload"></i> Upload Existing Resume
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tab-content" id="settings">
                        <div class="settings-section">
                            <h3>Account Settings</h3>
                            
                            <div class="settings-group">
                                <h4>Change Password</h4>
                                <form class="settings-form">
                                    <div class="form-group">
                                        <label for="current_password">Current Password</label>
                                        <input type="password" id="current_password">
                                    </div>
                                    <div class="form-group">
                                        <label for="new_password">New Password</label>
                                        <input type="password" id="new_password">
                                    </div>
                                    <div class="form-group">
                                        <label for="confirm_new_password">Confirm New Password</label>
                                        <input type="password" id="confirm_new_password">
                                    </div>
                                    <button type="button" class="btn btn-primary">Update Password</button>
                                </form>
                            </div>
                            
                            <div class="settings-group">
                                <h4>Notifications</h4>
                                <div class="notification-settings">
                                    <label class="checkbox">
                                        <input type="checkbox" checked> Email notifications for new job matches
                                    </label>
                                    <label class="checkbox">
                                        <input type="checkbox" checked> Application status updates
                                    </label>
                                    <label class="checkbox">
                                        <input type="checkbox"> Weekly career tips
                                    </label>
                                    <label class="checkbox">
                                        <input type="checkbox" checked> Mentor recommendations
                                    </label>
                                </div>
                            </div>
                            
                            <div class="settings-group">
                                <h4>Privacy Settings</h4>
                                <div class="privacy-settings">
                                    <label class="checkbox">
                                        <input type="checkbox" checked> Make profile visible to recruiters
                                    </label>
                                    <label class="checkbox">
                                        <input type="checkbox"> Show contact information
                                    </label>
                                    <label class="checkbox">
                                        <input type="checkbox" checked> Allow mentorship requests
                                    </label>
                                </div>
                            </div>
                            
                            <div class="settings-group danger-zone">
                                <h4>Danger Zone</h4>
                                <p>Once you delete your account, there is no going back. Please be certain.</p>
                                <button class="btn btn-danger">
                                    <i class="fas fa-trash"></i> Delete My Account
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/profile.js"></script>
</body>
</html>