<?php
require_once __DIR__ . '/backend/auth.php';

require_login();
require_super_admin('delete this job application');

include_once "backend/Database/db.php";

$database = new Db();

$conn = $database->connect();

$sql = "SELECT * FROM cv_applications WHERE applicationId = :applicationId";

$stmt = $conn->prepare($sql);


$applicationId = htmlspecialchars($_GET["applicationId"]);

$stmt->bindParam(":applicationId", $applicationId);

$stmt->execute();

$cvs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$fileDir = $cvs[0]["fileDir"];

if (file_exists($fileDir)) {
    chmod($fileDir, 0644);
    // 0644 Grants read and write access
    unlink($fileDir);


    $delSql = "DELETE FROM cv_applications WHERE applicationId = :applicationId";

    $delStmt = $conn->prepare($delSql);

    $delStmt->bindParam(":applicationId", $applicationId);

    $delStmt->execute();

    header("Location: backend/DashBoard/dashboard.php");
} else {
    $delSql = "DELETE FROM cv_applications WHERE applicationId = :applicationId";

    $delStmt = $conn->prepare($delSql);

    $delStmt->bindParam(":applicationId", $applicationId);

    if($delStmt->execute()){
        header("Location: backend/DashBoard/dashboard.php");
    }
    exit();
}
