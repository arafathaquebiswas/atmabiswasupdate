<?php
/**
 * Public pages made of cards (Social, Events) and what an admin changes on them.
 *
 * Each page's cards, their order and their built-in titles, descriptions and
 * images live in code (social_cards.php, events_cards.php). From the admin
 * panel (backend/DashBoard/social_images.php, events_images.php) an admin can
 * change each card's image and its title and description; those changes are
 * stored in the page's own two tables, keyed by the card's 'key'. Keys are
 * permanent -- renaming one orphans whatever was saved for it.
 *
 * Image, one row per card in the page's images table:
 *   no row                 -> its built-in 'img' (the logo when it has none)
 *   row with a file path   -> that upload
 *   row with NULL / empty  -> the official logo (the admin removed the image)
 * and the official logo whenever the chosen file is missing on disk.
 *
 * Text, one row per card in the page's texts table:
 *   no row                 -> its built-in title and description
 *   row                    -> the saved title and description
 *
 * Added cards, one row per card in the page's extras table: an admin can add
 * cards to a section, up to CARDS_PER_SEGMENT_MAX in all. An added card has
 * no built-in anything -- its key is "<section key>-x<row id>", and its
 * title, description and image live in the same two tables as every other
 * card's, so editing them works the same way.
 */
require_once __DIR__ . '/storage.php';
require_once __DIR__ . '/image_optimize.php';
require_once __DIR__ . '/social_cards.php';
require_once __DIR__ . '/events_cards.php';

/** The official ATMA BISWAS logo: placeholder cards and every fallback use it. */
const CARD_LOGO = 'LOGO/NGO_logo_monogram.png';

/** Accepted upload types => extension saved with (never taken from the filename). */
const CARD_IMG_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

const CARD_IMG_MAX_BYTES = 5 * 1024 * 1024;

/** Longest card title and description an admin can save, in characters. */
const CARD_TITLE_MAX = 150;
const CARD_DESC_MAX  = 1000;

/** Most cards a section may hold, built-in and added together. */
const CARDS_PER_SEGMENT_MAX = 8;

/**
 * The pages whose cards can be managed, by id. Table names and upload
 * folders come only from here, never from a request.
 */
function card_pages(): array
{
    return [
        'social' => [
            'name'   => 'Social',
            'public' => 'social.php',
            'admin'  => 'social_images.php',
            'cards'  => 'social_cards',
            'images' => 'social_card_images',
            'texts'  => 'social_card_texts',
            'extras' => 'social_card_extras',
            'folder' => 'social',
            'prefix' => 'SOCIAL',
        ],
        'events' => [
            'name'   => 'Events',
            'public' => 'Events.php',
            'admin'  => 'events_images.php',
            'cards'  => 'events_cards',
            'images' => 'event_card_images',
            'texts'  => 'event_card_texts',
            'extras' => 'event_card_extras',
            'folder' => 'events',
            'prefix' => 'EVENT',
        ],
    ];
}

/** One page's settings plus its 'id'; throws for an id card_pages() lacks. */
function card_page(string $page): array
{
    $pages = card_pages();
    if (!isset($pages[$page])) {
        throw new InvalidArgumentException("Unknown card page: {$page}");
    }
    return $pages[$page] + ['id' => $page];
}

/** The page's segments in order, each card with its built-in text and image. */
function cards_segments(string $page): array
{
    return (card_page($page)['cards'])();
}

/**
 * Cards added from the admin panel as ['id', 'segment_key'] rows, oldest
 * first. Never throws: without a database or the table there are none.
 */
