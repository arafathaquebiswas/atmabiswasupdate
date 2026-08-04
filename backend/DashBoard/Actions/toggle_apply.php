<?php
session_start();
include '../../Database/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$job_id       = (int)($_POST['job_id']       ?? 0);
$apply_enabled = (int)($_POST['apply_enabled'] ?? 0);

if (!$job_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid job_id']);
    exit();
}

try {
    $db   = new Db();
    $conn = $db->connect();
    $stmt = $conn->prepare("UPDATE jobs SET apply_enabled = :enabled WHERE job_id = :job_id");
    $stmt->bindParam(':enabled',  $apply_enabled, PDO::PARAM_INT);
    $stmt->bindParam(':job_id',   $job_id,        PDO::PARAM_INT);
    $stmt->execute();
    echo json_encode(['success' => true, 'apply_enabled' => $apply_enabled]);
} catch (PDOException $e) {
    error_log("toggle_apply error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
