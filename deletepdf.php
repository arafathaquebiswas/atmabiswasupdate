<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: backend/login/loging.php");
    exit();
}

include 'backend/Database/db.php';

$db = new Db();
$conn = $db->connect();

try {

  $sql = "SELECT * FROM pdsfiles WHERE pdf_id=:pdf_id";

  $stmt = $conn->prepare($sql);

  $stmt->bindParam(":pdf_id", $_GET['pdf_id']);

  $stmt->execute();

  $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $uploadDir =  $res[0]['pdf_path'];

  if (file_exists($uploadDir)) {

    chmod($uploadDir, 0644);
    unlink($uploadDir);
  } else {
    $delsQL = "DELETE FROM pdsfiles WHERE pdf_id=:pdf_id";

    $detStmt = $conn->prepare($delsQL);

    $detStmt->bindParam(":pdf_id", $_GET['pdf_id']);

    if($detStmt->execute()){
       header("Location: backend/DashBoard/dashboard.php");
    }
    exit();
  }

  $delsQL = "DELETE FROM pdsfiles WHERE pdf_id=:pdf_id";

  $detStmt = $conn->prepare($delsQL);

  $detStmt->bindParam(":pdf_id", $_GET['pdf_id']);

  $detStmt->execute();

  header("Location: backend/DashBoard/dashboard.php");
} catch (PDOException $e) {
  echo $e;
}
