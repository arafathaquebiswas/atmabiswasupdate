<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../../login/loging.php");
    exit();
}

include '../../Database/db.php';

$db = new Db();
$connection = $db->connect();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    try {
        $job_code = trim($_POST["job_code"]);
        $job_title = trim($_POST["job_title"]);
        $deadline = trim($_POST["deadline"]);
        $job_dept = trim($_POST["job_dept"]);
        $job_location = trim($_POST["job_location"]);
        $salary_range = trim($_POST["salary_range"]);
        $job_experience = trim($_POST["job_experience"]);
        $job_skillset = trim($_POST["job_skillset"]);
        $job_description = trim($_POST["job_description"]);
        $job_req = trim($_POST["job_req"]);
        $job_benefits = trim($_POST["job_benefits"]);

        $vacancy       = trim($_POST["vacancy"]);
        $bdjobs_link   = trim($_POST["bdjobs_link"] ?? '');
        $apply_enabled = isset($_POST["apply_enabled"]) ? 1 : 0;

        $sql = "INSERT INTO jobs (job_code, job_title, deadline, job_dept, job_location, salary_range, job_experience, job_skillset, job_description, job_req, job_benefits, vacancy, bdjobs_link, apply_enabled)
                VALUES (:job_code, :job_title, :deadline, :job_dept, :job_location, :salary_range, :job_experience, :job_skillset, :job_description, :job_req, :job_benefits, :vacancy, :bdjobs_link, :apply_enabled)";


        $stmt = $connection->prepare($sql);

        $stmt->bindParam(':job_code', $job_code);
        $stmt->bindParam(':job_title', $job_title);
        $stmt->bindParam(':deadline', $deadline);
        $stmt->bindParam(':job_dept', $job_dept);
        $stmt->bindParam(':job_location', $job_location);
        $stmt->bindParam(':salary_range', $salary_range);
        $stmt->bindParam(':job_experience', $job_experience);
        $stmt->bindParam(':job_skillset', $job_skillset);
        $stmt->bindParam(':job_description', $job_description);
        $stmt->bindParam(':job_req', $job_req);
        $stmt->bindParam(":job_benefits", $job_benefits);
        $stmt->bindParam(":vacancy", $vacancy);
        $stmt->bindParam(":bdjobs_link",   $bdjobs_link);
        $stmt->bindParam(":apply_enabled", $apply_enabled, PDO::PARAM_INT);

        $stmt->execute();
        header("Location: ../dashboard.php");
    } catch (PDOException $err) {
        echo $err;
    }
}