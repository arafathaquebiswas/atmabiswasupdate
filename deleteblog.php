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

  $sql = "SELECT * FROM blogs WHERE blog_id=:blog_id";

  $stmt = $conn->prepare($sql);

  $stmt->bindParam(":blog_id", $_GET['blog_id']);

  $stmt->execute();  

  

  $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $uploadDir =  $res[0]['cover_img'];

  if (file_exists($uploadDir)) {

    chmod($uploadDir, 0644);
    unlink($uploadDir);
  } else {
     $delsQL = "DELETE FROM blogs WHERE blog_id=:blog_id";

      $detStmt = $conn->prepare($delsQL);

       $detStmt->bindParam(":blog_id", $_GET['blog_id']);

       if($detStmt->execute()){
          header("Location: backend/DashBoard/dashboard.php");
       }
    exit();
  }

  $delsQL = "DELETE FROM blogs WHERE blog_id=:blog_id";

  $detStmt = $conn->prepare($delsQL);

  $detStmt->bindParam(":blog_id", $_GET['blog_id']);

  $detStmt->execute();

  header("Location: backend/DashBoard/dashboard.php");
} catch (PDOException $e) {
  echo $e;
}
