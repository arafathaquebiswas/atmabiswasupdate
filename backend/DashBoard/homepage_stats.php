<?php
/**
 * Editor for the two homepage counters that are not derived from anything.
 *
 * Employees and Served Clients were literals in index.js, so changing a staff
 * number meant editing JavaScript and redeploying. They are stored values now.
 *
 * Branches and Years of Foundation are deliberately absent from this screen.
 * Branches is a COUNT over the branches table and the year count is derived
 * from today's date; giving either an editable copy would create a second
 * number free to disagree with the first, which is exactly how the homepage
 * came to show 33 branches while the admin's table held 41.
 */
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../site_stats.php';

require_login();

$message = '';
$messageType = '';
$conn = null;

try {
    $conn = (new Db())->connect();
} catch (Throwable $e) {
    error_log('homepage_stats connect failed: ' . $e->getMessage());
}

if ($conn && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $saved = 0;
    foreach (SITE_STAT_KEYS as $key) {
        if (!isset($_POST[$key])) {
            continue;
        }
        // Strip separators an admin may type ("54,880") before validating.
        $raw = preg_replace('/[^0-9]/', '', (string) $_POST[$key]);
        if ($raw === '') {
            $message = 'Please enter a number for every field.';
            $messageType = 'error';
            break;
        }
        if (site_stats_set($conn, $key, (int) $raw)) {
            $saved++;
        }
    }
    if ($messageType !== 'error') {
        $message = $saved > 0
            ? 'Homepage statistics updated. The homepage shows the new values immediately.'
            : 'Nothing was changed.';
        $messageType = $saved > 0 ? 'success' : 'error';
    }
}

$stats = $conn ? site_stats_all($conn) : SITE_STAT_DEFAULTS;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage Statistics - ATMABISWAS</title>
    <link rel="stylesheet" href="css/uploadfile.css">
    <link rel="stylesheet" href="css/admin-sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="../images/logo/logo.png">
    <style>
        .stats-wrap { max-width: 760px; margin: 1.5rem auto 3rem; padding: 0 2rem; }
        .stats-card { background:#fff; border:1px solid #e2e8f0; border-radius:14px;
                      padding:1.75rem; box-shadow:0 4px 16px rgba(15,23,42,.05); }
        .stats-card h2 { margin:0 0 .35rem; font-size:1.15rem; color:#0f172a; }
        .stats-sub { color:#64748b; font-size:.85rem; margin:0 0 1.5rem; line-height:1.55; }
        .stat-row { margin-bottom:1.25rem; }
        .stat-row label { display:block; font-weight:600; font-size:.9rem; color:#1e293b; margin-bottom:.4rem; }
        .stat-row input { width:100%; padding:.7rem .9rem; border:1.5px solid #e2e8f0;
                          border-radius:8px; font-size:1rem; font-family:inherit; color:#0f172a; }
        .stat-row input:focus { outline:none; border-color:#2563eb; }
        .stat-hint { font-size:.75rem; color:#94a3b8; margin-top:.35rem; }
        .stats-save { background:#0f766e; color:#fff; border:none; padding:.75rem 1.6rem;
                      border-radius:8px; font-weight:600; font-size:.92rem; cursor:pointer; font-family:inherit; }
        .stats-save:hover { background:#115e59; }
        .stats-msg { padding:.8rem 1rem; border-radius:8px; margin-bottom:1.25rem; font-size:.88rem; }
        .stats-msg.success { background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; }
        .stats-msg.error   { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
        .stats-note { margin-top:1.5rem; padding-top:1.25rem; border-top:1px solid #f1f5f9;
                      font-size:.8rem; color:#64748b; line-height:1.6; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar-container">
            <?php include 'sidebar.php'; ?>
        </div>
        <div class="main-content">
            <div class="stats-wrap">
                <?php if ($message): ?>
                    <div class="stats-msg <?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>

                <div class="stats-card">
                    <h2>Homepage Statistics</h2>
                    <p class="stats-sub">
                        These two numbers appear in the counter row on the homepage. Save here and the
                        homepage shows the new figure on its next load &mdash; nothing else needs doing.
                    </p>

                    <form method="POST">
                        <div class="stat-row">
                            <label for="employees">Employees</label>
                            <input type="text" inputmode="numeric" id="employees" name="employees"
                                   value="<?= htmlspecialchars((string) $stats['employees']) ?>" required>
                            <div class="stat-hint">Whole number. Commas are fine &mdash; they are stripped on save.</div>
                        </div>

                        <div class="stat-row">
                            <label for="served_clients">Served Clients</label>
                            <input type="text" inputmode="numeric" id="served_clients" name="served_clients"
                                   value="<?= htmlspecialchars((string) $stats['served_clients']) ?>" required>
                            <div class="stat-hint">Whole number. Shown on the homepage with thousands separators.</div>
                        </div>

                        <button type="submit" class="stats-save">
                            <i class="fas fa-save"></i> Save Statistics
                        </button>
                    </form>

                    <div class="stats-note">
                        <strong>Branches</strong> and <strong>Years of Foundation</strong> are not editable here
                        by design. Branches is counted live from the Branch section, so adding or removing a
                        branch updates the homepage on its own, and Years of Foundation is calculated from the
                        founding year (1991) and increases itself every January. An editable copy of either
                        could drift out of step with the real figure.
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
