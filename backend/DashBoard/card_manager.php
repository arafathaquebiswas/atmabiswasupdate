<?php
/**
 * Page images: every card on a public card page (Social, Events) with the
 * image and text it shows right now, and controls to upload, replace, remove
 * or restore that image and to edit or restore its title and description.
 *
 * Opened through social_images.php or events_images.php, which set $cardPage.
 * The cards and their order live in social_cards.php / events_cards.php. The
 * changes themselves are made by Actions/card_image.php and
 * Actions/card_text.php, which redirect back with a message.
 */
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/csrf_helper.php';
require_once __DIR__ . '/../../page_cards.php';

require_login();

// Opened directly rather than through a page's own screen.
if (!isset($cardPage) || !isset(card_pages()[$cardPage])) {
    header('Location: social_images.php');
    exit();
}
$page     = card_page($cardPage);
$pageName = $page['name'];

$dbError   = '';
$overrides = [];
$texts     = [];
try {
    $conn = (new Db())->connect();
    cards_ensure_tables($conn, $cardPage);
    $overrides = cards_image_overrides($cardPage, $conn);
    $texts     = cards_text_overrides($cardPage, $conn);
} catch (Throwable $e) {
    error_log('card_manager: database unavailable: ' . $e->getMessage());
    $dbError = 'Could not reach the database, so cards cannot be changed right now. '
             . 'The cards below show their built-in images and text.';
}
$segments = cards_apply_texts(cards_segments($cardPage), $texts);

$flash = $_SESSION['cards_flash'] ?? null;
unset($_SESSION['cards_flash']);

