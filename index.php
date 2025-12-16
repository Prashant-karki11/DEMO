<?php
session_start();
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="CareerPath - AI-powered career guidance and job matching platform">
    <title>CareerPath - AI Career Guidance Platform</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <!-- Hero Section -->
    <main class="hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="fade-in">
                    <span style="background: linear-gradient(135deg, #ffffff 0%, #e0f2fe 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                        Find Your Dream Career with AI Guidance
                    </span>
                </h1>
                <p class="subtitle fade-in">Intelligent job matching, personalized career advice, and mentorship - all in one platform</p>
                
                <div class="cta-buttons fade-in">
                    <?php if(!isset($_SESSION['user_id'])): ?>
                        <a href="register.php" class="btn btn-primary">
                            <i class="fas fa-rocket"></i> Get Started
                        </a>
                        <a href="login.php" class="btn btn-secondary">
                            <i class="fas fa-sign-in-alt"></i> Sign In
                        </a>
                    <?php else: ?>
                        <a href="dashboard.php" class="btn btn-primary">
                            <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <h2 class="section-title">
                <i class="fas fa-sparkles"></i> Key Features
            </h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h3>AI Career Guidance</h3>
                    <p>Get personalized career suggestions powered by advanced AI technology. Receive tailored recommendations based on your skills and goals.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-crosshairs"></i>
                    </div>
                    <h3>Smart Job Matching</h3>
                    <p>Our advanced algorithm analyzes your profile to find the most relevant opportunities that align with your career aspirations.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h3>Real-time Messaging</h3>
                    <p>Connect instantly with recruiters and mentors. Chat in real-time to discuss opportunities and get guidance.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Progress Tracking</h3>
                    <p>Monitor your job applications, interview progress, and career milestones in one centralized dashboard.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                    <h3>Resume Builder</h3>
                    <p>Create professional resumes with our AI-powered builder. Choose from multiple templates and export as PDF.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Mentorship Program</h3>
                    <p>Get guidance from experienced professionals. Build meaningful connections and accelerate your career growth.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Roles Section -->
    <section class="roles">
        <div class="container">
            <h2 class="section-title">
                <i class="fas fa-users-cog"></i> Who Can Join?
            </h2>
            <div class="roles-grid">
                <div class="role-card job-seeker">
                    <h3><i class="fas fa-user-graduate"></i> Job Seeker</h3>
                    <ul>
                        <li>AI-powered career guidance</li>
                        <li>Personalized job matching</li>
                        <li>Resume builder with templates</li>
                        <li>Application tracker</li>
                        <li>Interview preparation</li>
                        <li>Skill assessment tools</li>
                    </ul>
                    <a href="register.php" class="btn btn-outline" style="margin-top: 1.5rem; align-self: flex-start;">
                        Join as Job Seeker
                    </a>
                </div>
                
                <div class="role-card recruiter">
                    <h3><i class="fas fa-briefcase"></i> Recruiter</h3>
                    <ul>
                        <li>Post unlimited job listings</li>
                        <li>Advanced applicant tracking</li>
                        <li>Analytics dashboard</li>
                        <li>Direct messaging</li>
                        <li>Candidate matching</li>
                        <li>Employer branding tools</li>
                    </ul>
                    <a href="register.php" class="btn btn-outline" style="margin-top: 1.5rem; align-self: flex-start;">
                        Join as Recruiter
                    </a>
                </div>
                
                <div class="role-card mentor">
                    <h3><i class="fas fa-chalkboard-teacher"></i> Mentor</h3>
                    <ul>
                        <li>Guide aspiring professionals</li>
                        <li>AI-assisted recommendations</li>
                        <li>Video call sessions</li>
                        <li>Build your reputation</li>
                        <li>Share your expertise</li>
                        <li>Flexible scheduling</li>
                    </ul>
                    <a href="register.php" class="btn btn-outline" style="margin-top: 1.5rem; align-self: flex-start;">
                        Join as Mentor
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="features" style="background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 50%, #1e3a8a 100%); color: white; padding: 4rem 0;">
        <div class="container" style="text-align: center;">
            <h2 style="color: white; margin-bottom: 1rem;">Ready to Transform Your Career?</h2>
            <p style="color: rgba(255, 255, 255, 0.9); font-size: 1.1rem; margin-bottom: 2rem;">
                Join thousands of professionals using CareerPath to achieve their career goals
            </p>
            <?php if(!isset($_SESSION['user_id'])): ?>
                <a href="register.php" class="btn btn-secondary" style="color: #1e3a8a; border-color: white; background: white;">
                    <i class="fas fa-arrow-right"></i> Get Started Today
                </a>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/script.js"></script>
</body>
</html>