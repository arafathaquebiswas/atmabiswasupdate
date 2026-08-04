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

$errors = [];
$values = ['branch_name' => '', 'address' => '', 'division' => '', 'district' => '', 'status' => 1];

$divisions = [];
try {
    $div_stmt  = $conn->query("SELECT name FROM divisions WHERE status = 1 ORDER BY name ASC");
    $divisions = $div_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    try {
        $div_stmt  = $conn->query("SELECT DISTINCT division FROM branches WHERE division != '' ORDER BY division ASC");
        $divisions = $div_stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e2) {
        $divisions = [];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $values['branch_name'] = trim($_POST['branch_name'] ?? '');
        $values['address']     = trim($_POST['address']     ?? '');
        $values['division']    = trim($_POST['division']    ?? '');
        $values['district']    = trim($_POST['district']    ?? '');
        $values['status']      = (int)($_POST['status']     ?? 1);

        if ($values['branch_name'] === '') $errors[] = 'Branch name is required.';
        if ($values['address']     === '') $errors[] = 'Address is required.';
        if ($values['division']    === '') $errors[] = 'Division is required.';
        if ($values['district']    === '') $errors[] = 'District is required.';

        if (empty($errors)) {
            $stmt = $conn->prepare(
                "INSERT INTO branches (branch_name, address, division, district, status)
                 VALUES (:branch_name, :address, :division, :district, :status)"
            );
            $stmt->execute([
                ':branch_name' => $values['branch_name'],
                ':address'     => $values['address'],
                ':division'    => $values['division'],
                ':district'    => $values['district'],
                ':status'      => $values['status'],
            ]);
            header('Location: branches.php?msg=' . rawurlencode('Branch added successfully.') . '&type=success');
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
    <title>Add Branch — Admin</title>
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
                    <div class="cm-title">Add Branch</div>
                    <div class="cm-subtitle">Add a new ATMABISWAS branch</div>
                </div>
                <a href="branches.php" class="btn-secondary">
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

                    <div class="cm-form-section">Branch Information</div>

                    <div class="cm-form-group">
                        <label>Branch Name <span class="required">*</span></label>
                        <input class="cm-form-control" type="text" name="branch_name" required
                               maxlength="255" placeholder="e.g. Chuadanga Branch"
                               value="<?= htmlspecialchars($values['branch_name']) ?>">
                    </div>

                    <div class="cm-form-group">
                        <label>Address <span class="required">*</span></label>
                        <textarea class="cm-form-control" name="address" required
                                  rows="3" placeholder="Full branch address"><?= htmlspecialchars($values['address']) ?></textarea>
                    </div>

                    <div class="cm-form-section">Location</div>

                    <div class="cm-form-row">
                        <div class="cm-form-group">
                            <label>Division <span class="required">*</span></label>
                            <?php if (empty($divisions)): ?>
                            <div class="cm-alert cm-alert-error" style="margin-bottom:.5rem;">
                                <i class="fas fa-exclamation-triangle"></i>
                                No active divisions found.
                                <a href="add_division.php" style="font-weight:700;color:#991b1b;text-decoration:underline;margin-left:.25rem;">
                                    Add a division first →
                                </a>
                            </div>
                            <select class="cm-form-control" name="division" required disabled>
                                <option value="">No divisions available</option>
                            </select>
                            <?php else: ?>
                            <select class="cm-form-control" id="divisionSelect" name="division" required>
                                <option value="">— Select Division —</option>
                                <?php foreach ($divisions as $div): ?>
                                <option value="<?= htmlspecialchars($div) ?>"
                                        <?= $values['division'] === $div ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($div) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="cm-form-hint">
                                Division not listed?
                                <a href="add_division.php" class="cm-add-link">
                                    <i class="fas fa-plus-circle"></i> Add a new division
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="cm-form-group">
                            <label>District <span class="required">*</span></label>
                            <select class="cm-form-control" id="districtSelect" name="district" required disabled>
                                <option value="">— Select division first —</option>
                            </select>
                            <input class="cm-form-control" id="districtText" type="text"
                                   maxlength="100" placeholder="Type district name"
                                   style="display:none; margin-top:.4rem;">
                            <div class="cm-form-hint" id="districtHint">
                                Select a division to load available districts.
                            </div>
                        </div>
                    </div>

                    <div class="cm-form-section">Settings</div>

                    <div class="cm-form-group">
                        <label>Status</label>
                        <select class="cm-form-control" name="status">
                            <option value="1" <?= $values['status'] ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= !$values['status'] ? 'selected' : '' ?>>Inactive</option>
                        </select>
                        <div class="cm-form-hint">Inactive branches are hidden from the Contact page.</div>
                    </div>

                    <div class="cm-form-actions">
                        <button class="btn-primary" type="submit"
                                <?= empty($divisions) ? 'disabled title="Add a division first"' : '' ?>>
                            <i class="fas fa-save"></i> Save Branch
                        </button>
                        <a href="branches.php" class="btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
<script>
(function () {
    var divisionSel  = document.getElementById('divisionSelect');
    var districtSel  = document.getElementById('districtSelect');
    var districtText = document.getElementById('districtText');
    var districtHint = document.getElementById('districtHint');
    var preselect    = '<?= htmlspecialchars(addslashes($values['district']), ENT_QUOTES) ?>';

    if (!divisionSel || !districtSel) return;

    function showSelectMode(districts, pre) {
        districtSel.innerHTML = '';
        var ph = document.createElement('option');
        ph.value = ''; ph.textContent = '— Select District —';
        districtSel.appendChild(ph);

        districts.forEach(function (d) {
            var opt = document.createElement('option');
            opt.value = d; opt.textContent = d;
            if (pre && d === pre) opt.selected = true;
            districtSel.appendChild(opt);
        });

        var other = document.createElement('option');
        other.value = '__other__';
        other.textContent = '— Other (type manually) —';
        districtSel.appendChild(other);

        districtSel.name = 'district'; districtSel.required = true; districtSel.disabled = false;
        districtSel.style.display = '';
        districtText.name = ''; districtText.required = false; districtText.style.display = 'none';
        districtHint.textContent = 'Districts under this division. Choose "Other" to enter a new one.';
    }

    function showTextMode(pre) {
        districtSel.name = ''; districtSel.required = false; districtSel.style.display = 'none';
        districtText.name = 'district'; districtText.required = true;
        districtText.style.display = ''; districtText.value = pre || '';
        districtText.focus();
        districtHint.innerHTML = 'No existing districts — type a new name. '
            + '<a href="#" id="backToList" class="cm-add-link">Back to list</a>';
        var back = document.getElementById('backToList');
        if (back) back.addEventListener('click', function (e) {
            e.preventDefault(); loadDistricts(divisionSel.value, null);
        });
    }

    function loadDistricts(division, pre) {
        if (!division) {
            districtSel.innerHTML = '<option value="">— Select division first —</option>';
            districtSel.name = 'district'; districtSel.required = true;
            districtSel.disabled = true; districtSel.style.display = '';
            districtText.name = ''; districtText.required = false; districtText.style.display = 'none';
            districtHint.textContent = 'Select a division to load available districts.';
            return;
        }

        districtSel.innerHTML = '<option value="">Loading…</option>';
        districtSel.disabled = true; districtSel.style.display = '';
        districtText.style.display = 'none';
        districtHint.textContent = 'Loading districts…';

        var xhr = new XMLHttpRequest();
        xhr.open('POST', '../../Action/get_districts.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (xhr.status !== 200) {
                districtSel.innerHTML = '<option value="">Error loading</option>'; return;
            }
            try {
                var list = JSON.parse(xhr.responseText);
                if (list.length > 0) {
                    showSelectMode(list, pre);
                } else {
                    showTextMode(pre);
                }
            } catch (e) {
                districtSel.innerHTML = '<option value="">Error</option>';
            }
        };
        xhr.onerror = function () {
            districtSel.innerHTML = '<option value="">Network error</option>';
        };
        xhr.send('division=' + encodeURIComponent(division));
    }

    districtSel.addEventListener('change', function () {
        if (this.value === '__other__') showTextMode('');
    });

    divisionSel.addEventListener('change', function () {
        loadDistricts(this.value, null);
    });

    if (divisionSel.value) loadDistricts(divisionSel.value, preselect);
}());
</script>
</body>
</html>
