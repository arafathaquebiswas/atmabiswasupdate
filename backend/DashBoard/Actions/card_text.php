<?php
/**
 * Change, or put back, the title and description of one Social page card.
 *
 * Posted to by social_images.php; always redirects back there with a message.
 * Editing the text is content editing, open to every admin. Restoring the
 * original throws the edited text away, so like every other delete in the
 * dashboard it needs a super admin. The card's image is never touched here.
 */
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../csrf_helper.php';
require_once __DIR__ . '/../../../social_cards.php';

require_login();

function social_text_back(string $type, string $message, string $key = ''): never
{
    $_SESSION['social_flash'] = ['type' => $type, 'msg' => $message];
    header('Location: ../social_images.php' . ($key !== '' ? '#card-' . rawurlencode($key) : ''));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    social_text_back('error', 'Invalid request.');
}
if (!csrf_verify()) {
    social_text_back('error', 'Your session expired. Please reload the page and try again. Nothing was changed.');
}

$cards  = social_card_index();
$key    = (string) ($_POST['card_key'] ?? '');
$action = (string) ($_POST['action'] ?? '');

if (!isset($cards[$key]) || !in_array($action, ['save', 'restore'], true)) {
    social_text_back('error', 'Unknown card or action. Nothing was changed.');
}
$card = $cards[$key];

if ($action === 'restore') {
    require_super_admin('restore a Social page card\'s original text');
}

if ($action === 'save') {
    $title = trim(str_replace("\r\n", "\n", (string) ($_POST['title'] ?? '')));
    $desc  = trim(str_replace("\r\n", "\n", (string) ($_POST['description'] ?? '')));

    if (!mb_check_encoding($title, 'UTF-8') || !mb_check_encoding($desc, 'UTF-8')) {
        social_text_back('error', 'The text contains characters that cannot be saved. Nothing was changed.', $key);
    }
    if ($title === '') {
        social_text_back('error', 'The title cannot be empty. Nothing was changed.', $key);
    }
    if (mb_strlen($title) > SOCIAL_TITLE_MAX) {
        social_text_back('error', 'The title can be at most ' . SOCIAL_TITLE_MAX . ' characters. Nothing was changed.', $key);
    }
    if (mb_strlen($desc) > SOCIAL_DESC_MAX) {
        social_text_back('error', 'The description can be at most ' . SOCIAL_DESC_MAX . ' characters. Nothing was changed.', $key);
    }
}

try {
    $pdo = (new Db())->connect();
    social_ensure_table($pdo);

    if ($action === 'save' && ($title !== $card['title'] || $desc !== $card['desc'])) {
        $pdo->prepare("REPLACE INTO social_card_texts (card_key, title, description) VALUES (?, ?, ?)")
            ->execute([$key, $title, $desc]);
        $done = "Text saved for “{$title}”. It is live on the Social page now.";
    } else {
        // Restoring, or saving text identical to the built-in one: no row
        // means the card shows its original title and description.
        $pdo->prepare("DELETE FROM social_card_texts WHERE card_key = ?")->execute([$key]);
        $done = "“{$card['title']}” shows its original title and description.";
    }
} catch (Throwable $e) {
    error_log('social_text: ' . $action . ' failed: ' . $e->getMessage());
    social_text_back('error', 'Could not save the change. The card text is unchanged.', $key);
}

social_text_back('success', $done, $key);
