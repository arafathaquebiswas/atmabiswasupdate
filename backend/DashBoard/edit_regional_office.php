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

$id = (int)($_GET['id'] ?? 0);
if ($id < 1) {
    header('Location: regional_offices.php');
    exit();
}

// Fetch existing record
$stmt = $conn->prepare("SELECT * FROM regional_offices WHERE id = :id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$office = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$office) {
    header('Location: regional_offices.php?msg=' . urlencode('Office not found.') . '&type=error');
    exit();
}

$errors = [];
$values = $office;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $values['region_name'] = trim($_POST['region_name'] ?? '');
        $values['address']     = trim($_POST['address']     ?? '');
        $values['designation'] = trim($_POST['designation'] ?? '');
        $values['phone']       = trim($_POST['phone']       ?? '');
        $values['status']      = (int)($_POST['status']     ?? 1);

        if ($values['region_name'] === '') $errors[] = 'Region name is required.';
        if ($values['address']     === '') $errors[] = 'Address is required.';
        if ($values['phone']       === '') $errors[] = 'Phone is required.';

        if (empty($errors)) {
            $upd = $conn->prepare(
                "UPDATE regional_offices
                 SET region_name = :region_name,
                     address     = :address,
                     designation = :designation,
                     phone       = :phone,
                     status      = :status
                 WHERE id = :id"
            );
            $upd->execute([
                ':region_name' => $values['region_name'],
                ':address'     => $values['address'],
                ':designation' => $values['designation'],
                ':phone'       => $values['phone'],
                ':status'      => $values['status'],
                ':id'          => $id,
            ]);
            header('Location: regional_offices.php?msg=' . urlencode('Regional office updated successfully.') . '&type=success');
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
    <title>Edit Regional Office — Admin</title>
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

            <div class="cm-header">
                <div>
                    <div class="cm-title">Edit Regional Office</div>
                    <div class="cm-subtitle">ID #<?= $id ?> — <?= htmlspecialchars($office['region_name']) ?></div>
                </div>
                <a href="regional_offices.php" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>

            <?php if (!empty($errors)): ?>
            <div class="cm-alert cm-alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
            </div>
            <?php endif; ?>

            <div class="cm-form-card">
                <form method="POST">
                    <?= csrf_field() ?>

                    <div class="cm-form-group">
                        <label>Region Name <span class="required">*</span></label>
                        <input class="cm-form-control" type="text" name="region_name" required
                               maxlength="255" value="<?= htmlspecialchars($values['region_name']) ?>">
                    </div>

                    <div class="cm-form-group">
                        <label>Address <span class="required">*</span></label>
                        <textarea class="cm-form-control" name="address" required
                                  rows="3"><?= htmlspecialchars($values['address']) ?></textarea>
                    </div>

                    <div class="cm-form-row">
                        <div class="cm-form-group">
                            <label>Designation <span class="required">*</span></label>
                            <input class="cm-form-control" type="text" name="designation" required
                                   maxlength="255" value="<?= htmlspecialchars($values['designation']) ?>">
                        </div>
                        <div class="cm-form-group">
                            <label>Phone <span class="required">*</span></label>
                            <input class="cm-form-control" type="text" name="phone" required
                                   maxlength="50" value="<?= htmlspecialchars($values['phone']) ?>">
                        </div>
                    </div>

                    <div class="cm-form-group">
                        <label>Status</label>
                        <select class="cm-form-control" name="status">
                            <option value="1" <?= $values['status'] ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= !$values['status'] ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="cm-form-actions">
                        <button class="btn-primary" type="submit">
                            <i class="fas fa-save"></i> Update Regional Office
                        </button>
                        <a href="regional_offices.php" class="btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
</body>
</html>
