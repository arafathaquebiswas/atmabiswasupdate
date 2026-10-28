<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Database/db.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../google_auth.php';
require_once __DIR__ . '/../DashBoard/csrf_helper.php';

$usernameErr = "";
$passErr     = "";
$invalid     = "";
// Empty until Google sign-in is configured; the button is hidden until then.
$googleClientId = google_client_id();

// The login form must never be shown inside another site's frame (clickjacking).
// Google's button opens a popup, so it needs no framing either.
header('X-Frame-Options: DENY');
header("Content-Security-Policy: frame-ancestors 'none'");

if ($_SERVER["REQUEST_METHOD"] === "POST" && !csrf_verify()) {
    $invalid = "Your session expired. Please try again.";
} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Both are used exactly as typed: the query is parameterised, and the
    // stored hash was made from the raw password, so filtering either one
    // only ever turned a correct login into "Invalid Credentials".
    $username = trim((string) ($_POST["username"] ?? ''));
    $password = (string) ($_POST["password"] ?? '');

    if ($username === '') {
        $usernameErr = "Email is Required";
    }

    if ($password === '') {
        $passErr = "Password is Required";
    }

    if ($username !== '' && $password !== '') {
        try {
            $database   = new Db();
            $connection = $database->connect();

            if ($connection) {
                // Ensures the role column exists before the row is read, so the
                // session is given the real role rather than defaulting to admin.
                auth_ensure_role_column($connection);

                // Rate limited, and refused with one message whatever was wrong.
                $user = auth_password_login($connection, $username, $password, $loginError);

                if (!$user) {
                    $invalid = $loginError;
                } else {
                    auth_login_admin($user);
                    header("Location: ../DashBoard/dashboard.php");
                    exit();
                }
            } else {
                $invalid = "Database connection unavailable.";
            }
        } catch (Throwable $e) {
            error_log("Login DB error: " . $e->getMessage());
            $invalid = "Database error occurred. Please try again later.";
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
            <?= csrf_field() ?>
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

        <?php if ($googleClientId !== ''): ?>
        <!-- Sign in with Google: only approved Google accounts get in (see ../google_auth.php). -->
        <div class="google-signin">
            <div class="google-or"><span>or</span></div>
            <div id="g_id_onload"
                 data-client_id="<?= htmlspecialchars($googleClientId) ?>"
                 data-callback="atmaGoogleSignIn"
                 data-ux_mode="popup"
                 data-auto_prompt="false"
                 data-itp_support="true"></div>
            <div class="g_id_signin" data-type="standard" data-theme="outline" data-size="large"
                 data-text="signin_with" data-shape="rectangular" data-logo_alignment="left" data-width="320"></div>
            <!-- Not .error-message: the page hides those 5 s after load, which would also hide this one for good. -->
            <div class="google-error" id="googleError" role="alert" hidden>
                <i class="fas fa-exclamation-triangle"></i>
                <span></span>
            </div>
        </div>
        <style>
            .google-signin { margin-top: 1.25rem; display: flex; flex-direction: column; align-items: center; gap: .75rem; }
            .google-or { width: 100%; display: flex; align-items: center; gap: .75rem; color: #fff; font-size: .85rem;
                         font-weight: 600; text-shadow: 0 1px 2px rgba(0,0,0,.35); }
            .google-or::before, .google-or::after { content: ""; flex: 1; height: 1px; background: rgba(255,255,255,.55); }
            .google-error { width: 100%; box-sizing: border-box; display: flex; gap: .5rem; align-items: center;
                            padding: .7rem .9rem; border-radius: 8px; font-size: .88rem;
                            background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
            .google-error[hidden] { display: none; }
        </style>
        <script src="https://accounts.google.com/gsi/client" async defer></script>
        <script>
            // Google hands us a signed ID token; the server verifies it and decides.
            function atmaGoogleSignIn(response) {
                var box = document.getElementById('googleError');
                var body = new FormData();
                body.append('credential', response.credential);
                body.append('csrf_token', <?= json_encode(csrf_token()) ?>);
                box.hidden = true;
                fetch('google.php', { method: 'POST', body: body, credentials: 'same-origin' })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                            return;
                        }
                        box.querySelector('span').textContent = data.error || 'Google sign-in could not be completed. Please try again.';
                        box.hidden = false;
                    })
                    .catch(function () {
                        box.querySelector('span').textContent = 'Google sign-in could not be completed. Please try again.';
                        box.hidden = false;
                    });
            }
        </script>
        <?php endif; ?>

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