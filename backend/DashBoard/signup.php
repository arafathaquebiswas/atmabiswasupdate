<?php

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../login/loging.php");
    exit();
}

include '../Database/db.php';

$database = new Db();

$conn = $database->connect();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullname = trim($_POST["fullname"]);

    $email = trim($_POST["email"]);

    $password = $_POST["password"];

    $confirm_password = $_POST["confirm_password"];

    if ($password === $confirm_password) {
        $sql = "INSERT INTO admins (fullname,email,pswd) VALUES (:fullname,:email,:pswd)";

        $hashpw = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(":fullname", $fullname);

        $stmt->bindParam(":email", $email);

        $stmt->bindParam(":pswd", $hashpw);

        if ($stmt->execute()) {
            header("Location: successfulRegistration.php");
        } else {
            echo "Registration Failed";
        }
    }
}
