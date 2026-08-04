<?php 


header('Content-Type: application/json');


include 'Database/db.php';

$database = new Db();

$conn = $database->connect();


try {
    $sql = "SELECT COUNT(*) as totalBranchs FROM branch";

$stmt = $conn->prepare($sql);

$stmt->execute();

$res = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['value' => (int)$res[0]['totalBranchs']]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}






?>