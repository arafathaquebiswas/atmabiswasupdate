<?php

require_once __DIR__ . '/../auth.php';

require_login();

include_once '../Database/db.php';

$database = new Db();

$conn = $database->connect();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullname = trim($_POST["fullname"]);

    $email = trim($_POST["email"]);

    $password = $_POST["password"];

    $confirm_password = $_POST["confirm_password"];

    if ($password === $confirm_password) {
        // Only a super admin may create another super admin. Anyone else gets a
        // plain admin no matter what the form posted, so an admin cannot grant
        // itself or anyone else super admin access.
        $requestedRole = auth_normalize_role($_POST['role'] ?? ROLE_ADMIN);
        $newRole = (is_super_admin() && $requestedRole === ROLE_SUPER_ADMIN)
            ? ROLE_SUPER_ADMIN
            : ROLE_ADMIN;

        $sql = "INSERT INTO admins (fullname,email,pswd,role) VALUES (:fullname,:email,:pswd,:role)";

        $hashpw = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(":fullname", $fullname);

        $stmt->bindParam(":email", $email);

        $stmt->bindParam(":pswd", $hashpw);

        $stmt->bindParam(":role", $newRole);

        if ($stmt->execute()) {
            header("Location: successfulRegistration.php");
        } else {
            echo "Registration Failed";
        }
    }
}
