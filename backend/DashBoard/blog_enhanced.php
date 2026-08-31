<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../login/loging.php");
    exit();
}

// Include config for paths
require_once '../../config.php';
require_once 'csrf_helper.php';

$cat_options = [
    'news'         => 'News',
    'media'        => 'Media Coverage',
    'announcement' => 'Announcement',
    'press'        => 'Press Release',
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ATMABISWAS — Press Editor</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="icon" type="image/png" href="../images/logo/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="assets/tinymce/tinymce.min.js" referrerpolicy="origin"></script>
    <style>
        :root {
            --pri: #0073e6;
            --pri-dark: #005bb8;
            --dark: #1e293b;
            --muted: #64748b;
            --border: #e2e8f0;
            --bg: #f4f6f9;
            --success: #16a34a;
            --danger: #dc3545;
            --radius: 12px;
        }

        * { box-sizing: border-box; }

        body {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: var(--bg);
            color: var(--dark);
            min-height: 100vh;
        }

        .am-header {
            background: linear-gradient(135deg, var(--dark) 0%, var(--pri) 100%);
            color: #fff;
            padding: 1.5rem 0;
            margin-bottom: 1.75rem;
            box-shadow: 0 4px 20px rgba(0,0,0,.12);
        }

        .am-header h1 {
            font-size: 1.5rem;
            font-weight: 800;
            margin: 0;
        }

        .am-header p { margin: .15rem 0 0; opacity: .85; font-size: .88rem; }

        .panel {
            background: #fff;
            border-radius: var(--radius);
            box-shadow: 0 2px 10px rgba(0,0,0,.06);
            padding: 1.5rem;
            margin-bottom: 1.25rem;
            border: 1px solid #eef1f5;
        }

        .panel-title {
            font-size: .75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: #94a3b8;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .form-label {
            font-size: .84rem;
            font-weight: 700;
            color: #374151;
            margin-bottom: .35rem;
            display: block;
        }

        .form-control, .form-select {
            font-size: .92rem;
            border-radius: 8px;
            border-color: var(--border);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--pri);
            box-shadow: 0 0 0 3px rgba(0,115,230,.12);
        }

        .char-counter { font-size: .74rem; color: #94a3b8; text-align: right; margin-top: .25rem; }
        .char-counter.warn { color: var(--danger); font-weight: 700; }

        .sticky-actions {
            position: sticky;
            bottom: 0;
            background: #fff;
            border-top: 1px solid var(--border);
            padding: 1rem 1.5rem;
            border-radius: var(--radius);
            box-shadow: 0 -4px 16px rgba(0,0,0,.06);
            margin-top: 1.25rem;
            display: flex;
            gap: .75rem;
            justify-content: flex-end;
            flex-wrap: wrap;
            z-index: 10;
        }

        .btn-publish {
            background: var(--success);
            border-color: var(--success);
            color: #fff;
            font-weight: 700;
        }
        .btn-publish:hover { background: #128a3e; border-color: #128a3e; color: #fff; }

        .thumb-drop {
            border: 2px dashed #cbd5e1;
            border-radius: 10px;
            padding: 1.25rem;
            text-align: center;
            cursor: pointer;
            background: #fafbfc;
            transition: border-color .2s, background .2s;
        }
        .thumb-drop:hover { border-color: var(--pri); background: #f0f7ff; }

        .tab-nav {
            display: flex;
            gap: .25rem;
            border-bottom: 2px solid var(--border);
            margin-bottom: 1.5rem;
        }
        .tab-btn {
            background: none;
            border: none;
            padding: .85rem 1.5rem;
            font-weight: 700;
            font-size: .9rem;
            color: var(--muted);
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            cursor: pointer;
        }
        .tab-btn.active { color: var(--pri); border-bottom-color: var(--pri); }

        .preview-toolbar {
            display: flex;
            justify-content: center;
            gap: .5rem;
            margin-bottom: 1.5rem;
        }
        .preview-toolbar button {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: .4rem .9rem;
            font-size: .82rem;
            font-weight: 700;
            color: var(--muted);
            cursor: pointer;
        }
        .preview-toolbar button.active { color: var(--pri); border-color: var(--pri); background: #f0f7ff; }

        .preview-frame {
            background: #fff;
            border-radius: var(--radius);
            box-shadow: 0 4px 20px rgba(0,0,0,.08);
            margin: 0 auto;
            padding: 2.5rem;
            max-width: 900px;
            transition: max-width .25s ease;
        }
        .preview-frame.mobile { max-width: 380px; padding: 1.5rem; }

        .preview-frame h1 { font-size: 1.8rem; font-weight: 800; color: var(--dark); margin-bottom: .5rem; }
        .preview-frame .meta { color: var(--muted); font-size: .85rem; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border); }
        .preview-frame img { max-width: 100%; border-radius: 8px; }
        .preview-frame table { border-collapse: collapse; width: 100%; }
        .preview-frame td, .preview-frame th { border: 1px solid var(--border); padding: .5rem; }
        .preview-frame .task-list { list-style: none; padding-left: 0; }
        .preview-frame .task-item::before { content: "\2610\0020"; }
        .preview-frame .task-item.checked::before { content: "\2611\0020"; color: var(--success); }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            background: var(--success);
            color: white;
            font-weight: 600;
            box-shadow: 0 10px 25px rgba(0,0,0,.2);
            transform: translateX(200%);
            transition: transform .3s ease;
            z-index: 2000;
        }
        .notification.show { transform: translateX(0); }
        .notification.error { background: var(--danger); }
        .notification.warning { background: #d97706; }

        .loading {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(255,255,255,.75);
            z-index: 1900;
            text-align: center;
            padding-top: 20vh;
        }
        .loading.show { display: block; }
        .spinner {
            width: 3rem; height: 3rem;
            border: 3px solid #e2e8f0;
            border-top: 3px solid var(--pri);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .tox-tinymce { border-radius: 8px !important; border-color: var(--border) !important; }
    </style>
</head>

<body>
    <div class="am-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1><i class="fas fa-pen-fancy"></i> Press Editor</h1>
                    <p>Create and publish press posts for ATMABISWAS</p>
                </div>
                <a href="dashboard.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <div class="container pb-5">

        <div class="tab-nav">
            <button type="button" class="tab-btn active" data-tab="editor" onclick="showTab('editor')">
                <i class="fas fa-edit"></i> Editor
            </button>
            <button type="button" class="tab-btn" data-tab="preview" onclick="showTab('preview')">
                <i class="fas fa-eye"></i> Preview
            </button>
        </div>

        <!-- Editor Tab -->
        <div id="editor-tab">
            <form id="blogForm" action="../blogUpload_process.php" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="row g-3">

                    <!-- Main column -->
                    <div class="col-lg-8">

                        <div class="panel">
                            <div class="panel-title"><i class="fas fa-heading"></i> Press Title</div>
                            <input type="text" class="form-control" id="blogTitle" name="blog_title"
                                   placeholder="Enter an engaging title…" required maxlength="255">
                            <div class="char-counter"><span id="titleCount">0</span>/255 characters</div>
                        </div>

                        <div class="panel">
                            <div class="panel-title"><i class="fas fa-file-alt"></i> Press Summary <span class="text-danger">*</span></div>
                            <textarea id="summaryEditor" name="summary_content_raw"></textarea>
                            <div class="char-counter" id="summaryWordCount">Words: 0</div>
                        </div>

                        <div class="panel">
                            <div class="panel-title"><i class="fas fa-newspaper"></i> Press Content <span class="text-danger">*</span></div>
                            <textarea id="contentEditor" name="blog_content_raw"></textarea>
                            <div class="char-counter" id="contentWordCount">Words: 0 · <span id="readingTime">1 min read</span></div>
                        </div>

                        <!-- Hidden inputs actually submitted -->
                        <input type="hidden" id="sanitizedContent" name="blog_content">
                        <input type="hidden" id="sanitizedSummary" name="summary_content">
                        <input type="hidden" id="postStatusAction" name="post_status_action" value="published">

                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4">

                        <div class="panel">
                            <div class="panel-title"><i class="fas fa-cog"></i> Publish Settings</div>
                            <label class="form-label">Category</label>
                            <select class="form-select mb-3" name="category" required>
                                <?php foreach ($cat_options as $k => $v): ?>
                                    <option value="<?= htmlspecialchars($k) ?>" <?= $k === 'news' ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="featuredCheck" name="featured" value="1">
                                <label class="form-check-label form-label mb-0" for="featuredCheck">Feature Press Post</label>
                            </div>
                        </div>

                        <div class="panel">
                            <div class="panel-title"><i class="fas fa-image"></i> Press Thumbnail <span class="text-danger">*</span></div>
                            <div id="thumbDropZone" class="thumb-drop"
                                 onclick="document.getElementById('thumbnailInput').click()"
                                 ondragover="event.preventDefault();this.style.borderColor='#0073e6';this.style.background='#e8f4fd';"
                                 ondragleave="this.style.borderColor='#cbd5e1';this.style.background='#fafbfc';"
                                 ondrop="handleThumbDrop(event)">
                                <i class="fas fa-cloud-upload-alt" style="font-size:1.8rem;color:#adb5bd;margin-bottom:.5rem;display:block;"></i>
                                <div id="thumbDropLabel" style="color:#6c757d;font-size:.88rem;">Click or drag &amp; drop</div>
                                <div style="font-size:.74rem;color:#adb5bd;margin-top:.25rem;">JPG, PNG, WebP — max 3 MB</div>
                            </div>
                            <input type="file" id="thumbnailInput" name="thumbnail" accept="image/jpeg,image/png,image/webp" style="display:none;" onchange="handleThumbSelect(this.files[0])">
                            <div id="thumbPreviewWrap" style="display:none;margin-top:1rem;position:relative;">
                                <img id="thumbPreviewImg" src="" alt="Thumbnail preview" style="max-width:100%;max-height:200px;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.1);">
                                <button type="button" onclick="clearThumb()" style="position:absolute;top:6px;right:6px;background:#dc3545;color:#fff;border:none;border-radius:50%;width:26px;height:26px;cursor:pointer;">&times;</button>
                            </div>
                            <div id="thumbError" style="display:none;color:#dc3545;font-size:.82rem;margin-top:.5rem;"><i class="fas fa-exclamation-circle"></i> Press thumbnail is required.</div>
                        </div>

                        <div class="panel">
                            <div class="panel-title"><i class="fab fa-youtube"></i> YouTube / Source Link</div>
                            <input type="url" class="form-control" name="source_link" placeholder="https://youtube.com/watch?v=…">
                            <div class="char-counter" style="text-align:left;">Optional — embeds above content.</div>
                        </div>

                        <div class="panel">
                            <div class="panel-title"><i class="fas fa-tags"></i> Tags</div>
                            <input type="text" class="form-control" name="tags" placeholder="microfinance, rural, health">
                            <div class="char-counter" style="text-align:left;">Comma-separated.</div>
                        </div>

                        <div class="panel">
                            <div class="panel-title"><i class="fas fa-search-plus"></i> SEO</div>

                            <label class="form-label">URL Slug</label>
                            <input type="text" class="form-control mb-3" id="postSlug" name="slug" placeholder="auto-generated-from-title">

                            <label class="form-label">SEO Title <span id="seoTitleCnt" class="text-muted" style="font-weight:400;">0/60</span></label>
                            <input type="text" class="form-control mb-3" id="seoTitle" name="seo_title" maxlength="60" placeholder="Leave blank = press title">

                            <label class="form-label">Meta Description <span id="seoDescCnt" class="text-muted" style="font-weight:400;">0/160</span></label>
                            <textarea class="form-control mb-3" id="seoDesc" name="seo_description" rows="3" maxlength="160" placeholder="Short summary for search results"></textarea>

                            <label class="form-label">Focus Keyword</label>
                            <input type="text" class="form-control mb-3" name="focus_keyword" placeholder="e.g. rural microfinance Bangladesh">

                            <label class="form-label">SEO Keywords</label>
                            <input type="text" class="form-control mb-3" name="seo_keywords" placeholder="keyword1, keyword2">

                            <label class="form-label">Canonical URL</label>
                            <input type="url" class="form-control mb-3" name="canonical_url" placeholder="https://atmabiswas.org/press.php?id=… (optional)">

                            <label class="form-label">Facebook URL</label>
                            <input type="url" class="form-control mb-3" name="facebook_url" placeholder="https://www.facebook.com/… (optional)">

                            <label class="form-label">Instagram URL</label>
                            <input type="url" class="form-control mb-3" name="instagram_url" placeholder="https://www.instagram.com/p/… (optional)">

                            <label class="form-label">Social Share Image URL</label>
                            <input type="url" class="form-control" name="social_image" placeholder="https://… (optional, falls back to thumbnail)">
                        </div>

                    </div>
                </div>

                <div class="sticky-actions">
                    <button type="submit" name="post_status_action" value="draft" class="btn btn-outline-secondary">
                        <i class="fas fa-save"></i> Save Draft
                    </button>
                    <button type="submit" name="post_status_action" value="published" class="btn btn-publish">
                        <i class="fas fa-paper-plane"></i> Publish Press Post
                    </button>
                </div>
            </form>
        </div>

        <!-- Preview Tab -->
        <div id="preview-tab" style="display:none;">
            <div class="preview-toolbar">
                <button type="button" id="btnDesktopPreview" class="active" onclick="setPreviewMode('desktop')"><i class="fas fa-desktop"></i> Desktop</button>
                <button type="button" id="btnMobilePreview" onclick="setPreviewMode('mobile')"><i class="fas fa-mobile-alt"></i> Mobile</button>
            </div>
            <div class="preview-frame" id="previewFrame">
                <h1 id="previewTitle">Press Title</h1>
                <div class="meta">
                    <i class="fas fa-user"></i> By <?= htmlspecialchars($_SESSION['username'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                    &nbsp;·&nbsp; <i class="fas fa-calendar"></i> <span id="previewDate"><?= date('F j, Y') ?></span>
                </div>
                <div class="mb-4">
                    <h5>Summary</h5>
                    <div id="previewSummary" class="text-muted">Press summary will appear here…</div>
                </div>
                <div id="previewContent">Press content will appear here…</div>
            </div>
        </div>

    </div>

    <div id="loadingOverlay" class="loading">
        <div class="spinner"></div>
        <p>Processing your request…</p>
    </div>
    <div class="notification" id="notification"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    (function () {
        const CSRF_TOKEN = <?= json_encode(csrf_token()) ?>;

        // ── Custom checklist button (community TinyMCE has no native
        // task-list plugin — this inserts the same markup the server
        // sanitizer allows: <ul class="task-list"><li class="task-item">) ──
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
                if (li && editor.getBody().contains(li)) {
                    li.classList.toggle('checked');
                }
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

        const sharedPlugins = 'advlist autolink lists link image charmap preview anchor ' +
            'searchreplace visualblocks code fullscreen insertdatetime media table help ' +
            'wordcount codesample directionality emoticons nonbreaking atmachecklist';

        const contentToolbar =
            'undo redo | blocks fontfamily fontsize | ' +
            'bold italic underline strikethrough superscript subscript | forecolor backcolor removeformat | ' +
            'alignleft aligncenter alignright alignjustify | indent outdent | ' +
            'bullist numlist checklist | link unlink image media table | ' +
            'blockquote hr codesample | charmap emoticons | searchreplace | code fullscreen help';

        // ── Debounce: avoid re-serializing the whole document on every
        // single keystroke (was the cause of the editor feeling like it
        // "buffers"/freezes while typing in long posts) ──
        function debounce(fn, delay) {
            let t;
            return function (...args) {
                clearTimeout(t);
                t = setTimeout(() => fn.apply(this, args), delay);
            };
        }
        const debouncedContentWordCount = debounce(() => updateWordCount('content'), 300);
        const debouncedSummaryWordCount = debounce(() => updateWordCount('summary'), 300);

        window.tinymce.init({
            selector: '#contentEditor',
            // Self-hosted, open-source (GPL) use — required since TinyMCE 6+
            // or the editor disables itself with a license-key warning.
            // See: https://www.tiny.cloud/docs/tinymce/latest/license-key/
            license_key: 'gpl',
            plugins: sharedPlugins,
            toolbar: contentToolbar,
            toolbar_sticky: true,
            menubar: false,
            height: 480,
            paste_data_images: true,
            automatic_uploads: true,
            images_upload_handler: uploadHandler,
            images_upload_credentials: true,
            default_link_target: '_blank',
            link_assume_external_targets: true,
            image_advtab: true,
            // 5 preset text sizes for the Press Content toolbar's "Font size" dropdown
            fontsize_formats: '12px 14px 16px 20px 28px',
            color_cols: 8,
            custom_colors: true,
            content_style: 'body{font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;font-size:15px;line-height:1.7;} img{max-width:100%;height:auto;} table{border-collapse:collapse;} td,th{border:1px solid #e2e8f0;padding:6px;} .task-list{list-style:none;padding-left:.5rem;} .task-item::before{content:"\2610  ";} .task-item.checked::before{content:"\2611  ";color:#16a34a;}',
            branding: false,
            promotion: false,
            setup: function (editor) {
                editor.on('input undo redo SetContent', debouncedContentWordCount);
            }
        });

        window.tinymce.init({
            selector: '#summaryEditor',
            license_key: 'gpl',
            plugins: 'link lists autolink wordcount',
            toolbar: 'bold italic underline | bullist numlist | link | removeformat',
            menubar: false,
            height: 180,
            branding: false,
            promotion: false,
            setup: function (editor) {
                editor.on('input undo redo SetContent', debouncedSummaryWordCount);
            }
        });

        window.addEventListener('DOMContentLoaded', function () {
            setupEventListeners();
        });
    })();

    // ── Tabs ─────────────────────────────────────────────────────────
    function showTab(name) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.toggle('active', b.dataset.tab === name));
        document.getElementById('editor-tab').style.display  = name === 'editor'  ? 'block' : 'none';
        document.getElementById('preview-tab').style.display = name === 'preview' ? 'block' : 'none';
        if (name === 'preview') updatePreview();
    }

    function setPreviewMode(mode) {
        document.getElementById('previewFrame').classList.toggle('mobile', mode === 'mobile');
        document.getElementById('btnDesktopPreview').classList.toggle('active', mode === 'desktop');
        document.getElementById('btnMobilePreview').classList.toggle('active', mode === 'mobile');
    }

    function updatePreview() {
        const title   = document.getElementById('blogTitle').value || 'Press Title';
        const summary = tinymce.get('summaryEditor') ? tinymce.get('summaryEditor').getContent() : '';
        const content = tinymce.get('contentEditor') ? tinymce.get('contentEditor').getContent() : '';
        document.getElementById('previewTitle').textContent = title;
        document.getElementById('previewSummary').innerHTML  = summary || 'Press summary will appear here…';
        document.getElementById('previewContent').innerHTML  = content || 'Press content will appear here…';
    }

    // ── Setup ────────────────────────────────────────────────────────
    function setupEventListeners() {
        document.getElementById('blogTitle').addEventListener('input', function () {
            document.getElementById('titleCount').textContent = this.value.length;
            document.getElementById('previewTitle').textContent = this.value || 'Press Title';
        });

        document.getElementById('blogForm').addEventListener('submit', handleFormSubmit);
        document.getElementById('thumbnailInput').addEventListener('change', function () {});

        // Auto-generate slug from title
        const titleEl = document.getElementById('blogTitle');
        const slugEl  = document.getElementById('postSlug');
        titleEl.addEventListener('input', function () {
            if (slugEl.dataset.manual) return;
            slugEl.value = this.value.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
        });
        slugEl.addEventListener('input', function () { this.dataset.manual = this.value ? '1' : ''; });

        // SEO counters
        const seoTitle = document.getElementById('seoTitle');
        const seoDesc  = document.getElementById('seoDesc');
        seoTitle.addEventListener('input', function () { updateCounter(this, 'seoTitleCnt', 60); });
        seoDesc.addEventListener('input',  function () { updateCounter(this, 'seoDescCnt', 160); });

        setInterval(autoSave, 30000);
        setTimeout(loadAutoSave, 1000);
    }

    function updateCounter(el, counterId, max) {
        const counter = document.getElementById(counterId);
        const len = el.value.length;
        counter.textContent = len + '/' + max;
        counter.classList.toggle('warn', len > max * .9);
    }

    // ── Word count / reading time ───────────────────────────────────
    function updateWordCount(type) {
        const editor = tinymce.get(type === 'summary' ? 'summaryEditor' : 'contentEditor');
        if (!editor) return;
        const text  = editor.getContent({ format: 'text' }).trim();
        const words = text ? text.split(/\s+/).length : 0;

        if (type === 'summary') {
            document.getElementById('summaryWordCount').textContent = 'Words: ' + words;
        } else {
            const minutes = Math.max(1, Math.ceil(words / 200));
            document.getElementById('contentWordCount').textContent = 'Words: ' + words;
            document.getElementById('readingTime').textContent = minutes + ' min read';
        }

        if (document.getElementById('preview-tab').style.display !== 'none') updatePreview();
    }

    // ── Thumbnail helpers ────────────────────────────────────────────
    function handleThumbSelect(file) {
        if (!file) return;
        const allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (!allowed.includes(file.type)) { showNotification('Thumbnail must be JPG, PNG, or WebP.', 'error'); return; }
        if (file.size > 3 * 1024 * 1024) { showNotification('Thumbnail must be under 3 MB.', 'error'); return; }
        const reader = new FileReader();
        reader.onload = function (ev) {
            document.getElementById('thumbPreviewImg').src = ev.target.result;
            document.getElementById('thumbPreviewWrap').style.display = 'block';
            document.getElementById('thumbDropLabel').textContent = file.name;
            document.getElementById('thumbDropZone').style.borderColor = '#198754';
            document.getElementById('thumbDropZone').style.background  = '#f0fff4';
            document.getElementById('thumbError').style.display = 'none';
        };
        reader.readAsDataURL(file);
    }

    function handleThumbDrop(e) {
        e.preventDefault();
        e.currentTarget.style.borderColor = '#cbd5e1';
        e.currentTarget.style.background  = '#fafbfc';
        const file = e.dataTransfer.files[0];
        if (file) {
            const dt = new DataTransfer();
            dt.items.add(file);
            document.getElementById('thumbnailInput').files = dt.files;
            handleThumbSelect(file);
        }
    }

    function clearThumb() {
        document.getElementById('thumbnailInput').value = '';
        document.getElementById('thumbPreviewWrap').style.display = 'none';
        document.getElementById('thumbDropLabel').textContent = 'Click or drag & drop';
        document.getElementById('thumbDropZone').style.borderColor = '#cbd5e1';
        document.getElementById('thumbDropZone').style.background  = '#fafbfc';
    }

    // ── Client-side belt-and-suspenders sanitization ─────────────────
    // (The real security boundary is the server-side HTMLPurifier pass in
    // blogUpload_process.php — this only strips obvious tags before send.)
    function sanitizeHTML(html) {
        const temp = document.createElement('div');
        temp.innerHTML = html;
        ['script', 'style', 'iframe', 'object', 'embed'].forEach(tag => {
            const els = temp.getElementsByTagName(tag);
            while (els[0]) els[0].parentNode.removeChild(els[0]);
        });
        return temp.innerHTML;
    }

    // ── Submit ────────────────────────────────────────────────────────
    async function handleFormSubmit(e) {
        e.preventDefault();

        const title   = document.getElementById('blogTitle').value.trim();
        const summary = tinymce.get('summaryEditor').getContent();
        const content = tinymce.get('contentEditor').getContent();
        const thumbInput = document.getElementById('thumbnailInput');

        if (!title) { showNotification('Please enter a press title.', 'error'); return; }
        if (!summary || summary === '<p></p>') { showNotification('Please write a press summary.', 'error'); return; }
        if (!content || content === '<p></p>') { showNotification('Please write the press content.', 'error'); return; }

        if (!thumbInput.files || !thumbInput.files[0]) {
            document.getElementById('thumbError').style.display = 'block';
            document.getElementById('thumbDropZone').style.borderColor = '#dc3545';
            showNotification('Press thumbnail is required.', 'error');
            return;
        }

        showLoading(true);

        document.getElementById('sanitizedContent').value = sanitizeHTML(content);
        document.getElementById('sanitizedSummary').value  = sanitizeHTML(summary);

        const formData = new FormData(e.target);
        if (e.submitter && e.submitter.name) formData.set(e.submitter.name, e.submitter.value);

        try {
            const response = await fetch(e.target.action, { method: 'POST', body: formData, credentials: 'same-origin' });
            const result = await response.json();

            if (result.status === 'success') {
                localStorage.removeItem('blogAutoSave');
                showNotification('Press post saved! Redirecting…', 'success');
                setTimeout(() => { window.location.href = 'blog_manager.php'; }, 1500);
            } else {
                throw new Error(result.message || 'Unknown error occurred');
            }
        } catch (error) {
            showNotification('Error saving post: ' + error.message, 'error');
        } finally {
            showLoading(false);
        }
    }

    // ── Auto-save ────────────────────────────────────────────────────
    function autoSave() {
        const title = document.getElementById('blogTitle').value.trim();
        const summaryEd = tinymce.get('summaryEditor');
        const contentEd = tinymce.get('contentEditor');
        if (!summaryEd || !contentEd) return;
        const summary = summaryEd.getContent();
        const content = contentEd.getContent();

        if (title && (summary || content)) {
            localStorage.setItem('blogAutoSave', JSON.stringify({
                title: title, summary: summary, content: content, timestamp: new Date().toISOString()
            }));
        }
    }

    function loadAutoSave() {
        const raw = localStorage.getItem('blogAutoSave');
        if (!raw) return;
        const data = JSON.parse(raw);
        const hoursDiff = (new Date() - new Date(data.timestamp)) / 36e5;
        if (hoursDiff >= 24) return;

        if (confirm('Auto-saved content found from ' + new Date(data.timestamp).toLocaleString() + '. Restore it?')) {
            document.getElementById('blogTitle').value = data.title;
            document.getElementById('blogTitle').dispatchEvent(new Event('input'));
            if (tinymce.get('summaryEditor')) tinymce.get('summaryEditor').setContent(data.summary || '');
            if (tinymce.get('contentEditor')) tinymce.get('contentEditor').setContent(data.content || '');
            showNotification('Auto-saved content restored!', 'success');
        }
    }

    // ── Utility ──────────────────────────────────────────────────────
    function showLoading(show) { document.getElementById('loadingOverlay').classList.toggle('show', show); }

    function showNotification(message, type) {
        const el = document.getElementById('notification');
        el.textContent = message;
        el.className = 'notification ' + (type || 'success') + ' show';
        setTimeout(() => el.classList.remove('show'), 5000);
    }

    window.addEventListener('beforeunload', function (e) {
        const title = document.getElementById('blogTitle').value.trim();
        if (title) { e.preventDefault(); e.returnValue = ''; }
    });
    </script>
</body>
</html>
