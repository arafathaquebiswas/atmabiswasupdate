<?php
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['division'])) {
    $division = trim($_POST['division']);

    try {
        include_once '../backend/Database/db.php';
        $db   = new Db();
        $conn = $db->connect();

        if ($conn) {
            $query = "SELECT * FROM branch WHERE division = :division ORDER BY branchName ASC";
            $stmt  = $conn->prepare($query);
            $stmt->bindParam(":division", $division);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($result ?: [], JSON_UNESCAPED_UNICODE);
            exit;
        }
    } catch (Throwable $e) {
        error_log("filter.php error: " . $e->getMessage());
    }
}

echo json_encode([]);