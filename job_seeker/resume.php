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
$user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// Get resume data if exists
$resume_stmt = $conn->prepare("SELECT * FROM resumes WHERE user_id = ? ORDER BY updated_at DESC LIMIT 1");
$resume_stmt->bind_param("i", $user_id);
$resume_stmt->execute();
$resume = $resume_stmt->get_result()->fetch_assoc();

// Function to get template colors
function getTemplateColors($template) {
    $templates = [
        'modern_blue' => [
            'primary' => '#1e3a8a',
            'secondary' => '#0f172a',
            'accent' => '#1e40af',
            'light' => '#e0e7ff',
        ],
        'professional_dark' => [
            'primary' => '#1e293b',
            'secondary' => '#0f172a',
            'accent' => '#334155',
            'light' => '#e2e8f0',
        ],
        'clean_green' => [
            'primary' => '#059669',
            'secondary' => '#047857',
            'accent' => '#10b981',
            'light' => '#d1fae5',
        ],
    ];
    return $templates[$template] ?? $templates['modern_blue'];
}

// Convert hex color to RGB array
function hexToRgb($hex) {
    $hex = ltrim($hex, '#');
    $hex = str_pad($hex, 6, '0', STR_PAD_LEFT);
    return [
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2))
    ];
}

// Save resume data
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_resume'])) {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone_num = trim($_POST['phone_num'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $professional_summary = trim($_POST['professional_summary'] ?? '');
    $experiences = trim($_POST['experiences'] ?? '');
    $education_data = trim($_POST['education_data'] ?? '');
    $skills_data = trim($_POST['skills_data'] ?? '');
    $certifications = trim($_POST['certifications'] ?? '');
    $template = trim($_POST['template'] ?? 'modern_blue');
    
    if(empty($full_name) || empty($email)) {
        $error = "Full name and email are required fields.";
    } else {
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
            if($resume_update->execute()) {
                $success = "Resume updated successfully!";
            } else {
                $error = "Error updating resume: " . $resume_update->error;
            }
        } else {
            $resume_insert = $conn->prepare("
                INSERT INTO resumes 
                (user_id, full_name, email, phone, location, professional_summary, 
                 experiences, education, skills, certifications, template, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $resume_insert->bind_param(
                "issssssssss", 
                $user_id, $full_name, $email, $phone_num, $location, $professional_summary, 
                $experiences, $education_data, $skills_data, $certifications, $template
            );
            if($resume_insert->execute()) {
                $success = "Resume created successfully!";
            } else {
                $error = "Error creating resume: " . $resume_insert->error;
            }
        }
        
        if($success) {
            $resume_stmt->execute();
            $resume = $resume_stmt->get_result()->fetch_assoc();
        }
    }
}

