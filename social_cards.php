<?php
/**
 * Cards on the Social page (social.php) and what an admin changes on them.
 *
 * The cards, their order and their built-in titles, descriptions and images
 * live here, in code. From the admin panel (backend/DashBoard/social_images.php)
 * an admin can change each card's image and its title and description; those
 * changes are stored in the database keyed by the card's 'key'. Keys are
 * permanent -- renaming one orphans whatever was saved for it.
 *
 * Image, one row per card in social_card_images:
 *   no row                 -> its built-in 'img' (the logo when it has none)
 *   row with a file path   -> that upload
 *   row with NULL / empty  -> the official logo (the admin removed the image)
 * and the official logo whenever the chosen file is missing on disk.
 *
 * Text, one row per card in social_card_texts:
 *   no row                 -> its built-in title and description
 *   row                    -> the saved title and description
 */
require_once __DIR__ . '/storage.php';
require_once __DIR__ . '/image_optimize.php';

/** The official ATMA BISWAS logo: placeholder cards and every fallback use it. */
const SOCIAL_LOGO = 'LOGO/NGO_logo_monogram.png';

/** Accepted upload types => extension saved with (never taken from the filename). */
const SOCIAL_IMG_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

const SOCIAL_IMG_MAX_BYTES = 5 * 1024 * 1024;

/** Longest card title and description an admin can save, in characters. */
const SOCIAL_TITLE_MAX = 150;
const SOCIAL_DESC_MAX  = 1000;

