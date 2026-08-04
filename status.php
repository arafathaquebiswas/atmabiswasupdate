<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/backend/Database/db.php';

// ── Live checks — every value below reflects a real check performed
//    on this request, not a static/hardcoded claim. ──────────────────

$dbOk = false;
$branchDataOk = false;

try {
    $db   = new Db();
    $conn = $db->connect();
    $dbOk = true;

    try {
        $conn->query("SELECT 1 FROM branches LIMIT 1");
        $branchDataOk = true;
    } catch (Exception $e) {
        // DB is reachable but the branches table query failed —
        // reported as "degraded", not fully down.
        $branchDataOk = false;
    }
} catch (Exception $e) {
    error_log('status.php: database check failed: ' . $e->getMessage());
    $dbOk = false;
}

$maintenanceActive = file_exists(__DIR__ . '/.maintenance');

// "Last update" is a filesystem timestamp on a core file, not a formal
// CI/CD deployment log (this site doesn't have one) — labeled honestly.
$lastUpdate = filemtime(__DIR__ . '/index.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-EZVV9DWWY7"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', 'G-EZVV9DWWY7');
    </script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Status &ndash; ATMABISWAS (আত্মবিশ্বাস) Bangladesh</title>
    <meta name="robots" content="noindex, follow">
    <link rel="icon" type="image/png" href="LOGO/NGO_logo_monogram.png">
    <link rel="stylesheet" href="pages.css?v=<?php echo filemtime(__DIR__ . '/pages.css'); ?>">
    <link rel="stylesheet" href="error.css?v=<?php echo filemtime(__DIR__ . '/error.css'); ?>">
    <link rel="stylesheet" href="status.css?v=<?php echo filemtime(__DIR__ . '/status.css'); ?>">
</head>
<body>
    <?php include 'Navbar.php' ?>

    <main>
    <section class="page-hero">
        <div class="page-hero-inner">
            <h1>System Status</h1>
            <p>Live status of the ATMABISWAS website and its core services</p>
        </div>
    </section>

    <div class="status-grid">
        <?php if ($maintenanceActive): ?>
        <div class="status-banner" role="status">
            <i class="fas fa-triangle-exclamation" aria-hidden="true"></i>
            Scheduled maintenance is currently active. Some features may be temporarily unavailable.
        </div>
        <?php endif; ?>

        <div class="status-row">
            <div class="status-row-left">
                <span class="status-dot ok" aria-hidden="true"></span>
                <div>
                    <div class="status-label">Website</div>
                    <div class="status-detail">This page loaded successfully</div>
                </div>
            </div>
            <span class="status-value ok">Online</span>
        </div>

        <div class="status-row">
            <div class="status-row-left">
                <span class="status-dot <?= $dbOk ? 'ok' : 'down' ?>" aria-hidden="true"></span>
                <div>
                    <div class="status-label">Database</div>
                    <div class="status-detail"><?= $dbOk ? 'Connection established' : 'Unable to connect' ?></div>
                </div>
            </div>
            <span class="status-value <?= $dbOk ? 'ok' : 'down' ?>"><?= $dbOk ? 'Connected' : 'Down' ?></span>
        </div>

        <div class="status-row">
            <div class="status-row-left">
                <?php
                    $branchStatus = !$dbOk ? 'down' : ($branchDataOk ? 'ok' : 'warn');
                    $branchText   = !$dbOk ? 'Unavailable (database down)' : ($branchDataOk ? 'Branch/location data reachable' : 'Degraded — query failed');
                    $branchLabel  = !$dbOk ? 'Down' : ($branchDataOk ? 'Operational' : 'Degraded');
                ?>
                <span class="status-dot <?= $branchStatus ?>" aria-hidden="true"></span>
                <div>
                    <div class="status-label">Dynamic Features (Branch Locator, Contact)</div>
                    <div class="status-detail"><?= $branchText ?></div>
                </div>
            </div>
            <span class="status-value <?= $branchStatus ?>"><?= $branchLabel ?></span>
        </div>

        <div class="status-row">
            <div class="status-row-left">
                <span class="status-dot <?= $maintenanceActive ? 'warn' : 'ok' ?>" aria-hidden="true"></span>
                <div>
                    <div class="status-label">Maintenance Mode</div>
                    <div class="status-detail"><?= $maintenanceActive ? 'Currently active' : 'Not active' ?></div>
                </div>
            </div>
            <span class="status-value <?= $maintenanceActive ? 'warn' : 'ok' ?>"><?= $maintenanceActive ? 'Active' : 'Off' ?></span>
        </div>

        <p class="status-meta">
            Last file update: <?= date('F j, Y \a\t g:i A', $lastUpdate) ?> (server time)<br>
            Checks performed live at page load, <?= date('g:i:s A T') ?> — refresh to check again.
        </p>

        <div class="status-actions">
            <button type="button" class="err-btn err-btn-secondary" onclick="window.location.reload()">
                <i class="fas fa-rotate-right" aria-hidden="true"></i> Refresh Status
            </button>
        </div>
    </div>

    </main>
    <?php include 'footer.php' ?>
</body>
</html>
