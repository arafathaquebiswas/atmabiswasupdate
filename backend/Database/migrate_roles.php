<?php
/**
 * One-time migration: adds the `role` column to the admins table and promotes
 * a first super admin so the system is never left without one.
 *
 * Run once from the browser while logged in:   /backend/Database/migrate_roles.php
 * Or from the command line:                    php migrate_roles.php
 *                                              php migrate_roles.php --promote=owner@example.org
 *
 * Safe to run more than once; every step is skipped if already applied.
 */

$isCli = php_sapi_name() === 'cli';

if (!$isCli) {
    session_start();
    if (!isset($_SESSION['username'])) {
        die('Unauthorized. Please log in to the dashboard first, then revisit this page.');
    }
}

include __DIR__ . '/db.php';
$db   = new Db();
$conn = $db->connect();

$results = [];

/** Does admins.role already exist? */
function roleColumnExists(PDO $conn): bool
{
    $stmt = $conn->query("SHOW COLUMNS FROM admins LIKE 'role'");
    return $stmt->fetch() !== false;
}

// ---------------------------------------------------------------- step 1
if (roleColumnExists($conn)) {
    $results[] = ['skip', 'Add role column — already exists, skipped.'];
} else {
    try {
        $conn->exec(
            "ALTER TABLE admins ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'admin'"
        );
        $results[] = ['ok', 'Add role column — done. All existing accounts defaulted to "admin".'];
    } catch (PDOException $e) {
        $results[] = ['err', 'Add role column — ERROR: ' . $e->getMessage()];
    }
}

// ---------------------------------------------------------------- step 2
if (roleColumnExists($conn)) {
    try {
        $existing = (int) $conn->query(
            "SELECT COUNT(*) FROM admins WHERE role = 'super_admin'"
        )->fetchColumn();

        if ($existing > 0) {
            $results[] = ['skip', "Promote first super admin — {$existing} already present, skipped."];
        } else {
            // Who gets promoted, in order of preference:
            //   1. --promote=<email> on the command line
            //   2. the admin running this migration in the browser
            //   3. the oldest account (lowest adminId)
            $target = null;

            $promoteEmail = null;
            foreach ($argv ?? [] as $arg) {
                if (strpos($arg, '--promote=') === 0) {
                    $promoteEmail = substr($arg, strlen('--promote='));
                }
            }

            if ($promoteEmail) {
                $stmt = $conn->prepare("SELECT adminId, fullname, email FROM admins WHERE email = ?");
                $stmt->execute([$promoteEmail]);
                $target = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$target) {
                    $results[] = ['err', "Promote first super admin — no account with email {$promoteEmail}."];
                }
            } elseif (!$isCli && !empty($_SESSION['username'])) {
                $stmt = $conn->prepare("SELECT adminId, fullname, email FROM admins WHERE fullname = ? LIMIT 1");
                $stmt->execute([$_SESSION['username']]);
                $target = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            if (!$target) {
                $target = $conn->query(
                    "SELECT adminId, fullname, email FROM admins ORDER BY adminId ASC LIMIT 1"
                )->fetch(PDO::FETCH_ASSOC);
            }

            if (!$target) {
                $results[] = ['err', 'Promote first super admin — no admin accounts exist yet.'];
            } else {
                $stmt = $conn->prepare("UPDATE admins SET role = 'super_admin' WHERE adminId = ?");
                $stmt->execute([$target['adminId']]);
                $results[] = ['ok', sprintf(
                    'Promote first super admin — %s (%s) is now Super Admin.',
                    $target['fullname'],
                    $target['email'] ?: 'no email'
                )];
            }
        }
    } catch (PDOException $e) {
        $results[] = ['err', 'Promote first super admin — ERROR: ' . $e->getMessage()];
    }
}

// ---------------------------------------------------------------- step 3
if (roleColumnExists($conn)) {
    try {
        // Any value that is not exactly one of the two known roles is forced
        // down to the least privileged one.
        $stmt = $conn->prepare(
            "UPDATE admins SET role = 'admin' WHERE role NOT IN ('super_admin', 'admin')"
        );
        $stmt->execute();
        $fixed = $stmt->rowCount();
        $results[] = $fixed > 0
            ? ['ok', "Normalise unknown roles — {$fixed} row(s) reset to \"admin\"."]
            : ['skip', 'Normalise unknown roles — nothing to fix.'];
    } catch (PDOException $e) {
        $results[] = ['err', 'Normalise unknown roles — ERROR: ' . $e->getMessage()];
    }
}

// ---------------------------------------------------------------- report
if ($isCli) {
    foreach ($results as [$status, $msg]) {
        echo strtoupper($status) . ': ' . $msg . PHP_EOL;
    }
    exit(0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Role Migration - ATMABISWAS Admin</title>
    <link rel="icon" type="image/png" href="../../LOGO/NGO_logo_monogram.png">
    <style>
        body  { font-family: sans-serif; max-width: 600px; margin: 60px auto; padding: 0 20px; }
        h2    { color: #1e293b; }
        .ok   { color: #16a34a; }
        .skip { color: #d97706; }
        .err  { color: #dc2626; }
        li    { margin: 10px 0; font-size: .95rem; }
        .back { display:inline-block; margin-top:24px; padding:10px 22px; background:#4f46e5; color:#fff; border-radius:6px; text-decoration:none; }
        .note { margin-top:20px; padding:14px; background:#fffbeb; border:1px solid #fde68a; border-radius:6px; font-size:.9rem; color:#78350f; }
    </style>
</head>
<body>
    <h2>Role Migration Results</h2>
    <ul>
        <?php foreach ($results as [$status, $msg]): ?>
            <li class="<?php echo $status; ?>"><?php echo htmlspecialchars($msg); ?></li>
        <?php endforeach; ?>
    </ul>
    <div class="note">
        Log out and back in so your session picks up the new role, then assign
        roles to the other accounts from <strong>Manage Admins</strong>.
        Delete this file once the migration has run.
    </div>
    <a class="back" href="../DashBoard/manageAdmins.php">Go to Manage Admins</a>
</body>
</html>
