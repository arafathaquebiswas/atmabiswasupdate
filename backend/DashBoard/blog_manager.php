<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../login/loging.php");
    exit();
}

require_once '../../config.php';

/* ── DB connection ──────────────────────────────────────────────── */
$pdo = null;
$db_error = null;
try {
    include '../Database/db.php';
    $pdo = (new Db())->connect();
} catch (Exception $e) {
    $db_error = 'Database connection failed.';
}

/* ── Helpers ────────────────────────────────────────────────────── */
function ytId(string $url): string {
    if (preg_match('/youtu\.be\/([a-zA-Z0-9_-]{11})/', $url, $m)) return $m[1];
    if (preg_match('/[?&]v=([a-zA-Z0-9_-]{11})/', $url, $m))       return $m[1];
    if (preg_match('/embed\/([a-zA-Z0-9_-]{11})/', $url, $m))       return $m[1];
    return '';
}

function articleThumb(array $post): string {
    if (!empty($post['cover_img'])) return $post['cover_img'];
    $yt = ytId($post['source_link'] ?? '');
    return $yt ? "https://img.youtube.com/vi/{$yt}/mqdefault.jpg" : '';
}

function articleType(array $post): string {
    if (!empty($post['cover_img']))   return 'image';
    $yt = ytId($post['source_link'] ?? '');
    if ($yt)                          return 'youtube';
    return 'text';
}

