<?php

require_once __DIR__ . '/auth.php';

require_login();
require_super_admin('delete this job position');

include "Database/db.php";

$database = new Db();

$conn = $database->connect();

$jobId = htmlspecialchars($_GET["job_id"]);

$sql = "DELETE FROM jobcodes WHERE jobid= :jobid";

$stmt = $conn->prepare($sql);

$stmt->bindParam(":jobid", $jobId);

if ($stmt->execute()) {
    header("Location: DashBoard/dashboard.php");
} else {
    echo "Unable to delete Job position of id {$jobId}";
}
