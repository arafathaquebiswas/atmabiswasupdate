<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');

require_once __DIR__ . '/../backend/Database/db.php';

try {
    $db   = new Db();
    $conn = $db->connect();

    // Try the dedicated divisions table first
    try {
        $stmt = $conn->query(
            "SELECT name FROM divisions
             WHERE status = 1
             ORDER BY name ASC"
        );
        $divisions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        // Fallback: get distinct values from branches (divisions table not yet created)
        $stmt = $conn->query(
            "SELECT DISTINCT division FROM branches
             WHERE status = 1 AND division != ''
             ORDER BY division ASC"
        );
        $divisions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    echo json_encode($divisions, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([]);
}
