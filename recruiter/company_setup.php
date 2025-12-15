<?php
session_start();
require_once '../config/database.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'recruiter') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Check if company already exists
$check_stmt = $conn->prepare("SELECT * FROM companies WHERE user_id = ?");
$check_stmt->bind_param("i", $user_id);
$check_stmt->execute();
$existing_company = $check_stmt->get_result()->fetch_assoc();

if($existing_company) {
    header("Location: post_job.php");
    exit();
}

// Create company
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_company'])) {
    $company_name = trim($_POST['company_name']);
    $industry = trim($_POST['industry']);
    $description = trim($_POST['description']);
    $website = trim($_POST['website']);
    $location = trim($_POST['location']);
    
    if(empty($company_name)) {
        $error = "Company name is required!";
    } else {
        $stmt = $conn->prepare("INSERT INTO companies (user_id, company_name, industry, description, website, location) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $user_id, $company_name, $industry, $description, $website, $location);
        
        if($stmt->execute()) {
            $success = "Company profile created successfully!";
            header("refresh:2;url=post_job.php");
        } else {
            $error = "Error creating company profile. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Company - CareerPath</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="dashboard-main">
        <div class="container">
            <div class="dashboard-header">
                <h1><i class="fas fa-building"></i> Setup Your Company</h1>
                <p>Before posting jobs, let's set up your company profile</p>
            </div>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="setup-container">
                <div class="setup-card">
                    <div class="setup-header">
                        <div class="setup-icon">
                            <i class="fas fa-building fa-3x"></i>
                        </div>
                        <div class="setup-info">
                            <h2>Company Information</h2>
                            <p>This information will be shown to job seekers when they view your job postings</p>
                        </div>
                    </div>
                    
                    <form method="POST" class="setup-form">
                        <div class="form-section">
                            <h3><i class="fas fa-info-circle"></i> Basic Details</h3>
                            <div class="form-group">
                                <label for="company_name">Company Name *</label>
                                <input type="text" id="company_name" name="company_name" required 
                                       placeholder="Enter your company name">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="industry">Industry</label>
                                    <select id="industry" name="industry">
                                        <option value="">Select industry</option>
                                        <option value="technology">Technology</option>
                                        <option value="finance">Finance</option>
                                        <option value="healthcare">Healthcare</option>
                                        <option value="education">Education</option>
                                        <option value="retail">Retail</option>
                                        <option value="manufacturing">Manufacturing</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="location">Location</label>
                                    <input type="text" id="location" name="location" 
                                           placeholder="e.g., Kathmandu, Nepal">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3><i class="fas fa-globe"></i> Online Presence</h3>
                            <div class="form-group">
                                <label for="website">Website</label>
                                <input type="url" id="website" name="website" 
                                       placeholder="https://yourcompany.com">
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Company Description</label>
                                <textarea id="description" name="description" rows="4" 
                                          placeholder="Tell job seekers about your company culture, mission, and values..."></textarea>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="create_company" class="btn btn-primary btn-lg">
                                <i class="fas fa-check-circle"></i> Create Company Profile
                            </button>
                        </div>
                        
                        <div class="setup-note">
                            <p><i class="fas fa-info-circle"></i> <strong>Note:</strong> You can edit this information later from your recruiter dashboard.</p>
                        </div>
                    </form>
                </div>
                
                <div class="setup-preview">
                    <h3><i class="fas fa-eye"></i> Preview</h3>
                    <div class="company-preview-card">
                        <div class="preview-header">
                            <div class="preview-logo">
                                <i class="fas fa-building fa-2x"></i>
                            </div>
                            <div class="preview-info">
                                <h4 id="previewCompanyName">Your Company</h4>
                                <p id="previewIndustryLocation">Industry • Location</p>
                            </div>
                        </div>
                        <div class="preview-body">
                            <p id="previewDescriptionText">Company description will appear here...</p>
                            <div class="preview-website" id="previewWebsite">
                                <i class="fas fa-link"></i>
                                <span>yourcompany.com</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="setup-benefits">
                        <h4><i class="fas fa-star"></i> Benefits of Company Profile</h4>
                        <ul>
                            <li><i class="fas fa-check"></i> Build trust with job seekers</li>
                            <li><i class="fas fa-check"></i> Attract better candidates</li>
                            <li><i class="fas fa-check"></i> Showcase company culture</li>
                            <li><i class="fas fa-check"></i> Increase application rates</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script>
    // Real-time preview updates
    document.getElementById('company_name').addEventListener('input', function() {
        document.getElementById('previewCompanyName').textContent = this.value || 'Your Company';
    });
    
    document.getElementById('industry').addEventListener('change', function() {
        updatePreviewLocation();
    });
    
    document.getElementById('location').addEventListener('input', function() {
        updatePreviewLocation();
    });
    
    document.getElementById('description').addEventListener('input', function() {
        document.getElementById('previewDescriptionText').textContent = this.value || 'Company description will appear here...';
    });
    
    document.getElementById('website').addEventListener('input', function() {
        const website = this.value || 'yourcompany.com';
        document.getElementById('previewWebsite').innerHTML = `
            <i class="fas fa-link"></i>
            <span>${website.replace('https://', '').replace('http://', '')}</span>
        `;
    });
    
    function updatePreviewLocation() {
        const industry = document.getElementById('industry').value || 'Industry';
        const location = document.getElementById('location').value || 'Location';
        document.getElementById('previewIndustryLocation').textContent = `${industry} • ${location}`;
    }
    </script>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>