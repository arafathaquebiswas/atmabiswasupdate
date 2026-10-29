<?php
require_once __DIR__ . '/storage.php';
require_once __DIR__ . '/brand_icons.php';
// Latest News image grid — included by index.php
require_once 'backend/Database/db.php';

$latest = [];
// Cards visible before "See More": three rows of four on desktop.
$ln_initial = 12;

try {
    $db   = new Db();
    $conn = $db->connect();
} catch (Exception $e) {
    return;
}

// Auto-add display_order column — isolated so a permission failure doesn't kill the query
$has_order_col = false;
try {
    $chk = $conn->query(
        "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME   = 'img_upload'
           AND COLUMN_NAME  = 'display_order'"
    );
    $has_order_col = (int)$chk->fetchColumn() > 0;
    if (!$has_order_col) {
        $conn->exec("ALTER TABLE img_upload ADD COLUMN display_order INT NOT NULL DEFAULT 0");
        $has_order_col = true;
    }
} catch (Exception $e) { /* non-fatal — column may not exist, query will skip it */ }

// This table has drifted between installs: some carry img_title and
// img_description, some only img_name. Alias whatever exists to the names the
// markup below expects, so a missing column cannot blank out the whole section.
$img_cols = [];
try {
    $img_cols = array_flip($conn->query("SHOW COLUMNS FROM img_upload")->fetchAll(PDO::FETCH_COLUMN));
} catch (Exception $e) { /* fall through to the safe defaults below */ }

$title_expr = isset($img_cols['img_title'])       ? 'img_title'
            : (isset($img_cols['img_name'])       ? 'img_name' : "''");
$desc_expr  = isset($img_cols['img_description']) ? 'img_description' : "''";
$order_expr = $has_order_col                      ? 'display_order'   : '0';
$newest     = isset($img_cols['img_id'])          ? 'img_id DESC'     : 'img_path ASC';

$fb_expr    = isset($img_cols['fb_link'])         ? 'fb_link'         : "''";
$press_expr = isset($img_cols['press_link'])      ? 'press_link'      : "''";

$sel = "{$title_expr} AS img_title, {$desc_expr} AS img_description, img_path, {$order_expr} AS display_order, {$fb_expr} AS fb_link, {$press_expr} AS press_link";
// Numbered images first, in the admin's order, then the rest newest first.
// This was "display_order ASC, img_path ASC": unnumbered rows are 0, so every
// image the admin gave a number sorted after all of them and fell past the
// LIMIT -- numbering an image hid it. And img_path is a random hex filename,
// so a fresh upload landed in a random slot and often missed the cut too.
$ord = $has_order_col ? "(display_order = 0) ASC, display_order ASC, {$newest}" : $newest;

if (!function_exists('check_latest_img_exists')) {
    function check_latest_img_exists($path) {
        if (empty($path)) return false;
        if (preg_match('~^https?://~i', $path)) return true;
        $clean = ltrim($path, '/');
        return file_exists(__DIR__ . '/' . $clean) || file_exists($clean);
    }
}

try {
    $stmt = $conn->prepare(
        "SELECT {$sel} FROM img_upload
         WHERE img_type = 'latest_news'
         ORDER BY {$ord}"
    );
    $stmt->execute();
    $latest = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Resolve each path under uploads/ or media/, then drop rows whose file is
    // genuinely absent. Replaces the old existence check, which only looked
    // where the row said and so hid anything that had moved between the two.
    foreach ($latest as $_i => $_img) {
        $latest[$_i]['img_path'] = media_resolve($_img['img_path'] ?? '');
    }
    $latest = array_values(array_filter($latest, fn($img) => $img['img_path'] !== ''));

    // Deliberately no fallback to img_slider rows here. This section and the
    // homepage slider (imageSlider.php) are separate sections: img_slider
    // belongs to the slider, latest_news belongs here, and the admin's choice
    // decides which. This block used to run
    //     UPDATE img_upload SET img_type='latest_news' WHERE img_type='img_slider'
    // whenever this section was empty, which rewrote every slider image into a
    // Latest image on page load and left the slider permanently empty.
} catch (Exception $e) {
    // Log rather than swallow: an empty section and a broken query looked
    // identical before, which hid a missing-column error indefinitely.
    error_log('Latest images query failed: ' . $e->getMessage());
    $latest = [];
}

if (empty($latest)) return;

