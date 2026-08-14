<?php
require_once __DIR__ . '/../auth.php';

require_login();
require_super_admin('delete this job');

include_once '../Database/db.php';

$db = new Db();
$connection = $db->connect();

if (isset($_GET['id']) && isset($_GET['deptCode'])) {
    try {
        $job_id = htmlspecialchars($_GET['id']);

        // Fetch the image filename first
        $fetchImgSql = "SELECT cover_img FROM blogs WHERE blog_id = :job_id";
        $fetchStmt = $connection->prepare($fetchImgSql);
        $fetchStmt->bindParam(":job_id", $job_id);
        $fetchStmt->execute();

        $row = $fetchStmt->fetch(PDO::FETCH_ASSOC);

        if ($row && !empty($row['cover_img'])) {
            $imagePath = '../uploads/' . $row['cover_img'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        // Delete the blog/job
        $sql = "DELETE FROM jobs WHERE job_id = :job_id";
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(":job_id", $job_id);
        $stmt->execute();

        header("Location: updatejobs.php");
    } catch (PDOException $err) {
        echo $err;
    }
}