$canRemove = is_super_admin();
$badges    = [
    'uploaded'    => ['Uploaded image',             'ok'],
    'original'    => ['Original image',             'ok'],
    'placeholder' => ['Logo — no image yet',        'muted'],
    'removed'     => ['Logo — image removed',       'muted'],
    'missing'     => ['Logo — image file missing',  'warn'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageName) ?> Page Images - ATMABISWAS</title>
    <link rel="stylesheet" href="css/uploadfile.css">
    <link rel="stylesheet" href="css/admin-sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="../images/logo/logo.png">
    <style>
        .sc-wrap { max-width: 1180px; margin: 1.5rem auto 3rem; padding: 0 2rem; }
        .sc-head { display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; flex-wrap:wrap; margin-bottom:1.25rem; }
        .sc-head h1 { margin:0 0 .35rem; font-size:1.4rem; color:#0f172a; }
        .sc-sub { color:#64748b; font-size:.88rem; margin:0; line-height:1.55; max-width:720px; }
        .sc-view { color:#2563eb; font-weight:600; font-size:.88rem; text-decoration:none; white-space:nowrap; }
        .sc-view:hover { text-decoration:underline; }
        .sc-msg { padding:.8rem 1rem; border-radius:8px; margin-bottom:1.25rem; font-size:.9rem; }
        .sc-msg.success { background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; }
        .sc-msg.error   { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
        .sc-msg.info    { background:#eff6ff; color:#1e40af; border:1px solid #bfdbfe; }
        .sc-segment { margin-bottom:2rem; }
        .sc-segment h2 { background:linear-gradient(135deg,#1e3a5f 0%,#0073e6 100%); color:#fff; font-size:1.05rem;
                         margin:0; padding:.85rem 1.25rem; border-radius:12px 12px 0 0; display:flex; gap:.6rem; align-items:center; }
        .sc-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(250px, 1fr)); gap:1rem;
                   padding:1.1rem; background:#f8fafc; border:1px solid #e2e8f0; border-top:none; border-radius:0 0 12px 12px; }
        .sc-card { background:#fff; border:1px solid #e2e8f0; border-radius:10px; overflow:hidden; display:flex; flex-direction:column;
                   scroll-margin-top:1.5rem; }
        .sc-card:target { box-shadow:0 0 0 3px #93c5fd; }
        .sc-img { width:100%; height:160px; object-fit:cover; display:block; border-bottom:1px solid #e2e8f0; background:#fff; }
        .sc-img.is-logo { object-fit:contain; padding:1rem; }
        .sc-body { padding:.85rem; display:flex; flex-direction:column; gap:.55rem; flex:1; }
        .sc-badge { align-self:flex-start; font-size:.7rem; font-weight:700; padding:.2rem .55rem; border-radius:999px; }
        .sc-badge.ok    { background:#dcfce7; color:#166534; }
        .sc-badge.muted { background:#f1f5f9; color:#475569; }
        .sc-badge.warn  { background:#fef3c7; color:#92400e; }
        .sc-card h3 { margin:0; font-size:.9rem; color:#0f172a; }
        .sc-desc { margin:0; font-size:.78rem; color:#64748b; line-height:1.45; }
        .sc-key { font-size:.68rem; color:#94a3b8; font-family:ui-monospace,monospace; }
        .sc-form { display:flex; flex-direction:column; gap:.45rem; margin-top:auto; }
        .sc-form input[type=file] { font-size:.78rem; max-width:100%; }
        .sc-btn { border:none; border-radius:7px; padding:.5rem .8rem; font-size:.8rem; font-weight:600; cursor:pointer;
                  font-family:inherit; display:inline-flex; align-items:center; justify-content:center; gap:.4rem; }
        .sc-btn:disabled { opacity:.6; cursor:wait; }
        .sc-btn-primary { background:#2563eb; color:#fff; }
        .sc-btn-primary:hover { background:#1d4ed8; }
        .sc-btn-danger  { background:#fff; color:#b91c1c; border:1px solid #fecaca; }
        .sc-btn-danger:hover { background:#fef2f2; }
        .sc-btn-plain   { background:#fff; color:#334155; border:1px solid #cbd5e1; }
        .sc-btn-plain:hover { background:#f8fafc; }
        .sc-row { display:flex; gap:.45rem; flex-wrap:wrap; }
        .sc-row form { flex:1; }
        .sc-row .sc-btn { width:100%; }
        .sc-badges { display:flex; gap:.35rem; flex-wrap:wrap; }
        .sc-edit { border:1px solid #e2e8f0; border-radius:8px; background:#f8fafc; }
        .sc-edit summary { cursor:pointer; padding:.5rem .7rem; font-size:.8rem; font-weight:600; color:#1d4ed8; list-style:none; }
        .sc-edit summary::-webkit-details-marker { display:none; }
        .sc-edit summary i { margin-right:.35rem; }
        .sc-edit[open] summary { border-bottom:1px solid #e2e8f0; }
        .sc-text-form { display:flex; flex-direction:column; gap:.4rem; padding:.7rem; }
        .sc-text-form label { font-size:.72rem; font-weight:600; color:#334155; }
        .sc-text-form input[type=text], .sc-text-form textarea { width:100%; box-sizing:border-box; font:inherit; font-size:.82rem;
                   padding:.45rem .55rem; border:1px solid #cbd5e1; border-radius:6px; background:#fff; color:#0f172a; }
        .sc-text-form textarea { min-height:90px; resize:vertical; line-height:1.45; }
        .sc-text-form input:focus, .sc-text-form textarea:focus { outline:2px solid #93c5fd; border-color:#2563eb; }
        .sc-hint { font-size:.7rem; color:#94a3b8; margin:0; }
        @media (max-width: 640px) { .sc-wrap { padding:0 1rem; } }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar-container">
            <?php include 'sidebar.php'; ?>
        </div>
        <div class="main-content">
            <div class="sc-wrap">
                <div class="sc-head">
                    <div>
                        <h1><i class="fas fa-images"></i> <?= htmlspecialchars($pageName) ?> Page Images</h1>
                        <p class="sc-sub">
                            Choose the picture, title and description each card on the <?= htmlspecialchars($pageName) ?> page shows. Changes are
                            live as soon as they are saved. A card with no image shows the ATMA BISWAS logo; removing an
                            image never removes the card, its title or its description. JPG, PNG or WebP, up to <?= cards_upload_limit_label() ?>.
                        </p>
                    </div>
                    <a class="sc-view" href="../../<?= htmlspecialchars($page['public']) ?>" target="_blank" rel="noopener">
                        <i class="fas fa-external-link-alt"></i> View <?= htmlspecialchars($pageName) ?> page
                    </a>
                </div>

                <?php if ($flash): ?>
                    <div class="sc-msg <?= htmlspecialchars($flash['type']) ?>" role="status"><?= htmlspecialchars($flash['msg']) ?></div>
                <?php endif; ?>
                <?php if ($dbError): ?>
                    <div class="sc-msg error"><?= htmlspecialchars($dbError) ?></div>
                <?php endif; ?>
                <?php if (!$canRemove): ?>
                    <div class="sc-msg info">You can upload and replace images and edit the text. Removing an image or restoring the original image or text needs a super admin.</div>
                <?php endif; ?>

                <?php foreach ($segments as $segment): ?>
                <section class="sc-segment">
                    <h2><i class="<?= htmlspecialchars($segment['icon']) ?>"></i> <?= htmlspecialchars($segment['title']) ?></h2>
                    <div class="sc-grid">
                        <?php foreach ($segment['cards'] as $card):
                            $image = cards_card_image($card, $overrides);
                            [$badgeText, $badgeClass] = $badges[$image['source']];
                            $hasPhoto = !$image['logo'];
                        ?>
                        <div class="sc-card" id="card-<?= htmlspecialchars($card['key']) ?>">
                            <img class="sc-img<?= $image['logo'] ? ' is-logo' : '' ?>"
                                 src="../../<?= htmlspecialchars($image['src']) ?>"
                                 alt="Current image: <?= htmlspecialchars($card['title']) ?>" loading="lazy"
                                 onerror="this.onerror=null;this.src='../../<?= htmlspecialchars(cards_logo_src()) ?>';this.classList.add('is-logo');">
                            <div class="sc-body">
                                <div class="sc-badges">
                                    <span class="sc-badge <?= $badgeClass ?>"><?= $badgeText ?></span>
                                    <?php if ($card['edited']): ?>
                                    <span class="sc-badge ok">Edited text</span>
                                    <?php endif; ?>
                                </div>
                                <h3><?= htmlspecialchars($card['title']) ?></h3>
                                <?php if ($card['desc'] !== ''): ?>
                                <p class="sc-desc"><?= htmlspecialchars($card['desc']) ?></p>
                                <?php endif; ?>
                                <span class="sc-key"><?= htmlspecialchars($card['key']) ?></span>

                                <?php if (!$dbError): ?>
                                <details class="sc-edit">
                                    <summary><i class="fas fa-pen"></i>Edit title &amp; description</summary>
                                    <form class="sc-text-form" method="POST" action="Actions/card_text.php">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="card_key" value="<?= htmlspecialchars($card['key']) ?>">
                                        <input type="hidden" name="page" value="<?= htmlspecialchars($cardPage) ?>">
                                        <input type="hidden" name="action" value="save">
                                        <label for="title-<?= htmlspecialchars($card['key']) ?>">Title</label>
                                        <input type="text" id="title-<?= htmlspecialchars($card['key']) ?>" name="title"
                                               value="<?= htmlspecialchars($card['title']) ?>" maxlength="<?= CARD_TITLE_MAX ?>" required>
                                        <label for="desc-<?= htmlspecialchars($card['key']) ?>">Description <span class="sc-hint">(optional)</span></label>
                                        <textarea id="desc-<?= htmlspecialchars($card['key']) ?>" name="description"
                                                  maxlength="<?= CARD_DESC_MAX ?>"><?= htmlspecialchars($card['desc']) ?></textarea>
                                        <button type="submit" class="sc-btn sc-btn-primary"><i class="fas fa-save"></i> Save text</button>
                                    </form>
                                    <?php if ($card['edited'] && $canRemove): ?>
                                    <form class="sc-text-form" method="POST" action="Actions/card_text.php"
                                          onsubmit="return confirm('Put back the original title and description for this card?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="card_key" value="<?= htmlspecialchars($card['key']) ?>">
                                        <input type="hidden" name="page" value="<?= htmlspecialchars($cardPage) ?>">
                                        <input type="hidden" name="action" value="restore">
                                        <p class="sc-hint">Original: <?= htmlspecialchars($card['orig_title']) ?></p>
                                        <button type="submit" class="sc-btn sc-btn-plain"><i class="fas fa-undo"></i> Restore original text</button>
                                    </form>
                                    <?php endif; ?>
                                </details>
                                <?php endif; ?>

                                <form class="sc-form" method="POST" action="Actions/card_image.php?page=<?= htmlspecialchars($cardPage) ?>" enctype="multipart/form-data">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="card_key" value="<?= htmlspecialchars($card['key']) ?>">
                                    <input type="hidden" name="page" value="<?= htmlspecialchars($cardPage) ?>">
                                    <input type="hidden" name="action" value="upload">
                                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp" required
                                           data-max="<?= cards_upload_limit() ?>" <?= $dbError ? 'disabled' : '' ?>>
                                    <button type="submit" class="sc-btn sc-btn-primary" <?= $dbError ? 'disabled' : '' ?>>
                                        <i class="fas fa-upload"></i> <?= $hasPhoto ? 'Replace image' : 'Upload image' ?>
                                    </button>
                                </form>

                                <?php if ($canRemove && !$dbError && ($hasPhoto || ($card['img'] && $image['source'] !== 'original'))): ?>
                                <div class="sc-row">
                                    <?php if ($hasPhoto): ?>
                                    <form method="POST" action="Actions/card_image.php?page=<?= htmlspecialchars($cardPage) ?>"
                                          onsubmit="return confirm('Remove this image? The card stays and shows the ATMA BISWAS logo.');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="card_key" value="<?= htmlspecialchars($card['key']) ?>">
                                        <input type="hidden" name="page" value="<?= htmlspecialchars($cardPage) ?>">
                                        <input type="hidden" name="action" value="remove">
                                        <button type="submit" class="sc-btn sc-btn-danger"><i class="fas fa-trash-alt"></i> Remove</button>
                                    </form>
                                    <?php endif; ?>
                                    <?php if ($card['img'] && $image['source'] !== 'original'): ?>
                                    <form method="POST" action="Actions/card_image.php?page=<?= htmlspecialchars($cardPage) ?>"
                                          onsubmit="return confirm('Put back the original image for this card?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="card_key" value="<?= htmlspecialchars($card['key']) ?>">
                                        <input type="hidden" name="page" value="<?= htmlspecialchars($cardPage) ?>">
                                        <input type="hidden" name="action" value="restore">
                                        <button type="submit" class="sc-btn sc-btn-plain"><i class="fas fa-undo"></i> Restore original</button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
    // Catch an oversized file before it is sent; the server checks again.
    document.querySelectorAll('.sc-form input[type=file]').forEach(function (input) {
        input.addEventListener('change', function () {
            var file = input.files[0];
            var max  = parseInt(input.dataset.max, 10);
            input.setCustomValidity(file && file.size > max
                ? 'This image is larger than <?= cards_upload_limit_label() ?>. Please choose a smaller one.'
                : '');
            input.reportValidity();
        });
    });
    // One click, one upload: stop a second submit while the first is sending.
    document.querySelectorAll('.sc-form').forEach(function (form) {
        form.addEventListener('submit', function () {
            var btn = form.querySelector('button');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading…';
        });
    });
    document.querySelectorAll('.sc-text-form').forEach(function (form) {
        form.addEventListener('submit', function () {
            var btn = form.querySelector('button');
            setTimeout(function () { btn.disabled = true; }, 0);
        });
    });
    </script>
</body>
</html>
