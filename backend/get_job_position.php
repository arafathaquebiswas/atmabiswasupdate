<?php

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

include "Database/db.php";

$database = new Db();

$conn = $database->connect();

$sql = "SELECT JobTitle FROM jobcodes";

$stmt = $conn->prepare($sql);

$stmt->execute();

$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$positions = [];
foreach ($jobs as $job) {
    $positions[] = $job["JobTitle"];
}

echo json_encode($positions);