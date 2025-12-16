<?php
session_start();
require_once '../config/database.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'job_seeker') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get resume data
$resume_stmt = $conn->prepare("
    SELECT * FROM resumes WHERE user_id = ? ORDER BY created_at DESC LIMIT 1
");
$resume_stmt->bind_param("i", $user_id);
$resume_stmt->execute();
$resume = $resume_stmt->get_result()->fetch_assoc();

if(!$resume) {
    header("Location: profile.php");
    exit();
}

// Template colors
$templates = [
    'modern_blue' => [
        'primary' => '#1e3a8a',
        'secondary' => '#0f172a',
        'accent' => '#1e40af',
        'gradient' => 'linear-gradient(135deg, #1e3a8a, #0f172a)',
    ],
    'professional_dark' => [
        'primary' => '#1e293b',
        'secondary' => '#0f172a',
        'accent' => '#334155',
        'gradient' => 'linear-gradient(135deg, #1e293b, #0f172a)',
    ],
    'clean_green' => [
        'primary' => '#059669',
        'secondary' => '#047857',
        'accent' => '#10b981',
        'gradient' => 'linear-gradient(135deg, #059669, #047857)',
    ],
];

$template = $resume['template'] ?? 'modern_blue';
$colors = $templates[$template] ?? $templates['modern_blue'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resume Preview - CareerPath</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .resume-preview-container {
            max-width: 900px;
            margin: 2rem auto;
            background: white;
            border-radius: 1rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .resume-header {
            padding: 2rem;
            background: <?php echo $colors['gradient']; ?>;
            color: white;
            text-align: center;
        }

        .resume-header h1 {
            margin: 0;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .resume-contact {
            font-size: 0.95rem;
            opacity: 0.95;
        }

        .resume-body {
            padding: 2rem;
        }

        .resume-section {
            margin-bottom: 2rem;
        }

        .resume-section h2 {
            color: <?php echo $colors['primary']; ?>;
            border-bottom: 2px solid <?php echo $colors['primary']; ?>;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }

        .resume-section p {
            margin: 0.5rem 0;
            line-height: 1.8;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .resume-footer {
            padding: 1.5rem 2rem;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #1e3a8a, #1e40af);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(30, 58, 138, 0.4);
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #1e293b;
        }

        .btn-secondary:hover {
            background: #cbd5e1;
        }

        @media print {
            .resume-footer {
                display: none;
            }

            body {
                background: white;
            }
        }

        @media (max-width: 768px) {
            .resume-header {
                padding: 1.5rem;
            }

            .resume-body {
                padding: 1.5rem;
            }

            .resume-header h1 {
                font-size: 1.5rem;
            }

            .resume-footer {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="resume-preview-container">
        <div class="resume-header">
            <h1><?php echo htmlspecialchars($resume['full_name']); ?></h1>
            <div class="resume-contact">
                <?php if($resume['location']): ?>
                    <span><?php echo htmlspecialchars($resume['location']); ?> | </span>
                <?php endif; ?>
                <?php if($resume['phone']): ?>
                    <span><?php echo htmlspecialchars($resume['phone']); ?> | </span>
                <?php endif; ?>
                <span><?php echo htmlspecialchars($resume['email']); ?></span>
            </div>
        </div>

        <div class="resume-body">
            <?php if($resume['professional_summary']): ?>
            <div class="resume-section">
                <h2><i class="fas fa-briefcase"></i> Professional Summary</h2>
                <p><?php echo htmlspecialchars($resume['professional_summary']); ?></p>
            </div>
            <?php endif; ?>

            <?php if($resume['experiences']): ?>
            <div class="resume-section">
                <h2><i class="fas fa-suitcase"></i> Work Experience</h2>
                <p><?php echo htmlspecialchars($resume['experiences']); ?></p>
            </div>
            <?php endif; ?>

            <?php if($resume['education']): ?>
            <div class="resume-section">
                <h2><i class="fas fa-graduation-cap"></i> Education</h2>
                <p><?php echo htmlspecialchars($resume['education']); ?></p>
            </div>
            <?php endif; ?>

            <?php if($resume['skills']): ?>
            <div class="resume-section">
                <h2><i class="fas fa-star"></i> Skills</h2>
                <p><?php echo htmlspecialchars($resume['skills']); ?></p>
            </div>
            <?php endif; ?>

            <?php if($resume['certifications']): ?>
            <div class="resume-section">
                <h2><i class="fas fa-certificate"></i> Certifications & Awards</h2>
                <p><?php echo htmlspecialchars($resume['certifications']); ?></p>
            </div>
            <?php endif; ?>
        </div>

        <div class="resume-footer">
            <a href="profile.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Profile
            </a>
            <a href="profile.php?download_pdf=1" class="btn btn-primary">
                <i class="fas fa-download"></i> Download PDF
            </a>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>

    <script>
        // Auto-focus on open if requested from profile
        if (window.opener) {
            window.focus();
        }
    </script>
</body>
</html>
