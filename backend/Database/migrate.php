<?php
/**
 * One-time migration: adds bdjobs_link and apply_enabled columns to the jobs table.
 * Visit this URL once, then you can delete this file.
 */
session_start();
if (!isset($_SESSION['username'])) {
    die('Unauthorized. Please log in to the dashboard first, then revisit this page.');
}

include 'db.php';
$db   = new Db();
$conn = $db->connect();

$migrations = [
    'Add bdjobs_link column'   => "ALTER TABLE jobs ADD COLUMN bdjobs_link   VARCHAR(500) DEFAULT NULL",
    'Add apply_enabled column' => "ALTER TABLE jobs ADD COLUMN apply_enabled TINYINT(1)   NOT NULL DEFAULT 1",
];

$results = [];
foreach ($migrations as $label => $sql) {
    try {
        $conn->exec($sql);
        $results[] = ['ok', $label . ' — done.'];
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            $results[] = ['skip', $label . ' — column already exists, skipped.'];
        } else {
            $results[] = ['err', $label . ' — ERROR: ' . $e->getMessage()];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Database Migration - ATMABISWAS Admin</title>
    <link rel="icon" type="image/png" href="../../LOGO/NGO_logo_monogram.png">
    <style>
        body  { font-family: sans-serif; max-width: 600px; margin: 60px auto; padding: 0 20px; }
        h2    { color: #1e293b; }
        .ok   { color: #16a34a; }
        .skip { color: #d97706; }
        .err  { color: #dc2626; }
        li    { margin: 10px 0; font-size: .95rem; }
        .back { display:inline-block; margin-top:24px; padding:10px 22px; background:#4f46e5; color:#fff; border-radius:6px; text-decoration:none; }
    </style>
</head>
<body>
    <h2>Migration Results</h2>
    <ul>
        <?php foreach ($results as [$status, $msg]): ?>
            <li class="<?php echo $status; ?>"><?php echo htmlspecialchars($msg); ?></li>
        <?php endforeach; ?>
    </ul>
    <a class="back" href="../DashBoard/createjob.php">Go to Create Job</a>
</body>
</html>
