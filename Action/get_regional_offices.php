<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store');

require_once __DIR__ . '/../backend/Database/db.php';

try {
    $db   = new Db();
    $conn = $db->connect();

    if ($conn) {
        $stmt = $conn->prepare(
            "SELECT id, region_name, address, designation, phone
             FROM regional_offices
             WHERE status = 1
             ORDER BY display_order ASC, id ASC"
        );
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($rows ?: [], JSON_UNESCAPED_UNICODE);
        exit;
    }
} catch (Throwable $e) {
    error_log("get_regional_offices error: " . $e->getMessage());
}

echo json_encode([]);