function cards_extra_rows(string $page, ?PDO $pdo = null): array
{
    try {
        $pdo = cards_pdo($pdo);
        if (!$pdo) {
            return [];
        }
        return $pdo->query("SELECT id, segment_key FROM `" . card_page($page)['extras'] . "` ORDER BY id")
                   ->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * cards_segments() with each section's added cards after its built-in ones.
 * An added card starts empty ('extra' => true); its text and image come from
 * the overrides like any other card's.
 */
function cards_all_segments(string $page, ?PDO $pdo = null): array
{
    $segments = cards_segments($page);
    $bySeg    = [];
    foreach (cards_extra_rows($page, $pdo) as $row) {
        $bySeg[$row['segment_key']][] = (int) $row['id'];
    }
    foreach ($segments as &$segment) {
        foreach ($bySeg[$segment['key']] ?? [] as $id) {
            $segment['cards'][] = ['key' => $segment['key'] . '-x' . $id, 'img' => null, 'alt' => '',
                                   'title' => '', 'desc' => '', 'extra' => true];
        }
    }
    unset($segment);
    return $segments;
}

/** The largest upload that gets through: our cap or PHP's, whichever is lower. */
function cards_upload_limit(): int
{
    $limit = CARD_IMG_MAX_BYTES;
    foreach (['upload_max_filesize', 'post_max_size'] as $setting) {
        $raw   = trim((string) ini_get($setting));
        $bytes = (int) $raw * match (strtolower(substr($raw, -1))) {
            'g' => 1024 * 1024 * 1024, 'm' => 1024 * 1024, 'k' => 1024, default => 1,
        };
        if ($bytes > 0) {               // 0 means no limit
            $limit = min($limit, $bytes);
        }
    }
    return $limit;
}

/** cards_upload_limit() for people: "5 MB", "1.5 MB". */
function cards_upload_limit_label(): string
{
    return rtrim(rtrim(number_format(cards_upload_limit() / 1048576, 1), '0'), '.') . ' MB';
}

/** Every card of the page by key, added ones too, with its segment title -- the whitelist for admin actions. */
function cards_index(string $page, ?PDO $pdo = null): array
{
    $index = [];
    foreach (cards_all_segments($page, $pdo) as $segment) {
        foreach ($segment['cards'] as $card) {
            $index[$card['key']] = $card + ['segment' => $segment['title'], 'segment_key' => $segment['key']];
        }
    }
    return $index;
}

/** Create the page's image and text tables, where this install lacks them. */
function cards_ensure_tables(PDO $pdo, string $page): void
{
    $p = card_page($page);
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `{$p['images']}` (
            card_key   VARCHAR(64)  NOT NULL PRIMARY KEY,
            img_path   VARCHAR(255) NULL,
            updated_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `{$p['texts']}` (
            card_key    VARCHAR(64)  NOT NULL PRIMARY KEY,
            title       VARCHAR(255) NOT NULL,
            description TEXT         NOT NULL,
            updated_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `{$p['extras']}` (
            id          INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
            segment_key VARCHAR(64)  NOT NULL,
            created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY segment_key (segment_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

/**
 * Check an uploaded image: null when it is fine (with $ext set to the
 * extension to save it with), otherwise the message to show the admin.
 * The type comes from the file's contents, never its name or the browser.
 */
function cards_accept_upload(?array $file, ?string &$ext): ?string
{
    $ext   = null;
    $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;

    if ($error === UPLOAD_ERR_NO_FILE) {
        return 'Please choose an image first.';
    }
    if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE
        || ($error === UPLOAD_ERR_OK && $file['size'] > cards_upload_limit())) {
        return 'The image is larger than ' . cards_upload_limit_label() . '. Please choose a smaller one.';
    }
    if ($error !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        return "The upload failed (error code {$error}). Please try again.";
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset(CARD_IMG_TYPES[$mime]) || @getimagesize($file['tmp_name']) === false) {
        return 'Only JPG, PNG or WebP images can be uploaded.';
    }
    $ext = CARD_IMG_TYPES[$mime];
    return null;
}

/**
 * $pdo, or else the site's own connection -- tried once per request, so a
 * public page makes one attempt however many lookups it does. Null without
 * a database.
 */
function cards_pdo(?PDO $pdo = null): ?PDO
{
    static $shared = false;
    if ($pdo !== null) {
        return $pdo;
    }
    if ($shared === false) {
        try {
            require_once __DIR__ . '/backend/Database/db.php';
            $shared = (new Db())->connect() ?: null;
        } catch (Throwable $e) {
            $shared = null;
        }
    }
    return $shared;
}

/**
 * Saved images as card_key => path (null when the admin removed the image).
 *
 * Never throws. With no database, or before an admin has opened the page's
 * images screen and the table exists, every card shows its built-in image,
 * so the public page cannot fail here.
 */
function cards_image_overrides(string $page, ?PDO $pdo = null): array
{
    try {
        $pdo = cards_pdo($pdo);
        if (!$pdo) {
            return [];
        }
        return $pdo->query("SELECT card_key, img_path FROM `" . card_page($page)['images'] . "`")
                   ->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * Saved text as card_key => ['title' => ..., 'desc' => ...].
 *
 * Never throws, for the same reason as cards_image_overrides(): without a
 * database or the table, every card keeps its built-in text.
 */
function cards_text_overrides(string $page, ?PDO $pdo = null): array
{
    try {
        $pdo = cards_pdo($pdo);
        if (!$pdo) {
            return [];
        }
        $texts = [];
        foreach ($pdo->query("SELECT card_key, title, description FROM `" . card_page($page)['texts'] . "`") as $row) {
            $texts[$row['card_key']] = ['title' => (string) $row['title'], 'desc' => (string) $row['description']];
        }
        return $texts;
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * Segments with saved text applied. Each card also gets 'edited' (bool)
 * and its built-in 'orig_title' / 'orig_desc', for the admin screen.
 */
function cards_apply_texts(array $segments, array $texts): array
{
    foreach ($segments as &$segment) {
        foreach ($segment['cards'] as &$card) {
            $card['orig_title'] = $card['title'];
            $card['orig_desc']  = $card['desc'];
            $card['edited']     = isset($texts[$card['key']]);
            if ($card['edited']) {
                $card['title'] = $texts[$card['key']]['title'];
                $card['desc']  = $texts[$card['key']]['desc'];
            }
            // Added cards have no alt text of their own; their title describes the picture.
            if ($card['alt'] === '') {
                $card['alt'] = $card['title'];
            }
        }
        unset($card);
    }
    unset($segment);
    return $segments;
}

/** The logo's URL, versioned by its mtime like the pages always did. */
function cards_logo_src(): string
{
    $abs = __DIR__ . '/' . CARD_LOGO;
    return CARD_LOGO . (is_file($abs) ? '?v=' . filemtime($abs) : '');
}

/**
 * The image a card shows: ['src', 'logo' => bool, 'source'], where source is
 * 'uploaded', 'original', 'placeholder' (no image of its own), 'removed'
 * (the admin removed it) or 'missing' (its file is gone from disk).
 */
function cards_card_image(array $card, array $overrides): array
{
    $logo = static fn(string $source): array => ['src' => cards_logo_src(), 'logo' => true, 'source' => $source];

    if (array_key_exists($card['key'], $overrides)) {
        $saved = trim((string) $overrides[$card['key']]);
        if ($saved === '') {
            return $logo('removed');
        }
        // '' when the file is not on disk under media/ or uploads/.
        $path = media_resolve($saved);
        return $path !== ''
            ? ['src' => $path, 'logo' => false, 'source' => 'uploaded']
            : $logo('missing');
    }

    if (empty($card['img'])) {
        return $logo('placeholder');
    }
    return is_file(__DIR__ . '/' . $card['img'])
        ? ['src' => $card['img'], 'logo' => false, 'source' => 'original']
        : $logo('missing');
}

/** A fresh document-root-relative path for a new upload to the page, e.g. media/events/EVENT_<hex>.jpg. */
function cards_upload_path(string $page, string $ext): string
{
    $p = card_page($page);
    return media_dir($p['folder']) . $p['prefix'] . '_' . bin2hex(random_bytes(16)) . '.' . $ext;
}

/**
 * Delete an image this page's admin uploaded, once no card refers to it any more.
 *
 * Only paths of the exact shape cards_upload_path() gives this page can
 * match, so the logo, the built-in card photos, other pages' uploads and
 * every other section's images are out of reach whatever the database
 * holds. Call it after the card's row has been changed, so the reference
 * check no longer counts that card.
 */
function cards_delete_upload(PDO $pdo, string $page, ?string $path): void
{
    $p    = card_page($page);
    $path = ltrim(trim((string) $path), '/');
    $re   = '~^media/' . preg_quote($p['folder'], '~') . '/' . preg_quote($p['prefix'], '~')
          . '_[0-9a-f]{32}\.(jpg|png|webp)$~';
    if (!preg_match($re, $path)) {
        return;
    }
    $st = $pdo->prepare("SELECT COUNT(*) FROM `{$p['images']}` WHERE img_path = ?");
    $st->execute([$path]);
    if ((int) $st->fetchColumn() > 0) {
        return;
    }
    $abs = __DIR__ . '/' . $path;
    // The original plus the WebP and resized copies image_optimize.php makes beside it.
    foreach (array_merge([$abs], glob($abs . '.*') ?: []) as $file) {
        if (is_file($file)) {
            @unlink($file);
        }
    }
}

/**
 * Print the page's segments and cards, with the admin's text and images
 * applied -- the markup social.php and Events.php have always used.
 */
function cards_render(string $page): void
{
    $segments  = cards_apply_texts(cards_all_segments($page), cards_text_overrides($page));
    $overrides = cards_image_overrides($page);
    $logoSrc   = htmlspecialchars(cards_logo_src());

    foreach ($segments as $segment): ?>
        <div class="segment">
            <div class="segment-header">
                <i class="<?= htmlspecialchars($segment['icon']) ?>"></i>
                <h2><?= htmlspecialchars($segment['title']) ?></h2>
            </div>
            <div class="card-grid">
                <?php foreach ($segment['cards'] as $card):
                    // An added card whose text was never saved has nothing to show.
                    if (!empty($card['extra']) && $card['title'] === '') {
                        continue;
                    }
                    $image = cards_card_image($card, $overrides);
                    // Uploads get the narrower copies image_optimize.php makes, when they exist.
                    $set   = $image['source'] === 'uploaded' ? img_srcset($image['src']) : ['fallback' => ''];
                ?>
                <div class="card">
                    <img src="<?= htmlspecialchars($image['src']) ?>"<?php if ($set['fallback'] !== ''): ?> srcset="<?= $set['fallback'] ?>" sizes="(max-width: 640px) 100vw, 400px"<?php endif; ?> loading="lazy" alt="<?= htmlspecialchars($card['alt']) ?>"<?= $image['logo'] ? ' class="card-img-logo"' : '' ?>
                         onerror="this.onerror=null;this.removeAttribute('srcset');this.src='<?= $logoSrc ?>';this.className='card-img-logo';">
                    <div class="card-content">
                        <h3><?= htmlspecialchars($card['title']) ?></h3>
                        <?php if ($card['desc'] !== ''): ?>
                        <p><?= htmlspecialchars($card['desc']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach;
}
