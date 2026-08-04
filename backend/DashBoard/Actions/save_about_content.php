<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../../Database/db.php';

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

$section_key  = trim($_POST['section_key']  ?? '');
$text_content = trim($_POST['text_content'] ?? '');
$image_alt    = trim($_POST['image_alt']    ?? '');

if (!in_array($section_key, ['about_us', 'our_team'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid section']);
    exit();
}

if ($text_content === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Text content cannot be empty']);
    exit();
}

// Site root is three levels up from this file
// File: backend/DashBoard/Actions/ → backend/DashBoard/ → backend/ → site root
$siteRoot  = dirname(dirname(dirname(__DIR__)));
$uploadDir = $siteRoot . DIRECTORY_SEPARATOR . 'office_pic';

// Determine if a new image was uploaded
$hasNewImage = !empty($_FILES['image_file']['name'])
            && (($_FILES['image_file']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK);

$new_image_path = null;

if ($hasNewImage) {
    $file = $_FILES['image_file'];

    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['error' => 'Image exceeds 5 MB limit']);
        exit();
    }

    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!array_key_exists($mimeType, $allowed)) {
        echo json_encode(['error' => 'Only JPG, PNG, or WebP images are allowed']);
        exit();
    }

    $ext      = $allowed[$mimeType];
    $slug     = preg_replace('/[^a-z0-9]/', '', str_replace(' ', '_', strtolower($section_key)));
    $filename = 'about_' . $slug . '_' . date('Ymd_His') . '_' . random_int(1000, 9999) . '.' . $ext;
    $target   = $uploadDir . DIRECTORY_SEPARATOR . $filename;

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        echo json_encode(['error' => 'Failed to save uploaded image']);
        exit();
    }

    $new_image_path = 'office_pic/' . $filename;
}

try {
    $db   = new Db();
    $conn = $db->connect();

    if ($new_image_path !== null) {
        // Fetch old image path so we can optionally clean up
        $fetchStmt = $conn->prepare(
            "SELECT image_path FROM about_us_content WHERE section_key = :key"
        );
        $fetchStmt->execute([':key' => $section_key]);
        $old = $fetchStmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $conn->prepare(
            "INSERT INTO about_us_content (section_key, image_path, image_alt, text_content)
             VALUES (:key, :img, :alt, :txt)
             ON DUPLICATE KEY UPDATE image_path = :img2, image_alt = :alt2, text_content = :txt2"
        );
        $stmt->execute([
            ':key'  => $section_key,
            ':img'  => $new_image_path,
            ':alt'  => $image_alt,
            ':txt'  => $text_content,
            ':img2' => $new_image_path,
            ':alt2' => $image_alt,
            ':txt2' => $text_content,
        ]);

        // Delete old uploaded file if it was previously uploaded (not the default seeded image)
        if ($old && strpos($old['image_path'], 'about_') !== false) {
            $oldFile = $siteRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $old['image_path']);
            if (file_exists($oldFile)) {
                @unlink($oldFile);
            }
        }

        echo json_encode(['success' => true, 'image_path' => $new_image_path]);
    } else {
        // Text-only update
        $stmt = $conn->prepare(
            "INSERT INTO about_us_content (section_key, image_alt, text_content, image_path)
             VALUES (:key, :alt, :txt, '')
             ON DUPLICATE KEY UPDATE image_alt = :alt2, text_content = :txt2"
        );
        $stmt->execute([
            ':key'  => $section_key,
            ':alt'  => $image_alt,
            ':txt'  => $text_content,
            ':alt2' => $image_alt,
            ':txt2' => $text_content,
        ]);

        echo json_encode(['success' => true, 'image_path' => null]);
    }

} catch (PDOException $e) {
    error_log('save_about_content error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error. Please try again.']);
}