/** The largest upload that gets through: our cap or PHP's, whichever is lower. */
function social_upload_limit(): int
{
    $limit = SOCIAL_IMG_MAX_BYTES;
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

/** social_upload_limit() for people: "5 MB", "1.5 MB". */
function social_upload_limit_label(): string
{
    return rtrim(rtrim(number_format(social_upload_limit() / 1048576, 1), '0'), '.') . ' MB';
}

/** Segments in page order; each card's 'img' is its built-in image, null for the logo. */
function social_cards(): array
{
    $logoCards = static function (string $prefix, int $count, string $title, string $alt): array {
        $cards = [];
        for ($i = 1; $i <= $count; $i++) {
            $cards[] = ['key' => "{$prefix}-{$i}", 'img' => null, 'alt' => $alt, 'title' => $title, 'desc' => ''];
        }
        return $cards;
    };

    return [
        [
            'icon'  => 'fa-solid fa-hand-holding-heart',
            'title' => 'Empowering Women',
            'cards' => [
                ['key' => 'women-1', 'img' => 'COW/cow_pic1.jpg', 'alt' => 'Empowering Women',
                 'title' => 'Empowering Women',
                 'desc'  => 'ATMABISWAS supports women by providing livestock for sustainable livelihoods.'],
                ['key' => 'women-2', 'img' => 'fish&redy/ABS_04.jpg', 'alt' => 'Culinary Training',
                 'title' => 'Empowering Women',
                 'desc'  => 'Women receive culinary training to enhance their skills and achieve financial independence.'],
                ['key' => 'women-3', 'img' => 'women_empowerment/women.jpg', 'alt' => 'Female Farmers',
                 'title' => 'Empowering Women',
                 'desc'  => 'ATMABISWAS empowers female farmers with free fertilizer and resources for better agricultural productivity.'],
            ],
        ],
        [
            'icon'  => 'fa-solid fa-chalkboard-user',
            'title' => 'ATMABISWAS Training Center',
            'cards' => [
                ['key' => 'training-1', 'img' => 'training/automachinic.jpeg', 'alt' => 'Auto Mechanic Training',
                 'title' => 'ATMABISWAS Auto Mechanic Training Center',
                 'desc'  => 'ATMABISWAS Auto Mechanic Training Center equips individuals with practical skills for better job opportunities and improved livelihoods.'],
                ['key' => 'training-2', 'img' => 'training/bevarag.jpeg', 'alt' => 'Beverage Training Center',
                 'title' => 'ATMABISWAS Food & Beverage Training Center',
                 'desc'  => 'ATMABISWAS provides hands-on food and beverage training for skill development and employment.'],
                ['key' => 'training-3', 'img' => 'training/cooking.jpeg', 'alt' => 'ATMABISWAS Cooking Training Center',
                 'title' => 'ATMABISWAS Cooking Training Center',
                 'desc'  => 'ATMABISWAS Cooking Training Center empowers individuals with culinary skills for self-reliance and employment.'],
                ['key' => 'training-4', 'img' => 'training/fashion.jpeg', 'alt' => 'ATMABISWAS Fashion Training Center',
                 'title' => 'ATMABISWAS Fashion Training Center',
                 'desc'  => 'ATMABISWAS Fashion Training Center offers skill-based training in tailoring and design for self-employment.'],
                ['key' => 'training-5', 'img' => 'training/pksf_fashion.jpeg', 'alt' => 'PKSF Visit Fashion Center',
                 'title' => 'PKSF Team Visited ATMABISWAS Fashion Training Center',
                 'desc'  => 'PKSF team visited ATMABISWAS Fashion Training Center to observe and encourage skill development initiatives.'],
                ['key' => 'training-6', 'img' => 'training/pksfvisitcooking.jpeg', 'alt' => 'PKSF Visit Cooking Center',
                 'title' => 'PKSF Team Visited ATMABISWAS Cooking Training Center',
                 'desc'  => 'PKSF team visited ATMABISWAS Cooking Training Center to inspire and support skill development initiatives.'],
            ],
        ],
        [
            'icon'  => 'fa-solid fa-baby',
            'title' => 'ATMABISWAS Daycare',
            'cards' => [
                ['key' => 'daycare-1', 'img' => 'daycare/daycare.jpeg', 'alt' => 'ATMABISWAS Daycare',
                 'title' => 'ATMABISWAS Daycare',
                 'desc'  => 'ATMABISWAS Daycare provides a safe and nurturing environment for children while their parents work.'],
                ['key' => 'daycare-2', 'img' => 'daycare/daycare2.png', 'alt' => 'ATMABISWAS Daycare',
                 'title' => 'ATMABISWAS Daycare',
                 'desc'  => 'ATMABISWAS Daycare fosters early childhood development in a caring environment.'],
                ['key' => 'daycare-3', 'img' => null, 'alt' => 'ATMABISWAS Daycare',
                 'title' => 'ATMABISWAS Daycare', 'desc' => ''],
            ],
        ],
        [
            'icon'  => 'fa-solid fa-book-open',
            'title' => 'ATMABISWAS Pathshala',
            'cards' => $logoCards('pathshala', 3, 'ATMABISWAS Pathshala', 'ATMABISWAS Pathshala'),
        ],
        [
            'icon'  => 'fa-solid fa-heart-pulse',
            'title' => 'Compassionate Care & Support',
            'cards' => $logoCards('care', 5, 'Compassionate Care & Support', 'Compassionate Care'),
        ],
        [
            'icon'  => 'fa-solid fa-circle-plus',
            'title' => 'Other Program',
            'cards' => $logoCards('other', 5, 'Supporting Disabilities', 'Supporting Disabilities'),
        ],
    ];
}

/** Every card by key, with its segment title -- the whitelist for admin actions. */
function social_card_index(): array
{
    $index = [];
    foreach (social_cards() as $segment) {
        foreach ($segment['cards'] as $card) {
            $index[$card['key']] = $card + ['segment' => $segment['title']];
        }
    }
    return $index;
}

/** Create the tables holding each card's image and text, where this install lacks them. */
function social_ensure_table(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS social_card_images (
            card_key   VARCHAR(64)  NOT NULL PRIMARY KEY,
            img_path   VARCHAR(255) NULL,
            updated_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS social_card_texts (
            card_key    VARCHAR(64)  NOT NULL PRIMARY KEY,
            title       VARCHAR(255) NOT NULL,
            description TEXT         NOT NULL,
            updated_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

/**
 * $pdo, or else the site's own connection -- tried once per request, so the
 * public page makes one attempt however many lookups it does. Null without
 * a database.
 */
function social_pdo(?PDO $pdo = null): ?PDO
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
 * Never throws. With no database, or before an admin has opened the Social
 * images screen and the table exists, every card shows its built-in image,
 * so the public page cannot fail here.
 */
function social_image_overrides(?PDO $pdo = null): array
{
    try {
        $pdo = social_pdo($pdo);
        if (!$pdo) {
            return [];
        }
        return $pdo->query("SELECT card_key, img_path FROM social_card_images")
                   ->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * Saved text as card_key => ['title' => ..., 'desc' => ...].
 *
 * Never throws, for the same reason as social_image_overrides(): without a
 * database or the table, every card keeps its built-in text.
 */
function social_text_overrides(?PDO $pdo = null): array
{
    try {
        $pdo = social_pdo($pdo);
        if (!$pdo) {
            return [];
        }
        $texts = [];
        foreach ($pdo->query("SELECT card_key, title, description FROM social_card_texts") as $row) {
            $texts[$row['card_key']] = ['title' => (string) $row['title'], 'desc' => (string) $row['description']];
        }
        return $texts;
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * social_cards() with saved text applied. Each card also gets 'edited' (bool)
 * and its built-in 'orig_title' / 'orig_desc', for the admin screen.
 */
function social_apply_texts(array $segments, array $texts): array
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
        }
        unset($card);
    }
    unset($segment);
    return $segments;
}

/** The logo's URL, versioned by its mtime like the page always did. */
function social_logo_src(): string
{
    $abs = __DIR__ . '/' . SOCIAL_LOGO;
    return SOCIAL_LOGO . (is_file($abs) ? '?v=' . filemtime($abs) : '');
}

/**
 * The image a card shows: ['src', 'logo' => bool, 'source'], where source is
 * 'uploaded', 'original', 'placeholder' (no image of its own), 'removed'
 * (the admin removed it) or 'missing' (its file is gone from disk).
 */
function social_card_image(array $card, array $overrides): array
{
    $logo = static fn(string $source): array => ['src' => social_logo_src(), 'logo' => true, 'source' => $source];

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

/**
 * Delete an image this feature uploaded, once no card refers to it any more.
 *
 * Only paths of the exact shape social uploads are given can match, so the
 * logo, the built-in card photos and every other section's images are out of
 * reach whatever the database holds. Call it after the card's row has been
 * changed, so the reference check no longer counts that card.
 */
function social_delete_upload(PDO $pdo, ?string $path): void
{
    $path = ltrim(trim((string) $path), '/');
    if (!preg_match('~^media/social/SOCIAL_[0-9a-f]{32}\.(jpg|png|webp)$~', $path)) {
        return;
    }
    $st = $pdo->prepare("SELECT COUNT(*) FROM social_card_images WHERE img_path = ?");
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
