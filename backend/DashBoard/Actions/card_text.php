<?php
/**
 * Change, or put back, the title and description of one card on a card page
 * (Social, Events).
 *
 * Posted to by card_manager.php; always redirects back to that page's screen
 * with a message. Editing the text is content editing, open to every admin.
 * Restoring the original throws the edited text away, so like every other
 * delete in the dashboard it needs a super admin. The card's image is never
 * touched here.
 */
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../csrf_helper.php';
require_once __DIR__ . '/../../../page_cards.php';

require_login();

$cardPage = (string) ($_POST['page'] ?? $_GET['page'] ?? '');
$page     = isset(card_pages()[$cardPage]) ? card_page($cardPage) : null;

function card_text_back(string $type, string $message, string $key = ''): never
{
    global $page;
    $_SESSION['cards_flash'] = ['type' => $type, 'msg' => $message];
    header('Location: ../' . ($page['admin'] ?? 'social_images.php') . ($key !== '' ? '#card-' . rawurlencode($key) : ''));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    card_text_back('error', 'Invalid request.');
}
if (!csrf_verify()) {
    card_text_back('error', 'Your session expired. Please reload the page and try again. Nothing was changed.');
}

$key    = (string) ($_POST['card_key'] ?? '');
$action = (string) ($_POST['action'] ?? '');
$cards  = $page ? cards_index($cardPage) : [];

if (!isset($cards[$key]) || !in_array($action, ['save', 'restore'], true)) {
    card_text_back('error', 'Unknown card or action. Nothing was changed.');
}
$card  = $cards[$key];
$table = $page['texts'];

if ($action === 'restore') {
    require_super_admin('restore a ' . $page['name'] . ' page card\'s original text');
}

if ($action === 'save') {
    $title = trim(str_replace("\r\n", "\n", (string) ($_POST['title'] ?? '')));
    $desc  = trim(str_replace("\r\n", "\n", (string) ($_POST['description'] ?? '')));

    if (!mb_check_encoding($title, 'UTF-8') || !mb_check_encoding($desc, 'UTF-8')) {
        card_text_back('error', 'The text contains characters that cannot be saved. Nothing was changed.', $key);
    }
    if ($title === '') {
        card_text_back('error', 'The title cannot be empty. Nothing was changed.', $key);
    }
    if (mb_strlen($title) > CARD_TITLE_MAX) {
        card_text_back('error', 'The title can be at most ' . CARD_TITLE_MAX . ' characters. Nothing was changed.', $key);
    }
    if (mb_strlen($desc) > CARD_DESC_MAX) {
        card_text_back('error', 'The description can be at most ' . CARD_DESC_MAX . ' characters. Nothing was changed.', $key);
    }
}

try {
    $pdo = (new Db())->connect();
    cards_ensure_tables($pdo, $cardPage);

    if ($action === 'save' && ($title !== $card['title'] || $desc !== $card['desc'])) {
        $pdo->prepare("REPLACE INTO `{$table}` (card_key, title, description) VALUES (?, ?, ?)")
            ->execute([$key, $title, $desc]);
        $done = "Text saved for “{$title}”. It is live on the {$page['name']} page now.";
    } else {
        // Restoring, or saving text identical to the built-in one: no row
        // means the card shows its original title and description.
        $pdo->prepare("DELETE FROM `{$table}` WHERE card_key = ?")->execute([$key]);
        $done = "“{$card['title']}” shows its original title and description.";
    }
} catch (Throwable $e) {
    error_log('card_text: ' . $action . ' failed: ' . $e->getMessage());
    card_text_back('error', 'Could not save the change. The card text is unchanged.', $key);
}

card_text_back('success', $done, $key);
