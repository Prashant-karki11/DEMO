<?php
// resume_builder.php
session_start();
require_once '../config/database.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'job_seeker') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user data for resume - CORRECTED THE COLUMN NAME
$user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?"); // CHANGED: user_id instead of user id
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_data = $user_stmt->get_result()->fetch_assoc();

// Check if user data exists
if(!$user_data) {
    // Initialize empty data array
    $user_data = [
        'name' => '',
        'bio' => '',
        'phone' => '',
        'skills' => '',
        'education' => '',
        'experience' => 0,
        
    ];
}

// Available templates
$templates = [
    'professional' => 'Professional Clean Layout',
    'modern' => 'Modern Design',
    'creative' => 'Creative Portfolio Style',
    'academic' => 'Academic/Research Focused'
];

// Generate PDF resume
if(isset($_POST['generate_resume'])) {
    $template = $_POST['template'];
    $include_photo = isset($_POST['include_photo']) ? 1 : 0;
    $sections = $_POST['sections'] ?? ['education', 'experience', 'skills'];
    
    // Generate resume using selected template
    $pdf_content = generateResumePDF($user_data, $template, $sections, $include_photo);
    
    // Save to database
    $save_stmt = $conn->prepare("INSERT INTO generated_resumes (user_id, template, file_path, generated_date) VALUES (?, ?, ?, NOW())");
    $file_path = "../uploads/resumes/generated/resume_" . $user_id . "_" . time() . ".pdf";
    file_put_contents($file_path, $pdf_content);
    $save_stmt->bind_param("iss", $user_id, $template, $file_path);
    $save_stmt->execute();
    
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="My_Resume.pdf"');
    echo $pdf_content;
    exit();
}

function generateResumePDF($user_data, $template, $sections, $include_photo) {
    // Check if TCPDF library exists
    if(file_exists('../libraries/tcpdf/tcpdf.php')) {
        require_once('../libraries/tcpdf/tcpdf.php');
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('CareerPath');
        $pdf->SetAuthor($user_data['full_name']);
        $pdf->SetTitle('Resume - ' . $user_data['full_name']);
        
        $pdf->AddPage();
        
        // Simple HTML resume if TCPDF is available
        $html = generateResumeHTML($user_data, $template, $sections);
        $pdf->writeHTML($html, true, false, true, false, '');
        
        return $pdf->Output('', 'S');
    } else {
        // Fallback to simple HTML output
        return generateResumeHTML($user_data, $template, $sections, true);
    }
}

