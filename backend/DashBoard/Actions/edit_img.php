<?php
require_once __DIR__ . '/../../../image_optimize.php';
require_once __DIR__ . '/../../../storage.php';
if (session_status() === PHP_SESSION_NONE) session_start();
include_once '../../Database/db.php';

header('Content-Type: application/json');

require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../csrf_helper.php';

if (!is_logged_in()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

if (!csrf_verify()) {
    http_response_code(403);
    echo json_encode(['error' => 'Your session token has expired. Please reload the page and try again.']);
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

// A Latest News card shows the title as its headline and the description under
// it, so both are required there; the slider keeps its old rules. Checked
// before any replacement file is stored, so a rejected edit leaves no orphan.
if ($img_type === 'latest_news' && $img_description === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Latest News needs a Description as well as a Title.']);
    exit();
}

// Only Latest News cards show a Facebook link; the slider has none.
$fb_link = $img_type === 'latest_news' ? img_upload_fb_link($_POST['fb_link'] ?? '') : null;
if ($fb_link === false) {
    http_response_code(400);
    echo json_encode(['error' => 'The Facebook link must be the address of a Facebook post, e.g. https://www.facebook.com/atmabiswas/posts/...']);
    exit();
}

try {
    $db   = new Db();
    $conn = $db->connect();

    // The Press article is checked against the blogs table, so this needs the
    // connection; still before any replacement file is stored.
    $press_link = $img_type === 'latest_news' ? img_upload_press_link($conn, $_POST['press_link'] ?? '') : null;
} catch (PDOException $e) {
    error_log('edit_img error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
    exit();
}
if ($press_link === false) {
    http_response_code(400);
    echo json_encode(['error' => 'The Press link must point to an existing Press article on this website, e.g. https://atmabiswas.org/press.php?id=12 — or pick one from the list.']);
    exit();
}

$new_path = $old_path;
$new_file = null;   // stored replacement, removed again if the database write fails
$old_file = null;   // replaced original, removed only once the database points elsewhere

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

    if (!img_store_uploaded($imageFile['tmp_name'], $target)) {
        echo json_encode(['error' => 'Failed to save new image']);
        exit();
    }
    $new_file = $target;

    // The old physical file goes only after the UPDATE succeeds. Deleting it
    // here first left the row pointing at a missing image whenever the write
    // failed, and test.php hides a card whose image is gone.
    // Validate the path stays in the images upload directory.
    $oldFull = realpath($rootDir . DIRECTORY_SEPARATOR . $old_path);
    if ($oldFull && strpos($oldFull, $uploadDir) === 0 && file_exists($oldFull)) {
        $old_file = $oldFull;
    }

    $new_path = media_dir('images') . $filename;
}

try {
    // Display Order must stay unique among slider images; latest_news is exempt.
    // The row being edited is excluded so re-saving it unchanged is allowed.
    $imgCols = img_upload_columns($conn);
    if ($img_type === 'img_slider'
        && isset($imgCols['display_order'], $imgCols['img_type'])) {
        $dupe = $conn->prepare(
            "SELECT COUNT(*) FROM img_upload
              WHERE img_type = 'img_slider' AND display_order = :order AND img_path <> :self"
        );
        $dupe->execute([':order' => $display_order, ':self' => $old_path]);

        if ((int) $dupe->fetchColumn() > 0) {
            if ($new_file !== null) @unlink($new_file);
            http_response_code(409);
            echo json_encode([
                'error' => "Display Order {$display_order} is already in use. Please choose another number."
            ]);
            exit();
        }
    }

    // Update only the columns this install has, for the same reason the insert
    // does: installs carrying img_name instead of img_title otherwise raise
    // "Unknown column" and the edit silently fails.
    img_upload_ensure_columns($conn);
    $payload = img_upload_payload(
        $conn,
        $img_title,
        $img_description,
        $new_path,
        $img_type,
        $display_order,
        $fb_link,
        $press_link
    );
    $assignments = [];
    foreach (array_keys($payload) as $column) {
        $assignments[] = "{$column} = :{$column}";
    }

    $stmt = $conn->prepare(
        "UPDATE img_upload SET " . implode(', ', $assignments) . "
         WHERE img_path = :old_path"
    );
    img_upload_bind($stmt, $payload);
    $stmt->bindValue(':old_path', $old_path, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        // MySQL counts only rows whose values changed, so saving a card
        // unchanged also reports 0. Only a missing row is an error.
        $exists = $conn->prepare("SELECT COUNT(*) FROM img_upload WHERE img_path = ?");
        $exists->execute([$new_path]);
        if ((int) $exists->fetchColumn() === 0) {
            if ($new_file !== null) @unlink($new_file);
            echo json_encode(['error' => 'Record not found. Reload the page and try again.']);
            exit();
        }
    }

    if ($old_file !== null) @unlink($old_file);

    echo json_encode([
        'success'       => true,
        'new_path'      => $new_path,
        'display_order' => $display_order,
        'fb_link'       => $fb_link ?? '',
        'press_link'    => $press_link ?? '',
    ]);
} catch (PDOException $e) {
    error_log('edit_img error: ' . $e->getMessage());
    if ($new_file !== null) @unlink($new_file);
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
