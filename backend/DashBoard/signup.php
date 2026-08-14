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

        $hashpw = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Adds the column on a database that predates the role system, so
            // the INSERT below always has somewhere to put the role.
            $hasRole = auth_ensure_role_column($conn);
            $has     = admin_table_columns($conn);

            $values = [
                'fullname' => $fullname,
                'email'    => $email,
                'pswd'     => $hashpw,
            ];

            if ($hasRole) {
                $values['role'] = $newRole;
            }

            // Older installs keep a NOT NULL `username` next to `email`; it has
            // no default, so it has to be written or the INSERT fails with 1364.
            if (isset($has['username'])) {
                $values['username'] = $email;
            }

            $fields       = array_keys($values);
            $placeholders = array_map(static fn($f) => ':' . $f, $fields);

            $sql  = 'INSERT INTO admins (' . implode(',', $fields) . ')'
                  . ' VALUES (' . implode(',', $placeholders) . ')';
            $stmt = $conn->prepare($sql);

            if ($stmt->execute($values)) {
                header("Location: successfulRegistration.php");
                exit();
            }

            $error = 'Registration failed. Please try again.';
        } catch (PDOException $e) {
            // Never render the driver message: it exposes server paths and schema.
            error_log('Admin signup failed: ' . $e->getMessage());
            $error = ($e->getCode() === '23000')
                ? 'An admin with that email already exists.'
                : 'Registration failed because of a database error. Please try again.';
        }
    } else {
        $error = 'Passwords do not match.';
    }
}

if (!empty($error)) {
    echo '<p style="font-family:sans-serif;color:#b91c1c;padding:16px">'
        . htmlspecialchars($error, ENT_QUOTES, 'UTF-8')
        . ' <a href="adminSignup.php">Go back</a></p>';
}
