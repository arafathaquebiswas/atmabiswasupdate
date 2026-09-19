<?php
/**
 * Homepage counter values the admin controls, as JSON.
 *
 * Mirrors getBranchNumber.php, which is how the homepage already reads a live
 * figure. Kept separate from it on purpose: branches is a COUNT over a table
 * and these two are stored values, and folding them together would mean
 * touching the branch counter to change a staff number.
 */
header('Content-Type: application/json');

require_once __DIR__ . '/site_stats.php';

try {
    $conn = (new Db())->connect();
    if ($conn) {
        echo json_encode(site_stats_all($conn));
        exit;
    }
} catch (Throwable $e) {
    error_log('getHomepageStats error: ' . $e->getMessage());
}

// Defaults rather than nulls: unlike the branch count these are not derived
// from anything, so the seeded figure is still the best answer available.
echo json_encode(SITE_STAT_DEFAULTS);
