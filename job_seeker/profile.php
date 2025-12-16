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

// Get resume data if exists
$resume_stmt = $conn->prepare("
    SELECT * FROM resumes WHERE user_id = ? ORDER BY created_at DESC LIMIT 1
");
$resume_stmt->bind_param("i", $user_id);
$resume_stmt->execute();
$resume = $resume_stmt->get_result()->fetch_assoc();

// Update profile
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $bio = trim($_POST['bio']);
    $skills = trim($_POST['skills']);
    $experience = trim($_POST['experience']);
    $education = trim($_POST['education']);
    
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

// Save resume data
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_resume'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone_num = trim($_POST['phone_num']);
    $location = trim($_POST['location']);
    $professional_summary = trim($_POST['professional_summary']);
    $experiences = trim($_POST['experiences']);
    $education_data = trim($_POST['education_data']);
    $skills_data = trim($_POST['skills_data']);
    $certifications = trim($_POST['certifications']);
    $template = trim($_POST['template']);
    
    // Check if resume exists
    $check_stmt = $conn->prepare("SELECT id FROM resumes WHERE user_id = ?");
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $existing = $check_stmt->get_result()->fetch_assoc();
    
    if($existing) {
        $resume_update = $conn->prepare("
            UPDATE resumes SET 
            full_name = ?, email = ?, phone = ?, location = ?, 
            professional_summary = ?, experiences = ?, education = ?, 
            skills = ?, certifications = ?, template = ?, updated_at = NOW()
            WHERE user_id = ?
        ");
        $resume_update->bind_param(
            "ssssssssssi", 
            $full_name, $email, $phone_num, $location, $professional_summary, 
            $experiences, $education_data, $skills_data, $certifications, $template, $user_id
        );
        $resume_update->execute();
    } else {
        $resume_insert = $conn->prepare("
            INSERT INTO resumes 
            (user_id, full_name, email, phone, location, professional_summary, 
             experiences, education, skills, certifications, template, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $resume_insert->bind_param(
            "isssssssss", 
            $user_id, $full_name, $email, $phone_num, $location, $professional_summary, 
            $experiences, $education_data, $skills_data, $certifications, $template
        );
        $resume_insert->execute();
    }
    
    $success = "Resume saved successfully!";
    
    // Refresh resume data
    $resume_stmt->execute();
    $resume = $resume_stmt->get_result()->fetch_assoc();
}

// Download resume as PDF
if(isset($_GET['download_pdf']) && $_GET['download_pdf'] == '1') {
    if(!$resume) {
        die("Resume not found");
    }
    
    // Include TCPDF or use alternative library
    require_once('../vendor/autoload.php');
    
    $pdf = new \TCPDF();
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(TRUE, 15);
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 16);
    
    // Header with name
    $pdf->SetTextColor(30, 58, 138);
    $pdf->Cell(0, 10, $resume['full_name'], 0, 1, 'C');
    
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(71, 85, 105);
    $contact = $resume['location'] . ' | ' . $resume['phone'] . ' | ' . $resume['email'];
    $pdf->Cell(0, 5, $contact, 0, 1, 'C');
    $pdf->Ln(5);
    
    // Professional Summary
    if($resume['professional_summary']) {
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetTextColor(30, 58, 138);
        $pdf->Cell(0, 8, 'PROFESSIONAL SUMMARY', 0, 1);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(0, 5, $resume['professional_summary'], 0, 'J');
        $pdf->Ln(3);
    }
    
    // Experience
    if($resume['experiences']) {
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetTextColor(30, 58, 138);
        $pdf->Cell(0, 8, 'WORK EXPERIENCE', 0, 1);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(0, 5, $resume['experiences'], 0, 'J');
        $pdf->Ln(3);
    }
    
    // Education
    if($resume['education']) {
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetTextColor(30, 58, 138);
        $pdf->Cell(0, 8, 'EDUCATION', 0, 1);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(0, 5, $resume['education'], 0, 'J');
        $pdf->Ln(3);
    }
    
    // Skills
    if($resume['skills']) {
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetTextColor(30, 58, 138);
        $pdf->Cell(0, 8, 'SKILLS', 0, 1);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(0, 5, $resume['skills'], 0, 'J');
        $pdf->Ln(3);
    }
    
    // Certifications
    if($resume['certifications']) {
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetTextColor(30, 58, 138);
        $pdf->Cell(0, 8, 'CERTIFICATIONS', 0, 1);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(0, 5, $resume['certifications'], 0, 'J');
    }
    
    $pdf->Output($resume['full_name'] . '_Resume.pdf', 'D');
    exit();
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
                            <h3><i class="fas fa-file-pdf"></i> Resume Builder</h3>
                            <p>Create a professional resume that you can download as PDF</p>
                            
                            <form method="POST" class="resume-builder-form" id="resumeForm">
                                <input type="hidden" name="save_resume" value="1">
                                
                                <!-- Template Selection -->
                                <div class="form-group">
                                    <label>Choose Template</label>
                                    <div class="template-selection">
                                        <div class="template-option">
                                            <input type="radio" id="template_modern" name="template" value="modern_blue" <?php echo ($resume && $resume['template'] == 'modern_blue') ? 'checked' : ''; ?>>
                                            <label for="template_modern" class="template-label">
                                                <span class="template-preview" style="background: linear-gradient(135deg, #1e3a8a, #0f172a);"></span>
                                                Modern Blue
                                            </label>
                                        </div>
                                        <div class="template-option">
                                            <input type="radio" id="template_dark" name="template" value="professional_dark" <?php echo ($resume && $resume['template'] == 'professional_dark') ? 'checked' : ''; ?>>
                                            <label for="template_dark" class="template-label">
                                                <span class="template-preview" style="background: #1e293b;"></span>
                                                Professional Dark
                                            </label>
                                        </div>
                                        <div class="template-option">
                                            <input type="radio" id="template_green" name="template" value="clean_green" checked>
                                            <label for="template_green" class="template-label">
                                                <span class="template-preview" style="background: linear-gradient(135deg, #059669, #047857);"></span>
                                                Clean Green
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Contact Information -->
                                <div class="resume-section-header">
                                    <h4><i class="fas fa-user"></i> Contact Information</h4>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="full_name">Full Name *</label>
                                        <input type="text" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($resume['full_name'] ?? $user['name']); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="location">Location</label>
                                        <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($resume['location'] ?? ''); ?>" placeholder="e.g., New York, USA">
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="email">Email *</label>
                                        <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($resume['email'] ?? $user['email']); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="phone_num">Phone Number</label>
                                        <input type="tel" id="phone_num" name="phone_num" value="<?php echo htmlspecialchars($resume['phone'] ?? $user['phone']); ?>">
                                    </div>
                                </div>
                                
                                <!-- Professional Summary -->
                                <div class="resume-section-header">
                                    <h4><i class="fas fa-briefcase"></i> Professional Summary</h4>
                                </div>
                                
                                <div class="form-group">
                                    <label for="professional_summary">Summary</label>
                                    <textarea id="professional_summary" name="professional_summary" rows="4" placeholder="Write a brief summary of your professional background and career objectives..."><?php echo htmlspecialchars($resume['professional_summary'] ?? ''); ?></textarea>
                                </div>
                                
                                <!-- Work Experience -->
                                <div class="resume-section-header">
                                    <h4><i class="fas fa-suitcase"></i> Work Experience</h4>
                                </div>
                                
                                <div class="form-group">
                                    <label for="experiences">Work Experience</label>
                                    <textarea id="experiences" name="experiences" rows="6" placeholder="Example:
Senior Software Engineer - XYZ Company (2022-Present)
• Led development of microservices architecture
• Mentored junior developers
• Improved system performance by 40%

Software Engineer - ABC Corp (2020-2022)
• Developed and maintained web applications
• Collaborated with cross-functional teams
• Implemented automated testing solutions"><?php echo htmlspecialchars($resume['experiences'] ?? ''); ?></textarea>
                                </div>
                                
                                <!-- Education -->
                                <div class="resume-section-header">
                                    <h4><i class="fas fa-graduation-cap"></i> Education</h4>
                                </div>
                                
                                <div class="form-group">
                                    <label for="education_data">Education</label>
                                    <textarea id="education_data" name="education_data" rows="4" placeholder="Example:
Bachelor of Science in Computer Science
University of Technology (2018-2020)
GPA: 3.8/4.0 | Honors: Cum Laude

Diploma in Information Technology
College Name (2016-2018)"><?php echo htmlspecialchars($resume['education'] ?? ''); ?></textarea>
                                </div>
                                
                                <!-- Skills -->
                                <div class="resume-section-header">
                                    <h4><i class="fas fa-star"></i> Skills</h4>
                                </div>
                                
                                <div class="form-group">
                                    <label for="skills_data">Skills</label>
                                    <textarea id="skills_data" name="skills_data" rows="3" placeholder="Example:
Technical: PHP, JavaScript, MySQL, React, Docker, AWS, Git
Soft Skills: Leadership, Communication, Problem Solving, Time Management"><?php echo htmlspecialchars($resume['skills'] ?? $user['skills']); ?></textarea>
                                </div>
                                
                                <!-- Certifications -->
                                <div class="resume-section-header">
                                    <h4><i class="fas fa-certificate"></i> Certifications & Awards</h4>
                                </div>
                                
                                <div class="form-group">
                                    <label for="certifications">Certifications (Optional)</label>
                                    <textarea id="certifications" name="certifications" rows="3" placeholder="Example:
AWS Solutions Architect Associate - Amazon (2023)
Docker Certified Associate - Docker (2022)
Scrum Master Certification - Scrum Alliance (2021)"><?php echo htmlspecialchars($resume['certifications'] ?? ''); ?></textarea>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="resume-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Resume
                                    </button>
                                    <?php if($resume): ?>
                                    <a href="?download_pdf=1" class="btn btn-success" style="background: linear-gradient(135deg, #059669, #047857);">
                                        <i class="fas fa-download"></i> Download PDF
                                    </a>
                                    <a href="preview_resume.php" class="btn btn-secondary" target="_blank">
                                        <i class="fas fa-eye"></i> Preview
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </form>
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