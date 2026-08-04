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
                $stmt = $conn->prepare("DELETE FROM branches WHERE id = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                header("Location: branches.php?msg=" . rawurlencode('Branch deleted successfully.') . "&type=success");
                exit();
            } elseif ($action === 'toggle') {
                $stmt = $conn->prepare("UPDATE branches SET status = IF(status = 1, 0, 1) WHERE id = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $qs = [];
                if (!empty($_GET['page']) && (int)$_GET['page'] > 1) $qs['page'] = (int)$_GET['page'];
                if (!empty($_GET['search'])) $qs['search'] = $_GET['search'];
                if (!empty($_GET['division'])) $qs['division'] = $_GET['division'];
                $qs['msg']  = 'Status updated.';
                $qs['type'] = 'success';
                header("Location: branches.php?" . http_build_query($qs));
                exit();
            }
        } catch (PDOException $e) {
            $msg      = 'Error: ' . htmlspecialchars($e->getMessage());
            $msg_type = 'error';
        }
    }
}

// Flash from redirect
if (isset($_GET['msg'])) {
    $msg = htmlspecialchars($_GET['msg']);
    $msg_type = $_GET['type'] ?? 'success';
}

// Fetch distinct divisions for filter (safe — branches table may not exist yet)
try {
    $div_stmt  = $conn->query("SELECT DISTINCT division FROM branches WHERE division != '' ORDER BY division ASC");
    $divisions = $div_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $divisions = [];
}

// Params
$per_page   = 10;
$page       = max(1, (int)($_GET['page'] ?? 1));
$search     = trim($_GET['search'] ?? '');
$filter_div = trim($_GET['division'] ?? '');
$offset     = ($page - 1) * $per_page;

$conditions = [];
$params     = [];

if ($search !== '') {
    $conditions[] = "(branch_name LIKE :search OR address LIKE :search2 OR district LIKE :search3)";
    $params[':search']  = "%$search%";
    $params[':search2'] = "%$search%";
    $params[':search3'] = "%$search%";
}
if ($filter_div !== '') {
    $conditions[] = "division = :division";
    $params[':division'] = $filter_div;
}
$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

$table_missing = false;

try {
    $count_stmt = $conn->prepare("SELECT COUNT(*) FROM branches $where");
    $count_stmt->execute($params);
    $total = (int)$count_stmt->fetchColumn();
    $total_pages = max(1, ceil($total / $per_page));

    $list_stmt = $conn->prepare("SELECT * FROM branches $where ORDER BY division ASC, branch_name ASC LIMIT :limit OFFSET :offset");
    foreach ($params as $k => $v) $list_stmt->bindValue($k, $v, PDO::PARAM_STR);
    $list_stmt->bindValue(':limit',  $per_page, PDO::PARAM_INT);
    $list_stmt->bindValue(':offset', $offset,   PDO::PARAM_INT);
    $list_stmt->execute();
    $branches = $list_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $table_missing = true;
    $total = 0; $total_pages = 1; $branches = [];
}

$query_string = http_build_query(array_filter(['search' => $search, 'division' => $filter_div]));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Branches — Admin</title>
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
                    <div class="cm-title">Branches</div>
                    <div class="cm-subtitle"><?= $total ?> branch<?= $total !== 1 ? 'es' : '' ?> total</div>
                </div>
                <a href="add_branch.php" class="btn-primary">
                    <i class="fas fa-plus"></i> Add Branch
                </a>
            </div>

            <div class="cm-toolbar">
                <form class="cm-search-form" method="GET">
                    <input class="cm-search-input" type="text" name="search"
                           placeholder="Search branches…" value="<?= htmlspecialchars($search) ?>">
                    <select class="cm-filter-select" name="division">
                        <option value="">All Divisions</option>
                        <?php foreach ($divisions as $div): ?>
                        <option value="<?= htmlspecialchars($div) ?>"
                                <?= $filter_div === $div ? 'selected' : '' ?>>
                            <?= htmlspecialchars($div) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn-primary" type="submit"><i class="fas fa-filter"></i> Filter</button>
                    <?php if ($search || $filter_div): ?>
                    <a href="branches.php" class="btn-secondary">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="cm-table-wrap">
                <table class="cm-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Branch Name</th>
                            <th>Address</th>
                            <th>Division</th>
                            <th>District</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($branches)): ?>
                        <tr><td colspan="7">
                            <div class="cm-empty">
                                <i class="fas fa-code-branch"></i>
                                No branches found.
                            </div>
                        </td></tr>
                    <?php else: ?>
                        <?php foreach ($branches as $i => $b): ?>
                        <tr>
                            <td><?= $offset + $i + 1 ?></td>
                            <td><strong><?= htmlspecialchars($b['branch_name']) ?></strong></td>
                            <td><?= htmlspecialchars($b['address']) ?></td>
                            <td><?= htmlspecialchars($b['division']) ?></td>
                            <td><?= htmlspecialchars($b['district']) ?></td>
                            <td>
                                <?php if ($b['status']): ?>
                                    <span class="badge-active">Active</span>
                                <?php else: ?>
                                    <span class="badge-inactive">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="cm-actions">
                                    <a class="btn-edit" href="edit_branch.php?id=<?= (int)$b['id'] ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form method="POST" onsubmit="return confirm('Toggle status?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                                        <button class="<?= $b['status'] ? 'btn-warning' : 'btn-success' ?>" type="submit">
                                            <i class="fas fa-<?= $b['status'] ? 'eye-slash' : 'eye' ?>"></i>
                                            <?= $b['status'] ? 'Disable' : 'Enable' ?>
                                        </button>
                                    </form>
                                    <form method="POST" onsubmit="return confirm('Delete this branch permanently?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
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
                        $base = '?page=%d' . ($query_string ? '&' . $query_string : '');
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
