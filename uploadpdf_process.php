<?php

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: backend/login/loging.php");
    exit();
}

include_once 'backend/Database/db.php';

$uploadDir = "uploads/pdfs/";
$maxSize = 10 * 1024 * 1024;

if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

function processPdf($pdfFile, $maxSize, $allowedTypes, $uploadDir)
{
    if (!isset($pdfFile) || $pdfFile["error"] !== UPLOAD_ERR_OK) {
        echo "<p>An Error Occurred!</p>";
        exit();
    }

    $fileInfo = new finfo(FILEINFO_MIME_TYPE);
    $mimetype = $fileInfo->file($pdfFile["tmp_name"]);

    if (!array_key_exists($mimetype, $allowedTypes)) {
        echo "<p>Invalid File Type</p>";
        exit();
    }

    if ($pdfFile['size'] > $maxSize) {
        echo "<p>File size is too Large</p>";
        exit();
    }

    $ext = $allowedTypes[$mimetype];
    $newFileName = "Notice_" . bin2hex(random_bytes(16)) . "." . $ext;
    $target = $uploadDir . $newFileName;

    if (!move_uploaded_file($pdfFile["tmp_name"], $target)) {
        echo "<p>Error Occurred While Uploading</p>";
        exit();
    }

    return $target;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $db = new Db();
        $conn = $db->connect();

        if (!$conn) {
            header("Location: backend/DashBoard/error.php?type=Upload");
            exit();
        }

        $pdf_title = htmlspecialchars($_POST["pdf_title"] ?? 'Official Notice');
        $allowedTypes = ["application/pdf" => "pdf"];

        if (!isset($_FILES["pdf_file"])) {
            header("Location: backend/DashBoard/error.php?type=Upload");
            exit();
        }

        $pdfFile = $_FILES["pdf_file"];
        $pdfPath = processPdf($pdfFile, $maxSize, $allowedTypes, $uploadDir);

        $sql = "INSERT INTO pdsfiles (pdf_title, pdf_path) VALUES (:pdf_title, :pdf_path)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":pdf_title", $pdf_title);
        $stmt->bindParam(":pdf_path", $pdfPath);

        $stmt->execute();
        header("Location: backend/DashBoard/success.php?type=Upload");
        exit();

    } catch (Throwable $e) {
        error_log("uploadpdf_process error: " . $e->getMessage());
        header("Location: backend/DashBoard/error.php?type=Upload");
        exit();
    }
}