/* ── Column detection ───────────────────────────────────────────── */
$existing_cols = [];
if ($pdo) {
    try {
        $existing_cols = $pdo->query(
            "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='blogs'"
        )->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {}
}

/* ── Auto-migration (adds any missing columns, runs once) ───────── */
$migrated = [];
if ($pdo) {
    $needed = [
        'category'        => "ALTER TABLE blogs ADD COLUMN category VARCHAR(50) NOT NULL DEFAULT 'news'",
        'source_link'     => "ALTER TABLE blogs ADD COLUMN source_link VARCHAR(500) NULL DEFAULT NULL",
        'slug'            => "ALTER TABLE blogs ADD COLUMN slug VARCHAR(300) NULL DEFAULT NULL",
        'tags'            => "ALTER TABLE blogs ADD COLUMN tags VARCHAR(500) NULL DEFAULT NULL",
        'featured'        => "ALTER TABLE blogs ADD COLUMN featured TINYINT(1) NOT NULL DEFAULT 0",
        'reading_time'    => "ALTER TABLE blogs ADD COLUMN reading_time TINYINT UNSIGNED NOT NULL DEFAULT 0",
        'views'           => "ALTER TABLE blogs ADD COLUMN views INT UNSIGNED NOT NULL DEFAULT 0",
        'status'          => "ALTER TABLE blogs ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'published'",
        'seo_title'       => "ALTER TABLE blogs ADD COLUMN seo_title VARCHAR(255) NULL DEFAULT NULL",
        'seo_description' => "ALTER TABLE blogs ADD COLUMN seo_description TEXT NULL DEFAULT NULL",
        'seo_keywords'    => "ALTER TABLE blogs ADD COLUMN seo_keywords VARCHAR(500) NULL DEFAULT NULL",
        'social_image'    => "ALTER TABLE blogs ADD COLUMN social_image VARCHAR(500) NULL DEFAULT NULL",
        'last_updated'    => "ALTER TABLE blogs ADD COLUMN last_updated TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP",
    ];
    foreach ($needed as $col => $ddl) {
        if (!in_array($col, $existing_cols)) {
            try {
                $pdo->exec($ddl);
                $migrated[]      = $col;
                $existing_cols[] = $col;
            } catch (PDOException $e) {
                // Duplicate column = already exists; safe to ignore
            }
        }
    }
}
$auto_migrated = count($migrated) > 0;
$has = array_flip($existing_cols);

/* ── AJAX handler (featured/status toggle) ──────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ajax']) && $pdo) {
    header('Content-Type: application/json');
    $aid = (int)($_POST['id'] ?? 0);
    if (!$aid) { echo json_encode(['ok' => false]); exit; }
    try {
        switch ($_POST['ajax']) {
            case 'featured':
                $cur = (int)$pdo->query("SELECT COALESCE(featured,0) FROM blogs WHERE blog_id=$aid")->fetchColumn();
                $new = $cur ? 0 : 1;
                $pdo->prepare("UPDATE blogs SET featured=? WHERE blog_id=?")->execute([$new, $aid]);
                echo json_encode(['ok' => true, 'val' => $new]);
                break;
            case 'status':
                $cur = (string)$pdo->query("SELECT COALESCE(status,'published') FROM blogs WHERE blog_id=$aid")->fetchColumn();
                $new = ($cur === 'draft') ? 'published' : 'draft';
                $pdo->prepare("UPDATE blogs SET status=? WHERE blog_id=?")->execute([$new, $aid]);
                echo json_encode(['ok' => true, 'val' => $new]);
                break;
            default:
                echo json_encode(['ok' => false]);
        }
    } catch (PDOException $e) {
        echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
    }
    exit;
}

/* ── GET action: delete ─────────────────────────────────────────── */
$flash = '';
$flash_type = 'success';
if (($_GET['action'] ?? '') === 'delete' && ($did = (int)($_GET['id'] ?? 0)) && $pdo) {
    try {
        $pdo->prepare("DELETE FROM blogs WHERE blog_id=?")->execute([$did]);
        $flash = 'Press post deleted.';
    } catch (PDOException $e) {
        $flash      = 'Delete failed: ' . htmlspecialchars($e->getMessage());
        $flash_type = 'danger';
    }
}

/* ── Filters + pagination ────────────────────────────────────────── */
$page     = max(1, (int)($_GET['page']   ?? 1));
$limit    = 15;
$offset   = ($page - 1) * $limit;
$search   = trim($_GET['search'] ?? '');
$status_f = trim($_GET['status'] ?? '');
$cat_f    = trim($_GET['cat']    ?? '');

$where  = [];
$params = [];
if ($search !== '') {
    $where[]  = "(blog_title LIKE ? OR blog_content LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
if ($status_f !== '' && isset($has['status'])) {
    if ($status_f === 'published') {
        $where[] = "(status='published' OR status IS NULL)";
    } else {
        $where[]  = "status=?";
        $params[] = $status_f;
    }
}
if ($cat_f !== '' && isset($has['category'])) {
    $where[]  = "category=?";
    $params[] = $cat_f;
}
$wc = $where ? 'WHERE '.implode(' AND ', $where) : '';

/* ── Dynamic SELECT ─────────────────────────────────────────────── */
$sel_status   = isset($has['status'])      ? "COALESCE(status,'published')" : "'published'";
$sel_views    = isset($has['views'])       ? "COALESCE(views,0)"            : "0";
$sel_featured = isset($has['featured'])    ? "COALESCE(featured,0)"         : "0";
$sel_category = isset($has['category'])    ? "category"                     : "'news'";
$sel_srclink  = isset($has['source_link']) ? "source_link"                  : "NULL";

/* ── Fetch posts + stats ─────────────────────────────────────────── */
$total = 0; $total_pages = 1; $posts = [];
$stats = ['total' => 0, 'published' => 0, 'drafts' => 0, 'views' => 0];

if ($pdo && !$db_error) {
    try {
        $cs = $pdo->prepare("SELECT COUNT(*) FROM blogs $wc");
        $cs->execute($params);
        $total       = (int)$cs->fetchColumn();
        $total_pages = max(1, (int)ceil($total / $limit));

        $sql = "SELECT blog_id, blog_title, blog_author, upload_date, cover_img,
                       {$sel_srclink}  AS source_link,
                       {$sel_category} AS category,
                       {$sel_status}   AS status,
                       {$sel_views}    AS views,
                       {$sel_featured} AS featured
                FROM blogs $wc
                ORDER BY upload_date DESC
                LIMIT $limit OFFSET $offset";
        $st = $pdo->prepare($sql);
        $st->execute($params);
        $posts = $st->fetchAll(PDO::FETCH_ASSOC);

        /* Stats */
        $stats['total'] = (int)$pdo->query("SELECT COUNT(*) FROM blogs")->fetchColumn();
        if (isset($has['status'])) {
            $sv = $pdo->query("SELECT SUM(status='published' OR status IS NULL) AS p, SUM(status='draft') AS d FROM blogs")->fetch();
            $stats['published'] = (int)($sv['p'] ?? 0);
            $stats['drafts']    = (int)($sv['d'] ?? 0);
        } else {
            $stats['published'] = $stats['total'];
        }
        if (isset($has['views'])) {
            $stats['views'] = (int)$pdo->query("SELECT COALESCE(SUM(views),0) FROM blogs")->fetchColumn();
        }
    } catch (PDOException $e) {
        $db_error = 'Query error: ' . $e->getMessage();
        $posts    = [];
    }
}

/* ── Category labels ─────────────────────────────────────────────── */
$cat_labels = [
    'news'         => 'News',
    'media'        => 'Media',
    'announcement' => 'Announcement',
    'press'        => 'Press Release',
];
$cat_colors = [
    'news'         => '#2563eb',
    'media'        => '#7c3aed',
    'announcement' => '#d97706',
    'press'        => '#059669',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Press Manager — ATMABISWAS Admin</title>
<link rel="icon" type="image/png" href="../images/logo/logo.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root {
    --pri:  #0073e6;
    --dark: #1e3a5f;
    --bg:   #f5f7fa;
    --rad:  12px;
    --sh:   0 2px 10px rgba(0,0,0,.07);
}
* { box-sizing: border-box; }
body { background: var(--bg); font-family: system-ui,-apple-system,'Segoe UI',sans-serif; font-size: .93rem; color: #222; }

/* ── Page header ── */
.am-header {
    background: linear-gradient(135deg, var(--dark) 0%, var(--pri) 100%);
    color: #fff; padding: 1.5rem 0;
}
.am-header h1 { font-size: 1.5rem; font-weight: 800; margin: 0 0 .2rem; }
.am-header p  { opacity: .75; margin: 0; font-size: .85rem; }

/* ── Stat cards ── */
.sc { background:#fff; border-radius: var(--rad); box-shadow: var(--sh);
      padding: 1rem 1.25rem; display:flex; align-items:center; gap:.85rem; }
.sc-icon { width:42px; height:42px; border-radius:9px; display:flex;
           align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; }
.sc-num  { font-size:1.5rem; font-weight:800; color:var(--dark); line-height:1; }
.sc-lbl  { font-size:.72rem; color:#6b7280; margin-top:.1rem; }

/* ── Filter bar ── */
.filter-bar { background:#fff; border-radius: var(--rad); box-shadow: var(--sh);
              padding: 1rem 1.25rem; margin-bottom:1.25rem; }

/* ── Table card ── */
.table-card { background:#fff; border-radius: var(--rad); box-shadow: var(--sh); overflow:hidden; }
.table-card table { margin:0; font-size:.875rem; }
.table-card thead th {
    background: #f8fafc; color: #475569; font-weight: 700;
    font-size: .72rem; text-transform: uppercase; letter-spacing: .06em;
    border-bottom: 1.5px solid #e2e8f0; padding: .65rem 1rem; white-space:nowrap;
}
.table-card tbody td { padding: .75rem 1rem; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
.table-card tbody tr:last-child td { border-bottom: none; }
.table-card tbody tr:hover { background: #fafbfd; }

/* ── Thumbnail ── */
.art-thumb {
    width: 76px; height: 52px; object-fit: cover;
    border-radius: 7px; display: block; background: #e2e8f0;
}
.art-thumb-empty {
    width: 76px; height: 52px; border-radius: 7px;
    background: linear-gradient(135deg,#334155,#64748b);
    display: flex; align-items: center; justify-content: center;
    color: rgba(255,255,255,.35); font-size: 1.1rem;
}
.art-thumb-yt { position:relative; display:inline-block; }
.art-thumb-yt img { display:block; }

/* ── Title cell ── */
.art-title { font-weight: 700; color: var(--dark); line-height: 1.35;
             display:-webkit-box; -webkit-line-clamp:2; line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;
             max-width: 240px; }
.art-title a { text-decoration:none; color:inherit; }
.art-title a:hover { color: var(--pri); }

/* ── Category badge ── */
.cat-badge {
    display: inline-block; font-size: .68rem; font-weight: 700;
    padding: .18rem .55rem; border-radius: 20px; white-space: nowrap;
    margin-top: .3rem;
    border: 1.5px solid currentColor;
}

/* ── Status pill ── */
.st-pill {
    display: inline-flex; align-items: center; gap: .3rem;
    font-size: .75rem; font-weight: 700; padding: .25rem .65rem;
    border-radius: 20px; white-space: nowrap; cursor: pointer;
    border: none; background: none;
    transition: opacity .15s;
}
.st-pill:hover { opacity: .75; }
.st-pub  { background: #dcfce7; color: #166534; }
.st-dft  { background: #f1f5f9; color: #475569; }
.st-dot  { width:6px; height:6px; border-radius:50%; background:currentColor; }

/* ── Featured switch ── */
.feat-switch { position:relative; display:inline-block; width:38px; height:22px; cursor:pointer; }
.feat-switch input { opacity:0; width:0; height:0; position:absolute; }
.feat-slider {
    position:absolute; top:0; left:0; right:0; bottom:0;
    background:#d1d5db; border-radius:22px; transition:background .2s;
}
.feat-slider::before {
    content:''; position:absolute; width:16px; height:16px;
    background:#fff; border-radius:50%; left:3px; top:3px; transition:transform .2s;
}
.feat-switch input:checked + .feat-slider { background: #d97706; }
.feat-switch input:checked + .feat-slider::before { transform: translateX(16px); }

/* ── Action buttons ── */
.act-btn {
    display: inline-flex; align-items: center; gap: .25rem;
    font-size: .75rem; font-weight: 600; padding: .3rem .6rem;
    border-radius: 6px; border: 1.5px solid #d1d5db;
    background: #fff; color: #374151; cursor: pointer;
    text-decoration: none; white-space: nowrap; font-family: inherit;
}
.act-btn:hover          { border-color: var(--pri); color: var(--pri); }
.act-btn.del:hover      { border-color: #dc2626;   color: #dc2626; }
.act-btn.preview:hover  { border-color: #059669;   color: #059669; }

/* ── Type icon ── */
.type-icon { font-size: .78rem; color: #94a3b8; margin-top:.3rem; }
.type-icon.yt  { color:#dc2626; }
.type-icon.img { color:#2563eb; }

/* ── Views ── */
.views-num { font-weight: 700; color: #374151; }
.views-lbl { font-size:.68rem; color:#94a3b8; }

/* ── Empty state ── */
.empty-state { text-align:center; padding:4rem 2rem; color:#94a3b8; }
.empty-state i { font-size:2.5rem; margin-bottom:.75rem; display:block; }

/* ── Pagination ── */
.pg-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 34px; height: 34px; border-radius: 7px;
    font-size: .82rem; font-weight: 600; text-decoration: none;
    border: 1.5px solid #e2e8f0; background: #fff; color: #374151;
}
.pg-btn:hover  { border-color: var(--pri); color: var(--pri); }
.pg-btn.active { background: var(--pri); border-color: var(--pri); color: #fff; }
.pg-btn.disabled { pointer-events:none; opacity:.4; }

/* ── Delete modal ── */
.del-modal-overlay {
    display:none; position:fixed; inset:0; background:rgba(0,0,0,.45);
    z-index:9999; align-items:center; justify-content:center;
}
.del-modal-overlay.show { display:flex; }
.del-modal {
    background:#fff; border-radius:14px; padding:2rem;
    max-width:380px; width:90%; box-shadow:0 20px 60px rgba(0,0,0,.2);
    text-align:center;
}
.del-modal i { font-size:2.5rem; color:#dc2626; margin-bottom:.75rem; }
.del-modal h5 { font-weight:800; color:#1e293b; margin-bottom:.4rem; }
.del-modal p { color:#64748b; font-size:.88rem; margin-bottom:1.25rem; }
</style>
</head>
<body>

<!-- Header -->
<div class="am-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1><i class="fas fa-newspaper me-2"></i>Press Manager</h1>
                <p>ATMABISWAS Press &amp; Media Center — All Press Posts</p>
            </div>
            <div class="d-flex gap-2">
                <a href="blog_enhanced.php" class="btn btn-light fw-bold btn-sm">
                    <i class="fas fa-plus"></i> New Press Post
                </a>
                <a href="dashboard.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>
                <a href="../../press.php" target="_blank" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-external-link-alt"></i> Newsroom
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container py-4">

<?php if ($db_error): ?>
<div class="alert alert-danger rounded-3 mb-3">
    <i class="fas fa-times-circle me-2"></i><strong>Error:</strong> <?= htmlspecialchars($db_error) ?>
</div>
<?php endif; ?>

<?php if ($auto_migrated): ?>
<div class="alert alert-success alert-dismissible rounded-3 mb-3 fade show">
    <i class="fas fa-check-circle me-2"></i>
    <strong>Database updated.</strong> Added: <code><?= implode('</code>, <code>', $migrated) ?></code>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash_type ?> alert-dismissible rounded-3 mb-3 fade show">
    <i class="fas fa-<?= $flash_type === 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
    <?= htmlspecialchars($flash) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="sc">
            <div class="sc-icon" style="background:#dbeafe;color:#1d4ed8"><i class="fas fa-newspaper"></i></div>
            <div><div class="sc-num"><?= number_format($stats['total']) ?></div><div class="sc-lbl">Total Press / News</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="sc">
            <div class="sc-icon" style="background:#dcfce7;color:#166534"><i class="fas fa-check-circle"></i></div>
            <div><div class="sc-num"><?= number_format($stats['published']) ?></div><div class="sc-lbl">Published</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="sc">
            <div class="sc-icon" style="background:#fef9c3;color:#92400e"><i class="fas fa-pencil-alt"></i></div>
            <div><div class="sc-num"><?= number_format($stats['drafts']) ?></div><div class="sc-lbl">Drafts</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="sc">
            <div class="sc-icon" style="background:#ede9fe;color:#5b21b6"><i class="fas fa-eye"></i></div>
            <div>
                <div class="sc-num"><?= isset($has['views']) ? number_format($stats['views']) : '—' ?></div>
                <div class="sc-lbl">Total Views</div>
            </div>
        </div>
    </div>
</div>

<!-- Filter bar -->
<div class="filter-bar mb-4">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-5">
            <label class="form-label small fw-bold text-muted mb-1">Search</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                <input type="text" class="form-control border-start-0 ps-1" name="search"
                       placeholder="Search by title…"
                       value="<?= htmlspecialchars($search) ?>">
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-bold text-muted mb-1">Status</label>
            <select class="form-select form-select-sm" name="status">
                <option value="">All Status</option>
                <option value="published" <?= $status_f === 'published' ? 'selected' : '' ?>>Published</option>
                <option value="draft"     <?= $status_f === 'draft'     ? 'selected' : '' ?>>Draft</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted mb-1">Category</label>
            <select class="form-select form-select-sm" name="cat">
                <option value="">All Categories</option>
                <?php foreach ($cat_labels as $k => $v): ?>
                <option value="<?= $k ?>" <?= $cat_f === $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm flex-fill">
                <i class="fas fa-filter"></i> Filter
            </button>
            <?php if ($search || $status_f || $cat_f): ?>
            <a href="blog_manager.php" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-times"></i>
            </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Results label -->
<?php if ($search || $status_f || $cat_f): ?>
<p class="text-muted small mb-2">
    Showing <strong><?= $total ?></strong> result<?= $total !== 1 ? 's' : '' ?>
    <?= $search ? ' for "<strong>'.htmlspecialchars($search).'</strong>"' : '' ?>
</p>
<?php endif; ?>

<!-- Press / News table -->
<div class="table-card">
<?php if (empty($posts)): ?>
    <div class="empty-state">
        <i class="far fa-file-alt"></i>
        <h6 class="fw-bold"><?= ($search || $status_f || $cat_f) ? 'No results found' : 'No press posts yet' ?></h6>
        <?php if ($search || $status_f || $cat_f): ?>
            <a href="blog_manager.php" class="text-primary small">Clear filters</a>
        <?php else: ?>
            <a href="blog_enhanced.php" class="btn btn-primary btn-sm mt-2"><i class="fas fa-plus"></i> Publish first press post</a>
        <?php endif; ?>
    </div>
<?php else: ?>
<div class="table-responsive">
<table class="table table-hover mb-0">
<thead>
    <tr>
        <th style="width:90px">Thumbnail</th>
        <th>Title / Category</th>
        <th style="width:110px">Status</th>
        <th style="width:80px">Views</th>
        <th style="width:100px">Date</th>
        <th style="width:80px;text-align:center">Featured</th>
        <th style="width:140px">Actions</th>
    </tr>
</thead>
<tbody>
<?php foreach ($posts as $post):
    $status   = $post['status'] ?? 'published';
    $views    = (int)($post['views'] ?? 0);
    $featured = (int)($post['featured'] ?? 0);
    $date     = !empty($post['upload_date']) ? date('M j, Y', strtotime($post['upload_date'])) : '—';
    $cat      = $post['category'] ?? 'news';
    $cat_lbl  = $cat_labels[$cat] ?? ucfirst($cat);
    $cat_col  = $cat_colors[$cat] ?? '#64748b';
    $thumb    = articleThumb($post);
    $type     = articleType($post);
    $is_pub   = ($status === 'published' || $status === null);
?>
<tr>
    <!-- Thumbnail -->
    <td>
        <?php if ($thumb): ?>
            <img src="<?= htmlspecialchars($thumb) ?>"
                 alt="thumbnail"
                 class="art-thumb"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
            <div class="art-thumb-empty" style="display:none"><i class="far fa-image"></i></div>
        <?php else: ?>
            <div class="art-thumb-empty"><i class="far fa-file-alt"></i></div>
        <?php endif; ?>
    </td>

    <!-- Title / Category -->
    <td>
        <div class="art-title">
            <a href="../../press.php?id=<?= $post['blog_id'] ?>" target="_blank">
                <?= htmlspecialchars($post['blog_title']) ?>
            </a>
        </div>
        <span class="cat-badge" style="color:<?= $cat_col ?>;border-color:<?= $cat_col ?>20;background:<?= $cat_col ?>12">
            <?= htmlspecialchars($cat_lbl) ?>
        </span>
        <div class="type-icon<?= $type === 'youtube' ? ' yt' : ($type === 'image' ? ' img' : '') ?>">
            <?php if ($type === 'youtube'): ?><i class="fab fa-youtube"></i> YouTube
            <?php elseif ($type === 'image'): ?><i class="fas fa-image"></i> Image
            <?php else: ?><i class="fas fa-align-left"></i> Text
            <?php endif; ?>
        </div>
    </td>

    <!-- Status (clickable toggle) -->
    <td>
        <?php if (isset($has['status'])): ?>
        <button class="st-pill <?= $is_pub ? 'st-pub' : 'st-dft' ?>"
                data-id="<?= $post['blog_id'] ?>"
                data-action="status"
                title="Click to toggle status">
            <span class="st-dot"></span>
            <span class="st-text"><?= $is_pub ? 'Published' : 'Draft' ?></span>
        </button>
        <?php else: ?>
        <span class="st-pill st-pub"><span class="st-dot"></span>Published</span>
        <?php endif; ?>
    </td>

    <!-- Views -->
    <td>
        <div class="views-num"><?= number_format($views) ?></div>
        <div class="views-lbl">views</div>
    </td>

    <!-- Date -->
    <td>
        <div style="font-size:.82rem;color:#374151;white-space:nowrap"><?= $date ?></div>
        <div style="font-size:.7rem;color:#94a3b8"><?= !empty($post['upload_date']) ? date('H:i', strtotime($post['upload_date'])) : '' ?></div>
    </td>

    <!-- Featured toggle -->
    <td style="text-align:center">
        <?php if (isset($has['featured'])): ?>
        <label class="feat-switch" title="<?= $featured ? 'Featured — click to unfeature' : 'Click to feature' ?>">
            <input type="checkbox"
                   class="feat-cb"
                   data-id="<?= $post['blog_id'] ?>"
                   <?= $featured ? 'checked' : '' ?>>
            <span class="feat-slider"></span>
        </label>
        <?php else: ?>
        <span class="text-muted">—</span>
        <?php endif; ?>
    </td>

    <!-- Actions -->
    <td>
        <div class="d-flex gap-1 flex-wrap">
            <a href="blog_edit.php?id=<?= $post['blog_id'] ?>"
               class="act-btn" title="Edit">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="../../press.php?id=<?= $post['blog_id'] ?>"
               target="_blank" class="act-btn preview" title="Preview">
                <i class="fas fa-eye"></i>
            </a>
            <button class="act-btn del"
                    onclick="confirmDelete(<?= $post['blog_id'] ?>, '<?= htmlspecialchars(addslashes($post['blog_title']), ENT_QUOTES) ?>')"
                    title="Delete">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<div class="d-flex justify-content-center align-items-center gap-1 mt-4 flex-wrap">
    <a href="?page=<?= max(1,$page-1) ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_f) ?>&cat=<?= urlencode($cat_f) ?>"
       class="pg-btn <?= $page <= 1 ? 'disabled' : '' ?>">‹</a>
    <?php
    $start = max(1, $page - 2);
    $end   = min($total_pages, $page + 2);
    if ($start > 1) echo '<a href="?page=1&search='.urlencode($search).'&status='.urlencode($status_f).'&cat='.urlencode($cat_f).'" class="pg-btn">1</a>';
    if ($start > 2) echo '<span class="pg-btn disabled" style="border:none;background:none">…</span>';
    for ($i = $start; $i <= $end; $i++):
    ?>
    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_f) ?>&cat=<?= urlencode($cat_f) ?>"
       class="pg-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor;
    if ($end < $total_pages - 1) echo '<span class="pg-btn disabled" style="border:none;background:none">…</span>';
    if ($end < $total_pages) echo '<a href="?page='.$total_pages.'&search='.urlencode($search).'&status='.urlencode($status_f).'&cat='.urlencode($cat_f).'" class="pg-btn">'.$total_pages.'</a>';
    ?>
    <a href="?page=<?= min($total_pages,$page+1) ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_f) ?>&cat=<?= urlencode($cat_f) ?>"
       class="pg-btn <?= $page >= $total_pages ? 'disabled' : '' ?>">›</a>
</div>
<p class="text-center text-muted small mt-2">
    Page <?= $page ?> of <?= $total_pages ?> &nbsp;·&nbsp; <?= $total ?> press / news total
</p>
<?php endif; ?>

</div><!-- /container -->

<!-- Delete confirmation modal -->
<div class="del-modal-overlay" id="delModal">
    <div class="del-modal">
        <i class="fas fa-trash-alt"></i>
        <h5>Delete Press / News Post?</h5>
        <p id="delModalMsg">This action cannot be undone.</p>
        <div class="d-flex gap-2 justify-content-center">
            <button class="btn btn-secondary btn-sm" onclick="closeDeleteModal()">Cancel</button>
            <a id="delConfirmBtn" href="#" class="btn btn-danger btn-sm">
                <i class="fas fa-trash"></i> Delete
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ── Delete modal ── */
function confirmDelete(id, title) {
    document.getElementById('delModalMsg').textContent = 'Delete "' + title + '"? This cannot be undone.';
    document.getElementById('delConfirmBtn').href = '?action=delete&id=' + id
        + '&search=<?= urlencode($search) ?>&status=<?= urlencode($status_f) ?>&cat=<?= urlencode($cat_f) ?>&page=<?= $page ?>';
    document.getElementById('delModal').classList.add('show');
}
function closeDeleteModal() {
    document.getElementById('delModal').classList.remove('show');
}
document.getElementById('delModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});

/* ── AJAX: Status toggle ── */
document.querySelectorAll('[data-action="status"]').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const fd = new FormData();
        fd.append('ajax', 'status');
        fd.append('id', id);

        fetch('blog_manager.php', { method:'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (!data.ok) return;
                const isPub = data.val === 'published';
                this.className = 'st-pill ' + (isPub ? 'st-pub' : 'st-dft');
                this.querySelector('.st-text').textContent = isPub ? 'Published' : 'Draft';
            })
            .catch(() => {});
    });
});

/* ── AJAX: Featured toggle ── */
document.querySelectorAll('.feat-cb').forEach(cb => {
    cb.addEventListener('change', function() {
        const id = this.dataset.id;
        const fd = new FormData();
        fd.append('ajax', 'featured');
        fd.append('id', id);
        const el = this;

        fetch('blog_manager.php', { method:'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (!data.ok) {
                    el.checked = !el.checked; // revert on failure
                }
            })
            .catch(() => { el.checked = !el.checked; });
    });
});
</script>
</body>
</html>
