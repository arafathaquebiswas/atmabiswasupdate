<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../login/loging.php");
    exit();
}

require_once '../Database/db.php';
$db   = new Db();
$conn = $db->connect();

// Auto-create and seed the table if it doesn't exist yet
$conn->exec("
    CREATE TABLE IF NOT EXISTS `about_us_content` (
        `id`           INT          NOT NULL AUTO_INCREMENT,
        `section_key`  VARCHAR(50)  NOT NULL,
        `image_path`   VARCHAR(500) NOT NULL DEFAULT '',
        `image_alt`    VARCHAR(255) NOT NULL DEFAULT '',
        `text_content` TEXT         NOT NULL,
        `updated_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `section_key_unique` (`section_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$defaultAboutText = 'ATMABISWAS is a non-governmental, non-profit, voluntary, and development-focused organization committed to creating meaningful social change and fostering sustainable development. Established in January 1991 under the Department of Social Welfare, ATMABISWAS has dedicated over three decades to empowering communities across Bangladesh. The organization primarily focuses on serving the disadvantaged populations, striving to uplift their living standards and enhance their access to essential resources and opportunities.

Since its inception, ATMABISWAS has worked tirelessly to support marginalized individuals and communities, with an initial emphasis on the district of Chuadanga. Through a range of social welfare programs, development projects, and micro-credit initiatives, the organization has impacted thousands of lives, enabling beneficiaries to break the cycle of poverty and build a better future.';

$defaultTeamText = 'Our team consists of dedicated professionals who are passionate about making a difference. We collaborate to create a positive impact and support each other in our mission to empower communities and foster sustainable development.

Our team members come from diverse backgrounds, bringing a wealth of experience and expertise to the organization. We are united by our shared commitment to social justice, equality, and sustainable development. Each member of our team plays a crucial role in driving our mission forward — from field workers to administrative staff, project managers, and volunteers. Together, we strive to create a positive and lasting impact on the communities we serve.';

$seedStmt = $conn->prepare("
    INSERT INTO `about_us_content` (`section_key`, `image_path`, `image_alt`, `text_content`)
    VALUES
        ('about_us', 'office_pic/office_pic.jpg', 'ATMABISWAS Office', :about_text),
        ('our_team', 'office_pic/00000.jpg', 'ATMABISWAS Team with PKSF', :team_text)
    ON DUPLICATE KEY UPDATE `section_key` = `section_key`
");
$seedStmt->execute([':about_text' => $defaultAboutText, ':team_text' => $defaultTeamText]);

// Load current content from DB
$sections = [];
$stmt = $conn->query("SELECT * FROM about_us_content");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $sections[$row['section_key']] = $row;
}

$about  = $sections['about_us'] ?? null;
$team   = $sections['our_team'] ?? null;

$aboutImgPath = $about['image_path'] ?? 'office_pic/office_pic.jpg';
$teamImgPath  = $team['image_path']  ?? 'office_pic/00000.jpg';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us Editor - ATMABISWAS</title>
    <link rel="stylesheet" href="css/uploadfile.css">
    <link rel="stylesheet" href="css/admin-sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="../images/logo/logo.png">
    <style>
        /* ── Editor grid ─────────────────────────────── */
        .editor-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            max-width: 1200px;
            margin: 1.5rem auto 3rem;
            padding: 0 2rem;
        }

        .editor-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }

        .editor-card-header {
            background: linear-gradient(135deg, #1e3a5f, #0073e6);
            color: #fff;
            padding: 1.1rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .editor-card-header i { font-size: 1.1rem; }

        .editor-card-header h2 {
            font-size: 1rem;
            font-weight: 700;
            margin: 0;
        }

        .editor-card-body { padding: 1.5rem; }

        /* ── Current image preview ───────────────────── */
        .current-preview-wrap {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            margin-bottom: 1.2rem;
            background: #f9fafb;
        }

        .current-preview-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }

        .current-preview-label {
            position: absolute;
            top: 8px; left: 8px;
            background: rgba(0, 0, 0, 0.55);
            color: #fff;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            backdrop-filter: blur(3px);
        }

        /* ── Upload zone ─────────────────────────────── */
        .upload-zone {
            border: 2px dashed #d1d5db;
            border-radius: 10px;
            padding: 1.2rem;
            text-align: center;
            cursor: pointer;
            position: relative;
            background: #f9fafb;
            transition: border-color 0.2s, background 0.2s;
            margin-bottom: 1rem;
        }

        .upload-zone:hover,
        .upload-zone.dragover {
            border-color: #0073e6;
            background: #eff6ff;
        }

        .upload-zone input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }

        .upload-zone i { font-size: 1.4rem; color: #9ca3af; }
        .upload-zone p { font-size: 0.82rem; color: #6b7280; margin: 0.3rem 0 0; }

        .new-preview-wrap {
            display: none;
            position: relative;
            margin-bottom: 1rem;
        }

        .new-preview-img {
            width: 100%;
            height: 170px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            display: block;
        }

        .preview-clear {
            position: absolute;
            top: 6px; right: 6px;
            width: 28px; height: 28px;
            border: none;
            background: rgba(0, 0, 0, 0.6);
            color: #fff;
            border-radius: 50%;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ── Form fields ─────────────────────────────── */
        .field-label {
            font-size: 0.82rem;
            font-weight: 700;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .field-label i { color: #0073e6; }

        .field-textarea {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 0.8rem 1rem;
            font-size: 0.9rem;
            line-height: 1.7;
            color: #1f2937;
            resize: vertical;
            font-family: "Times New Roman", Times, serif;
            transition: border-color 0.2s, box-shadow 0.2s;
            margin-bottom: 1rem;
        }

        .field-textarea:focus {
            outline: none;
            border-color: #0073e6;
            box-shadow: 0 0 0 3px rgba(0, 115, 230, 0.12);
        }

        .field-input {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 0.65rem 1rem;
            font-size: 0.9rem;
            color: #1f2937;
            margin-bottom: 1rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .field-input:focus {
            outline: none;
            border-color: #0073e6;
            box-shadow: 0 0 0 3px rgba(0, 115, 230, 0.12);
        }

        .hint {
            font-size: 0.76rem;
            color: #9ca3af;
            margin-bottom: 0.8rem;
            margin-top: -0.6rem;
        }

        /* ── Save button ─────────────────────────────── */
        .btn-save {
            width: 100%;
            padding: 0.8rem;
            background: #0073e6;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s, transform 0.15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-save:hover   { background: #005bb5; }
        .btn-save:active  { transform: scale(0.98); }
        .btn-save:disabled{ opacity: 0.65; cursor: not-allowed; }

        /* ── Last-saved badge ────────────────────────── */
        .last-saved {
            font-size: 0.76rem;
            color: #6b7280;
            text-align: center;
            margin-top: 0.7rem;
        }

        .last-saved span { color: #10b981; font-weight: 700; }

        /* ── Toast ───────────────────────────────────── */
        .toast {
            position: fixed;
            top: -80px;
            left: 50%;
            transform: translateX(-50%);
            padding: 0.85rem 2rem;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            z-index: 9999;
            transition: top 0.4s ease;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18);
            white-space: nowrap;
        }
        .toast.show    { top: 20px; }
        .toast.success { background: #10b981; color: #fff; }
        .toast.error   { background: #dc2626; color: #fff; }

        @media (max-width: 900px) {
            .editor-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 600px) {
            .editor-grid { padding: 0 1rem; }
        }
    </style>
</head>
<body>
    <div id="toast" class="toast"></div>

    <div class="dashboard-container">
        <div class="sidebar-container">
            <?php include 'sidebar.php'; ?>
        </div>

        <div class="main-content">
            <!-- Header -->
            <div class="page-header">
                <div class="header-content">
                    <div class="header-left">
                        <h1 class="page-title">
                            <i class="fas fa-pen-to-square"></i>
                            About Us Editor
                        </h1>
                        <p class="page-subtitle">Update the "About Us" and "Our Team" sections on the website</p>
                    </div>
                    <div class="header-right">
                        <a href="../../aboutus.php" target="_blank" class="btn btn-secondary" style="display:inline-flex;align-items:center;gap:6px;padding:.6rem 1.2rem;border-radius:8px;background:#f3f4f6;color:#374151;text-decoration:none;font-size:.9rem;font-weight:600;border:1px solid #d1d5db;">
                            <i class="fas fa-external-link-alt"></i> View Live Page
                        </a>
                    </div>
                </div>
            </div>

            <!-- Editor Cards -->
            <div class="editor-grid">

                <!-- About Us Card -->
                <div class="editor-card">
                    <div class="editor-card-header">
                        <i class="fas fa-building-ngo"></i>
                        <h2>About Us Section</h2>
                    </div>
                    <div class="editor-card-body">

                        <!-- Current image -->
                        <div class="current-preview-wrap">
                            <img
                                class="current-preview-img"
                                id="about-current-img"
                                src="../../<?= htmlspecialchars($aboutImgPath) ?>"
                                alt="Current About Us image">
                            <span class="current-preview-label">Current image</span>
                        </div>

                        <!-- New image upload -->
                        <label class="field-label"><i class="fas fa-image"></i> Replace Image (optional)</label>
                        <div class="upload-zone" id="about-zone">
                            <input type="file" id="about-file" accept=".jpg,.jpeg,.png,.webp"
                                   onchange="previewNew('about', this)">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Drag &amp; drop or click to browse</p>
                            <p style="font-size:.74rem;color:#9ca3af;margin-top:3px;">JPG, PNG, WebP — max 5 MB</p>
                        </div>
                        <div class="new-preview-wrap" id="about-new-wrap">
                            <img class="new-preview-img" id="about-new-img" src="" alt="New image">
                            <button class="preview-clear" onclick="clearNew('about')" title="Remove">&times;</button>
                        </div>

                        <!-- Text content -->
                        <label class="field-label" for="about-text"><i class="fas fa-align-left"></i> Description</label>
                        <p class="hint">Separate paragraphs with a blank line.</p>
                        <textarea class="field-textarea" id="about-text" rows="9"><?= htmlspecialchars($about['text_content'] ?? '') ?></textarea>

                        <label class="field-label" for="about-alt"><i class="fas fa-tag"></i> Image Alt Text</label>
                        <input type="text" class="field-input" id="about-alt"
                               value="<?= htmlspecialchars($about['image_alt'] ?? 'ATMABISWAS Office') ?>"
                               placeholder="e.g. ATMABISWAS Office">

                        <button class="btn-save" id="about-save" onclick="save('about_us')">
                            <i class="fas fa-save"></i> Save About Us
                        </button>
                        <?php if (!empty($about['updated_at'])): ?>
                        <p class="last-saved">Last saved: <span><?= htmlspecialchars(date('d M Y, H:i', strtotime($about['updated_at']))) ?></span></p>
                        <?php endif; ?>

                    </div>
                </div>

                <!-- Our Team Card -->
                <div class="editor-card">
                    <div class="editor-card-header">
                        <i class="fas fa-people-group"></i>
                        <h2>Our Team Section</h2>
                    </div>
                    <div class="editor-card-body">

                        <!-- Current image -->
                        <div class="current-preview-wrap">
                            <img
                                class="current-preview-img"
                                id="team-current-img"
                                src="../../<?= htmlspecialchars($teamImgPath) ?>"
                                alt="Current Our Team image">
                            <span class="current-preview-label">Current image</span>
                        </div>

                        <!-- New image upload -->
                        <label class="field-label"><i class="fas fa-image"></i> Replace Image (optional)</label>
                        <div class="upload-zone" id="team-zone">
                            <input type="file" id="team-file" accept=".jpg,.jpeg,.png,.webp"
                                   onchange="previewNew('team', this)">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Drag &amp; drop or click to browse</p>
                            <p style="font-size:.74rem;color:#9ca3af;margin-top:3px;">JPG, PNG, WebP — max 5 MB</p>
                        </div>
                        <div class="new-preview-wrap" id="team-new-wrap">
                            <img class="new-preview-img" id="team-new-img" src="" alt="New image">
                            <button class="preview-clear" onclick="clearNew('team')" title="Remove">&times;</button>
                        </div>

                        <!-- Text content -->
                        <label class="field-label" for="team-text"><i class="fas fa-align-left"></i> Description</label>
                        <p class="hint">Separate paragraphs with a blank line.</p>
                        <textarea class="field-textarea" id="team-text" rows="9"><?= htmlspecialchars($team['text_content'] ?? '') ?></textarea>

                        <label class="field-label" for="team-alt"><i class="fas fa-tag"></i> Image Alt Text</label>
                        <input type="text" class="field-input" id="team-alt"
                               value="<?= htmlspecialchars($team['image_alt'] ?? 'ATMABISWAS Team') ?>"
                               placeholder="e.g. ATMABISWAS Team with PKSF">

                        <button class="btn-save" id="team-save" onclick="save('our_team')">
                            <i class="fas fa-save"></i> Save Our Team
                        </button>
                        <?php if (!empty($team['updated_at'])): ?>
                        <p class="last-saved">Last saved: <span><?= htmlspecialchars(date('d M Y, H:i', strtotime($team['updated_at']))) ?></span></p>
                        <?php endif; ?>

                    </div>
                </div>

            </div><!-- /.editor-grid -->
        </div><!-- /.main-content -->
    </div><!-- /.dashboard-container -->

    <script>
    // ── Toast helper ─────────────────────────────────
    function showToast(msg, type) {
        var t = document.getElementById('toast');
        t.textContent = msg;
        t.className = 'toast ' + type;
        t.classList.add('show');
        setTimeout(function () { t.classList.remove('show'); }, 3500);
    }

    // ── Image preview on file select ─────────────────
    function previewNew(prefix, input) {
        var file = input.files[0];
        if (!file) return;
        var wrap = document.getElementById(prefix + '-new-wrap');
        var img  = document.getElementById(prefix + '-new-img');
        var zone = document.getElementById(prefix + '-zone');
        var reader = new FileReader();
        reader.onload = function (e) { img.src = e.target.result; };
        reader.readAsDataURL(file);
        wrap.style.display = 'block';
        zone.style.display = 'none';
    }

    function clearNew(prefix) {
        document.getElementById(prefix + '-file').value = '';
        document.getElementById(prefix + '-new-img').src = '';
        document.getElementById(prefix + '-new-wrap').style.display = 'none';
        document.getElementById(prefix + '-zone').style.display = '';
    }

    // ── Drag-and-drop ────────────────────────────────
    ['about', 'team'].forEach(function (prefix) {
        var zone = document.getElementById(prefix + '-zone');
        zone.addEventListener('dragover', function (e) {
            e.preventDefault();
            zone.classList.add('dragover');
        });
        zone.addEventListener('dragleave', function () {
            zone.classList.remove('dragover');
        });
        zone.addEventListener('drop', function (e) {
            e.preventDefault();
            zone.classList.remove('dragover');
            var files = e.dataTransfer.files;
            if (files.length > 0) {
                var input = document.getElementById(prefix + '-file');
                input.files = files;
                previewNew(prefix, input);
            }
        });
    });

    // ── Save section ─────────────────────────────────
    function save(sectionKey) {
        var prefix  = sectionKey === 'about_us' ? 'about' : 'team';
        var text    = document.getElementById(prefix + '-text').value.trim();
        var alt     = document.getElementById(prefix + '-alt').value.trim();
        var fileEl  = document.getElementById(prefix + '-file');
        var saveBtn = document.getElementById(prefix + '-save');

        if (!text) {
            showToast('Text content cannot be empty.', 'error');
            return;
        }

        var fd = new FormData();
        fd.append('section_key',  sectionKey);
        fd.append('text_content', text);
        fd.append('image_alt',    alt);

        if (fileEl.files.length > 0) {
            fd.append('image_file', fileEl.files[0]);
        }

        saveBtn.disabled   = true;
        saveBtn.innerHTML  = '<i class="fas fa-spinner fa-spin"></i> Saving…';

        fetch('Actions/save_about_content.php', { method: 'POST', body: fd })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                showToast('Saved successfully!', 'success');

                // If image was updated, refresh the "current" preview
                if (data.image_path) {
                    var currentImg = document.getElementById(prefix + '-current-img');
                    currentImg.src = '../../' + data.image_path + '?t=' + Date.now();
                    clearNew(prefix);
                }

                // Update "last saved" text
                var savedP = saveBtn.nextElementSibling;
                if (savedP && savedP.classList.contains('last-saved')) {
                    var now = new Date();
                    var formatted = now.getDate().toString().padStart(2,'0') + ' '
                        + now.toLocaleString('en-GB', { month: 'short' }) + ' '
                        + now.getFullYear() + ', '
                        + now.getHours().toString().padStart(2,'0') + ':'
                        + now.getMinutes().toString().padStart(2,'0');
                    savedP.innerHTML = 'Last saved: <span>' + formatted + '</span>';
                } else {
                    var p = document.createElement('p');
                    p.className = 'last-saved';
                    var now = new Date();
                    var formatted = now.getDate().toString().padStart(2,'0') + ' '
                        + now.toLocaleString('en-GB', { month: 'short' }) + ' '
                        + now.getFullYear() + ', '
                        + now.getHours().toString().padStart(2,'0') + ':'
                        + now.getMinutes().toString().padStart(2,'0');
                    p.innerHTML = 'Last saved: <span>' + formatted + '</span>';
                    saveBtn.insertAdjacentElement('afterend', p);
                }
            } else {
                showToast('Error: ' + (data.error || 'Unknown error'), 'error');
            }
        })
        .catch(function () {
            showToast('Network error. Please try again.', 'error');
        })
        .finally(function () {
            saveBtn.disabled  = false;
            saveBtn.innerHTML = (prefix === 'about' ? '<i class="fas fa-save"></i> Save About Us' : '<i class="fas fa-save"></i> Save Our Team');
        });
    }
    </script>
</body>
</html>
