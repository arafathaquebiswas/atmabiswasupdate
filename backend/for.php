// === forgot_password.php ===
<form action="send_reset_link.php" method="POST">
    <h2>Forgot Password</h2>
    <input type="email" name="email" placeholder="Enter your email" required>
    <button type="submit">Send Reset Link</button>
</form>


// === send_reset_link.php ===
<?php
include '../Database/db.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

$database = new Db();
$connection = $database->connect();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);

    $stmt = $connection->prepare("SELECT * FROM admins WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+30 minutes'));

        $update = $connection->prepare("UPDATE admins SET reset_token = :token, token_expiry = :expiry WHERE email = :email");
        $update->execute([
            ':token' => $token,
            ':expiry' => $expiry,
            ':email' => $email
        ]);

        $resetLink = "http://yourdomain.com/reset_password.php?token=$token";

        // $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'smtp.yourmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your-email@example.com';
        $mail->Password = 'yourpassword';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('admin@yourdomain.com', 'Admin');
        $mail->addAddress($email);
        $mail->Subject = 'Password Reset';
        $mail->Body = "Click the link to reset: $resetLink";

        if ($mail->send()) {
            echo "Check your email for reset link.";
        } else {
            echo "Email sending failed.";
        }
    } else {
        echo "Email not found.";
    }
}
?>


// === reset_password.php ===
<?php
$token = $_GET['token'] ?? '';
?>
<form action="update_password.php" method="POST">
    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
    <input type="password" name="new_password" placeholder="New password" required>
    <button type="submit">Reset Password</button>
</form>


// === update_password.php ===
<?php
include '../Database/db.php';
$database = new Db();
$connection = $database->connect();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST['token'];
    $newPassword = $_POST['new_password'];
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    $stmt = $connection->prepare("SELECT * FROM admins WHERE reset_token = :token AND token_expiry >= NOW()");
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $update = $connection->prepare("UPDATE admins SET pswd = :pswd, reset_token = NULL, token_expiry = NULL WHERE id = :id");
        $update->execute([
            ':pswd' => $hashedPassword,
            ':id' => $user['id']
        ]);
        echo "Password updated. You may now login.";
    } else {
        echo "Invalid or expired token.";
    }
}
?>