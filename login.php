<?php
session_start();
require_once 'config/database.php';

if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT id, name, email, password, user_type FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($row = $result->fetch_assoc()) {
        if(password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['name'];
            $_SESSION['user_type'] = $row['user_type'];
            $_SESSION['user_email'] = $row['email'];
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "No account found with this email!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CareerPath</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div style="text-align: center; margin-bottom: 1.5rem;">
                    <i class="fas fa-route" style="font-size: 2.5rem; color: var(--primary-blue); margin-bottom: 0.5rem;"></i>
                    <h1 style="font-size: 1.75rem; margin: 0;">CareerPath</h1>
                </div>
                <h2>Welcome Back!</h2>
                <p>Sign in to your account and continue your journey</p>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email Address
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        placeholder="you@example.com"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        placeholder="Enter your password"
                    >
                </div>
                
                <div class="form-options">
                    <label class="checkbox">
                        <input type="checkbox" name="remember"> 
                        <span>Remember me for 30 days</span>
                    </label>
                    <a href="#" class="forgot-password">Forgot Password?</a>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
                
                <div style="text-align: center; margin: 1.5rem 0; color: var(--gray-500); display: flex; align-items: center; gap: 1rem;">
                    <hr style="flex: 1; border: none; border-top: 1px solid var(--gray-200);">
                    <span style="font-size: 0.9rem;">or</span>
                    <hr style="flex: 1; border: none; border-top: 1px solid var(--gray-200);">
                </div>
                
                <div class="auth-footer">
                    <p>Don't have an account yet?</p>
                    <p><a href="register.php" style="color: var(--primary-blue); font-weight: 700;">Create Account</a></p>
                    <hr style="margin: 1rem 0; border: none; border-top: 1px solid var(--gray-200);">
                    <p><a href="index.php"><i class="fas fa-home"></i> Back to Home</a></p>
                </div>
            </form>
        </div>
    </div>
    
    <script src="assets/js/script.js"></script>
</body>
</html>