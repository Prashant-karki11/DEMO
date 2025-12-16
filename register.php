<?php
// Start session at the very beginning
session_start();

// Include database configuration
require_once 'config/database.php';

// Check if user is already logged in
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Initialize variables
$error = '';
$success = '';

// Check if form was submitted
if($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $user_type = $_POST['user_type'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    
    // Validate inputs
    if(empty($name) || empty($email) || empty($password) || empty($user_type)) {
        $error = "Please fill in all required fields!";
    } elseif($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif(strlen($password) < 6) {
        $error = "Password must be at least 6 characters long!";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address!";
    } else {
        // Check if email already exists
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if($check_stmt->num_rows > 0) {
            $error = "Email already exists! Please use a different email.";
            $check_stmt->close();
        } else {
            $check_stmt->close();
            
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user into database
            $insert_stmt = $conn->prepare("INSERT INTO users (name, email, password, user_type, phone) VALUES (?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("sssss", $name, $email, $hashed_password, $user_type, $phone);
            
            if($insert_stmt->execute()) {
                $success = "Registration successful! You can now login.";
                // Clear form
                $_POST = array();
            } else {
                $error = "Registration failed. Please try again. Error: " . $conn->error;
            }
            $insert_stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - CareerPath</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div style="text-align: center; margin-bottom: 1.5rem;">
                    <i class="fas fa-rocket" style="font-size: 2.5rem; color: var(--primary-blue); margin-bottom: 0.5rem;"></i>
                    <h1 style="font-size: 1.75rem; margin: 0;">CareerPath</h1>
                </div>
                <h2>Get Started Today!</h2>
                <p>Create your account and begin your career transformation</p>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong><?php echo htmlspecialchars($success); ?></strong>
                        <p style="margin: 0.5rem 0 0; font-size: 0.9rem;">
                            <a href="login.php" style="color: var(--success); font-weight: 600;">Click here to login</a>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="name"><i class="fas fa-user"></i> Full Name *</label>
                    <input type="text" id="name" name="name" required 
                           placeholder="John Doe"
                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email Address *</label>
                    <input type="email" id="email" name="email" required 
                           placeholder="you@example.com"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone"><i class="fas fa-phone"></i> Phone Number</label>
                    <input type="tel" id="phone" name="phone" 
                           placeholder="+1 (555) 000-0000"
                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="user_type"><i class="fas fa-briefcase"></i> Join As *</label>
                    <select id="user_type" name="user_type" required>
                        <option value="">-- Select your role --</option>
                        <option value="job_seeker" <?php echo ($_POST['user_type'] ?? '') == 'job_seeker' ? 'selected' : ''; ?>>
                            <i class="fas fa-user-graduate"></i> Job Seeker
                        </option>
                        <option value="recruiter" <?php echo ($_POST['user_type'] ?? '') == 'recruiter' ? 'selected' : ''; ?>>
                            <i class="fas fa-building"></i> Recruiter/Employer
                        </option>
                        <option value="mentor" <?php echo ($_POST['user_type'] ?? '') == 'mentor' ? 'selected' : ''; ?>>
                            <i class="fas fa-chalkboard-teacher"></i> Mentor
                        </option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password *</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="At least 6 characters">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password"><i class="fas fa-lock"></i> Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required 
                           placeholder="Confirm your password">
                </div>
                
                <label class="checkbox" style="margin-bottom: 1.75rem;">
                    <input type="checkbox" name="agree_terms" required> 
                    <span>I agree to the <a href="#" style="color: var(--primary-blue);">Terms of Service</a> and <a href="#" style="color: var(--primary-blue);">Privacy Policy</a></span>
                </label>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
                
                <div style="text-align: center; margin: 1.5rem 0; color: var(--gray-500); display: flex; align-items: center; gap: 1rem;">
                    <hr style="flex: 1; border: none; border-top: 1px solid var(--gray-200);">
                    <span style="font-size: 0.9rem;">or</span>
                    <hr style="flex: 1; border: none; border-top: 1px solid var(--gray-200);">
                </div>
                
                <div class="auth-footer">
                    <p>Already have an account?</p>
                    <p><a href="login.php" style="color: var(--primary-blue); font-weight: 700;">Sign In</a></p>
                    <hr style="margin: 1rem 0; border: none; border-top: 1px solid var(--gray-200);">
                    <p><a href="index.php"><i class="fas fa-home"></i> Back to Home</a></p>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    // Password toggle functionality
    document.querySelectorAll('.password-toggle').forEach(button => {
        button.addEventListener('click', function() {
            const passwordInput = this.previousElementSibling;
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
    });
    
    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (password.length < 6) {
            e.preventDefault();
            alert('Password must be at least 6 characters long.');
            return false;
        }
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match.');
            return false;
        }
    });
    </script>
</body>
</html>