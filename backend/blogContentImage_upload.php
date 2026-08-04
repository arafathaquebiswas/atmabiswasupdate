<?php
session_start();

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access. Please login first.']);
    exit();
}

header('Content-Type: application/json');

require_once __DIR__ . '/DashBoard/csrf_helper.php';

try {
    if (!csrf_verify()) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid or expired session token. Please reload the editor and try again.']);
        exit();
    }

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No image received.');
    }

    $file  = $_FILES['file'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);

    // Maps a validated MIME type to the extension we save with — never
    // taken from the attacker-supplied filename.
    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/jpg'  => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];
    if (!array_key_exists($mime, $allowedTypes)) {
        throw new Exception('Only JPG, PNG, WebP, or GIF images are allowed.');
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('Image must be under 5MB.');
    }

    $uploadDir = __DIR__ . '/../uploads/blog_content_imgs/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $ext      = $allowedTypes[$mime];
    $filename = 'CONTENT_' . bin2hex(random_bytes(16)) . '.' . $ext;
    $dest     = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new Exception('Failed to save image. Check upload directory permissions.');
    }

    echo json_encode(['location' => '/uploads/blog_content_imgs/' . $filename]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
