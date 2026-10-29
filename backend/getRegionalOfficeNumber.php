<?php
header('Content-Type: application/json');

require_once __DIR__ . '/Database/db.php';

/**
 * Live regional office count for the homepage counter.
 *
 * Counts what the admin Regional Offices section manages, so adding, deleting
 * or deactivating an office changes the homepage on its own. status = 1
 * matches the public office list (Action/get_regional_offices.php), so the
 * homepage never advertises an office a visitor cannot then find. The filter
 * is applied only when the column is present, as getBranchNumber.php does.
 */
try {
    $conn = (new Db())->connect();

    if ($conn) {
        $hasStatus = $conn->query("SHOW COLUMNS FROM regional_offices LIKE 'status'")->fetch() !== false;
        $sql = $hasStatus
            ? "SELECT COUNT(*) FROM regional_offices WHERE status = 1"
            : "SELECT COUNT(*) FROM regional_offices";
        echo json_encode(['value' => (int) $conn->query($sql)->fetchColumn()]);
        exit;
    }
} catch (Throwable $e) {
    error_log("getRegionalOfficeNumber error: " . $e->getMessage());
}

// No count could be established. null rather than a number, so the homepage
// leaves the counter alone instead of animating to an invented figure.
echo json_encode(['value' => null]);
