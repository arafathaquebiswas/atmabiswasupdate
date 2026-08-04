<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: /backend/login/loging.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: /backend/DashBoard/addJobPosition.php");
    exit();
}

include "Database/db.php";

$database = new Db();
$conn     = $database->connect();

$jobTitle = trim($_POST["jobtitle"] ?? '');
$jobSec   = trim($_POST["jobsector"] ?? '');

if (empty($jobTitle)) {
    header("Location: /backend/DashBoard/addJobPosition.php?error=empty");
    exit();
}

try {
    // --- Insert job title (skip if already exists) ---
    $checkTitle = $conn->prepare("SELECT jobid FROM jobcodes WHERE JobTitle = :job_title LIMIT 1");
    $checkTitle->bindParam(":job_title", $jobTitle);
    $checkTitle->execute();

    if ($checkTitle->rowCount() > 0) {
        header("Location: /backend/DashBoard/addJobPosition.php?success=exists");
        exit();
    }

    // Auto-generate job code: initials of each word + 2-digit random number
    // e.g. "Field Officer" → "FO47", "Senior Manager" → "SM13"
    $words    = preg_split('/\s+/', strtoupper(trim($jobTitle)));
    $initials = '';
    foreach ($words as $word) {
        if ($word !== '') $initials .= $word[0];
    }
    $jobCode = substr($initials, 0, 4) . rand(10, 99);

    $stmt = $conn->prepare("INSERT INTO jobcodes (JobTitle, JobCode) VALUES (:job_title, :job_code)");
    $stmt->bindParam(":job_title", $jobTitle);
    $stmt->bindParam(":job_code",  $jobCode);
    $stmt->execute();

    // --- Insert sector only if provided and not already in the table ---
    if (!empty($jobSec)) {
        $checkSec = $conn->prepare("SELECT sector_id FROM sectors WHERE sector_name = :sector LIMIT 1");
        $checkSec->bindParam(":sector", $jobSec);
        $checkSec->execute();

        if ($checkSec->rowCount() === 0) {
            $stmtSec = $conn->prepare("INSERT INTO sectors (sector_name) VALUES (:sector)");
            $stmtSec->bindParam(":sector", $jobSec);
            $stmtSec->execute();
        }
    }

    header("Location: /backend/DashBoard/addJobPosition.php?success=added");
    exit();

} catch (PDOException $e) {
    error_log("addJob_processing error: " . $e->getMessage());
    header("Location: /backend/DashBoard/addJobPosition.php?error=db");
    exit();
}
