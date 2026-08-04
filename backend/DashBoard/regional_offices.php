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

$msg = '';
$msg_type = '';

// Handle POST actions (delete / toggle status)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);

    if ($id > 0) {
        try {
            if ($action === 'delete') {
                $stmt = $conn->prepare("DELETE FROM regional_offices WHERE id = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                header("Location: regional_offices.php?msg=" . rawurlencode('Regional office deleted successfully.') . "&type=success");
                exit();
            } elseif ($action === 'toggle') {
                $stmt = $conn->prepare("UPDATE regional_offices SET status = IF(status = 1, 0, 1) WHERE id = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $qs = [];
                if (!empty($_GET['page']) && (int)$_GET['page'] > 1) $qs['page'] = (int)$_GET['page'];
                if (!empty($_GET['search'])) $qs['search'] = $_GET['search'];
                $qs['msg']  = 'Status updated.';
                $qs['type'] = 'success';
                header("Location: regional_offices.php?" . http_build_query($qs));
                exit();
            }
        } catch (PDOException $e) {
            $msg      = 'Error: ' . htmlspecialchars($e->getMessage());
            $msg_type = 'error';
        }
    }
}

// Flash message from add/edit redirect
if (isset($_GET['msg'])) {
    $msg = htmlspecialchars($_GET['msg']);
    $msg_type = $_GET['type'] ?? 'success';
}

// Pagination & search
$per_page = 10;
$page     = max(1, (int)($_GET['page'] ?? 1));
$search   = trim($_GET['search'] ?? '');
$offset   = ($page - 1) * $per_page;

$table_missing = false;
$where  = $search ? "WHERE region_name LIKE :search OR address LIKE :search2 OR phone LIKE :search3" : "";
$params = $search ? [':search' => "%$search%", ':search2' => "%$search%", ':search3' => "%$search%"] : [];

try {
    $count_stmt = $conn->prepare("SELECT COUNT(*) FROM regional_offices $where");
    $count_stmt->execute($params);
    $total = (int)$count_stmt->fetchColumn();
    $total_pages = max(1, ceil($total / $per_page));

    $stmt = $conn->prepare("SELECT * FROM regional_offices $where ORDER BY id ASC LIMIT :limit OFFSET :offset");
    if ($search) {
        $stmt->bindValue(':search',  "%$search%", PDO::PARAM_STR);
        $stmt->bindValue(':search2', "%$search%", PDO::PARAM_STR);
        $stmt->bindValue(':search3', "%$search%", PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit',  $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset,   PDO::PARAM_INT);
    $stmt->execute();
    $offices = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $table_missing = true;
    $total = 0; $total_pages = 1; $offices = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regional Offices — Admin</title>
    <link rel="stylesheet" href="css/admin-sidebar.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/contacts.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="../images/logo/logo.png">
</head>
<body>
<div class="dashboard-container">
    <div class="sidebar-container">
        <?php include 'sidebar.php'; ?>
    </div>
    <div class="main-content">
        <?php include 'navbar.inc.php'; ?>
        <div class="dashboard-main">

            <?php if ($table_missing): ?>
            <div class="cm-alert cm-alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                Database tables are not set up yet.
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
                    <div class="cm-title">Regional Offices</div>
                    <div class="cm-subtitle"><?= $total ?> office<?= $total !== 1 ? 's' : '' ?> total</div>
                </div>
                <a href="add_regional_office.php" class="btn-primary">
                    <i class="fas fa-plus"></i> Add Regional Office
                </a>
            </div>

            <div class="cm-toolbar">
                <form class="cm-search-form" method="GET">
                    <input class="cm-search-input" type="text" name="search"
                           placeholder="Search offices…" value="<?= htmlspecialchars($search) ?>">
                    <button class="btn-primary" type="submit"><i class="fas fa-search"></i></button>
                    <?php if ($search): ?>
                    <a href="regional_offices.php" class="btn-secondary">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="cm-table-wrap">
                <table class="cm-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Region Name</th>
                            <th>Address</th>
                            <th>Designation</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($offices)): ?>
                        <tr><td colspan="7">
                            <div class="cm-empty">
                                <i class="fas fa-map-marker-alt"></i>
                                No regional offices found.
                            </div>
                        </td></tr>
                    <?php else: ?>
                        <?php foreach ($offices as $i => $o): ?>
                        <tr>
                            <td><?= $offset + $i + 1 ?></td>
                            <td><strong><?= htmlspecialchars($o['region_name']) ?></strong></td>
                            <td><?= htmlspecialchars($o['address']) ?></td>
                            <td><?= htmlspecialchars($o['designation']) ?></td>
                            <td><?= htmlspecialchars($o['phone']) ?></td>
                            <td>
                                <?php if ($o['status']): ?>
                                    <span class="badge-active">Active</span>
                                <?php else: ?>
                                    <span class="badge-inactive">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="cm-actions">
                                    <a class="btn-edit" href="edit_regional_office.php?id=<?= (int)$o['id'] ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form method="POST" onsubmit="return confirm('Toggle status?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
                                        <button class="<?= $o['status'] ? 'btn-warning' : 'btn-success' ?>" type="submit">
                                            <i class="fas fa-<?= $o['status'] ? 'eye-slash' : 'eye' ?>"></i>
                                            <?= $o['status'] ? 'Disable' : 'Enable' ?>
                                        </button>
                                    </form>
                                    <form method="POST" onsubmit="return confirm('Delete this office permanently?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
                                        <button class="btn-danger" type="submit">
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

                <?php if ($total_pages > 1): ?>
                <div class="cm-pagination">
                    <div class="cm-pagination-info">
                        Showing <?= $offset + 1 ?>–<?= min($offset + $per_page, $total) ?> of <?= $total ?>
                    </div>
                    <div class="cm-pagination-links">
                        <?php
                        $base = '?page=%d' . ($search ? '&search=' . urlencode($search) : '');
                        for ($p = 1; $p <= $total_pages; $p++):
                        ?>
                        <a class="cm-page-link <?= $p === $page ? 'active' : '' ?>"
                           href="<?= sprintf($base, $p) ?>"><?= $p ?></a>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>
</body>
</html>
