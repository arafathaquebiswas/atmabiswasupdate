<?php
require_once __DIR__ . '/backend/auth.php';

require_login();
require_super_admin('delete this PDF');

include_once 'backend/Database/db.php';

if (empty($_GET['pdf_id'])) {
    header("Location: backend/DashBoard/dashboard.php");
    exit();
}

try {
    $db = new Db();
    $conn = $db->connect();

    if ($conn) {
        $sql = "SELECT pdf_path FROM pdsfiles WHERE pdf_id = :pdf_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":pdf_id", $_GET['pdf_id']);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($res[0]['pdf_path']) && file_exists($res[0]['pdf_path'])) {
            @chmod($res[0]['pdf_path'], 0644);
            @unlink($res[0]['pdf_path']);
        }

        $delsQL = "DELETE FROM pdsfiles WHERE pdf_id = :pdf_id";
        $detStmt = $conn->prepare($delsQL);
        $detStmt->bindParam(":pdf_id", $_GET['pdf_id']);
        $detStmt->execute();
    }
} catch (Throwable $e) {
    error_log("deletepdf error: " . $e->getMessage());
}

header("Location: backend/DashBoard/dashboard.php");
exit();

