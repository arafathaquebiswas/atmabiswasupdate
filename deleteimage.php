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

  $sql = "SELECT * FROM img_upload WHERE img_id=:img_id";

  $stmt = $conn->prepare($sql);

  $stmt->bindParam(":img_id", $_GET['img_id']);

  $stmt->execute();

  $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $uploadDir =  $res[0]['img_path'];

  if (file_exists($uploadDir)) {

    chmod($uploadDir, 0644);
    unlink($uploadDir);
  } else {
      $delsQL = "DELETE FROM img_upload WHERE img_id=:img_id";

  $detStmt = $conn->prepare($delsQL);

  $detStmt->bindParam(":img_id", $_GET['img_id']);

  if($detStmt->execute()){
    header("Location: backend/DashBoard/dashboard.php");


  }
    exit();
  }

  $delsQL = "DELETE FROM img_upload WHERE img_id=:img_id";

  $detStmt = $conn->prepare($delsQL);

  $detStmt->bindParam(":img_id", $_GET['img_id']);

  $detStmt->execute();

  header("Location: backend/DashBoard/dashboard.php");
} catch (PDOException $e) {
  echo $e;
}
