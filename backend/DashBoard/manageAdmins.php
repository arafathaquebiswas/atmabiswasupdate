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

// Handle delete admin request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_admin'])) {
    $adminId = $_POST['admin_id'] ?? '';
    $currentUsername = $_SESSION['username'];

    if (empty($adminId)) {
        $message = 'Invalid admin ID.';
        $messageType = 'error';
    } else {
        try {
            // Get admin info to check if trying to delete self
            $stmt = $conn->prepare("SELECT fullname FROM admins WHERE adminId = ?");
            $stmt->execute([$adminId]);
            $adminToDelete = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$adminToDelete) {
                $message = 'Admin not found.';
                $messageType = 'error';
            } elseif ($adminToDelete['fullname'] === $currentUsername) {
                $message = 'You cannot delete your own account.';
                $messageType = 'error';
            } else {
                // Delete the admin
                $stmt = $conn->prepare("DELETE FROM admins WHERE adminId = ?");
                if ($stmt->execute([$adminId])) {
                    $message = 'Admin deleted successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Failed to delete admin.';
                    $messageType = 'error';
                }
            }
        } catch (PDOException $e) {
            $message = 'Database error occurred.';
            $messageType = 'error';
        }
    }
}

// Get all admins
$admins = [];
try {
    $stmt = $conn->prepare("SELECT adminId, fullname, email FROM admins ORDER BY fullname ASC");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = 'Failed to load admins list.';
    $messageType = 'error';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins - ATMABISWAS</title>
    <link rel="stylesheet" href="css/admin-sidebar.css">
    <link rel="stylesheet" href="css/manageAdmins.css">
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
            <!-- Top Navbar -->
            <?php include 'navbar.inc.php'; ?>

            <!-- Content Area -->
            <main class="dashboard-main">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="header-content">
                        <div class="header-left">
                            <h1>Manage Admins</h1>
                            <p class="page-subtitle">View and manage admin accounts</p>
                        </div>
                        <div class="header-actions">
                            <a href="adminSignup.php" class="btn btn-primary">
                                <i class="fas fa-user-plus"></i>
                                Create New Admin
                            </a>
                            <a href="dashboard.php" class="btn btn-outline">
                                <i class="fas fa-arrow-left"></i>
                                Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Notification -->
                <?php if (!empty($message)): ?>
                    <div class="notification <?php echo $messageType; ?>">
                        <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                        <span><?php echo htmlspecialchars($message); ?></span>
                    </div>
                <?php endif; ?>

                <!-- Admins List -->
                <div class="admins-section">
                    <div class="admins-container">
                        <div class="admins-header">
                            <div class="admins-title">
                                <i class="fas fa-users-cog"></i>
                                <div>
                                    <h2>Admin Accounts</h2>
                                    <p>Manage admin user accounts and permissions</p>
                                </div>
                            </div>
                            <div class="admins-count">
                                <span class="count-number"><?php echo count($admins); ?></span>
                                <span class="count-label">Total Admins</span>
                            </div>
                        </div>

                        <div class="admins-content">
                            <?php if (empty($admins)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-users"></i>
                                    <h3>No Admins Found</h3>
                                    <p>There are no admin accounts in the system.</p>
                                    <a href="adminSignup.php" class="btn btn-primary">
                                        <i class="fas fa-user-plus"></i>
                                        Create First Admin
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="admins-grid">
                                    <?php foreach ($admins as $admin): ?>
                                        <div class="admin-card <?php echo $admin['fullname'] === $_SESSION['username'] ? 'current-user' : ''; ?>">
                                            <div class="admin-avatar">
                                                <i class="fas fa-user-shield"></i>
                                            </div>
                                            <div class="admin-info">
                                                <h3 class="admin-name">
                                                    <?php echo htmlspecialchars($admin['fullname']); ?>
                                                    <?php if ($admin['fullname'] === $_SESSION['username']): ?>
                                                        <span class="current-badge">You</span>
                                                    <?php endif; ?>
                                                </h3>
                                                <p class="admin-email">
                                                    <i class="fas fa-envelope"></i>
                                                    <?php echo htmlspecialchars($admin['email'] ?: 'No email set'); ?>
                                                </p>
                                                <p class="admin-id">
                                                    <i class="fas fa-id-card"></i>
                                                    ID: <?php echo $admin['adminId']; ?>
                                                </p>
                                            </div>
                                            <div class="admin-actions">
                                                <?php if ($admin['fullname'] !== $_SESSION['username']): ?>
                                                    <button class="btn btn-danger btn-sm"
                                                        onclick="confirmDeleteAdmin(<?php echo $admin['adminId']; ?>, '<?php echo htmlspecialchars($admin['fullname']); ?>')">
                                                        <i class="fas fa-trash"></i>
                                                        Delete
                                                    </button>
                                                <?php else: ?>
                                                    <span class="btn btn-outline btn-sm disabled">
                                                        <i class="fas fa-lock"></i>
                                                        Current User
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Security Notice -->
                <div class="security-notice">
                    <div class="notice-header">
                        <i class="fas fa-shield-alt"></i>
                        <h3>Security Notice</h3>
                    </div>
                    <div class="notice-content">
                        <div class="notice-item">
                            <i class="fas fa-info-circle"></i>
                            <span>Only delete admin accounts that are no longer needed</span>
                        </div>
                        <div class="notice-item">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Deleting an admin account cannot be undone</span>
                        </div>
                        <div class="notice-item">
                            <i class="fas fa-user-shield"></i>
                            <span>You cannot delete your own account</span>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Confirm Delete</h3>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the admin account for:</p>
                <p class="admin-to-delete"><strong id="adminName"></strong></p>
                <p class="warning-text">This action cannot be undone!</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="closeDeleteModal()">Cancel</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="admin_id" id="adminIdToDelete">
                    <button type="submit" name="delete_admin" class="btn btn-danger">
                        <i class="fas fa-trash"></i>
                        Delete Admin
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function confirmDeleteAdmin(adminId, adminName) {
            document.getElementById('adminIdToDelete').value = adminId;
            document.getElementById('adminName').textContent = adminName;
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target === modal) {
                closeDeleteModal();
            }
        }

        // Auto-hide notifications after 5 seconds
        setTimeout(function() {
            const notifications = document.querySelectorAll('.notification');
            notifications.forEach(function(notification) {
                notification.style.opacity = '0';
                setTimeout(function() {
                    notification.style.display = 'none';
                }, 300);
            });
        }, 5000);
    </script>
</body>

</html>