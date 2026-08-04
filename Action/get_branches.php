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

    $stmt = $conn->prepare(
        "SELECT branch_name, address, division, district
         FROM branches
         WHERE status = 1 AND division = :division
         ORDER BY branch_name ASC"
    );
    $stmt->bindParam(':division', $division, PDO::PARAM_STR);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($rows, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load branches']);
}
