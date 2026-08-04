<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

include "Database/db.php";

$database = new Db();
$conn = $database->connect();

$jobtitle = trim($_GET["job_title"] ?? '');

if (empty($jobtitle)) {
    echo json_encode([]);
    exit();
}

$sql = "SELECT JobCode FROM jobcodes WHERE JobTitle = :job_title LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bindParam(":job_title", $jobtitle);
$stmt->execute();

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
