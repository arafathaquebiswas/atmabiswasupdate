<?php

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: backend/login/loging.php");
    exit();
}

include 'backend/Database/db.php';

$db = new Db();
$conn = $db->connect();

$pdf_title = htmlspecialchars($_POST["pdf_title"]);

$uploadDir = "uploads/pdfs/";

$maxSize = 10 * 1024 * 1024;

if (!file_exists($uploadDir)) {

    mkdir($uploadDir, 0755, true);
}

function processPdf($pdfFile, $maxSize, $allowedTypes, $uploadDir)
{

    $fileInfo = new finfo(FILEINFO_MIME_TYPE);
    $mimetype = $fileInfo->file($pdfFile["tmp_name"]);

    if ($pdfFile["error"] !== UPLOAD_ERR_OK) {
        echo "<p>An Error Occurd!</p>";
        exit();
    }

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
        echo "<p>Error Occurd While Uploading</p>";
        exit();
    }

    return $target;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {

        $maxSize = 10 * 1024 * 1024;
        // Maps a validated MIME type to the extension we save with — never
        // taken from the attacker-supplied filename.
        $allowedTypes = ["application/pdf" => "pdf"];
        $uploadDir = "uploads/pdfs/";

        $pdfFile = $_FILES["pdf_file"];

        $pdfPath = processPdf($pdfFile, $maxSize, $allowedTypes, $uploadDir);

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $sql = "INSERT INTO pdsfiles (pdf_title,pdf_path) VALUES (:pdf_title,:pdf_path)";
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(":pdf_title", $pdf_title);
        $stmt->bindParam(":pdf_path", $pdfPath);


        $stmt->execute();
        header("Location: backend/DashBoard/success.php?type=Upload");
    } catch (PDOException $e) {
        header("Location: backend/DashBoard/error.php?type=Upload");
    }
}
