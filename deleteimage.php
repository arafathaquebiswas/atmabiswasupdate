<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: backend/login/loging.php");
    exit();
}

include_once 'backend/Database/db.php';

if (empty($_GET['img_id'])) {
    header("Location: backend/DashBoard/dashboard.php");
    exit();
}

try {
    $db = new Db();
    $conn = $db->connect();

    if ($conn) {
        $sql = "SELECT img_path FROM img_upload WHERE img_id = :img_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":img_id", $_GET['img_id']);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($res[0]['img_path']) && file_exists($res[0]['img_path'])) {
            @chmod($res[0]['img_path'], 0644);
            @unlink($res[0]['img_path']);
        }

        $delsQL = "DELETE FROM img_upload WHERE img_id = :img_id";
        $detStmt = $conn->prepare($delsQL);
        $detStmt->bindParam(":img_id", $_GET['img_id']);
        $detStmt->execute();
    }
} catch (Throwable $e) {
    error_log("deleteimage error: " . $e->getMessage());
}

header("Location: backend/DashBoard/dashboard.php");
exit();

