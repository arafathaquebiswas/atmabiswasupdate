<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: backend/login/loging.php");
    exit();
}

include 'backend/Database/db.php';

$db         = new Db();
$connection = $db->connect();

$uploadDir    = "uploads/images/";
// Maps a validated MIME type to the extension we save with — the saved
// file's extension is never taken from the attacker-supplied filename.
$allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
$imageSize    = 2 * 1024 * 1024;

if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Auto-add display_order column if missing
try {
    $col = $connection->query(
        "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME   = 'img_upload'
           AND COLUMN_NAME  = 'display_order'"
    );
    if ((int)$col->fetchColumn() === 0) {
        $connection->exec("ALTER TABLE img_upload ADD COLUMN display_order INT NOT NULL DEFAULT 0");
    }
} catch (Exception $e) { /* non-fatal */ }

function processFile($imageFile, $allowedTypes, $imageSize, $uploadDir)
{
    $fileType = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $fileType->file($imageFile['tmp_name']);

    if ($imageFile['error'] !== UPLOAD_ERR_OK) {
        echo "<p>Uploading ran into an error!</p>";
        exit();
    }

    if ($imageFile['size'] > $imageSize) {
        echo "<p>File is too large! Maximum size is 2 MB.</p>";
        exit();
    }

    if (!array_key_exists($mimeType, $allowedTypes)) {
        echo "<p>Invalid file format. Only JPG and PNG are allowed.</p>";
        exit();
    }

    $ext    = $allowedTypes[$mimeType];
    $new    = "PHOTO_" . bin2hex(random_bytes(16)) . "." . $ext;
    $target = $uploadDir . $new;

    if (!move_uploaded_file($imageFile['tmp_name'], $target)) {
        echo "<p>Failed to move uploaded image!</p>";
        exit();
    }
    return $target;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $img_title       = htmlspecialchars($_POST["img_title"]       ?? "ATMA BISWAS");
        $img_description = htmlspecialchars($_POST["img_description"] ?? "");
        $img_type        = $_POST["imagetype"] ?? "latest_news";
        $display_order   = (int)($_POST["display_order"] ?? 0);

        $imageFile  = $_FILES["image_file"];
        $image_path = processFile($imageFile, $allowedTypes, $imageSize, $uploadDir);

        $sql  = "INSERT INTO img_upload (img_title, img_description, img_path, img_type, display_order)
                 VALUES (:img_title, :img_description, :img_path, :img_type, :display_order)";
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(":img_title",       $img_title);
        $stmt->bindParam(":img_description", $img_description);
        $stmt->bindParam(":img_path",        $image_path);
        $stmt->bindParam(":img_type",        $img_type);
        $stmt->bindParam(":display_order",   $display_order, PDO::PARAM_INT);
        $stmt->execute();

        header("Location: backend/DashBoard/success.php?type=upload");
    } catch (Exception $e) {
        header("Location: backend/DashBoard/error.php?type=upload");
    }
}
