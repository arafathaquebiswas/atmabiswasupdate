<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['division'])) {
    echo json_encode([]);
    exit;
}

$division = trim($_POST['division']);
if ($division === '') {
    echo json_encode([]);
    exit;
}

require_once __DIR__ . '/../backend/Database/db.php';

try {
    $db   = new Db();
    $conn = $db->connect();

    if ($conn) {
        try {
            $stmt = $conn->prepare(
                "SELECT branch_name, address, division, district
                 FROM branches
                 WHERE status = 1 AND division = :division
                 ORDER BY branch_name ASC"
            );
            $stmt->bindParam(':division', $division, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $stmt = $conn->prepare(
                "SELECT branchName AS branch_name, branchLoc AS address, division, dist AS district
                 FROM branch
                 WHERE division = :division
                 ORDER BY branchName ASC"
            );
            $stmt->bindParam(':division', $division, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        echo json_encode($rows ?: [], JSON_UNESCAPED_UNICODE);
        exit;
    }
} catch (Throwable $e) {
    error_log("get_branches error: " . $e->getMessage());
}

echo json_encode([]);

