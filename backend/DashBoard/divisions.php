<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../login/loging.php");
    exit();
}

require_once __DIR__ . '/../Database/db.php';
require_once __DIR__ . '/csrf_helper.php';

$db   = new Db();
$conn = $db->connect();

$table_missing = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);

    if ($id > 0) {
        try {
            if ($action === 'delete') {
                $row = $conn->prepare("SELECT name FROM divisions WHERE id = :id");
                $row->bindParam(':id', $id, PDO::PARAM_INT);
                $row->execute();
                $div_name = $row->fetchColumn();

                if ($div_name) {
                    $cnt = $conn->prepare("SELECT COUNT(*) FROM branches WHERE division = :name");
                    $cnt->bindParam(':name', $div_name, PDO::PARAM_STR);
                    $cnt->execute();
                    $branch_count = (int)$cnt->fetchColumn();

                    if ($branch_count > 0) {
                        header("Location: divisions.php?msg=" . rawurlencode("Cannot delete: {$branch_count} branch(es) use this division. Remove or reassign them first.") . "&type=error");
                        exit();
                    }
                }

                $stmt = $conn->prepare("DELETE FROM divisions WHERE id = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                header("Location: divisions.php?msg=" . rawurlencode('Division deleted.') . "&type=success");
                exit();

            } elseif ($action === 'toggle') {
                $stmt = $conn->prepare("UPDATE divisions SET status = IF(status = 1, 0, 1) WHERE id = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                header("Location: divisions.php?msg=" . rawurlencode('Status updated.') . "&type=success");
                exit();
            }
        } catch (PDOException $e) {
            header("Location: divisions.php?msg=" . rawurlencode('Error: ' . $e->getMessage()) . "&type=error");
            exit();
        }
    }
}

$msg = '';
$msg_type = '';
if (isset($_GET['msg'])) {
    $msg      = htmlspecialchars($_GET['msg']);
    $msg_type = $_GET['type'] ?? 'success';
}

try {
    $stmt = $conn->query(
        "SELECT d.id, d.name, d.status,
                (SELECT COUNT(*) FROM branches WHERE division = d.name) AS branch_count
         FROM divisions d
         ORDER BY d.name ASC"
    );
    $divisions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $table_missing = true;
    $divisions     = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Divisions — Admin</title>
    <link rel="stylesheet" href="css/admin-sidebar.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/contacts.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="../images/logo/logo.png">
</head>
<body>
<div class="dashboard-container">
    <div class="sidebar-container"><?php include 'sidebar.php'; ?></div>
    <div class="main-content">
        <?php include 'navbar.inc.php'; ?>
        <div class="dashboard-main">

            <?php if ($table_missing): ?>
            <div class="cm-alert cm-alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                The <code>divisions</code> table is not set up yet.
                <a href="setup_contact_tables.php" style="color:#991b1b;font-weight:700;text-decoration:underline;margin-left:.5rem;">
                    Run Setup Now →
                </a>
            </div>
            <?php endif; ?>

            <?php if ($msg): ?>
            <div class="cm-alert cm-alert-<?= $msg_type ?>">
                <i class="fas fa-<?= $msg_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <?= $msg ?>
            </div>
            <?php endif; ?>

            <div class="cm-header">
                <div>
                    <div class="cm-title">Divisions</div>
                    <div class="cm-subtitle">
                        <?= count($divisions) ?> division<?= count($divisions) !== 1 ? 's' : '' ?> —
                        used in the Branch filter on the Contact page
                    </div>
                </div>
                <a href="add_division.php" class="btn-primary">
                    <i class="fas fa-plus"></i> Add Division
                </a>
            </div>

            <div class="cm-info-box">
                <i class="fas fa-info-circle"></i>
                Divisions appear in the <strong>Add Branch</strong> dropdown and in the
                <strong>Contact page filter</strong>. Disabling a division hides it from the
                frontend without removing any branches.
            </div>

            <div class="cm-table-wrap">
                <table class="cm-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Division Name</th>
                            <th>Active Branches</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($divisions)): ?>
                        <tr><td colspan="5">
                            <div class="cm-empty">
                                <i class="fas fa-layer-group"></i>
                                No divisions yet. Add one to get started.
                            </div>
                        </td></tr>
                    <?php else: ?>
                        <?php foreach ($divisions as $i => $d): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <strong><?= htmlspecialchars($d['name']) ?></strong>
                            </td>
                            <td>
                                <?php if ($d['branch_count'] > 0): ?>
                                    <span class="cm-branch-count"><?= (int)$d['branch_count'] ?></span>
                                <?php else: ?>
                                    <span style="color:#9ca3af;">0</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($d['status']): ?>
                                    <span class="badge-active">Active</span>
                                <?php else: ?>
                                    <span class="badge-inactive">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="cm-actions">
                                    <form method="POST" onsubmit="return confirm('Toggle status of this division?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="id" value="<?= (int)$d['id'] ?>">
                                        <button class="<?= $d['status'] ? 'btn-warning' : 'btn-success' ?>" type="submit">
                                            <i class="fas fa-<?= $d['status'] ? 'eye-slash' : 'eye' ?>"></i>
                                            <?= $d['status'] ? 'Disable' : 'Enable' ?>
                                        </button>
                                    </form>
                                    <form method="POST" onsubmit="return confirm('Delete this division? This will fail if branches are using it.')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int)$d['id'] ?>">
                                        <button class="btn-danger" type="submit"
                                                <?= $d['branch_count'] > 0 ? 'title="Has active branches — cannot delete"' : '' ?>>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
</body>
</html>
