<?php
require_once __DIR__ . '/../../../storage.php';
if (session_status() === PHP_SESSION_NONE) session_start();
include '../../Database/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$old_path        = trim($_POST['old_path']        ?? '');
$img_title       = trim($_POST['img_title']       ?? '');
$img_description = trim($_POST['img_description'] ?? '');
$img_type        = trim($_POST['img_type']         ?? '');
$display_order   = (int)($_POST['display_order']  ?? 0);

if (!$old_path || !$img_title || !in_array($img_type, ['img_slider', 'latest_news'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid fields']);
    exit();
}

$new_path = $old_path;

// Optional image replacement
$hasNewFile = !empty($_FILES['image_file']['name'])
           && ($_FILES['image_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

if ($hasNewFile) {
    $imageFile = $_FILES['image_file'];

    if ($imageFile['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['error' => 'File upload error (code ' . $imageFile['error'] . ')']);
        exit();
    }

    if ($imageFile['size'] > 2 * 1024 * 1024) {
        echo json_encode(['error' => 'New image exceeds 2 MB limit']);
        exit();
    }

    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($imageFile['tmp_name']);

    // Maps a validated MIME type to the extension we save with — never
    // taken from the attacker-supplied filename.
    $allowedImgTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
    if (!array_key_exists($mimeType, $allowedImgTypes)) {
        echo json_encode(['error' => 'Only JPG and PNG images are allowed']);
        exit();
    }

    $ext      = $allowedImgTypes[$mimeType];
    $filename = 'PHOTO_' . bin2hex(random_bytes(16)) . '.' . $ext;

    $rootDir   = dirname(dirname(dirname(__DIR__)));
    $uploadDir = rtrim(media_path('images'), DIRECTORY_SEPARATOR);
    $target    = $uploadDir . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($imageFile['tmp_name'], $target)) {
        echo json_encode(['error' => 'Failed to save new image']);
        exit();
    }

    // Delete old physical file — validate path stays in uploads/images/
    $oldFull = realpath($rootDir . DIRECTORY_SEPARATOR . $old_path);
    if ($oldFull && strpos($oldFull, $uploadDir) === 0 && file_exists($oldFull)) {
        unlink($oldFull);
    }

    $new_path = media_dir('images') . $filename;
}

try {
    $db   = new Db();
    $conn = $db->connect();

    // Display Order must stay unique among slider images; latest_news is exempt.
    // The row being edited is excluded so re-saving it unchanged is allowed.
    if ($img_type === 'img_slider') {
        $dupe = $conn->prepare(
            "SELECT COUNT(*) FROM img_upload
              WHERE img_type = 'img_slider' AND display_order = :order AND img_path <> :self"
        );
        $dupe->execute([':order' => $display_order, ':self' => $old_path]);

        if ((int) $dupe->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode([
                'error' => "Display Order {$display_order} is already in use. Please choose another number."
            ]);
            exit();
        }
    }

    $stmt = $conn->prepare(
        "UPDATE img_upload
         SET img_title = :title, img_description = :desc, img_type = :type,
             img_path = :new_path, display_order = :order
         WHERE img_path = :old_path"
    );
    $stmt->bindParam(':title',    $img_title);
    $stmt->bindParam(':desc',     $img_description);
    $stmt->bindParam(':type',     $img_type);
    $stmt->bindParam(':new_path', $new_path);
    $stmt->bindParam(':old_path', $old_path);
    $stmt->bindParam(':order',    $display_order, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'Record not found or no changes made']);
        exit();
    }

    echo json_encode(['success' => true, 'new_path' => $new_path, 'display_order' => $display_order]);
} catch (PDOException $e) {
    error_log('edit_img error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
