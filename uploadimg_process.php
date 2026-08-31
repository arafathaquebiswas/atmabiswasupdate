<?php
require_once __DIR__ . '/image_optimize.php';
require_once __DIR__ . '/storage.php';
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: backend/login/loging.php");
    exit();
}

include_once 'backend/Database/db.php';

$uploadDir    = media_dir("images");
$allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
$imageSize    = 2 * 1024 * 1024;

if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

function processFile($imageFile, $allowedTypes, $imageSize, $uploadDir)
{
    if (!isset($imageFile) || $imageFile['error'] !== UPLOAD_ERR_OK) {
        echo "<p>Uploading ran into an error!</p>";
        exit();
    }

    $fileType = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $fileType->file($imageFile['tmp_name']);

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

    if (!img_store_uploaded($imageFile['tmp_name'], $target)) {
        echo "<p>Failed to move uploaded image!</p>";
        exit();
    }
    return $target;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $db         = new Db();
        $connection = $db->connect();

        if (!$connection) {
            header("Location: backend/DashBoard/error.php?type=upload");
            exit();
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

        $img_title       = htmlspecialchars($_POST["img_title"]       ?? "ATMA BISWAS");
        $img_description = htmlspecialchars($_POST["img_description"] ?? "");
        $img_type        = $_POST["imagetype"] ?? "latest_news";
        $display_order   = (int)($_POST["display_order"] ?? 0);

        // Display Order must be unique among slider images. latest_news has no
        // ordering system, so it is exempt. Nothing else is renumbered to make
        // room — the admin picks a free number.
        // display_order and img_type are not present on every install; the
        // uniqueness rule only applies where the columns actually exist.
        $imgCols = img_upload_columns($connection);
        if ($img_type === 'img_slider'
            && isset($imgCols['display_order'], $imgCols['img_type'])) {
            $dupe = $connection->prepare(
                "SELECT COUNT(*) FROM img_upload
                  WHERE img_type = 'img_slider' AND display_order = :order"
            );
            $dupe->execute([':order' => $display_order]);

            if ((int) $dupe->fetchColumn() > 0) {
                // Checked before processFile() moves anything, so a rejected
                // upload leaves no orphan file behind in uploads/images/.
                header("Location: backend/DashBoard/error.php?type=upload&msg="
                    . rawurlencode("Display Order {$display_order} is already in use. Please choose another number."));
                exit();
            }
        }

        if (!isset($_FILES["image_file"])) {
            header("Location: backend/DashBoard/error.php?type=upload");
            exit();
        }

        $imageFile  = $_FILES["image_file"];
        $image_path = processFile($imageFile, $allowedTypes, $imageSize, $uploadDir);

        // Write only the columns this install has. Hardcoding img_title and
        // img_description made every upload fail on installs whose table carries
        // img_name instead: the PDOException was caught below and shown as a bare
        // "File upload failed." with no indication that the schema was the cause.
        $payload = img_upload_payload(
            $connection,
            $img_title,
            $img_description,
            $image_path,
            $img_type,
            $display_order
        );
        $columns = array_keys($payload);
        $sql     = "INSERT INTO img_upload (" . implode(', ', $columns) . ")
                 VALUES (:" . implode(', :', $columns) . ")";
        $stmt = $connection->prepare($sql);
        img_upload_bind($stmt, $payload);
        $stmt->execute();

        header("Location: backend/DashBoard/success.php?type=upload");
        exit();
    } catch (Throwable $e) {
        // Full detail goes to the server log; the admin gets a short, safe reason.
        // Previously every failure here rendered an identical blank "File upload
        // failed." page, which is why a schema mismatch looked like a broken
        // uploader for months.
        error_log("uploadimg_process error: " . $e->getMessage());
        header("Location: backend/DashBoard/error.php?type=upload&msg="
            . rawurlencode("Could not save the image record. Details are in the server error log."));
        exit();
    }
}
