<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__ . '/../backend/Database/db.php';

$division = trim($_POST['division'] ?? '');

if ($division === '') {
    echo json_encode([]);
    exit();
}

try {
    $db   = new Db();
    $conn = $db->connect();

    if ($conn) {
        try {
            $stmt = $conn->prepare(
                "SELECT DISTINCT district FROM branches
                 WHERE division = :division AND district != ''
                 ORDER BY district ASC"
            );
            $stmt->bindParam(':division', $division, PDO::PARAM_STR);
            $stmt->execute();
            $districts = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Throwable $e) {
            $stmt = $conn->prepare(
                "SELECT DISTINCT dist AS district FROM branch
                 WHERE division = :division AND dist != ''
                 ORDER BY dist ASC"
            );
            $stmt->bindParam(':division', $division, PDO::PARAM_STR);
            $stmt->execute();
            $districts = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        echo json_encode($districts ?: [], JSON_UNESCAPED_UNICODE);
        exit;
    }
} catch (Throwable $e) {
    error_log("get_districts error: " . $e->getMessage());
}

echo json_encode([]);

