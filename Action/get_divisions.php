<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');

require_once __DIR__ . '/../backend/Database/db.php';

try {
    $db   = new Db();
    $conn = $db->connect();

    if ($conn) {
        $divisions = [];
        try {
            $stmt = $conn->query(
                "SELECT name FROM divisions
                 WHERE status = 1
                 ORDER BY name ASC"
            );
            $divisions = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Throwable $e) {
            try {
                $stmt = $conn->query(
                    "SELECT DISTINCT division FROM branches
                     WHERE status = 1 AND division != ''
                     ORDER BY division ASC"
                );
                $divisions = $stmt->fetchAll(PDO::FETCH_COLUMN);
            } catch (Throwable $e2) {
                $stmt = $conn->query(
                    "SELECT DISTINCT division FROM branch
                     WHERE division != ''
                     ORDER BY division ASC"
                );
                $divisions = $stmt->fetchAll(PDO::FETCH_COLUMN);
            }
        }

        echo json_encode($divisions ?: [], JSON_UNESCAPED_UNICODE);
        exit;
    }
} catch (Throwable $e) {
    error_log("get_divisions error: " . $e->getMessage());
}

echo json_encode([]);

