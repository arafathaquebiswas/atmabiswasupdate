<?php

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login/loging.php");
    exit();
}

include 'Database/db.php';

$database = new Db();

$conn = $database->connect();

$sectorID = htmlspecialchars($_GET["sec_id"]);

$deleteSql = "DELETE FROM sectors WHERE sector_id=:sector_id";

$stmt = $conn->prepare($deleteSql);

$stmt->bindParam(":sector_id", $sectorID);

if ($stmt->execute()) {
    header("Location: DashBoard/dashboard.php");
} else {
    echo "Unable to delete Sector";
}
