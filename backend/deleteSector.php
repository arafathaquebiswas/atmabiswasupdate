<?php

require_once __DIR__ . '/auth.php';

require_login();
require_super_admin('delete this sector');

include_once 'Database/db.php';

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
