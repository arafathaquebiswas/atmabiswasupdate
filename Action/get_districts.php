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

    $stmt = $conn->prepare(
        "SELECT DISTINCT district FROM branches
         WHERE division = :division AND district != ''
         ORDER BY district ASC"
    );
    $stmt->bindParam(':division', $division, PDO::PARAM_STR);
    $stmt->execute();
    $districts = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode($districts, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([]);
}