// Download resume as PDF
if(isset($_GET['download_pdf']) && $_GET['download_pdf'] == '1') {
    if(!$resume) {
        die("No resume found to download");
    }
    
    try {
        // Load TCPDF with proper initialization
        require_once('tcpdf_init.php');
        
        // Get template colors
        $colors = getTemplateColors($resume['template']);
        $primaryRgb = hexToRgb($colors['primary']);
        $secondaryRgb = hexToRgb($colors['secondary']);
        
        $pdf = new \TCPDF();
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(12, 12, 12);
        $pdf->SetAutoPageBreak(TRUE, 12);
        $pdf->AddPage();
        $pdf->SetFont('courier', '', 10);
        
        // Header with name - using template primary color
        $pdf->SetFont('courier', 'B', 18);
        $pdf->SetTextColor($primaryRgb[0], $primaryRgb[1], $primaryRgb[2]);
        $pdf->Cell(0, 8, strtoupper($resume['full_name']), 0, 1, 'C');
        
        // Contact info
        $pdf->SetFont('courier', '', 9);
        $pdf->SetTextColor(71, 85, 105);
        $contact_parts = [];
        if($resume['location']) $contact_parts[] = $resume['location'];
        if($resume['phone']) $contact_parts[] = $resume['phone'];
        if($resume['email']) $contact_parts[] = $resume['email'];
        $pdf->Cell(0, 5, implode(' | ', $contact_parts), 0, 1, 'C');
        $pdf->SetDrawColor(225, 232, 240);
        $pdf->Line(12, $pdf->GetY() + 2, 198, $pdf->GetY() + 2);
        $pdf->Ln(5);
        
        // Professional Summary
        if(!empty($resume['professional_summary'])) {
            $pdf->SetFont('courier', 'B', 11);
            $pdf->SetTextColor($primaryRgb[0], $primaryRgb[1], $primaryRgb[2]);
            $pdf->Cell(0, 6, 'PROFESSIONAL SUMMARY', 0, 1);
            $pdf->SetFont('courier', '', 10);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->MultiCell(0, 4, $resume['professional_summary'], 0, 'J');
            $pdf->Ln(3);
        }
        
        // Experience
        if(!empty($resume['experiences'])) {
            $pdf->SetFont('courier', 'B', 11);
            $pdf->SetTextColor($primaryRgb[0], $primaryRgb[1], $primaryRgb[2]);
            $pdf->Cell(0, 6, 'WORK EXPERIENCE', 0, 1);
            $pdf->SetFont('courier', '', 10);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->MultiCell(0, 4, $resume['experiences'], 0, 'J');
            $pdf->Ln(3);
        }
        
        // Education
        if(!empty($resume['education'])) {
            $pdf->SetFont('courier', 'B', 11);
            $pdf->SetTextColor($primaryRgb[0], $primaryRgb[1], $primaryRgb[2]);
            $pdf->Cell(0, 6, 'EDUCATION', 0, 1);
            $pdf->SetFont('courier', '', 10);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->MultiCell(0, 4, $resume['education'], 0, 'J');
            $pdf->Ln(3);
        }
        
        // Skills
        if(!empty($resume['skills'])) {
            $pdf->SetFont('courier', 'B', 11);
            $pdf->SetTextColor($primaryRgb[0], $primaryRgb[1], $primaryRgb[2]);
            $pdf->Cell(0, 6, 'SKILLS', 0, 1);
            $pdf->SetFont('courier', '', 10);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->MultiCell(0, 4, $resume['skills'], 0, 'J');
            $pdf->Ln(3);
        }
        
        // Certifications
        if(!empty($resume['certifications'])) {
            $pdf->SetFont('courier', 'B', 11);
            $pdf->SetTextColor($primaryRgb[0], $primaryRgb[1], $primaryRgb[2]);
            $pdf->Cell(0, 6, 'CERTIFICATIONS & AWARDS', 0, 1);
            $pdf->SetFont('courier', '', 10);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->MultiCell(0, 4, $resume['certifications'], 0, 'J');
        }
        
        $filename = str_replace(' ', '_', $resume['full_name']) . '_Resume.pdf';
        $pdf->Output($filename, 'D');
        exit();
        
    } catch(Exception $e) {
        die("Error generating PDF: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resume Builder - CareerPath</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .resume-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 20px;
        }

        .resume-header {
            background: linear-gradient(135deg, #1e3a8a, #0f172a);
            color: white;
            padding: 2.5rem 2rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(30, 58, 138, 0.15);
        }

        .resume-header h1 {
            margin: 0;
            font-size: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .resume-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
            font-size: 1.05rem;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
        }

        .alert-success {
            background-color: rgba(5, 150, 105, 0.1);
            border-left: 4px solid #059669;
            color: #065f46;
        }

        .alert-error {
            background-color: rgba(220, 38, 38, 0.1);
            border-left: 4px solid #dc2626;
            color: #7f1d1d;
        }

        .resume-content {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 2rem;
            align-items: start;
        }

        @media (max-width: 1024px) {
            .resume-content {
                grid-template-columns: 1fr;
            }
        }

        .resume-form {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .form-section h3 {
            color: #1e3a8a;
            border-bottom: 2px solid #dbeafe;
            padding-bottom: 0.75rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.15rem;
        }

        .form-section h3 i {
            font-size: 1.25rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-row.full {
            grid-template-columns: 1fr;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .form-group label {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.95rem;
        }

        .form-group label .required {
            color: #dc2626;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 0.5rem;
            font-family: inherit;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #1e3a8a;
            box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .help-text {
            font-size: 0.85rem;
            color: #64748b;
            margin-top: 0.25rem;
        }

        .template-selector {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .template-selector {
                grid-template-columns: 1fr;
            }
        }

        .template-option {
            position: relative;
        }

        .template-option input[type="radio"] {
            display: none;
        }

        .template-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }

        .template-option input[type="radio"]:checked + .template-label {
            border-color: #1e3a8a;
            background-color: rgba(30, 58, 138, 0.05);
            box-shadow: 0 0 0 2px rgba(30, 58, 138, 0.1);
        }

        .template-preview {
            width: 100%;
            height: 70px;
            border-radius: 0.4rem;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .template-name {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.9rem;
            text-align: center;
        }

        .sidebar {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .sidebar h3 {
            color: #1e3a8a;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .resume-status {
            padding: 1rem;
            background: #f0fdf4;
            border-left: 4px solid #059669;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .resume-status.no-resume {
            background: #fef2f2;
            border-color: #dc2626;
        }

        .status-label {
            font-size: 0.85rem;
            color: #4b5563;
            font-weight: 500;
        }

        .status-value {
            font-size: 0.95rem;
            color: #1e293b;
            margin-top: 0.25rem;
            font-weight: 600;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .btn {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            border: none;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            text-decoration: none;
            font-size: 0.95rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #1e3a8a, #1e40af);
            color: white;
            box-shadow: 0 4px 12px rgba(30, 58, 138, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(30, 58, 138, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #059669, #047857);
            color: white;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(5, 150, 105, 0.4);
        }

        .btn-outline {
            background: white;
            border: 2px solid #e2e8f0;
            color: #1e293b;
        }

        .btn-outline:hover {
            border-color: #1e3a8a;
            background: #f8fafc;
        }

        .tips-box {
            background: #f0f9ff;
            border-left: 4px solid #0ea5e9;
            border-radius: 0.5rem;
            padding: 1rem;
            font-size: 0.9rem;
            color: #164e63;
        }

        .tips-box h4 {
            color: #0c4a6e;
            margin: 0 0 0.5rem 0;
            font-size: 0.95rem;
        }

        .tips-box ul {
            margin: 0;
            padding-left: 1.2rem;
        }

        .tips-box li {
            margin: 0.25rem 0;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #e2e8f0;
        }

        .form-actions button {
            flex: 1;
        }

        @media (max-width: 768px) {
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/navigation.php'; ?>

    <div class="resume-container">
        <!-- Header -->
        <div class="resume-header">
            <h1><i class="fas fa-file-pdf"></i> Resume Builder</h1>
            <p>Create, edit, and download your professional resume in minutes</p>
        </div>

        <!-- Messages -->
        <?php if($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span><?php echo htmlspecialchars($success); ?></span>
        </div>
        <?php endif; ?>

        <?php if($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
        <?php endif; ?>

        <!-- Main Content -->
        <div class="resume-content">
            <!-- Resume Form -->
            <div class="resume-form">
                <form method="POST" id="resumeForm">
                    <input type="hidden" name="save_resume" value="1">

                    <!-- Template Selection -->
                    <div class="form-section">
                        <h3><i class="fas fa-palette"></i> Choose Template</h3>
                        <div class="template-selector">
                            <div class="template-option">
                                <input type="radio" id="template_modern" name="template" value="modern_blue" <?php echo (!$resume || $resume['template'] == 'modern_blue') ? 'checked' : ''; ?>>
                                <label for="template_modern" class="template-label">
                                    <div class="template-preview" style="background: linear-gradient(135deg, #1e3a8a, #0f172a);"></div>
                                    <span class="template-name">Modern Blue</span>
                                </label>
                            </div>
                            <div class="template-option">
                                <input type="radio" id="template_dark" name="template" value="professional_dark" <?php echo ($resume && $resume['template'] == 'professional_dark') ? 'checked' : ''; ?>>
                                <label for="template_dark" class="template-label">
                                    <div class="template-preview" style="background: #1e293b;"></div>
                                    <span class="template-name">Professional Dark</span>
                                </label>
                            </div>
                            <div class="template-option">
                                <input type="radio" id="template_green" name="template" value="clean_green" <?php echo ($resume && $resume['template'] == 'clean_green') ? 'checked' : ''; ?>>
                                <label for="template_green" class="template-label">
                                    <div class="template-preview" style="background: linear-gradient(135deg, #059669, #047857);"></div>
                                    <span class="template-name">Clean Green</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="form-section">
                        <h3><i class="fas fa-user"></i> Contact Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="full_name">Full Name <span class="required">*</span></label>
                                <input type="text" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($resume['full_name'] ?? $user['name'] ?? ''); ?>" placeholder="John Doe">
                            </div>
                            <div class="form-group">
                                <label for="location">Location</label>
                                <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($resume['location'] ?? ''); ?>" placeholder="New York, USA">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email Address <span class="required">*</span></label>
                                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($resume['email'] ?? $user['email'] ?? ''); ?>" placeholder="john@example.com">
                            </div>
                            <div class="form-group">
                                <label for="phone_num">Phone Number</label>
                                <input type="tel" id="phone_num" name="phone_num" value="<?php echo htmlspecialchars($resume['phone'] ?? $user['phone'] ?? ''); ?>" placeholder="+1 (555) 123-4567">
                            </div>
                        </div>
                    </div>

                    <!-- Professional Summary -->
                    <div class="form-section">
                        <h3><i class="fas fa-briefcase"></i> Professional Summary</h3>
                        <div class="form-row full">
                            <div class="form-group">
                                <label for="professional_summary">Summary</label>
                                <textarea id="professional_summary" name="professional_summary" placeholder="Write a compelling professional summary (2-3 sentences) that highlights your key achievements and career goals..."><?php echo htmlspecialchars($resume['professional_summary'] ?? ''); ?></textarea>
                                <div class="help-text">Keep it concise - recruiters typically spend 10-15 seconds here</div>
                            </div>
                        </div>
                    </div>

                    <!-- Work Experience -->
                    <div class="form-section">
                        <h3><i class="fas fa-suitcase"></i> Work Experience</h3>
                        <div class="form-row full">
                            <div class="form-group">
                                <label for="experiences">Experience</label>
                                <textarea id="experiences" name="experiences" placeholder="Senior Developer - Tech Corp (2022 - Present)
• Led development of cloud migration project
• Improved system performance by 40%
• Mentored 5 junior developers

Software Engineer - StartUp Inc (2020 - 2022)
• Designed and implemented REST APIs
• Collaborated with product team"><?php echo htmlspecialchars($resume['experiences'] ?? ''); ?></textarea>
                                <div class="help-text">Use bullet points and quantify achievements when possible</div>
                            </div>
                        </div>
                    </div>

                    <!-- Education -->
                    <div class="form-section">
                        <h3><i class="fas fa-graduation-cap"></i> Education</h3>
                        <div class="form-row full">
                            <div class="form-group">
                                <label for="education_data">Education</label>
                                <textarea id="education_data" name="education_data" placeholder="Bachelor of Science in Computer Science
University of Technology (2016 - 2020)
GPA: 3.8/4.0 | Honors: Cum Laude"><?php echo htmlspecialchars($resume['education'] ?? ''); ?></textarea>
                                <div class="help-text">Include degree, school, year, and honors if applicable</div>
                            </div>
                        </div>
                    </div>

                    <!-- Skills -->
                    <div class="form-section">
                        <h3><i class="fas fa-star"></i> Skills</h3>
                        <div class="form-row full">
                            <div class="form-group">
                                <label for="skills_data">Skills</label>
                                <textarea id="skills_data" name="skills_data" placeholder="Technical: Python, JavaScript, React, Node.js, AWS
Soft Skills: Leadership, Problem-solving, Communication"><?php echo htmlspecialchars($resume['skills'] ?? ''); ?></textarea>
                                <div class="help-text">List key technical and soft skills separated by categories</div>
                            </div>
                        </div>
                    </div>

                    <!-- Certifications -->
                    <div class="form-section">
                        <h3><i class="fas fa-certificate"></i> Certifications & Awards</h3>
                        <div class="form-row full">
                            <div class="form-group">
                                <label for="certifications">Certifications (Optional)</label>
                                <textarea id="certifications" name="certifications" placeholder="AWS Solutions Architect (2023)
Docker Certified Associate (2022)
Best Innovator Award - Company Name (2021)"><?php echo htmlspecialchars($resume['certifications'] ?? ''); ?></textarea>
                                <div class="help-text">Professional credentials and awards that strengthen your profile</div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Resume
                        </button>
                        <button type="reset" class="btn btn-outline">
                            <i class="fas fa-redo"></i> Clear Form
                        </button>
                    </div>
                </form>
            </div>

            <!-- Sidebar -->
            <div class="sidebar">
                <h3><i class="fas fa-info-circle"></i> Resume Status</h3>

                <?php if($resume): ?>
                <div class="resume-status">
                    <div class="status-label"><i class="fas fa-check-circle" style="color: #059669;"></i> Resume Active</div>
                    <div class="status-value">Updated: <?php echo date('M d, Y', strtotime($resume['updated_at'])); ?></div>
                </div>
                <div class="action-buttons">
                    <a href="?download_pdf=1" class="btn btn-success">
                        <i class="fas fa-download"></i> Download PDF
                    </a>
                    <a href="preview_resume.php" class="btn btn-outline" target="_blank">
                        <i class="fas fa-eye"></i> Preview Resume
                    </a>
                </div>
                <?php else: ?>
                <div class="resume-status no-resume">
                    <div class="status-label"><i class="fas fa-circle-exclamation"></i> No Resume Yet</div>
                    <div class="status-value">Fill in the form and click Save to create</div>
                </div>
                <?php endif; ?>

                <div class="tips-box">
                    <h4><i class="fas fa-lightbulb"></i> Pro Tips</h4>
                    <ul>
                        <li>Be specific with achievements</li>
                        <li>Use action verbs (Led, Built, Managed)</li>
                        <li>Include metrics & numbers</li>
                        <li>Keep to 1-2 pages max</li>
                        <li>Proofread carefully</li>
                        <li>Tailor for each job</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        document.getElementById('resumeForm').addEventListener('submit', function(e) {
            const fullName = document.getElementById('full_name').value.trim();
            const email = document.getElementById('email').value.trim();

            if (!fullName || !email) {
                e.preventDefault();
                alert('Please fill in Full Name and Email fields');
                return false;
            }
        });
    </script>
</body>
</html>