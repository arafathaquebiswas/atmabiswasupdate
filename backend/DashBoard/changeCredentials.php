<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../login/loging.php");
    exit();
}

include '../Database/db.php';

$db = new Db();
$conn = $db->connect();

$message = '';
$messageType = '';

// Get current user info for display
$currentEmail = '';
try {
    $stmt = $conn->prepare("SELECT email FROM admins WHERE fullname = ?");
    $stmt->execute([$_SESSION['username']]);
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($userInfo) {
        $currentEmail = $userInfo['email'];
    }
} catch (PDOException $e) {
    // Handle error silently
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $newUsername = $_POST['new_username'] ?? '';
    $newEmail = $_POST['new_email'] ?? '';

    // Validation
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $message = 'All required fields must be filled.';
        $messageType = 'error';
    } elseif ($newPassword !== $confirmPassword) {
        $message = 'New passwords do not match.';
        $messageType = 'error';
    } elseif (strlen($newPassword) < 6) {
        $message = 'New password must be at least 6 characters long.';
        $messageType = 'error';
    } elseif (!empty($newEmail) && !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $messageType = 'error';
    } else {
        try {
            // Verify current password - using existing admins table structure
            $stmt = $conn->prepare("SELECT adminId, pswd, fullname, email FROM admins WHERE fullname = ?");
            $stmt->execute([$_SESSION['username']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($currentPassword, $user['pswd'])) {
                $message = 'Current password is incorrect.';
                $messageType = 'error';
            } else {
                // Check if new username already exists (if username is being changed)
                if (!empty($newUsername) && $newUsername !== $_SESSION['username']) {
                    $stmt = $conn->prepare("SELECT adminId FROM admins WHERE fullname = ? AND fullname != ?");
                    $stmt->execute([$newUsername, $_SESSION['username']]);
                    if ($stmt->fetch()) {
                        $message = 'Username already exists.';
                        $messageType = 'error';
                    }
                }

                if ($messageType !== 'error') {
                    // Update credentials using adminId for more reliable updates
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $finalUsername = !empty($newUsername) ? $newUsername : $_SESSION['username'];
                    $finalEmail = !empty($newEmail) ? $newEmail : $user['email'];

                    $stmt = $conn->prepare("UPDATE admins SET fullname = ?, pswd = ?, email = ? WHERE adminId = ?");
                    if ($stmt->execute([$finalUsername, $hashedPassword, $finalEmail, $user['adminId']])) {
                        $_SESSION['username'] = $finalUsername;
                        $message = 'Credentials updated successfully!';
                        $messageType = 'success';
                    } else {
                        $message = 'Failed to update credentials.';
                        $messageType = 'error';
                    }
                }
            }
        } catch (PDOException $e) {
            $message = 'Database error occurred.';
            $messageType = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Credentials - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="css/admin-sidebar.css">
    <link rel="stylesheet" href="css/changeCredentials.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="icon" type="image/png" href="../images/logo/logo.png">
</head>

<body class="bg-gray-50">
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar-container">
            <?php include 'sidebar.php' ?>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Content Area -->
            <main class="dashboard-main">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="header-content">
                        <div class="header-left">
                            <h1>Change Credentials</h1>
                            <p class="page-subtitle">Update your login username and password</p>
                        </div>
                        <div class="header-actions">
                            <a href="dashboard.php" class="btn btn-outline">
                                <i class="fas fa-arrow-left"></i>
                                Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Change Credentials Form -->
                <div class="credentials-section">
                    <div class="credentials-container">
                        <div class="form-header">
                            <div class="form-title">
                                <i class="fas fa-user-shield"></i>
                                <div>
                                    <h2>Update Your Credentials</h2>
                                    <p>Change your username and password for enhanced security</p>
                                </div>
                            </div>
                        </div>

                        <?php if ($message): ?>
                            <div class="notification <?php echo $messageType; ?>">
                                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                                <span><?php echo htmlspecialchars($message); ?></span>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="credentials-form">
                            <div class="form-body">
                                <div class="form-group">
                                    <label for="current_username" class="form-label">
                                        <i class="fas fa-user"></i>
                                        Current Username
                                    </label>
                                    <input type="text" id="current_username" class="form-input"
                                        value="<?php echo htmlspecialchars($_SESSION['username']); ?>"
                                        readonly>
                                </div>

                                <div class="form-group">
                                    <label for="current_password" class="form-label">
                                        <i class="fas fa-lock"></i>
                                        Current Password
                                    </label>
                                    <input type="password" id="current_password" name="current_password"
                                        class="form-input" required>
                                </div>

                                <div class="form-group">
                                    <label for="current_email" class="form-label">
                                        <i class="fas fa-envelope"></i>
                                        Current Email
                                    </label>
                                    <input type="email" id="current_email" class="form-input"
                                        value="<?php echo htmlspecialchars($currentEmail); ?>"
                                        readonly>
                                </div>

                                <div class="form-group">
                                    <label for="new_username" class="form-label">
                                        <i class="fas fa-user-edit"></i>
                                        New Username (Optional)
                                    </label>
                                    <input type="text" id="new_username" name="new_username"
                                        class="form-input" placeholder="Enter new username">
                                </div>

                                <div class="form-group">
                                    <label for="new_email" class="form-label">
                                        <i class="fas fa-envelope-open"></i>
                                        New Email (Optional)
                                    </label>
                                    <input type="email" id="new_email" name="new_email"
                                        class="form-input" placeholder="Enter new email address">
                                </div>

                                <div class="form-group">
                                    <label for="new_password" class="form-label">
                                        <i class="fas fa-key"></i>
                                        New Password
                                    </label>
                                    <input type="password" id="new_password" name="new_password"
                                        class="form-input" required minlength="6">
                                    <div class="password-requirements">
                                        <small>Password must be at least 6 characters long</small>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="confirm_password" class="form-label">
                                        <i class="fas fa-check-circle"></i>
                                        Confirm New Password
                                    </label>
                                    <input type="password" id="confirm_password" name="confirm_password"
                                        class="form-input" required>
                                </div>
                            </div>

                            <div class="form-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    Update Credentials
                                </button>
                                <button type="button" class="btn btn-outline" onclick="clearForm()">
                                    <i class="fas fa-undo"></i>
                                    Clear Form
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Security Tips -->
                <div class="security-tips">
                    <div class="tips-header">
                        <i class="fas fa-shield-alt"></i>
                        <h3>Security Tips</h3>
                    </div>
                    <div class="tips-content">
                        <div class="tip-item">
                            <i class="fas fa-check"></i>
                            <span>Use a strong password with at least 6 characters</span>
                        </div>
                        <div class="tip-item">
                            <i class="fas fa-check"></i>
                            <span>Include a mix of letters, numbers, and special characters</span>
                        </div>
                        <div class="tip-item">
                            <i class="fas fa-check"></i>
                            <span>Don't share your credentials with anyone</span>
                        </div>
                        <div class="tip-item">
                            <i class="fas fa-check"></i>
                            <span>Change your password regularly for better security</span>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        function clearForm() {
            document.getElementById('current_password').value = '';
            document.getElementById('new_username').value = '';
            document.getElementById('new_email').value = '';
            document.getElementById('new_password').value = '';
            document.getElementById('confirm_password').value = '';
        }

        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;

            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });

        // Form validation
        document.querySelector('.credentials-form').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
        });
    </script>
</body>

</html>