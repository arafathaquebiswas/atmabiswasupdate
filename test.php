<?php
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

$sel = $has_order_col
    ? "img_title, img_description, img_path, display_order"
    : "img_title, img_description, img_path, 0 AS display_order";
$ord = $has_order_col ? "display_order ASC, img_path ASC" : "img_path ASC";

try {
    $stmt = $conn->prepare(
        "SELECT {$sel} FROM img_upload
         WHERE img_type = 'latest_news'
         ORDER BY {$ord} LIMIT 12"
    );
    $stmt->execute();
    $latest = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $latest = array_values(array_filter($latest, fn($img) => file_exists($img['img_path'])));

    // Auto-migrate: if no latest_news rows exist but img_slider rows do, convert them.
    // (The public homepage slider is hardcoded HTML; img_slider type is unused in the public site.)
    if (empty($latest)) {
        $chk2 = $conn->query("SELECT COUNT(*) FROM img_upload WHERE img_type = 'img_slider'");
        if ((int)$chk2->fetchColumn() > 0) {
            $conn->exec("UPDATE img_upload SET img_type = 'latest_news' WHERE img_type = 'img_slider'");
            $stmt->execute();
            $latest = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $latest = array_values(array_filter($latest, fn($img) => file_exists($img['img_path'])));
        }
    }
} catch (Exception $e) {
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
                     src="<?= htmlspecialchars($img['img_path']) ?>"
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

