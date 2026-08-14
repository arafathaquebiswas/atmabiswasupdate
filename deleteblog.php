<?php
require_once __DIR__ . '/backend/auth.php';

require_login();
require_super_admin('delete this blog post');

include_once 'backend/Database/db.php';

if (empty($_GET['blog_id'])) {
    header("Location: backend/DashBoard/dashboard.php");
    exit();
}

try {
    $db = new Db();
    $conn = $db->connect();

    if ($conn) {
        $sql = "SELECT cover_img FROM blogs WHERE blog_id = :blog_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":blog_id", $_GET['blog_id']);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($res[0]['cover_img']) && file_exists($res[0]['cover_img'])) {
            @chmod($res[0]['cover_img'], 0644);
            @unlink($res[0]['cover_img']);
        }

        $delsQL = "DELETE FROM blogs WHERE blog_id = :blog_id";
        $detStmt = $conn->prepare($delsQL);
        $detStmt->bindParam(":blog_id", $_GET['blog_id']);
        $detStmt->execute();
    }
} catch (Throwable $e) {
    error_log("deleteblog error: " . $e->getMessage());
}

header("Location: backend/DashBoard/dashboard.php");
exit();

