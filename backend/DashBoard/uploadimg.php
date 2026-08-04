<?php
include '../Database/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../login/loging.php");
    exit();
}

$db         = new Db();
$connection = $db->connect();

// Fetch all images for gallery + stats
try {
    $stmt       = $connection->query("SELECT * FROM img_upload ORDER BY img_path DESC");
    $all_images = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $all_images = [];
}

$slider_count = 0;
$latest_count = 0;
foreach ($all_images as $img) {
    if ($img['img_type'] === 'img_slider')  $slider_count++;
    if ($img['img_type'] === 'latest_news') $latest_count++;
}
$total_count = count($all_images);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Upload Images - ATMABISWAS</title>
    <link rel="stylesheet" href="css/uploadfile.css">
    <link rel="stylesheet" href="css/admin-sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="icon" type="image/png" href="../images/logo/logo.png">
    <style>
        /* ── Stats Bar ──────────────────────────────── */
        .stats-bar {
            display: flex;
            gap: 1rem;
            max-width: 800px;
            margin: 0 auto 1.5rem;
            padding: 0 2rem;
            flex-wrap: wrap;
        }
        .stat-card {
            flex: 1;
            min-width: 140px;
            background: #fff;
            border-radius: 12px;
            padding: 1.1rem 1.4rem;
            box-shadow: 0 2px 12px rgba(0,0,0,.07);
            border: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: .9rem;
        }
        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        .stat-icon.blue  { background: rgba(0,115,230,.12); color: #0073e6; }
        .stat-icon.green { background: rgba(16,185,129,.12); color: #10b981; }
        .stat-icon.purple{ background: rgba(139,92,246,.12); color: #8b5cf6; }
        .stat-value { font-size: 1.6rem; font-weight: 700; color: #1e3a5f; line-height: 1; }
        .stat-label { font-size: .78rem; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; margin-top: 3px; }

        /* ── Gallery Section ────────────────────────── */
        .gallery-section {
            max-width: 1100px;
            margin: 2rem auto 3rem;
            padding: 0 2rem;
        }
        .gallery-container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,.08);
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }
        .gallery-header {
            padding: 1.5rem 2rem 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .gallery-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e3a5f;
            display: flex;
            align-items: center;
            gap: .6rem;
            margin-bottom: 1rem;
        }
        .gallery-title i { color: #0073e6; }
        .gallery-tabs {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
        }
        .tab-btn {
            padding: .5rem 1.1rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px 8px 0 0;
            background: transparent;
            color: #6b7280;
            font-size: .85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s;
            border-bottom: none;
            margin-bottom: -1px;
        }
        .tab-btn:hover { border-color: #0073e6; color: #0073e6; background: #f0f6ff; }
        .tab-btn.active { border-color: #0073e6; color: #0073e6; background: #fff; }

        /* ── Gallery Grid ───────────────────────────── */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.25rem;
            padding: 1.5rem 2rem 2rem;
        }
        .gallery-card {
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
            background: #f9fafb;
            transition: box-shadow .25s, transform .25s;
        }
        .gallery-card:hover {
            box-shadow: 0 8px 24px rgba(0,0,0,.11);
            transform: translateY(-3px);
        }
        .gallery-card-img-wrap {
            position: relative;
            height: 155px;
            overflow: hidden;
        }
        .gallery-card-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform .35s ease;
        }
        .gallery-card:hover .gallery-card-img { transform: scale(1.06); }
        .type-badge {
            position: absolute;
            top: 8px; left: 8px;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        .type-badge.type-img_slider  { background: #8b5cf6; color: #fff; }
        .type-badge.type-latest_news { background: #10b981; color: #fff; }
        .gallery-card-body {
            padding: 10px 12px 12px;
        }
        .gallery-card-title {
            font-size: .82rem;
            font-weight: 700;
            color: #1e3a5f;
            margin: 0 0 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .gallery-card-desc {
            font-size: .75rem;
            color: #9ca3af;
            margin: 0 0 10px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .btn-delete {
            display: flex;
            align-items: center;
            gap: .35rem;
            padding: .35rem .75rem;
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fca5a5;
            border-radius: 6px;
            font-size: .75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s;
            width: 100%;
            justify-content: center;
        }
        .btn-delete:hover { background: #dc2626; color: #fff; border-color: #dc2626; }
        .btn-delete:disabled { opacity: .6; cursor: not-allowed; }

        .gallery-empty {
            padding: 4rem 2rem;
            text-align: center;
            color: #9ca3af;
        }
        .gallery-empty i { font-size: 3rem; display: block; margin-bottom: 1rem; }

        /* ── Order badge on gallery card ─────────────── */
        .gallery-order-badge {
            position: absolute;
            top: 8px; right: 8px;
            background: rgba(0,0,0,.52);
            color: #fff;
            font-size: .68rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            letter-spacing: .04em;
            backdrop-filter: blur(3px);
        }

        /* ── Button actions row ─────────────────────── */
        .btn-actions { display: flex; gap: .5rem; margin-top: 8px; }
        .btn-edit {
            display: flex; align-items: center; gap: .35rem;
            padding: .35rem .75rem;
            background: #eff6ff; color: #0073e6;
            border: 1px solid #bfdbfe; border-radius: 6px;
            font-size: .75rem; font-weight: 600; cursor: pointer;
            transition: all .2s; flex: 1; justify-content: center;
        }
        .btn-edit:hover { background: #0073e6; color: #fff; border-color: #0073e6; }
        .btn-delete { flex: 1; margin-top: 0; }

        /* ── Edit Modal ─────────────────────────────── */
        .modal-overlay {
            position: fixed;
            top: 0; right: 0; bottom: 0; left: 0;
            background: rgba(0,0,0,.55);
            z-index: 10000;
            display: flex; align-items: center; justify-content: center;
            padding: 1rem;
            opacity: 0; pointer-events: none;
            transition: opacity .25s;
        }
        .modal-overlay.open { opacity: 1; pointer-events: auto; }
        .modal-box {
            background: #fff; border-radius: 16px;
            width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,.25);
            transform: translateY(20px) scale(0.97);
            transition: transform .25s;
        }
        .modal-overlay.open .modal-box { transform: translateY(0) scale(1); }
        .modal-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb;
            position: sticky; top: 0; background: #fff; z-index: 1;
        }
        .modal-header h3 {
            font-size: 1.1rem; font-weight: 700; color: #1e3a5f;
            display: flex; align-items: center; gap: .5rem; margin: 0;
        }
        .modal-header h3 i { color: #0073e6; }
        .modal-close {
            width: 32px; height: 32px; border: none; background: #f3f4f6;
            border-radius: 50%; font-size: 1.1rem; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            color: #6b7280; transition: all .2s;
        }
        .modal-close:hover { background: #dc2626; color: #fff; }
        .modal-body { padding: 1.5rem; }
        .modal-current-img {
            width: 100%; height: 200px; object-fit: cover;
            border-radius: 10px; margin-bottom: 1.25rem;
            border: 1px solid #e5e7eb; display: block;
        }
        .modal-replace-zone {
            border: 2px dashed #d1d5db; border-radius: 10px;
            padding: 1.25rem; text-align: center; cursor: pointer;
            transition: all .2s; position: relative; background: #f9fafb;
        }
        .modal-replace-zone:hover { border-color: #0073e6; background: #eff6ff; }
        .modal-replace-zone input[type="file"] {
            position: absolute; top: 0; right: 0; bottom: 0; left: 0;
            opacity: 0; cursor: pointer; width: 100%; height: 100%;
        }
        .modal-replace-zone i { font-size: 1.4rem; color: #9ca3af; }
        .modal-replace-zone p { font-size: .85rem; color: #6b7280; margin: .4rem 0 0; }
        .replace-preview { margin-top: .75rem; display: none; position: relative; }
        .replace-preview img {
            width: 100%; height: 150px; object-fit: cover;
            border-radius: 8px; border: 1px solid #e5e7eb; display: block;
        }
        .replace-preview-clear {
            position: absolute; top: 6px; right: 6px;
            width: 26px; height: 26px; background: rgba(0,0,0,.6);
            color: #fff; border: none; border-radius: 50%; font-size: .85rem;
            cursor: pointer; display: flex; align-items: center; justify-content: center;
        }
        .modal-footer {
            padding: 1rem 1.5rem 1.5rem;
            display: flex; gap: .75rem; justify-content: flex-end;
            border-top: 1px solid #e5e7eb;
        }
        .btn-modal-cancel {
            padding: .65rem 1.4rem; background: #f3f4f6; color: #374151;
            border: 1px solid #d1d5db; border-radius: 8px;
            font-size: .9rem; font-weight: 600; cursor: pointer; transition: all .2s;
        }
        .btn-modal-cancel:hover { background: #e5e7eb; }
        .btn-modal-save {
            padding: .65rem 1.6rem; background: #0073e6; color: #fff;
            border: none; border-radius: 8px;
            font-size: .9rem; font-weight: 600; cursor: pointer;
            transition: all .2s; display: flex; align-items: center; gap: .45rem;
        }
        .btn-modal-save:hover { background: #005bb5; }
        .btn-modal-save:disabled { opacity: .6; cursor: not-allowed; }

        /* ── Notification Toast ─────────────────────── */
        .toast {
            position: fixed;
            top: -80px;
            left: 50%;
            transform: translateX(-50%);
            padding: .85rem 1.8rem;
            border-radius: 10px;
            font-size: .9rem;
            font-weight: 600;
            z-index: 9999;
            transition: top .4s ease;
            box-shadow: 0 8px 24px rgba(0,0,0,.18);
        }
        .toast.show { top: 20px; }
        .toast.success { background: #10b981; color: #fff; }
        .toast.error   { background: #dc2626; color: #fff; }

        @media (max-width: 768px) {
            .stats-bar { padding: 0 1rem; }
            .gallery-section { padding: 0 1rem; }
            .gallery-grid { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: .85rem; padding: 1rem; }
            .gallery-header { padding: 1rem 1rem 0; }
            .gallery-card-img-wrap { height: 120px; }
        }
    </style>
</head>

<body>
    <div id="toast" class="toast"></div>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar-container">
            <?php include 'sidebar.php' ?>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="page-header">
                <div class="header-content">
                    <div class="header-left">
                        <h1 class="page-title">
                            <i class="fas fa-images"></i>
                            Image Manager
                        </h1>
                        <p class="page-subtitle">Upload and manage website images</p>
                    </div>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="stats-bar">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fas fa-images"></i></div>
                    <div>
                        <div class="stat-value"><?= $total_count ?></div>
                        <div class="stat-label">Total Images</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple"><i class="fas fa-film"></i></div>
                    <div>
                        <div class="stat-value"><?= $slider_count ?></div>
                        <div class="stat-label">Slider Images</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fas fa-newspaper"></i></div>
                    <div>
                        <div class="stat-value"><?= $latest_count ?></div>
                        <div class="stat-label">Latest News</div>
                    </div>
                </div>
            </div>

            <!-- Upload Section -->
            <section class="upload-section">
                <div class="upload-container">

                    <form action="../../uploadimg_process.php" method="POST" enctype="multipart/form-data" class="upload-form" id="uploadForm">
                        <div class="form-header">
                            <h2 class="form-title">
                                <i class="fas fa-cloud-upload-alt"></i>
                                Upload Image
                            </h2>
                            <p class="form-description">Upload images for the website with proper categorization</p>
                        </div>

                        <div class="upload-area">
                            <div class="upload-zone" id="uploadZone">
                                <div class="upload-icon">
                                    <i class="fas fa-image"></i>
                                </div>
                                <h3 class="upload-title">Drag &amp; Drop or Browse</h3>
                                <p class="upload-info">JPG, JPEG, PNG — Max 2 MB</p>
                                <label for="imageUpload" class="upload-btn">
                                    <i class="fas fa-folder-open"></i>
                                    Browse Files
                                    <input type="file" id="imageUpload" name="image_file" class="file-input"
                                        accept=".jpg,.jpeg,.png" required>
                                </label>
                                <div class="preview-container" id="imagePreview">
                                    <img src="#" alt="Image preview">
                                </div>
                            </div>
                        </div>

                        <div class="form-body">
                            <div class="form-group">
                                <label for="imagetype" class="form-label">
                                    <i class="fas fa-tags"></i>
                                    Image Type
                                </label>
                                <select name="imagetype" id="imagetype" class="form-select" required>
                                    <option value="latest_news">Latest News</option>
                                    <option value="img_slider">Image Slider</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="img_title" class="form-label">
                                    <i class="fas fa-heading"></i>
                                    Image Title
                                </label>
                                <input type="text" id="img_title" name="img_title" class="form-input"
                                    placeholder="Enter image title..." required />
                            </div>

                            <div class="form-group">
                                <label for="img_description" class="form-label">
                                    <i class="fas fa-align-left"></i>
                                    Description <span style="font-weight:400;color:#9ca3af;">(optional)</span>
                                </label>
                                <textarea id="img_description" name="img_description" class="form-textarea"
                                    rows="3" placeholder="Enter image description..."></textarea>
                            </div>

                            <div class="form-group">
                                <label for="display_order" class="form-label">
                                    <i class="fas fa-sort-numeric-down"></i>
                                    Display Order <span style="font-weight:400;color:#9ca3af;">(optional — lower numbers appear first)</span>
                                </label>
                                <input type="number" id="display_order" name="display_order" class="form-input"
                                    min="0" value="0" placeholder="0">
                            </div>
                        </div>

                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-upload"></i>
                                Upload Image
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <!-- Gallery Section -->
            <section class="gallery-section">
                <div class="gallery-container">
                    <div class="gallery-header">
                        <h2 class="gallery-title">
                            <i class="fas fa-th"></i>
                            Uploaded Images
                        </h2>
                        <div class="gallery-tabs">
                            <button class="tab-btn active" data-filter="all">
                                All (<?= $total_count ?>)
                            </button>
                            <button class="tab-btn" data-filter="img_slider">
                                <i class="fas fa-film"></i> Slider (<?= $slider_count ?>)
                            </button>
                            <button class="tab-btn" data-filter="latest_news">
                                <i class="fas fa-newspaper"></i> Latest News (<?= $latest_count ?>)
                            </button>
                        </div>
                    </div>

                    <?php if (empty($all_images)): ?>
                    <div class="gallery-empty">
                        <i class="fas fa-images"></i>
                        <p>No images uploaded yet. Upload your first image above.</p>
                    </div>
                    <?php else: ?>
                    <div class="gallery-grid" id="galleryGrid">
                        <?php foreach ($all_images as $img): ?>
                        <?php $cardOrder = (int)($img['display_order'] ?? 0); ?>
                        <div class="gallery-card"
                             data-type="<?= htmlspecialchars($img['img_type']) ?>"
                             data-path="<?= htmlspecialchars($img['img_path']) ?>"
                             data-title="<?= htmlspecialchars($img['img_title']) ?>"
                             data-desc="<?= htmlspecialchars($img['img_description'] ?? '') ?>"
                             data-imgtype="<?= htmlspecialchars($img['img_type']) ?>"
                             data-order="<?= $cardOrder ?>">
                            <div class="gallery-card-img-wrap">
                                <img class="gallery-card-img"
                                     src="../../<?= htmlspecialchars($img['img_path']) ?>"
                                     alt="<?= htmlspecialchars($img['img_title']) ?>"
                                     loading="lazy">
                                <span class="type-badge type-<?= htmlspecialchars($img['img_type']) ?>">
                                    <?= $img['img_type'] === 'img_slider' ? 'Slider' : 'Latest' ?>
                                </span>
                                <?php if ($cardOrder > 0): ?>
                                <span class="gallery-order-badge">#<?= $cardOrder ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="gallery-card-body">
                                <p class="gallery-card-title"><?= htmlspecialchars($img['img_title']) ?></p>
                                <?php if (!empty(trim($img['img_description']))): ?>
                                <p class="gallery-card-desc"><?= htmlspecialchars($img['img_description']) ?></p>
                                <?php endif; ?>
                                <div class="btn-actions">
                                    <button class="btn-edit"
                                            onclick="openEditModal(this.closest('.gallery-card'))">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn-delete"
                                            data-path="<?= htmlspecialchars($img['img_path']) ?>"
                                            onclick="deleteImage(this)">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </section>

        </div><!-- /.main-content -->
    </div><!-- /.dashboard-container -->

    <!-- ── Edit Modal ───────────────────────────── -->
    <div class="modal-overlay" id="editModal" onclick="closeEditModal(event)">
        <div class="modal-box" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Edit Image</h3>
                <button class="modal-close" onclick="closeEditModal()" title="Close">&times;</button>
            </div>

            <form class="modal-body" id="editForm" enctype="multipart/form-data">
                <img src="" alt="Current image" class="modal-current-img" id="modalCurrentImg">

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-tags"></i> Image Type
                    </label>
                    <select name="img_type" id="editImgType" class="form-select" required>
                        <option value="img_slider">Image Slider</option>
                        <option value="latest_news">Latest News</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-heading"></i> Title
                    </label>
                    <input type="text" name="img_title" id="editTitle" class="form-input"
                           placeholder="Image title" required>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-align-left"></i>
                        Description <span style="font-weight:400;color:#9ca3af;">(optional)</span>
                    </label>
                    <textarea name="img_description" id="editDesc" class="form-textarea"
                              rows="3" placeholder="Image description..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-sort-numeric-down"></i>
                        Display Order <span style="font-weight:400;color:#9ca3af;">(lower numbers appear first)</span>
                    </label>
                    <input type="number" name="display_order" id="editOrder" class="form-input"
                           min="0" value="0">
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-exchange-alt"></i>
                        Replace Image <span style="font-weight:400;color:#9ca3af;">(optional)</span>
                    </label>
                    <div class="modal-replace-zone" id="replaceZone">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Click or drag a new image here</p>
                        <p style="font-size:.78rem;color:#9ca3af;margin-top:.2rem;">JPG, PNG — Max 2 MB</p>
                        <input type="file" name="image_file" id="replaceFile"
                               accept=".jpg,.jpeg,.png" onchange="previewReplace(this)">
                    </div>
                    <div class="replace-preview" id="replacePreview">
                        <img src="" id="replacePreviewImg" alt="New image preview">
                        <button type="button" class="replace-preview-clear"
                                onclick="clearReplace()" title="Remove">&times;</button>
                    </div>
                </div>

                <input type="hidden" name="old_path" id="editOldPath">
            </form>

            <div class="modal-footer">
                <button class="btn-modal-cancel" onclick="closeEditModal()">Cancel</button>
                <button class="btn-modal-save" id="saveBtn" onclick="submitEdit()">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </div>
    </div>

    <script src="js/dashboard.js"></script>
    <script>
    // ── Toast notification ──────────────────────────
    function showToast(msg, type) {
        const t = document.getElementById('toast');
        t.textContent = msg;
        t.className = 'toast ' + type;
        t.classList.add('show');
        setTimeout(() => t.classList.remove('show'), 3000);
    }

    // ── Image preview ───────────────────────────────
    document.getElementById('imageUpload').addEventListener('change', function (e) {
        const preview = document.getElementById('imagePreview');
        const file = e.target.files[0];
        if (file) {
            preview.style.display = 'block';
            const reader = new FileReader();
            reader.onload = function (ev) {
                preview.querySelector('img').src = ev.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    // ── Drag & drop ─────────────────────────────────
    const uploadZone = document.getElementById('uploadZone');
    uploadZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadZone.classList.add('dragover');
    });
    uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('dragover'));
    uploadZone.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadZone.classList.remove('dragover');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            document.getElementById('imageUpload').files = files;
            document.getElementById('imageUpload').dispatchEvent(new Event('change'));
        }
    });

    // ── Upload submit spinner ───────────────────────
    document.getElementById('uploadForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading…';
        btn.disabled = true;
    });

    // ── Tab filter ──────────────────────────────────
    document.querySelectorAll('.tab-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.tab-btn').forEach(function (b) {
                b.classList.remove('active');
            });
            this.classList.add('active');
            const filter = this.dataset.filter;
            document.querySelectorAll('.gallery-card').forEach(function (card) {
                card.style.display = (filter === 'all' || card.dataset.type === filter) ? '' : 'none';
            });
        });
    });

    // ── Delete image ────────────────────────────────
    function deleteImage(btn) {
        if (!confirm('Delete this image? This action cannot be undone.')) return;
        const card = btn.closest('.gallery-card');
        const path = btn.dataset.path;

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting…';

        fetch('Actions/delete_img.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'img_path=' + encodeURIComponent(path)
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                card.style.transition = 'opacity .3s, transform .3s';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.85)';
                setTimeout(function () { card.remove(); updateCounters(); }, 320);
                showToast('Image deleted successfully.', 'success');
            } else {
                showToast('Delete failed: ' + (data.error || 'Unknown error'), 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-trash-alt"></i> Delete';
            }
        })
        .catch(function () {
            showToast('Network error. Please try again.', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-trash-alt"></i> Delete';
        });
    }

    // ── Edit Modal ──────────────────────────────────
    function openEditModal(card) {
        const path    = card.dataset.path;
        const title   = card.dataset.title;
        const desc    = card.dataset.desc    || '';
        const imgtype = card.dataset.imgtype || 'img_slider';
        const order   = card.dataset.order   || '0';

        document.getElementById('modalCurrentImg').src = '../../' + path;
        document.getElementById('editOldPath').value   = path;
        document.getElementById('editTitle').value     = title;
        document.getElementById('editDesc').value      = desc;
        document.getElementById('editImgType').value   = imgtype;
        document.getElementById('editOrder').value     = order;

        // Reset replace zone
        clearReplace();

        document.getElementById('editModal').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeEditModal(e) {
        if (e && e.target !== document.getElementById('editModal')) return;
        document.getElementById('editModal').classList.remove('open');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.getElementById('editModal').classList.remove('open');
            document.body.style.overflow = '';
        }
    });

    function previewReplace(input) {
        const file = input.files[0];
        if (!file) return;
        const preview = document.getElementById('replacePreview');
        const img     = document.getElementById('replacePreviewImg');
        const reader  = new FileReader();
        reader.onload  = function (e) { img.src = e.target.result; };
        reader.readAsDataURL(file);
        preview.style.display = 'block';
        document.getElementById('replaceZone').style.display = 'none';
    }

    function clearReplace() {
        document.getElementById('replaceFile').value    = '';
        document.getElementById('replacePreview').style.display = 'none';
        document.getElementById('replacePreviewImg').src = '';
        document.getElementById('replaceZone').style.display = '';
    }

    function submitEdit() {
        const form    = document.getElementById('editForm');
        const saveBtn = document.getElementById('saveBtn');

        if (!form.reportValidity()) return;

        const fd = new FormData(form);

        saveBtn.disabled    = true;
        saveBtn.innerHTML   = '<i class="fas fa-spinner fa-spin"></i> Saving…';

        fetch('Actions/edit_img.php', { method: 'POST', body: fd })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                const oldPath = document.getElementById('editOldPath').value;
                const card    = document.querySelector('.gallery-card[data-path="' + CSS.escape(oldPath) + '"]');
                if (card) {
                    const newPath  = data.new_path;
                    const newType  = document.getElementById('editImgType').value;
                    const newTitle = document.getElementById('editTitle').value;
                    const newDesc  = document.getElementById('editDesc').value;
                    const newOrder = data.display_order !== undefined ? data.display_order : (parseInt(document.getElementById('editOrder').value) || 0);

                    // Update data attributes
                    card.dataset.path    = newPath;
                    card.dataset.title   = newTitle;
                    card.dataset.desc    = newDesc;
                    card.dataset.imgtype = newType;
                    card.dataset.type    = newType;
                    card.dataset.order   = newOrder;

                    // Update image src
                    const cardImg = card.querySelector('.gallery-card-img');
                    if (cardImg) cardImg.src = '../../' + newPath + '?t=' + Date.now();

                    // Update type badge
                    const badge = card.querySelector('.type-badge');
                    if (badge) {
                        badge.className = 'type-badge type-' + newType;
                        badge.textContent = newType === 'img_slider' ? 'Slider' : 'Latest';
                    }

                    // Update title + desc text
                    const titleEl = card.querySelector('.gallery-card-title');
                    if (titleEl) titleEl.textContent = newTitle;
                    const descEl  = card.querySelector('.gallery-card-desc');
                    if (descEl)  descEl.textContent  = newDesc;

                    // Update delete button's data-path
                    const delBtn = card.querySelector('.btn-delete');
                    if (delBtn) delBtn.dataset.path = newPath;

                    // Update order badge
                    var imgWrap = card.querySelector('.gallery-card-img-wrap');
                    var existingBadge = card.querySelector('.gallery-order-badge');
                    if (newOrder > 0) {
                        if (existingBadge) {
                            existingBadge.textContent = '#' + newOrder;
                        } else {
                            var ob = document.createElement('span');
                            ob.className   = 'gallery-order-badge';
                            ob.textContent = '#' + newOrder;
                            imgWrap.appendChild(ob);
                        }
                    } else {
                        if (existingBadge) existingBadge.remove();
                    }
                }

                showToast('Image updated successfully.', 'success');
                document.getElementById('editModal').classList.remove('open');
                document.body.style.overflow = '';
                updateCounters();
            } else {
                showToast('Save failed: ' + (data.error || 'Unknown error'), 'error');
            }
        })
        .catch(function () {
            showToast('Network error. Please try again.', 'error');
        })
        .finally(function () {
            saveBtn.disabled  = false;
            saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
        });
    }

    // ── Update tab counters after deletion
    function updateCounters() {
        const cards = document.querySelectorAll('.gallery-card');
        let total = cards.length, slider = 0, latest = 0;
        cards.forEach(function (c) {
            if (c.dataset.type === 'img_slider')  slider++;
            if (c.dataset.type === 'latest_news') latest++;
        });
        const tabs = document.querySelectorAll('.tab-btn');
        if (tabs[0]) tabs[0].childNodes[0].textContent = 'All (' + total + ')';
        // Rebuild tab labels preserving icons
        const tabDefs = [
            { el: tabs[1], icon: 'fas fa-film',      label: 'Slider',      count: slider },
            { el: tabs[2], icon: 'fas fa-newspaper', label: 'Latest News', count: latest }
        ];
        tabDefs.forEach(function (t) {
            if (t.el) t.el.innerHTML = '<i class="' + t.icon + '"></i> ' + t.label + ' (' + t.count + ')';
        });
        // Also update stat cards
        const statValues = document.querySelectorAll('.stat-value');
        if (statValues[0]) statValues[0].textContent = total;
        if (statValues[1]) statValues[1].textContent = slider;
        if (statValues[2]) statValues[2].textContent = latest;
    }
    </script>

</body>
</html>
