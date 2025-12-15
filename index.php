<?php
session_start();
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CareerPath - AI Career Guidance Platform</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="fade-in">Find Your Dream Career with AI Guidance</h1>
                <p class="subtitle fade-in">Intelligent job matching, personalized career advice, and mentorship - all in one platform</p>
                
                <div class="cta-buttons fade-in">
                    <?php if(!isset($_SESSION['user_id'])): ?>
                        <a href="register.php" class="btn btn-primary">Get Started</a>
                        <a href="login.php" class="btn btn-secondary">Sign In</a>
                    <?php else: ?>
                        <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <section class="features">
        <div class="container">
            <h2 class="section-title">Key Features</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-robot"></i>
                    </div>
                    <h3>AI Career Guidance</h3>
                    <p>Personalized career suggestions using GROQ AI API</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Smart Job Matching</h3>
                    <p>TF-IDF algorithm finds the most relevant jobs for you</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h3>Real-time Messaging</h3>
                    <p>Chat directly with recruiters and mentors</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Progress Tracking</h3>
                    <p>Track applications and manage tasks efficiently</p>
                </div>
            </div>
        </div>
    </section>

    <section class="roles">
        <div class="container">
            <h2 class="section-title">Who Can Join?</h2>
            <div class="roles-grid">
                <div class="role-card job-seeker">
                    <h3><i class="fas fa-user-graduate"></i> Job Seeker</h3>
                    <ul>
                        <li>AI-powered career guidance</li>
                        <li>Personalized job matching</li>
                        <li>Resume builder with templates</li>
                        <li>Application tracker</li>
                    </ul>
                </div>
                
                <div class="role-card recruiter">
                    <h3><i class="fas fa-briefcase"></i> Recruiter</h3>
                    <ul>
                        <li>Post unlimited job listings</li>
                        <li>Advanced applicant tracking</li>
                        <li>Analytics dashboard</li>
                        <li>Direct messaging</li>
                    </ul>
                </div>
                
                <div class="role-card mentor">
                    <h3><i class="fas fa-chalkboard-teacher"></i> Mentor</h3>
                    <ul>
                        <li>Guide aspiring professionals</li>
                        <li>AI-assisted recommendations</li>
                        <li>Video call sessions</li>
                        <li>Build your reputation</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/script.js"></script>
</body>
</html>