<?php
/**
 * Add a new card to a section of a card page (Social, Events), or delete a
 * card that was added this way.
 *
 * Posted to by card_manager.php; always redirects back to that page's screen
 * with a message. Adding is content editing, open to every admin, up to
 * CARDS_PER_SEGMENT_MAX cards in the section. Deleting takes a card off the
 * public site, so like every other delete in the dashboard it needs a super
 * admin. Built-in cards can never be deleted here.
 *
 * A new card is written in one transaction (its extras, texts and images
 * rows) only after its image is safely on disk, so a failure at any point
 * leaves no half-made card and no stray file.
 */
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../csrf_helper.php';
require_once __DIR__ . '/../../../page_cards.php';

require_login();

// The page also rides in the query string, so it is known even when an
// oversized upload leaves $_POST empty.
$cardPage = (string) ($_POST['page'] ?? $_GET['page'] ?? '');
$page     = isset(card_pages()[$cardPage]) ? card_page($cardPage) : null;

function card_extra_back(string $type, string $message, string $anchor = ''): never
{
    global $page;
    $_SESSION['cards_flash'] = ['type' => $type, 'msg' => $message];
    header('Location: ../' . ($page['admin'] ?? 'social_images.php') . ($anchor !== '' ? '#' . rawurlencode($anchor) : ''));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    card_extra_back('error', 'Invalid request.');
}
// A request over PHP's post_max_size arrives with $_POST and $_FILES both
// empty, which the CSRF check below would report as an expired session.
if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    card_extra_back('error', 'The image is larger than ' . cards_upload_limit_label() . '. Please choose a smaller one.');
}
if (!csrf_verify()) {
    card_extra_back('error', 'Your session expired. Please reload the page and try again. Nothing was changed.');
}

$action = (string) ($_POST['action'] ?? '');
if (!$page || !in_array($action, ['add', 'delete'], true)) {
    card_extra_back('error', 'Unknown page or action. Nothing was changed.');
}

try {
    $pdo = (new Db())->connect();
    cards_ensure_tables($pdo, $cardPage);
} catch (Throwable $e) {
    error_log('card_extra: database unavailable: ' . $e->getMessage());
    card_extra_back('error', 'Could not reach the database. Nothing was changed.');
}

if ($action === 'delete') {
    require_super_admin('delete a ' . $page['name'] . ' page card');

    $key   = (string) ($_POST['card_key'] ?? '');
    $cards = cards_index($cardPage, $pdo);
    if (!isset($cards[$key]) || empty($cards[$key]['extra'])) {
        card_extra_back('error', 'Only cards added from the admin panel can be deleted. Nothing was changed.');
    }
    $id = (int) substr($key, strrpos($key, '-x') + 2);

    try {
        $st = $pdo->prepare("SELECT img_path FROM `{$page['images']}` WHERE card_key = ?");
        $st->execute([$key]);
        $oldPath = $st->fetchColumn() ?: null;
        $title   = cards_text_overrides($cardPage, $pdo)[$key]['title'] ?? '';

        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM `{$page['extras']}` WHERE id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM `{$page['texts']}` WHERE card_key = ?")->execute([$key]);
        $pdo->prepare("DELETE FROM `{$page['images']}` WHERE card_key = ?")->execute([$key]);
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('card_extra: delete failed: ' . $e->getMessage());
        card_extra_back('error', 'Could not delete the card. Nothing was changed.', 'card-' . $key);
    }

    cards_delete_upload($pdo, $cardPage, $oldPath);
    card_extra_back('success', "Card “{$title}” deleted from {$cards[$key]['segment']}.", 'seg-' . $cards[$key]['segment_key']);
}

// Adding a card.
$segKey   = (string) ($_POST['segment'] ?? '');
$segments = array_column(cards_all_segments($cardPage, $pdo), null, 'key');
if (!isset($segments[$segKey])) {
    card_extra_back('error', 'Unknown section. Nothing was changed.');
}
$segment = $segments[$segKey];
$anchor  = 'seg-' . $segKey;

if (count($segment['cards']) >= CARDS_PER_SEGMENT_MAX) {
    card_extra_back('error', "“{$segment['title']}” already has " . CARDS_PER_SEGMENT_MAX
        . ' cards, the most a section can hold. Delete an added card first. Nothing was changed.', $anchor);
}

$title = trim(str_replace("\r\n", "\n", (string) ($_POST['title'] ?? '')));
$desc  = trim(str_replace("\r\n", "\n", (string) ($_POST['description'] ?? '')));
if (!mb_check_encoding($title, 'UTF-8') || !mb_check_encoding($desc, 'UTF-8')) {
    card_extra_back('error', 'The text contains characters that cannot be saved. Nothing was changed.', $anchor);
}
if ($title === '') {
    card_extra_back('error', 'The new card needs a title. Nothing was changed.', $anchor);
}
if (mb_strlen($title) > CARD_TITLE_MAX) {
    card_extra_back('error', 'The title can be at most ' . CARD_TITLE_MAX . ' characters. Nothing was changed.', $anchor);
}
if (mb_strlen($desc) > CARD_DESC_MAX) {
    card_extra_back('error', 'The description can be at most ' . CARD_DESC_MAX . ' characters. Nothing was changed.', $anchor);
}
if (($problem = cards_accept_upload($_FILES['image'] ?? null, $ext)) !== null) {
    card_extra_back('error', $problem . ' Nothing was changed.', $anchor);
}

$newPath = cards_upload_path($cardPage, $ext);
$newAbs  = dirname(__DIR__, 3) . '/' . $newPath;
if (!img_store_uploaded($_FILES['image']['tmp_name'], $newAbs)) {
    card_extra_back('error', 'Could not save the image on the server. Nothing was changed.', $anchor);
}

try {
    $pdo->beginTransaction();
    $pdo->prepare("INSERT INTO `{$page['extras']}` (segment_key) VALUES (?)")->execute([$segKey]);
    $key = $segKey . '-x' . (int) $pdo->lastInsertId();
    $pdo->prepare("INSERT INTO `{$page['texts']}` (card_key, title, description) VALUES (?, ?, ?)")
        ->execute([$key, $title, $desc]);
    $pdo->prepare("INSERT INTO `{$page['images']}` (card_key, img_path) VALUES (?, ?)")
        ->execute([$key, $newPath]);
    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('card_extra: add failed: ' . $e->getMessage());
    foreach (array_merge([$newAbs], glob($newAbs . '.*') ?: []) as $file) {
        @unlink($file);
    }
    card_extra_back('error', 'Could not save the new card. Nothing was changed.', $anchor);
}

card_extra_back('success', "New card “{$title}” added to {$segment['title']}. It is live on the {$page['name']} page now.", 'card-' . $key);
