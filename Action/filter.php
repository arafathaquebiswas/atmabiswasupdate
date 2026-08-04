<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['division'])) {
    header("Content-Type: application/json");
    $division = $_POST['division'];

    function getResult($conn, $division) {
        $query = "SELECT * FROM branch WHERE division = :division order by branchName ASC";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":division", $division);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    include '../backend/Database/db.php';
    $db = new Db();
    $conn = $db->connect();
    $result = getResult($conn, $division);

    if ($result) {
        echo json_encode($result);

    } else {
        echo json_encode($result);
    }
}