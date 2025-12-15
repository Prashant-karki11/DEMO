<header class="main-header">
    <div class="container">
        <nav class="navbar">
            <div class="logo">
                <a href="index.php">
                    <i class="fas fa-route"></i>
                    <span>CareerPath</span>
                </a>
            </div>
            
            <div class="nav-links">
                <a href="index.php" class="nav-link"><i class="fas fa-home"></i> Home</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    <?php if($_SESSION['user_type'] == 'job_seeker'): ?>
                        <a href="job_seeker/jobs.php" class="nav-link"><i class="fas fa-briefcase"></i> Jobs</a>
                        <a href="job_seeker/ai_guidance.php" class="nav-link"><i class="fas fa-robot"></i> AI Guidance</a>
                    <?php elseif($_SESSION['user_type'] == 'recruiter'): ?>
                        <a href="recruiter/post_job.php" class="nav-link"><i class="fas fa-plus-circle"></i> Post Job</a>
                        <a href="recruiter/manage_applications.php" class="nav-link"><i class="fas fa-users"></i> Applicants</a>
                    <?php endif; ?>
                    <a href="messages.php" class="nav-link"><i class="fas fa-envelope"></i> Messages</a>
                <?php else: ?>
                    <a href="jobs.php" class="nav-link"><i class="fas fa-briefcase"></i> Browse Jobs</a>
                    <a href="#features" class="nav-link"><i class="fas fa-star"></i> Features</a>
                <?php endif; ?>
            </div>
            
            <div class="user-actions">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="user-dropdown">
                        <button class="user-btn">
                            <i class="fas fa-user-circle"></i>
                            <span><?php echo $_SESSION['user_name']; ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a href="dashboard.php" class="dropdown-item"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                            <a href="profile.php" class="dropdown-item"><i class="fas fa-user-edit"></i> Profile</a>
                            <a href="settings.php" class="dropdown-item"><i class="fas fa-cog"></i> Settings</a>
                            <div class="dropdown-divider"></div>
                            <a href="logout.php" class="dropdown-item"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline">Sign In</a>
                    <a href="register.php" class="btn btn-primary">Get Started</a>
                <?php endif; ?>
            </div>
            
            <button class="mobile-toggle">
                <i class="fas fa-bars"></i>
            </button>
        </nav>
    </div>
</header>