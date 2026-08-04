<?php

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__ . '/Database/db.php';

$positions = [];
try {
    $database = new Db();
    $conn     = $database->connect();

    if ($conn) {
        $sql  = "SELECT JobTitle FROM jobcodes ORDER BY JobTitle ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($jobs as $job) {
            if (!empty($job["JobTitle"])) {
                $positions[] = $job["JobTitle"];
            }
        }
    }
} catch (Throwable $e) {
    error_log("get_job_position error: " . $e->getMessage());
}

echo json_encode($positions, JSON_UNESCAPED_UNICODE);