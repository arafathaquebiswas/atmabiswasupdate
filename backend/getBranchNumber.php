<?php
header('Content-Type: application/json');

require_once __DIR__ . '/Database/db.php';

/**
 * Live branch count for the homepage counter.
 *
 * Two branch tables exist. `branch` is the legacy one, still read by
 * Action/filter.php and used as a fallback elsewhere; `branches` is the one the
 * admin Branch section actually writes — add_branch.php inserts into it,
 * edit_branch.php updates it and branches.php deletes from it. This endpoint
 * counted `branch` first, and because that table still exists the query never
 * threw, so the fallback to `branches` was unreachable. The homepage sat on the
 * legacy table's 33 while the admin's table held 41, and no amount of adding or
 * deleting branches moved the number.
 *
 * `branches` is therefore queried first now, and the legacy table is reached
 * only where `branches` genuinely does not exist.
 *
 * status = 1 matches how the public side already defines a visible branch:
 * Action/get_branches.php and get_districts.php both filter on it, so a branch
 * the admin has deactivated disappears from the locator. Counting it on the
 * homepage would advertise a branch a visitor cannot then find. The filter is
 * applied only when the column is present, following the same
 * check-before-you-query approach the rest of the project uses for a schema
 * that has drifted between installs.
 */
try {
    $database = new Db();
    $conn     = $database->connect();

    if ($conn) {
        try {
            $hasStatus = $conn->query("SHOW COLUMNS FROM branches LIKE 'status'")->fetch() !== false;
            $sql = $hasStatus
                ? "SELECT COUNT(*) FROM branches WHERE status = 1"
                : "SELECT COUNT(*) FROM branches";
            echo json_encode(['value' => (int) $conn->query($sql)->fetchColumn()]);
            exit;
        } catch (Throwable $e) {
            // Only reached on an install that predates the `branches` table.
            $total = (int) $conn->query("SELECT COUNT(*) FROM branch")->fetchColumn();
            echo json_encode(['value' => $total]);
            exit;
        }
    }
} catch (Throwable $e) {
    error_log("getBranchNumber error: " . $e->getMessage());
}

// No count could be established. null rather than a number, so the homepage
// leaves the counter alone instead of animating to an invented figure.
echo json_encode(['value' => null]);
