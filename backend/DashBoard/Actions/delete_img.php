<?php
if (session_status() === PHP_SESSION_NONE) session_start();
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

$img_path = trim($_POST['img_path'] ?? '');

if (!$img_path) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid path']);
    exit();
}

try {
    $db   = new Db();
    $conn = $db->connect();

    $stmt = $conn->prepare("DELETE FROM img_upload WHERE img_path = :img_path");
    $stmt->bindParam(':img_path', $img_path);
    $stmt->execute();

    // Delete the physical file — validate path stays inside uploads/images/
    $rootDir    = dirname(dirname(dirname(__DIR__)));
    $uploadsDir = $rootDir . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'images';
    $fullPath   = realpath($rootDir . DIRECTORY_SEPARATOR . $img_path);

    if ($fullPath && strpos($fullPath, $uploadsDir) === 0 && file_exists($fullPath)) {
        unlink($fullPath);
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    error_log('delete_img error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
