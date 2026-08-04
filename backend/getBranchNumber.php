<?php 
header('Content-Type: application/json');

require_once __DIR__ . '/Database/db.php';

try {
    $database = new Db();
    $conn     = $database->connect();

    if ($conn) {
        $total = 0;
        try {
            $stmt = $conn->query("SELECT COUNT(*) as totalBranchs FROM branch");
            $total = (int)$stmt->fetchColumn();
        } catch (Throwable $e) {
            $stmt = $conn->query("SELECT COUNT(*) as totalBranchs FROM branches");
            $total = (int)$stmt->fetchColumn();
        }
        echo json_encode(['value' => $total]);
        exit;
    }
} catch (Throwable $e) {
    error_log("getBranchNumber error: " . $e->getMessage());
}

echo json_encode(['value' => 0]);