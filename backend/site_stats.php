<?php
/**
 * Editable homepage statistics.
 *
 * Two of the four homepage counters are figures nobody can derive: how many
 * people the organisation employs and how many clients it has served. They sat
 * as literals in index.js, so changing them meant editing and redeploying
 * JavaScript. They live in the database now and the admin edits them like any
 * other content.
 *
 * The other two counters are deliberately NOT here. Branches is a COUNT over
 * the branches table and years-of-foundation is derived from the current year;
 * copying either into a stored value would create a second number that could
 * disagree with the first, which is the failure that had the homepage showing
 * 33 branches while the admin's table held 41.
 *
 * The table creates and seeds itself on first use, the same way
 * about_us_content does in about_us_editor.php, so an install that has never
 * run a migration still works.
 */

require_once __DIR__ . '/Database/db.php';

/** Keys this module will read or write. Anything else is rejected. */
const SITE_STAT_KEYS = ['employees', 'served_clients'];

/** Shown until an admin saves something, and seeded on first use. */
const SITE_STAT_DEFAULTS = [
    'employees'      => 342,
    'served_clients' => 54880,
];

/**
 * Create the table if absent and seed the defaults.
 *
 * ON DUPLICATE KEY UPDATE on the key itself makes the seed a no-op once a row
 * exists, so this never overwrites a value the admin has set.
 */
function site_stats_ensure(PDO $conn): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    $conn->exec("
        CREATE TABLE IF NOT EXISTS `site_stats` (
            `id`         INT         NOT NULL AUTO_INCREMENT,
            `stat_key`   VARCHAR(50) NOT NULL,
            `stat_value` BIGINT      NOT NULL DEFAULT 0,
            `updated_at` TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `stat_key_unique` (`stat_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $seed = $conn->prepare(
        "INSERT INTO `site_stats` (`stat_key`, `stat_value`) VALUES (:k, :v)
         ON DUPLICATE KEY UPDATE `stat_key` = `stat_key`"
    );
    foreach (SITE_STAT_DEFAULTS as $k => $v) {
        $seed->execute([':k' => $k, ':v' => $v]);
    }
}

/**
 * All editable stats as key => int.
 *
 * Falls back to the defaults rather than throwing: a homepage counter is not
 * worth a fatal, and a missing row should read as "not customised yet".
 */
function site_stats_all(PDO $conn): array
{
    try {
        site_stats_ensure($conn);
        $rows = $conn->query("SELECT stat_key, stat_value FROM site_stats")
                     ->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (Throwable $e) {
        error_log('site_stats_all failed: ' . $e->getMessage());
        $rows = [];
    }

    $out = [];
    foreach (SITE_STAT_KEYS as $k) {
        $out[$k] = isset($rows[$k]) ? (int) $rows[$k] : SITE_STAT_DEFAULTS[$k];
    }
    return $out;
}

/**
 * Write one stat. Returns false for an unknown key or a negative value.
 */
function site_stats_set(PDO $conn, string $key, int $value): bool
{
    if (!in_array($key, SITE_STAT_KEYS, true) || $value < 0) {
        return false;
    }
    site_stats_ensure($conn);

    $stmt = $conn->prepare(
        "INSERT INTO `site_stats` (`stat_key`, `stat_value`) VALUES (:k, :v)
         ON DUPLICATE KEY UPDATE `stat_value` = VALUES(`stat_value`)"
    );
    return $stmt->execute([':k' => $key, ':v' => $value]);
}
