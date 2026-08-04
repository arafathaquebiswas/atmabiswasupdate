<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../login/loging.php");
    exit();
}

require_once __DIR__ . '/../Database/db.php';

$db   = new Db();
$conn = $db->connect();

// Check which tables already exist
function tableExists(PDO $conn, string $table): bool {
    try {
        $conn->query("SELECT 1 FROM `$table` LIMIT 1");
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

$ro_exists  = tableExists($conn, 'regional_offices');
$br_exists  = tableExists($conn, 'branches');
$div_exists = tableExists($conn, 'divisions');
$messages   = [];
$errors     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --------------------------------------------------------
    // Create & seed regional_offices
    // --------------------------------------------------------
    if (!$ro_exists) {
        try {
            $conn->exec("
                CREATE TABLE `regional_offices` (
                    `id`            INT AUTO_INCREMENT PRIMARY KEY,
                    `region_name`   VARCHAR(255)  NOT NULL,
                    `address`       TEXT          NOT NULL,
                    `designation`   VARCHAR(255)  NOT NULL DEFAULT 'Regional Manager',
                    `phone`         VARCHAR(50)   NOT NULL,
                    `display_order` INT           NOT NULL DEFAULT 0,
                    `status`        TINYINT(1)    NOT NULL DEFAULT 1,
                    `created_at`    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at`    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX `idx_status`        (`status`),
                    INDEX `idx_display_order` (`display_order`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $messages[] = '✅ Table <strong>regional_offices</strong> created.';

            $conn->exec("
                INSERT INTO `regional_offices` (`region_name`, `address`, `designation`, `phone`, `display_order`, `status`) VALUES
                ('Chuadanga Region',  'Cinama Hall Para, Chuadanga',                          'Regional Manager', '01725-683174', 1, 1),
                ('Dingadah Region',   'Dingadah Khejura, Chuadanga Sadar, Chuadanga',         'Regional Manager', '01958-573119', 2, 1),
                ('AsmanKhali Region', 'AsmanKhali Bazar, AsmanKhali, Alamdanga, Chuadanga',   'Regional Manager', '01725-186276', 3, 1),
                ('Alamdanga Region',  'Rail Station Para, Alamdanga, Chuadanga',               'Regional Manager', '01958-573194', 4, 1),
                ('Kushtia Region',    'Stadium Para, Kushtia Sadar, Kushtia',                  'Regional Manager', '01958-573194', 5, 1),
                ('Jibonnagar Region', 'Jibonnagar Eidga Para, Jibonnagar, Chuadanga',          'Regional Manager', '01725-683174', 6, 1),
                ('Jhikorgasa Region', 'Jhikorgasa Pazila Mor, Jhikorgasa, Jessore',            'Regional Manager', '01721-505833', 7, 1),
                ('Chowgasha Region',  'Isapur Dewan Para, Chowgasha, Jessore',                 'Regional Manager', '01722-603003', 8, 1),
                ('Pangsha Region',    'Dotto Para, Pangsha, Rajbari',                          'Regional Manager', '01958-573119', 9, 1)
            ");
            $ro_count = $conn->query("SELECT COUNT(*) FROM regional_offices")->fetchColumn();
            $messages[] = "✅ Inserted <strong>$ro_count regional offices</strong> from original data.";
            $ro_exists = true;
        } catch (PDOException $e) {
            $errors[] = '❌ regional_offices: ' . htmlspecialchars($e->getMessage());
        }
    } else {
        $messages[] = 'ℹ️ Table <strong>regional_offices</strong> already exists — skipped.';
    }

    // --------------------------------------------------------
    // Create & populate branches (migrate from branch table)
    // --------------------------------------------------------
    if (!$br_exists) {
        try {
            $conn->exec("
                CREATE TABLE `branches` (
                    `id`            INT AUTO_INCREMENT PRIMARY KEY,
                    `branch_name`   VARCHAR(255)  NOT NULL,
                    `address`       TEXT          NOT NULL,
                    `division`      VARCHAR(100)  NOT NULL,
                    `district`      VARCHAR(100)  NOT NULL,
                    `display_order` INT           NOT NULL DEFAULT 0,
                    `status`        TINYINT(1)    NOT NULL DEFAULT 1,
                    `created_at`    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at`    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX `idx_status`        (`status`),
                    INDEX `idx_division`      (`division`),
                    INDEX `idx_display_order` (`display_order`),
                    INDEX `idx_division_name` (`division`, `branch_name`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $messages[] = '✅ Table <strong>branches</strong> created.';

            // Migrate from existing branch table
            $migrated = $conn->exec("
                INSERT INTO `branches` (`branch_name`, `address`, `division`, `district`, `display_order`, `status`)
                SELECT `branchName`, `branchLoc`, `division`, `dist`, 0, 1
                FROM `branch`
                WHERE `branchName` IS NOT NULL AND `branchName` != ''
                ORDER BY `division`, `branchName`
            ");
            $br_count = $conn->query("SELECT COUNT(*) FROM branches")->fetchColumn();
            $messages[] = "✅ Migrated <strong>$br_count branches</strong> from existing <code>branch</code> table.";
            $br_exists = true;
        } catch (PDOException $e) {
            $errors[] = '❌ branches: ' . htmlspecialchars($e->getMessage());
        }
    } else {
        $messages[] = 'ℹ️ Table <strong>branches</strong> already exists — skipped.';
    }

    // --------------------------------------------------------
    // Create & seed divisions (from DISTINCT values in branches)
    // --------------------------------------------------------
    if (!$div_exists) {
        try {
            $conn->exec("
                CREATE TABLE `divisions` (
                    `id`         INT          AUTO_INCREMENT PRIMARY KEY,
                    `name`       VARCHAR(100) NOT NULL,
                    `status`     TINYINT(1)   NOT NULL DEFAULT 1,
                    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY `uq_name` (`name`),
                    INDEX `idx_status`   (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $messages[] = '✅ Table <strong>divisions</strong> created.';

            // Seed from existing branch data
            if ($br_exists || tableExists($conn, 'branches')) {
                $seeded = $conn->exec("
                    INSERT IGNORE INTO `divisions` (`name`, `status`)
                    SELECT DISTINCT division, 1
                    FROM `branches`
                    WHERE division IS NOT NULL AND division != ''
                ");
                $div_count = $conn->query("SELECT COUNT(*) FROM divisions")->fetchColumn();
                $messages[] = "✅ Seeded <strong>$div_count division(s)</strong> from existing branch data.";
            }

            $div_exists = true;
        } catch (PDOException $e) {
            $errors[] = '❌ divisions: ' . htmlspecialchars($e->getMessage());
        }
    } else {
        $messages[] = 'ℹ️ Table <strong>divisions</strong> already exists — skipped.';
    }
}

// Current row counts
$ro_count  = $ro_exists  ? $conn->query("SELECT COUNT(*) FROM regional_offices")->fetchColumn() : null;
$br_count  = $br_exists  ? $conn->query("SELECT COUNT(*) FROM branches")->fetchColumn() : null;
$div_count = $div_exists ? $conn->query("SELECT COUNT(*) FROM divisions")->fetchColumn() : null;
$src_count = $conn->query("SELECT COUNT(*) FROM branch")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Tables Setup — Admin</title>
    <link rel="stylesheet" href="css/admin-sidebar.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/contacts.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="../images/logo/logo.png">
    <style>
        .setup-card { background:#fff; border-radius:10px; box-shadow:0 1px 3px rgba(0,0,0,.08); padding:2rem; max-width:700px; }
        .status-row { display:flex; justify-content:space-between; align-items:center; padding:.75rem 0; border-bottom:1px solid #f3f4f6; }
        .status-row:last-child { border-bottom:none; }
        code { background:#f3f4f6; padding:2px 6px; border-radius:4px; font-size:.85rem; }
    </style>
</head>
<body>
<div class="dashboard-container">
    <div class="sidebar-container"><?php include 'sidebar.php'; ?></div>
    <div class="main-content">
        <?php include 'navbar.inc.php'; ?>
        <div class="dashboard-main">

            <div class="cm-header">
                <div>
                    <div class="cm-title">Contact Tables Setup</div>
                    <div class="cm-subtitle">One-time setup — creates tables and migrates existing data</div>
                </div>
            </div>

            <?php foreach ($messages as $m): ?>
            <div class="cm-alert cm-alert-success"><?= $m ?></div>
            <?php endforeach; ?>

            <?php foreach ($errors as $e): ?>
            <div class="cm-alert cm-alert-error"><?= $e ?></div>
            <?php endforeach; ?>

            <div class="setup-card">
                <h3 style="margin-bottom:1.25rem;font-size:1rem;font-weight:700;color:#1f2937;">
                    Current Table Status
                </h3>

                <div class="status-row">
                    <div>
                        <code>regional_offices</code>
                        <?php if ($ro_exists): ?>
                            <span class="badge-active" style="margin-left:.5rem;">Exists</span>
                        <?php else: ?>
                            <span class="badge-inactive" style="margin-left:.5rem;">Missing</span>
                        <?php endif; ?>
                    </div>
                    <div style="color:#6b7280;font-size:.875rem;">
                        <?= $ro_exists ? "$ro_count rows" : 'Will create + seed 9 offices' ?>
                    </div>
                </div>

                <div class="status-row">
                    <div>
                        <code>branches</code>
                        <?php if ($br_exists): ?>
                            <span class="badge-active" style="margin-left:.5rem;">Exists</span>
                        <?php else: ?>
                            <span class="badge-inactive" style="margin-left:.5rem;">Missing</span>
                        <?php endif; ?>
                    </div>
                    <div style="color:#6b7280;font-size:.875rem;">
                        <?= $br_exists ? "$br_count rows" : "Will migrate $src_count rows from <code>branch</code>" ?>
                    </div>
                </div>

                <div class="status-row">
                    <div>
                        <code>divisions</code>
                        <?php if ($div_exists): ?>
                            <span class="badge-active" style="margin-left:.5rem;">Exists</span>
                        <?php else: ?>
                            <span class="badge-inactive" style="margin-left:.5rem;">Missing</span>
                        <?php endif; ?>
                    </div>
                    <div style="color:#6b7280;font-size:.875rem;">
                        <?php if ($div_exists): ?>
                            <?= $div_count ?> rows
                        <?php else: ?>
                            Will create + seed from <code>branches.division</code>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="status-row">
                    <div><code>branch</code> <span style="font-size:.8rem;color:#6b7280;">(source, read-only)</span></div>
                    <div style="color:#6b7280;font-size:.875rem;"><?= $src_count ?> rows</div>
                </div>

                <?php if (!$ro_exists || !$br_exists || !$div_exists): ?>
                <form method="POST" style="margin-top:1.5rem;">
                    <button class="btn-primary" type="submit" style="font-size:1rem;padding:.75rem 1.5rem;">
                        <i class="fas fa-database"></i>
                        Run Setup Now
                    </button>
                    <p style="margin-top:.75rem;font-size:.8rem;color:#6b7280;">
                        Safe to run — will not touch any existing tables or data.
                    </p>
                </form>
                <?php else: ?>
                <div style="margin-top:1.5rem;display:flex;gap:.75rem;flex-wrap:wrap;">
                    <a href="regional_offices.php" class="btn-primary">
                        <i class="fas fa-map-marker-alt"></i> Regional Offices
                    </a>
                    <a href="divisions.php" class="btn-primary">
                        <i class="fas fa-layer-group"></i> Divisions
                    </a>
                    <a href="branches.php" class="btn-primary">
                        <i class="fas fa-code-branch"></i> Branches
                    </a>
                </div>
                <p style="margin-top:.75rem;font-size:.8rem;color:#16a34a;font-weight:600;">
                    <i class="fas fa-check-circle"></i> All tables are ready. You can delete this setup file from the server.
                </p>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>
</body>
</html>
