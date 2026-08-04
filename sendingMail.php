<?php
include 'backend/Database/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';  // Load PHPMailer classes via Composer autoload

$db = new Db();
$conn = $db->connect();

$uploadDir = "uploads/application_cvs/";
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

function processPdf($pdfFile, $maxSize, $allowedTypes, $uploadDir)
{
    if (!isset($pdfFile) || $pdfFile['error'] !== UPLOAD_ERR_OK) {
        echo "<p>An error occurred during file upload.</p>";
        exit();
    }

    $fileInfo = new finfo(FILEINFO_MIME_TYPE);
    $mimetype = $fileInfo->file($pdfFile["tmp_name"]);

    if (!array_key_exists($mimetype, $allowedTypes)) {
        echo "<p>Invalid file type.</p>";
        exit();
    }

    if ($pdfFile['size'] > $maxSize) {
        echo "<p>File size is too large.</p>";
        exit();
    }

    $ext = $allowedTypes[$mimetype];
    $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $_POST['fullname'] ?? 'Anonymous');
    $newFileName = "CvApplication_{$safeName}_" . bin2hex(random_bytes(16)) . "." . $ext;

    $target = $uploadDir . $newFileName;
    if (!move_uploaded_file($pdfFile["tmp_name"], $target)) {
        echo "<p>Error occurred while saving file.</p>";
        exit();
    }

    return $target;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $jobId    = $_POST["job_id"] ?? '';
    $jobCode  = $_POST["job_code"] ?? '';
    $jobTitle = $_POST["job-title"] ?? '';
    $fullName = htmlspecialchars(trim($_POST["fullname"] ?? ''));
    $email    = filter_var(trim($_POST["email"] ?? ''), FILTER_SANITIZE_EMAIL);
    $phone    = htmlspecialchars(trim($_POST["phone"] ?? ''));
    $mailBody = htmlspecialchars(trim($_POST["mailbody"] ?? ''));

    if (!$jobId || !$jobTitle || !$fullName || !$email || !$phone || !$mailBody) {
        echo "Please fill in all required fields.";
        exit();
    }

    // Maps a validated MIME type to the extension we save with — never
    // taken from the attacker-supplied filename.
    $allowedTypes = ["application/pdf" => "pdf"];
    $maxSize = 5 * 1024 * 1024; // 5 MB
    $cvFile = processPdf($_FILES["cvfile"], $maxSize, $allowedTypes, $uploadDir);

    // Insert into database
    $sql = "INSERT INTO cv_applications (jobId, job_title, fullname, email, phone_no, fileDir)
            VALUES (:job_id, :job_title, :fullname, :email, :phone_no, :fileDir)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":job_id", $jobId);
    $stmt->bindParam(":job_title", $jobTitle);
    $stmt->bindParam(":fullname", $fullName);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":phone_no", $phone);
    $stmt->bindParam(":fileDir", $cvFile);
    $stmt->execute();

    // Send Email
    try {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'atmabiswas.org@gmail.com'; // Your Gmail address
        $mail->Password   = 'fvxccnybrdibkadr';                  // Your Gmail app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom($email, $fullName);
        $mail->addAddress('arafatbiswas.edu01@gmail.com', 'Arafat Biswas');

        $mail->isHTML(true);
        $mail->Subject = "Application for Position: {$jobTitle} - Job ID: {$jobId} - From {$fullName}";
        $mail->Body = "
            <strong>Name:</strong> {$fullName}<br>
            <strong>Email:</strong> <span style='color:blue;'>{$email}</span><br>
            <strong>Phone:</strong> <span style='color:green;'>+88{$phone}</span><br>
            <strong>Applicant's Message:</strong><br>
            <p>{$mailBody}</p>
        ";

        $mail->addAttachment($cvFile);

        if ($mail->send()) {
            header("Location: backend/career/availableJobs.php");
            exit();
        } else {
            echo "Failed to send email.";
        }

    } catch (Exception $e) {
        echo "Mailer Error: " . $mail->ErrorInfo;
    }
}
?>
