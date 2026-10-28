<?php
/**
 * Upload, replace, remove or restore the image of one Social page card.
 *
 * Posted to by social_images.php; always redirects back there with a message.
 * Uploading or replacing is content editing, open to every admin. Removing or
 * restoring takes an image off the public site, so like every other delete in
 * the dashboard it needs a super admin.
 *
 * The database changes only after the new file is safely on disk, and the old
 * file is deleted only after the database no longer points at it, so a failed
 * step at any point leaves the card showing the image it had before.
 */
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../csrf_helper.php';
require_once __DIR__ . '/../../../social_cards.php';

require_login();

function social_back(string $type, string $message, string $key = ''): never
{
    $_SESSION['social_flash'] = ['type' => $type, 'msg' => $message];
    header('Location: ../social_images.php' . ($key !== '' ? '#card-' . rawurlencode($key) : ''));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    social_back('error', 'Invalid request.');
}
// A request over PHP's post_max_size arrives with $_POST and $_FILES both
// empty, which the CSRF check below would report as an expired session.
if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    social_back('error', 'The image is larger than ' . social_upload_limit_label() . '. Please choose a smaller one.');
}
if (!csrf_verify()) {
    social_back('error', 'Your session expired. Please reload the page and try again. Nothing was changed.');
}

$cards  = social_card_index();
$key    = (string) ($_POST['card_key'] ?? '');
$action = (string) ($_POST['action'] ?? '');

if (!isset($cards[$key]) || !in_array($action, ['upload', 'remove', 'restore'], true)) {
    social_back('error', 'Unknown card or action. Nothing was changed.');
}
$label = $cards[$key]['segment'] . ' — ' . $cards[$key]['title'];

if ($action !== 'upload') {
    require_super_admin('remove a Social page image');
}

try {
    $pdo = (new Db())->connect();
    social_ensure_table($pdo);
    $st = $pdo->prepare("SELECT img_path FROM social_card_images WHERE card_key = ?");
    $st->execute([$key]);
    $oldPath = $st->fetchColumn();
    $oldPath = $oldPath === false ? null : $oldPath;
} catch (Throwable $e) {
    error_log('social_image: database unavailable: ' . $e->getMessage());
    social_back('error', 'Could not reach the database. Nothing was changed.', $key);
}

if ($action === 'upload') {
    $file  = $_FILES['image'] ?? null;
    $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;

    if ($error === UPLOAD_ERR_NO_FILE) {
        social_back('error', 'Please choose an image first.', $key);
    }
    if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE
        || ($error === UPLOAD_ERR_OK && $file['size'] > social_upload_limit())) {
        social_back('error', 'The image is larger than ' . social_upload_limit_label() . '. Please choose a smaller one.', $key);
    }
    if ($error !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        social_back('error', "The upload failed (error code {$error}). The previous image is unchanged.", $key);
    }

    // Type comes from the file's contents, never from its name or the browser.
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset(SOCIAL_IMG_TYPES[$mime]) || @getimagesize($file['tmp_name']) === false) {
        social_back('error', 'Only JPG, PNG or WebP images can be uploaded.', $key);
    }

    $newPath = media_dir('social') . 'SOCIAL_' . bin2hex(random_bytes(16)) . '.' . SOCIAL_IMG_TYPES[$mime];
    $newAbs  = dirname(__DIR__, 3) . '/' . $newPath;

    if (!img_store_uploaded($file['tmp_name'], $newAbs)) {
        social_back('error', 'Could not save the image on the server. The previous image is unchanged.', $key);
    }

    try {
        $pdo->prepare("REPLACE INTO social_card_images (card_key, img_path) VALUES (?, ?)")
            ->execute([$key, $newPath]);
    } catch (Throwable $e) {
        error_log('social_image: save failed: ' . $e->getMessage());
        @unlink($newAbs);
        social_back('error', 'Could not save the change. The previous image is unchanged.', $key);
    }

    social_delete_upload($pdo, $oldPath);
    social_back('success', "New image saved for “{$label}”. It is live on the Social page now.", $key);
}

try {
    if ($action === 'remove') {
        // A NULL path means "removed": the card stays and shows the logo.
        $pdo->prepare("REPLACE INTO social_card_images (card_key, img_path) VALUES (?, NULL)")
            ->execute([$key]);
        $done = "Image removed from “{$label}”. The card now shows the ATMA BISWAS logo.";
    } else {
        // No row means the card's built-in image, as shipped with the site.
        $pdo->prepare("DELETE FROM social_card_images WHERE card_key = ?")->execute([$key]);
        $done = "“{$label}” is back to its original image.";
    }
} catch (Throwable $e) {
    error_log('social_image: ' . $action . ' failed: ' . $e->getMessage());
    social_back('error', 'Could not save the change. The card is unchanged.', $key);
}

social_delete_upload($pdo, $oldPath);
social_back('success', $done, $key);
