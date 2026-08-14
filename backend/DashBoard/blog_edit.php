<?php
require_once __DIR__ . '/../auth.php';

require_login();

require_once '../../config.php';
require_once 'csrf_helper.php';
require_once __DIR__ . '/../blogSanitizer.php';

$blog_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$blog_id) {
    header("Location: blog_manager.php");
    exit();
}

$pdo = null;
try {
    include_once '../Database/db.php';
    $pdo = (new Db())->connect();
} catch (Exception $e) {
    die('Database connection failed.');
}

/* ── Detect available columns ────────────────────────────────────── */
$existing_cols = $pdo->query(
    "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='blogs'"
)->fetchAll(PDO::FETCH_COLUMN);
$has = array_flip($existing_cols);

/* ── Fetch post ──────────────────────────────────────────────────── */
$stmt = $pdo->prepare("SELECT * FROM blogs WHERE blog_id = ?");
$stmt->execute([$blog_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$post) {
    header("Location: blog_manager.php");
    exit();
}

/* ── Handle form submission ──────────────────────────────────────── */
$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!csrf_verify()) {
            throw new Exception('Invalid or expired session token. Please reload the page and try again.');
        }

        $title   = trim($_POST['blog_title']    ?? '');
        $content = sanitize_blog_html($_POST['blog_content']    ?? '');
        $summary = sanitize_blog_html($_POST['summary_content'] ?? '');

        if (empty($title))   throw new Exception('Title is required.');
        if (empty($content)) throw new Exception('Content is required.');
        if (empty($summary)) throw new Exception('Summary is required.');

        /* Build UPDATE dynamically — only set columns that exist */
        $sets   = ['blog_title=?', 'blog_content=?', 'summary=?'];
        $values = [$title, $content, $summary];

        /* ── Thumbnail management ──────────────────────────────── */
        if (isset($has['cover_img'])) {
            if (!empty($_POST['remove_thumbnail'])) {
                if (!empty($post['cover_img'])) {
                    $old_file = __DIR__ . '/../../' . ltrim($post['cover_img'], '/');
                    if (file_exists($old_file)) @unlink($old_file);
                }
                $sets[]   = 'cover_img=?';
                $values[] = null;
                if (isset($has['image_title'])) {
                    $sets[]   = 'image_title=?';
                    $values[] = null;
                }
            } elseif (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                $thumb_file = $_FILES['thumbnail'];
                $finfo      = new finfo(FILEINFO_MIME_TYPE);
                $mime       = $finfo->file($thumb_file['tmp_name']);
                // Maps a validated MIME type to the extension we save with —
                // never taken from the attacker-supplied filename.
                $allowedThumbTypes = ['image/jpeg' => 'jpg', 'image/jpg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
                if (!array_key_exists($mime, $allowedThumbTypes)) {
                    throw new Exception('Thumbnail must be a JPG, PNG, or WebP image.');
                }
                if ($thumb_file['size'] > 3 * 1024 * 1024) {
                    throw new Exception('Thumbnail must be under 3MB.');
                }
                $uploadDir = __DIR__ . '/../../uploads/blog_imgs/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $ext      = $allowedThumbTypes[$mime];
                $filename = 'PRESS_' . bin2hex(random_bytes(16)) . '.' . $ext;
                if (!move_uploaded_file($thumb_file['tmp_name'], $uploadDir . $filename)) {
                    throw new Exception('Failed to save thumbnail. Check upload directory permissions.');
                }
                if (!empty($post['cover_img'])) {
                    $old_file = __DIR__ . '/../../' . ltrim($post['cover_img'], '/');
                    if (file_exists($old_file)) @unlink($old_file);
                }
                $sets[]   = 'cover_img=?';
                $values[] = 'uploads/blog_imgs/' . $filename;
            }
        }

        if (isset($has['status'])) {
            $currentStatus   = $post['status'] ?? 'published';
            $submittedStatus = $_POST['status'] ?? 'published';
            // Switching a post to draft unpublishes it, so a plain admin keeps
            // whatever status the post already had.
            if ($submittedStatus !== $currentStatus && !is_super_admin()) {
                $submittedStatus = $currentStatus;
            }
            $sets[]   = 'status=?';
            $values[] = $submittedStatus;
        }
        if (isset($has['category'])) {
            $sets[]   = 'category=?';
            $values[] = $_POST['category'] ?? 'news';
        }
        if (isset($has['source_link'])) {
            $sets[]   = 'source_link=?';
            $values[] = trim($_POST['source_link'] ?? '');
        }
        if (isset($has['tags'])) {
            $sets[]   = 'tags=?';
            $values[] = trim($_POST['tags'] ?? '');
        }
        if (isset($has['featured'])) {
            $sets[]   = 'featured=?';
            $values[] = isset($_POST['featured']) ? 1 : 0;
        }
        if (isset($has['seo_title'])) {
            $sets[]   = 'seo_title=?';
            $values[] = trim($_POST['seo_title'] ?? '');
        }
        if (isset($has['seo_description'])) {
            $sets[]   = 'seo_description=?';
            $values[] = trim($_POST['seo_description'] ?? '');
        }
        if (isset($has['focus_keyword'])) {
            $sets[]   = 'focus_keyword=?';
            $values[] = trim($_POST['focus_keyword'] ?? '');
        }
        if (isset($has['canonical_url'])) {
            $sets[]   = 'canonical_url=?';
            $values[] = trim($_POST['canonical_url'] ?? '');
        }
        if (isset($has['reading_time'])) {
            $sets[]   = 'reading_time=?';
            $values[] = max(1, (int)ceil(str_word_count(strip_tags($content)) / 200));
        }
        /* last_updated auto-updates via ON UPDATE CURRENT_TIMESTAMP — no need to set it */

        $values[] = $blog_id;
        $sql = "UPDATE blogs SET " . implode(', ', $sets) . " WHERE blog_id=?";
        $pdo->prepare($sql)->execute($values);

        $success = 'Press post updated successfully.';

        /* Refresh */
        $stmt = $pdo->prepare("SELECT * FROM blogs WHERE blog_id = ?");
        $stmt->execute([$blog_id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$cat_options = [
    'news'         => 'News',
    'media'        => 'Media',
    'announcement' => 'Announcement',
    'press'        => 'Press Release',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Press Post — ATMABISWAS Admin</title>
<link rel="icon" type="image/png" href="../images/logo/logo.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="assets/tinymce/tinymce.min.js" referrerpolicy="origin"></script>
<style>
:root { --pri:#0073e6; --dark:#1e3a5f; }
body { background:#f5f7fa; font-family:system-ui,-apple-system,'Segoe UI',sans-serif; }
.am-header { background:linear-gradient(135deg,var(--dark) 0%,var(--pri) 100%);
             color:#fff; padding:1.25rem 0; margin-bottom:1.75rem; }
.am-header h1 { font-size:1.35rem; font-weight:800; margin:0; }
.panel { background:#fff; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,.07); padding:1.5rem; margin-bottom:1.25rem; }
.panel-title { font-size:.75rem; font-weight:800; text-transform:uppercase; letter-spacing:.07em; color:#94a3b8; margin-bottom:1rem; }
.form-label { font-size:.82rem; font-weight:700; color:#374151; margin-bottom:.35rem; }
.editor-area {
    min-height:220px; border:1.5px solid #e2e8f0; border-radius:8px;
    padding:1rem; background:#fff; font-size:.92rem; line-height:1.7;
}
.editor-area:focus { outline:none; border-color:var(--pri); box-shadow:0 0 0 3px rgba(0,115,230,.1); }
.toolbar { background:#f8fafc; border:1.5px solid #e2e8f0; border-radius:8px; padding:.6rem .75rem; margin-bottom:.5rem; }
.toolbar .btn { padding:.2rem .45rem; font-size:.8rem; }
.char-counter { font-size:.72rem; color:#94a3b8; text-align:right; margin-top:.25rem; }
</style>
</head>
<body>

<div class="am-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h1><i class="fas fa-edit me-2"></i>Edit Press Post</h1>
            <div class="d-flex gap-2">
                <a href="../../press.php?id=<?= $blog_id ?>" target="_blank" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-eye"></i> Preview
                </a>
                <a href="blog_manager.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible rounded-3 fade show">
        <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible rounded-3 fade show">
        <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <form method="POST" id="editForm" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row g-3">

        <!-- Left column: content -->
        <div class="col-lg-8">

            <!-- Title -->
            <div class="panel">
                <div class="panel-title">Press Title</div>
                <input type="text" class="form-control" name="blog_title"
                       value="<?= htmlspecialchars($post['blog_title']) ?>"
                       placeholder="Press post title…" required maxlength="255">
            </div>

            <!-- Summary -->
            <div class="panel">
                <div class="panel-title">Summary <span class="text-danger">*</span></div>
                <textarea id="summaryEditor" name="summary_content"><?= $post['summary'] ?? '' ?></textarea>
            </div>

            <!-- Content -->
            <div class="panel">
                <div class="panel-title">Press Content <span class="text-danger">*</span></div>
                <textarea id="contentEditor" name="blog_content"><?= $post['blog_content'] ?? '' ?></textarea>
            </div>

        </div>

        <!-- Right column: meta -->
        <div class="col-lg-4">

            <!-- Publish -->
            <div class="panel">
                <div class="panel-title">Publish Settings</div>

                <?php if (isset($has['status'])): ?>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-select form-select-sm" name="status"
                        <?= is_super_admin() ? '' : 'disabled title="Only a super admin can publish or unpublish"' ?>>
                        <option value="published" <?= ($post['status'] ?? 'published') === 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="draft"     <?= ($post['status'] ?? '') === 'draft'     ? 'selected' : '' ?>>Draft</option>
                    </select>
                    <?php if (!is_super_admin()): ?>
                        <small class="text-muted">Super admin permission required to change this.</small>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (isset($has['category'])): ?>
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select class="form-select form-select-sm" name="category">
                        <?php foreach ($cat_options as $k => $v): ?>
                        <option value="<?= $k ?>" <?= ($post['category'] ?? 'news') === $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <?php if (isset($has['featured'])): ?>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch"
                           id="featuredCheck" name="featured" value="1"
                           <?= !empty($post['featured']) ? 'checked' : '' ?>>
                    <label class="form-check-label form-label mb-0" for="featuredCheck">
                        Feature Press Post
                    </label>
                </div>
                <?php endif; ?>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="blog_manager.php" class="btn btn-outline-secondary btn-sm">Cancel</a>
                </div>
            </div>

            <!-- Post Info -->
            <div class="panel">
                <div class="panel-title">Press Info</div>
                <div class="small text-muted">
                    <div class="mb-1"><span class="fw-bold">Author:</span> <?= htmlspecialchars($post['blog_author'] ?? '—') ?></div>
                    <div class="mb-1"><span class="fw-bold">Created:</span>
                        <?= !empty($post['upload_date']) ? date('M j, Y g:i A', strtotime($post['upload_date'])) : '—' ?>
                    </div>
                    <?php if (!empty($post['last_updated'])): ?>
                    <div class="mb-1"><span class="fw-bold">Updated:</span>
                        <?= date('M j, Y g:i A', strtotime($post['last_updated'])) ?>
                    </div>
                    <?php endif; ?>
                    <?php if (isset($has['views'])): ?>
                    <div class="mb-1"><span class="fw-bold">Views:</span> <?= number_format((int)($post['views'] ?? 0)) ?></div>
                    <?php endif; ?>
                    <div><span class="fw-bold">ID:</span> #<?= $blog_id ?></div>
                </div>
            </div>

            <!-- Thumbnail -->
            <?php if (isset($has['cover_img'])): ?>
            <div class="panel">
                <div class="panel-title">Press Thumbnail</div>
                <?php if (!empty($post['cover_img'])): ?>
                <div class="mb-2" id="currentThumbWrap">
                    <img src="<?= htmlspecialchars('../../' . $post['cover_img']) ?>"
                         alt="Current thumbnail"
                         style="width:100%;border-radius:8px;display:block;margin-bottom:.5rem;box-shadow:0 1px 4px rgba(0,0,0,.12);">
                    <label style="display:flex;align-items:center;gap:.4rem;font-size:.8rem;color:#6b7280;cursor:pointer;">
                        <input type="checkbox" name="remove_thumbnail" value="1" id="removeThumb"
                               onchange="document.getElementById('currentThumbWrap').style.opacity=this.checked?'.35':'1'">
                        Remove current thumbnail
                    </label>
                </div>
                <div class="form-label" style="font-size:.78rem;color:#64748b;margin-bottom:.25rem;">Replace with new image:</div>
                <?php else: ?>
                <div class="text-muted small mb-2">No thumbnail uploaded yet.</div>
                <div class="form-label" style="font-size:.78rem;color:#64748b;margin-bottom:.25rem;">Upload thumbnail:</div>
                <?php endif; ?>
                <div id="editThumbZone" style="border:1.5px dashed #cbd5e1;border-radius:8px;padding:1rem;text-align:center;cursor:pointer;font-size:.8rem;color:#94a3b8;"
                     onclick="document.getElementById('editThumbInput').click()">
                    <i class="fas fa-upload"></i> Click to select image (JPG, PNG, WebP · max 3 MB)
                </div>
                <input type="file" id="editThumbInput" name="thumbnail" accept="image/jpeg,image/png,image/webp" style="display:none;"
                       onchange="previewEditThumb(this)">
                <div id="editThumbPreview" style="display:none;margin-top:.6rem;">
                    <img id="editThumbImg" src="" style="width:100%;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.1);">
                    <button type="button" onclick="clearEditThumb()" style="margin-top:.35rem;font-size:.75rem;background:none;border:none;color:#dc3545;cursor:pointer;padding:0;">
                        <i class="fas fa-times"></i> Remove selection
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <!-- YouTube / Source link -->
            <?php if (isset($has['source_link'])): ?>
            <div class="panel">
                <div class="panel-title">YouTube / Source Link</div>
                <input type="url" class="form-control form-control-sm" name="source_link"
                       placeholder="https://youtube.com/watch?v=…"
                       value="<?= htmlspecialchars($post['source_link'] ?? '') ?>">
                <div class="char-counter">Paste YouTube URL to embed video on press page. Clear field to remove.</div>
            </div>
            <?php endif; ?>

            <!-- Tags -->
            <?php if (isset($has['tags'])): ?>
            <div class="panel">
                <div class="panel-title">Tags</div>
                <input type="text" class="form-control form-control-sm" name="tags"
                       placeholder="microfinance, rural, health"
                       value="<?= htmlspecialchars($post['tags'] ?? '') ?>">
                <div class="char-counter">Comma-separated keywords</div>
            </div>
            <?php endif; ?>

            <!-- SEO -->
            <?php if (isset($has['seo_title'])): ?>
            <div class="panel">
                <div class="panel-title">SEO</div>
                <div class="mb-2">
                    <label class="form-label">SEO Title</label>
                    <input type="text" class="form-control form-control-sm" name="seo_title"
                           maxlength="60" id="seoTitle"
                           value="<?= htmlspecialchars($post['seo_title'] ?? '') ?>"
                           oninput="document.getElementById('seoTitleCnt').textContent=this.value.length+'/60'">
                    <div class="char-counter"><span id="seoTitleCnt"><?= strlen($post['seo_title'] ?? '') ?>/60</span></div>
                </div>
                <div class="mb-2">
                    <label class="form-label">Meta Description</label>
                    <textarea class="form-control form-control-sm" name="seo_description"
                              maxlength="160" rows="3" id="seoDesc"
                              oninput="document.getElementById('seoDescCnt').textContent=this.value.length+'/160'"><?= htmlspecialchars($post['seo_description'] ?? '') ?></textarea>
                    <div class="char-counter"><span id="seoDescCnt"><?= strlen($post['seo_description'] ?? '') ?>/160</span></div>
                </div>
                <?php if (isset($has['focus_keyword'])): ?>
                <div class="mb-2">
                    <label class="form-label">Focus Keyword</label>
                    <input type="text" class="form-control form-control-sm" name="focus_keyword"
                           value="<?= htmlspecialchars($post['focus_keyword'] ?? '') ?>"
                           placeholder="e.g. rural microfinance Bangladesh">
                </div>
                <?php endif; ?>
                <?php if (isset($has['canonical_url'])): ?>
                <div>
                    <label class="form-label">Canonical URL</label>
                    <input type="url" class="form-control form-control-sm" name="canonical_url"
                           value="<?= htmlspecialchars($post['canonical_url'] ?? '') ?>"
                           placeholder="https://atmabiswas.org/press.php?id=…">
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </div>
    </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    const CSRF_TOKEN = document.querySelector('input[name="csrf_token"]').value;

    tinymce.PluginManager.add('atmachecklist', function (editor) {
        editor.ui.registry.addButton('checklist', {
            icon: 'checklist',
            tooltip: 'Insert Checklist',
            onAction: function () {
                editor.insertContent('<ul class="task-list"><li class="task-item">Task item</li></ul><p></p>');
            }
        });
        editor.on('click', function (e) {
            const li = e.target.closest && e.target.closest('.task-item');
            if (li && editor.getBody().contains(li)) li.classList.toggle('checked');
        });
    });

    function uploadHandler(blobInfo) {
        return new Promise(function (resolve, reject) {
            const fd = new FormData();
            fd.append('file', blobInfo.blob(), blobInfo.filename());
            fd.append('csrf_token', CSRF_TOKEN);
            fetch('../blogContentImage_upload.php', { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(r => r.json())
                .then(json => {
                    if (!json || !json.location) { reject(json && json.error ? json.error : 'Upload failed'); return; }
                    resolve(json.location);
                })
                .catch(() => reject('Image upload failed. Please try again.'));
        });
    }

    tinymce.init({
        selector: '#contentEditor',
        // Self-hosted, open-source (GPL) use — required since TinyMCE 6+
        // or the editor disables itself with a license-key warning.
        // See: https://www.tiny.cloud/docs/tinymce/latest/license-key/
        license_key: 'gpl',
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace ' +
                 'visualblocks code fullscreen insertdatetime media table help wordcount ' +
                 'codesample directionality emoticons nonbreaking atmachecklist',
        toolbar: 'undo redo | blocks fontfamily fontsizeinput | ' +
                 'bold italic underline strikethrough superscript subscript | forecolor backcolor removeformat | ' +
                 'alignleft aligncenter alignright alignjustify | indent outdent | ' +
                 'bullist numlist checklist | link unlink image media table | ' +
                 'blockquote hr codesample | charmap emoticons | searchreplace | code fullscreen help',
        toolbar_sticky: true,
        menubar: false,
        height: 420,
        paste_data_images: true,
        automatic_uploads: true,
        images_upload_handler: uploadHandler,
        images_upload_credentials: true,
        image_advtab: true,
        default_link_target: '_blank',
        link_assume_external_targets: true,
        content_style: 'body{font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;font-size:15px;line-height:1.7;} img{max-width:100%;height:auto;} table{border-collapse:collapse;} td,th{border:1px solid #e2e8f0;padding:6px;} .task-list{list-style:none;padding-left:.5rem;} .task-item::before{content:"\2610  ";} .task-item.checked::before{content:"\2611  ";color:#16a34a;}',
        branding: false,
        promotion: false
    });

    tinymce.init({
        selector: '#summaryEditor',
        license_key: 'gpl',
        plugins: 'link lists autolink wordcount',
        toolbar: 'bold italic underline | bullist numlist | link | removeformat',
        menubar: false,
        height: 160,
        branding: false,
        promotion: false
    });
})();

document.getElementById('editForm').addEventListener('submit', () => {
    tinymce.triggerSave();
});

function previewEditThumb(input) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('editThumbImg').src = e.target.result;
        document.getElementById('editThumbPreview').style.display = 'block';
        document.getElementById('editThumbZone').style.borderColor = '#198754';
    };
    reader.readAsDataURL(file);
}

function clearEditThumb() {
    document.getElementById('editThumbInput').value = '';
    document.getElementById('editThumbPreview').style.display = 'none';
    document.getElementById('editThumbZone').style.borderColor = '#cbd5e1';
}
</script>
</body>
</html>
