<?php
require_once __DIR__ . '/storage.php';
// Latest News image grid — included by index.php
require_once 'backend/Database/db.php';

$latest = [];

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

$sel = "{$title_expr} AS img_title, {$desc_expr} AS img_description, img_path, {$order_expr} AS display_order";
$ord = $has_order_col ? "display_order ASC, img_path ASC" : "img_path ASC";

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
         ORDER BY {$ord} LIMIT 12"
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
?>
<section class="ln-section">
    <div class="ln-grid">
        <?php foreach ($latest as $i => $img):
            $descId  = 'lndesc-' . $i;
            $btnId   = 'lnbtn-'  . $i;
            $hasDesc = !empty(trim($img['img_description']));
        ?>
        <div class="ln-card">
            <div class="ln-card-img-wrap">
                <img class="ln-card-img"
                     src="<?= htmlspecialchars((preg_match('~^https?://~i', $img['img_path']) ? '' : '/') . ltrim($img['img_path'], '/')) ?>"
                     alt="<?= htmlspecialchars($img['img_title']) ?>"
                     loading="lazy">
                <div class="ln-card-accent"></div>
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
</script>