// A card's Press button appears only while its article is published: press.php
// shows nothing for a draft or a deleted article, so the button would lead to
// the newsroom front page instead of the story. One query covers every card.
$ln_live_press = [];
$ln_press_ids  = [];
foreach ($latest as $img) {
    if (preg_match('/^press\.php\?id=([1-9][0-9]{0,9})$/', (string) $img['press_link'], $m)) {
        $ln_press_ids[(int) $m[1]] = true;
    }
}
if ($ln_press_ids) {
    try {
        $ids = array_keys($ln_press_ids);
        $chk = $conn->prepare(
            "SELECT blog_id FROM blogs
              WHERE blog_id IN (" . implode(',', array_fill(0, count($ids), '?')) . ")
                AND (status = 'published' OR status IS NULL)"
        );
        $chk->execute($ids);
        $ln_live_press = array_flip(array_map('intval', $chk->fetchAll(PDO::FETCH_COLUMN)));
    } catch (Exception $e) {
        // Unverifiable links stay hidden rather than risk a dead button.
        error_log('Latest Press link check failed: ' . $e->getMessage());
    }
}
?>
<section class="ln-section">
    <div class="ln-grid" id="lnGrid">
        <?php foreach ($latest as $i => $img):
            $descId  = 'lndesc-' . $i;
            $btnId   = 'lnbtn-'  . $i;
            $hasDesc = !empty(trim($img['img_description']));
            // Validated when saved; re-checked so only an http(s) URL reaches the href.
            $fbLink  = trim((string) $img['fb_link']);
            if (!preg_match('~^https?://~i', $fbLink)) $fbLink = '';
            // Stored as "press.php?id=N"; shown only while that article is published.
            $pressHref = '';
            if (preg_match('/^press\.php\?id=([1-9][0-9]{0,9})$/', (string) $img['press_link'], $m)
                && isset($ln_live_press[(int) $m[1]])) {
                $pressHref = 'press.php?id=' . (int) $m[1];
            }
            $titleSr = htmlspecialchars($img['img_title']);
        ?>
        <div class="ln-card<?= $i >= $ln_initial ? ' ln-extra' : '' ?>">
            <div class="ln-card-img-wrap">
                <img class="ln-card-img"
                     src="<?= htmlspecialchars((preg_match('~^https?://~i', $img['img_path']) ? '' : '/') . ltrim($img['img_path'], '/')) ?>"
                     alt="<?= htmlspecialchars($img['img_title']) ?>"
                     loading="lazy">
                <div class="ln-card-accent"></div>
                <?php if ($fbLink !== '' || $pressHref !== ''): ?>
                <?php // Over the picture: revealed on hover or keyboard focus,
                      // always visible where there is no hover (phones, tablets). ?>
                <div class="ln-card-links">
                    <?php if ($fbLink !== ''): ?>
                    <a class="ln-link ln-link-fb" href="<?= htmlspecialchars($fbLink) ?>"
                       target="_blank" rel="noopener noreferrer" onclick="event.stopPropagation()">
                        <?= brand_icon('facebook-f') ?>
                        <span data-en="Facebook" data-bn="ফেসবুক">Facebook</span><span class="ln-sr">: <?= $titleSr ?> (opens in a new tab)</span>
                    </a>
                    <?php endif; ?>
                    <?php if ($pressHref !== ''): ?>
                    <a class="ln-link ln-link-press" href="<?= htmlspecialchars($pressHref) ?>" onclick="event.stopPropagation()">
                        <i class="fas fa-newspaper" aria-hidden="true"></i>
                        <span data-en="Press" data-bn="প্রেস">Press</span><span class="ln-sr">: <?= $titleSr ?></span>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="ln-card-body">
                <h3 class="ln-card-title"><?= htmlspecialchars($img['img_title']) ?></h3>
                <?php if ($hasDesc): ?>
                <div class="ln-desc-wrap" id="<?= $descId ?>">
                    <p class="ln-card-desc"><?= htmlspecialchars($img['img_description']) ?></p>
                </div>
                <button class="ln-read-more" id="<?= $btnId ?>"
                        onclick="lnToggle('<?= $descId ?>','<?= $btnId ?>')">
                    <span class="ln-read-more-label" data-en="Read More" data-bn="আরও পড়ুন">Read More</span> <i class="fas fa-chevron-down"></i>
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php if (count($latest) > $ln_initial): ?>
    <div class="ln-more-wrap">
        <button type="button" class="ln-more-btn" id="lnMoreBtn"
                aria-controls="lnGrid" aria-expanded="false" onclick="lnToggleAll()">
            <span class="ln-more-label" data-en="See More" data-bn="আরও দেখুন">See More</span> <i class="fas fa-chevron-down"></i>
        </button>
    </div>
    <?php endif; ?>
</section>

<script>
function lnToggle(descId, btnId) {
    var wrap = document.getElementById(descId);
    var btn  = document.getElementById(btnId);
    var open = wrap.classList.toggle('expanded');
    btn.classList.toggle('expanded', open);
    btn.querySelector('i').style.transform = open ? 'rotate(180deg)' : 'rotate(0deg)';
    // Update label, keep icon
    var label = btn.querySelector('.ln-read-more-label');
    var lang  = window.atmaLang ? window.atmaLang.get() : 'en';
    label.setAttribute('data-en', open ? 'Read Less' : 'Read More');
    label.setAttribute('data-bn', open ? 'কম পড়ুন' : 'আরও পড়ুন');
    label.textContent = lang === 'bn'
        ? label.getAttribute('data-bn')
        : label.getAttribute('data-en');
}

// See More / See Less: reveal every Latest card, or fold back to the first 12.
function lnToggleAll() {
    var grid  = document.getElementById('lnGrid');
    var btn   = document.getElementById('lnMoreBtn');
    var open  = grid.classList.toggle('ln-show-all');
    var label = btn.querySelector('.ln-more-label');
    var lang  = window.atmaLang ? window.atmaLang.get() : 'en';
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    label.setAttribute('data-en', open ? 'See Less' : 'See More');
    label.setAttribute('data-bn', open ? 'কম দেখুন' : 'আরও দেখুন');
    label.textContent = lang === 'bn'
        ? label.getAttribute('data-bn')
        : label.getAttribute('data-en');
    // Folding removes everything below the twelfth card; bring the button
    // back into view so the visitor is not left stranded at the footer.
    if (!open) btn.scrollIntoView({ block: 'center' });
}
</script>

