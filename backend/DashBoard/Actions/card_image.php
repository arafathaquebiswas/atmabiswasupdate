<?php
/**
 * Upload, replace, remove or restore the image of one card on a card page
 * (Social, Events).
 *
 * Posted to by card_manager.php; always redirects back to that page's screen
 * with a message. Uploading or replacing is content editing, open to every
 * admin. Removing or restoring takes an image off the public site, so like
 * every other delete in the dashboard it needs a super admin.
 *
 * The database changes only after the new file is safely on disk, and the old
 * file is deleted only after the database no longer points at it, so a failed
 * step at any point leaves the card showing the image it had before.
 */
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../csrf_helper.php';
require_once __DIR__ . '/../../../page_cards.php';

require_login();

// The page also rides in the query string, so it is known even when an
// oversized upload leaves $_POST empty.
$cardPage = (string) ($_POST['page'] ?? $_GET['page'] ?? '');
$page     = isset(card_pages()[$cardPage]) ? card_page($cardPage) : null;

function card_image_back(string $type, string $message, string $key = ''): never
{
    global $page;
    $_SESSION['cards_flash'] = ['type' => $type, 'msg' => $message];
    header('Location: ../' . ($page['admin'] ?? 'social_images.php') . ($key !== '' ? '#card-' . rawurlencode($key) : ''));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    card_image_back('error', 'Invalid request.');
}
// A request over PHP's post_max_size arrives with $_POST and $_FILES both
// empty, which the CSRF check below would report as an expired session.
if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    card_image_back('error', 'The image is larger than ' . cards_upload_limit_label() . '. Please choose a smaller one.');
}
if (!csrf_verify()) {
    card_image_back('error', 'Your session expired. Please reload the page and try again. Nothing was changed.');
}

$key    = (string) ($_POST['card_key'] ?? '');
$action = (string) ($_POST['action'] ?? '');
$cards  = $page ? cards_index($cardPage) : [];

if (!isset($cards[$key]) || !in_array($action, ['upload', 'remove', 'restore'], true)) {
    card_image_back('error', 'Unknown card or action. Nothing was changed.');
}
$isExtra = !empty($cards[$key]['extra']);
// An added card's title is in the texts table, not in the built-in list.
$title = $isExtra ? (cards_text_overrides($cardPage)[$key]['title'] ?? '') : $cards[$key]['title'];
$label = $cards[$key]['segment'] . ' — ' . $title;
$table = $page['images'];

if ($action !== 'upload') {
    require_super_admin('remove a ' . $page['name'] . ' page image');
}
if ($action === 'restore' && $isExtra) {
    card_image_back('error', 'This card was added from the admin panel, so it has no original image. Nothing was changed.', $key);
}

try {
    $pdo = (new Db())->connect();
    cards_ensure_tables($pdo, $cardPage);
    $st = $pdo->prepare("SELECT img_path FROM `{$table}` WHERE card_key = ?");
    $st->execute([$key]);
    $oldPath = $st->fetchColumn();
    $oldPath = $oldPath === false ? null : $oldPath;
} catch (Throwable $e) {
    error_log('card_image: database unavailable: ' . $e->getMessage());
    card_image_back('error', 'Could not reach the database. Nothing was changed.', $key);
}

if ($action === 'upload') {
    $file = $_FILES['image'] ?? null;
    if (($problem = cards_accept_upload($file, $ext)) !== null) {
        card_image_back('error', $problem, $key);
    }

    $newPath = cards_upload_path($cardPage, $ext);
    $newAbs  = dirname(__DIR__, 3) . '/' . $newPath;

    if (!img_store_uploaded($file['tmp_name'], $newAbs)) {
        card_image_back('error', 'Could not save the image on the server. The previous image is unchanged.', $key);
    }

    try {
        $pdo->prepare("REPLACE INTO `{$table}` (card_key, img_path) VALUES (?, ?)")
            ->execute([$key, $newPath]);
    } catch (Throwable $e) {
        error_log('card_image: save failed: ' . $e->getMessage());
        @unlink($newAbs);
        card_image_back('error', 'Could not save the change. The previous image is unchanged.', $key);
    }

    cards_delete_upload($pdo, $cardPage, $oldPath);
    card_image_back('success', "New image saved for “{$label}”. It is live on the {$page['name']} page now.", $key);
}

try {
    if ($action === 'remove') {
        // A NULL path means "removed": the card stays and shows the logo.
        $pdo->prepare("REPLACE INTO `{$table}` (card_key, img_path) VALUES (?, NULL)")
            ->execute([$key]);
        $done = "Image removed from “{$label}”. The card now shows the ATMA BISWAS logo.";
    } else {
        // No row means the card's built-in image, as shipped with the site.
        $pdo->prepare("DELETE FROM `{$table}` WHERE card_key = ?")->execute([$key]);
        $done = "“{$label}” is back to its original image.";
    }
} catch (Throwable $e) {
    error_log('card_image: ' . $action . ' failed: ' . $e->getMessage());
    card_image_back('error', 'Could not save the change. The card is unchanged.', $key);
}

cards_delete_upload($pdo, $cardPage, $oldPath);
card_image_back('success', $done, $key);
