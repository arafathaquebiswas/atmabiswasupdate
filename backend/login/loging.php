<?php
include '../Database/db.php';
session_start();

$database = new Db();
$connection = $database->connect();

$usernameErr = "";
$passErr = "";
$invalid = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_SPECIAL_CHARS);

    if (empty($username)) {
        $usernameErr = "Email is Required";
    }

    if (empty($password)) {
        $passErr = "Password is Required";
    }

    if ($username && $password) {

        $sql = "SELECT * FROM admins WHERE email = :username";
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['pswd'])) {

            $invalid = "Invalid Credentials";
        } else {

            $_SESSION['username'] = $user['fullname'];
            header("Location: ../DashBoard/dashboard.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATMABISWAS - Admin Login</title>
    <meta name="description" content="Secure admin login for ATMABISWAS dashboard">
    <meta name="robots" content="noindex, nofollow">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Modern CSS -->
    <link rel="stylesheet" href="css/modern-login.css">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../images/logo/logo.png">

    <!-- Preload critical resources -->
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" as="style">
</head>

<body>
    <!-- Notification Bar -->
    <div id="notificationBar" class="notification-bar">
        <div class="notification-content">
            <i class="fas fa-envelope"></i>
            <span>
                Contact <strong>ATMABISWAS IT</strong> via
                <a href="mailto:support@atmabiswas.org" class="notification-link">support@atmabiswas.org</a>
            </span>
        </div>
        <button class="notification-close" onclick="hideNotification()" aria-label="Close notification">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- Main Login Container -->
    <div class="login-container">
        <!-- Header Section -->
        <div class="login-header">
            <div class="logo-container">
                <img src="../images/logo/logo.png" alt="ATMABISWAS Logo" class="logo">
            </div>
            <h1 class="login-title">Welcome Back</h1>
            <p class="login-subtitle">Sign in to your admin dashboard</p>
        </div>

        <!-- Form Section -->
        <form class="login-form" action="" method="POST" novalidate>
            <!-- Email Field -->
            <div class="form-group">
                <label for="username" class="form-label">Email Address</label>
                <div class="input-container">
                    <input
                        type="email"
                        id="username"
                        name="username"
                        class="form-input"
                        placeholder="Enter your email"
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        required
                        autocomplete="email"
                        autofocus>
                    <i class="fas fa-envelope input-icon"></i>
                </div>
                <?php if (!empty($usernameErr)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo htmlspecialchars($usernameErr); ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Password Field -->
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div class="input-container">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input"
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password">
                    <i class="fas fa-lock input-icon"></i>
                    <button type="button" class="password-toggle" onclick="togglePassword()" aria-label="Toggle password visibility">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
                <?php if (!empty($passErr)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo htmlspecialchars($passErr); ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- General Error Message -->
            <?php if (!empty($invalid)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span><?php echo htmlspecialchars($invalid); ?></span>
                </div>
            <?php endif; ?>

            <!-- Login Button -->
            <button type="submit" class="login-button" id="loginBtn">
                <span>Sign In</span>
            </button>
        </form>

        <!-- Links Section -->
        <div class="login-links">
            <a href="#" onclick="showNotification()" class="login-link">
                <i class="fas fa-key"></i>
                <span>Forgot Password?</span>
            </a>
            <a href="../../index.php" class="login-link">
                <i class="fas fa-home"></i>
                <span>Back to Home</span>
            </a>
        </div>
    </div>
    <!-- JavaScript -->
    <script>
        // Password Toggle Functionality
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }

        // Notification Functions
        function showNotification() {
            const bar = document.getElementById('notificationBar');
            bar.classList.add('show');

            // Auto-hide after 10 seconds
            setTimeout(() => {
                hideNotification();
            }, 10000);
        }

        function hideNotification() {
            const bar = document.getElementById('notificationBar');
            bar.classList.remove('show');
        }

        // Form Enhancement
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.login-form');
            const loginBtn = document.getElementById('loginBtn');
            const inputs = document.querySelectorAll('.form-input');

            // Add loading state to form submission
            form.addEventListener('submit', function(e) {
                loginBtn.classList.add('loading');
                loginBtn.disabled = true;
                loginBtn.innerHTML = '<span>Signing In...</span>';
            });

            // Enhanced input interactions
            inputs.forEach(input => {
                // Add focus/blur effects
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });

                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('focused');
                });

                // Real-time validation
                input.addEventListener('input', function() {
                    if (this.checkValidity()) {
                        this.style.borderColor = 'var(--success)';
                    } else {
                        this.style.borderColor = 'var(--gray-200)';
                    }
                });
            });

            // Auto-focus first empty field
            const firstEmptyField = Array.from(inputs).find(input => !input.value);
            if (firstEmptyField) {
                firstEmptyField.focus();
            }

            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Enter key on password field submits form
                if (e.key === 'Enter' && e.target.id === 'password') {
                    form.submit();
                }
            });
        });

        // Accessibility improvements
        document.addEventListener('keydown', function(e) {
            // Escape key closes notification
            if (e.key === 'Escape') {
                hideNotification();
            }
        });

        // Auto-hide error messages after 5 seconds
        setTimeout(function() {
            const errorMessages = document.querySelectorAll('.error-message');
            errorMessages.forEach(function(error) {
                error.style.opacity = '0';
                setTimeout(function() {
                    error.style.display = 'none';
                }, 300);
            });
        }, 5000);

        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>

</body>

</html>