function generateResumeHTML($user_data, $template, $sections, $for_pdf = false) {
    $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Resume - ' . htmlspecialchars($user_data['full_name']) . '</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            .resume-container { max-width: 800px; margin: 0 auto; }
            .header { border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
            .name { font-size: 28px; font-weight: bold; color: #333; }
            .contact-info { margin-top: 10px; color: #666; }
            .section { margin-bottom: 25px; }
            .section-title { font-size: 18px; font-weight: bold; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-bottom: 10px; }
            .skill-item, .education-item, .experience-item { margin-bottom: 8px; }
            .date { color: #666; font-style: italic; }
        </style>
    </head>
    <body>
        <div class="resume-container">
            <div class="header">
                <div class="name">' . htmlspecialchars($user_data['full_name']) . '</div>
                <div class="contact-info">
                    ' . htmlspecialchars($user_data['email']) . ' | ' . htmlspecialchars($user_data['phone']) . '
                </div>
            </div>';
    
    if(in_array('skills', $sections) && !empty($user_data['skills'])) {
        $html .= '<div class="section">
            <div class="section-title">Skills</div>
            <div class="skill-item">' . nl2br(htmlspecialchars($user_data['skills'])) . '</div>
        </div>';
    }
    
    if(in_array('education', $sections) && !empty($user_data['education'])) {
        $html .= '<div class="section">
            <div class="section-title">Education</div>
            <div class="education-item">' . nl2br(htmlspecialchars($user_data['education'])) . '</div>
        </div>';
    }
    
    if(in_array('experience', $sections) && (!empty($user_data['experience_years']) || !empty($user_data['current_job_title']))) {
        $html .= '<div class="section">
            <div class="section-title">Experience</div>
            <div class="experience-item">';
        
        if(!empty($user_data['current_job_title'])) {
            $html .= '<strong>' . htmlspecialchars($user_data['current_job_title']) . '</strong><br>';
        }
        
        if(!empty($user_data['experience_years'])) {
            $html .= 'Experience: ' . htmlspecialchars($user_data['experience_years']) . ' years<br>';
        }
        
        $html .= '</div></div>';
    }
    
    if(!empty($user_data['certifications'])) {
        $html .= '<div class="section">
            <div class="section-title">Certifications</div>
            <div class="certification-item">' . nl2br(htmlspecialchars($user_data['certifications'])) . '</div>
        </div>';
    }
    
    if(!empty($user_data['languages'])) {
        $html .= '<div class="section">
            <div class="section-title">Languages</div>
            <div class="language-item">' . htmlspecialchars($user_data['languages']) . '</div>
        </div>';
    }
    
    $html .= '</div></body></html>';
    
    return $html;
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
        .template-preview {
            border: 1px solid #ddd;
            padding: 20px;
            margin: 10px 0;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .template-preview:hover {
            border-color: #3b82f6;
            background-color: #f8fafc;
        }
        .template-preview.selected {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }
        .template-radio {
            display: none;
        }
        .section-checkbox {
            margin-right: 10px;
        }
        .resume-preview {
            background: white;
            padding: 30px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-top: 20px;
            min-height: 400px;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/navigation.php'; ?>
    
    <main class="dashboard-main">
        <div class="container">
            <div class="dashboard-header">
                <h1><i class="fas fa-file-alt"></i> Resume Builder</h1>
                <p>Create a professional resume using our templates</p>
            </div>
            
            <?php if(empty($user_data['full_name']) || empty($user_data['skills'])): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Profile Incomplete:</strong> Please update your profile with personal details and skills before generating a resume.
                    <a href="profile.php" class="btn btn-outline btn-sm">Update Profile</a>
                </div>
            <?php endif; ?>
            
            <div class="resume-builder">
                <form method="POST" id="resumeForm">
                    <div class="builder-section">
                        <h3>1. Choose Template</h3>
                        <div class="templates-grid">
                            <?php foreach($templates as $key => $name): ?>
                            <label class="template-preview" for="template_<?php echo $key; ?>">
                                <input type="radio" name="template" value="<?php echo $key; ?>" 
                                       id="template_<?php echo $key; ?>" class="template-radio"
                                       <?php echo ($key == 'professional') ? 'checked' : ''; ?>>
                                <div class="template-header">
                                    <i class="fas fa-file-pdf fa-2x"></i>
                                    <h4><?php echo $name; ?></h4>
                                </div>
                                <div class="template-preview-content">
                                    <div class="preview-sample">
                                        <div class="sample-header" style="background: #3b82f6; height: 10px; margin-bottom: 10px;"></div>
                                        <div class="sample-name" style="height: 20px; background: #e5e7eb; margin-bottom: 5px;"></div>
                                        <div class="sample-section" style="height: 15px; background: #e5e7eb; margin-bottom: 15px;"></div>
                                        <div class="sample-item" style="height: 10px; background: #f3f4f6; margin-bottom: 5px;"></div>
                                        <div class="sample-item" style="height: 10px; background: #f3f4f6; margin-bottom: 5px;"></div>
                                    </div>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="builder-section">
                        <h3>2. Select Sections to Include</h3>
                        <div class="sections-list">
                            <label class="section-checkbox-label">
                                <input type="checkbox" name="sections[]" value="skills" class="section-checkbox" checked>
                                Skills & Expertise
                            </label>
                            <label class="section-checkbox-label">
                                <input type="checkbox" name="sections[]" value="education" class="section-checkbox" checked>
                                Education
                            </label>
                            <label class="section-checkbox-label">
                                <input type="checkbox" name="sections[]" value="experience" class="section-checkbox" checked>
                                Work Experience
                            </label>
                            <?php if(!empty($user_data['certifications'])): ?>
                            <label class="section-checkbox-label">
                                <input type="checkbox" name="sections[]" value="certifications" class="section-checkbox" checked>
                                Certifications
                            </label>
                            <?php endif; ?>
                            <?php if(!empty($user_data['languages'])): ?>
                            <label class="section-checkbox-label">
                                <input type="checkbox" name="sections[]" value="languages" class="section-checkbox" checked>
                                Languages
                            </label>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="builder-section">
                        <h3>3. Options</h3>
                        <label class="option-label">
                            <input type="checkbox" name="include_photo" value="1">
                            Include Profile Photo (if available)
                        </label>
                    </div>
                    
                    <div class="builder-section">
                        <h3>4. Preview & Generate</h3>
                        <div class="resume-preview" id="resumePreview">
                            <!-- Preview will be loaded here via JavaScript -->
                            <p class="text-center">Select options to see preview...</p>
                        </div>
                        
                        <div class="builder-actions">
                            <button type="button" class="btn btn-outline" id="previewResume">
                                <i class="fas fa-eye"></i> Preview
                            </button>
                            <button type="submit" name="generate_resume" class="btn btn-primary">
                                <i class="fas fa-download"></i> Download PDF
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <?php if(file_exists('../libraries/tcpdf/tcpdf.php')): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    PDF generation requires TCPDF library. Make sure it's installed in the libraries folder.
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    TCPDF library not found. Resume will be generated as HTML instead of PDF.
                    <a href="https://github.com/tecnickcom/TCPDF" target="_blank" class="btn btn-outline btn-sm">Download TCPDF</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/script.js"></script>
    <script>
        // Template selection
        document.querySelectorAll('.template-radio').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.template-preview').forEach(preview => {
                    preview.classList.remove('selected');
                });
                if(this.checked) {
                    this.closest('.template-preview').classList.add('selected');
                }
            });
        });
        
        // Initialize first template as selected
        document.querySelector('.template-radio:checked').closest('.template-preview').classList.add('selected');
        
        // Preview functionality
        document.getElementById('previewResume').addEventListener('click', function() {
            const formData = new FormData(document.getElementById('resumeForm'));
            const previewDiv = document.getElementById('resumePreview');
            
            previewDiv.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Generating preview...</div>';
            
            // AJAX call to generate preview
            fetch('resume_preview.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                previewDiv.innerHTML = html;
            })
            .catch(error => {
                previewDiv.innerHTML = '<div class="alert alert-error">Error generating preview: ' + error + '</div>';
            });
        });
    </script>
</body>
</html>