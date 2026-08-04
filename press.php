<?php
session_start();
include 'config.php';
include 'backend/Database/db.php';

/* ── Helpers ──────────────────────────────────────────────────── */

function extractYouTubeId(string $url): string {
    if (empty($url)) return '';
    if (strpos($url, 'youtu.be/') !== false) {
        $path = ltrim(parse_url($url, PHP_URL_PATH) ?? '', '/');
        return preg_replace('/[^a-zA-Z0-9_-]/', '', strtok($path, '?&'));
    }
    if (preg_match('/[?&]v=([a-zA-Z0-9_-]+)/', $url, $m)) return $m[1];
    return '';
}

function getThumbnail(array $item): string {
    if (!empty($item['cover_img'])) return $item['cover_img'];
    $ytId = extractYouTubeId($item['source_link'] ?? '');
    return $ytId ? "https://img.youtube.com/vi/{$ytId}/hqdefault.jpg" : '';
}

function getCategoryLabel(string $cat): string {
    return ['news' => 'News', 'media' => 'Media', 'announcement' => 'Announcement', 'press' => 'Press Release'][$cat] ?? 'News';
}

function getCategoryClass(string $cat): string {
    return in_array($cat, ['news','media','announcement','press']) ? $cat : 'news';
}

function calcReadingTime(string $html): int {
    return max(1, (int)ceil(str_word_count(strip_tags($html)) / 200));
}

function buildListUrl(array $params = []): string {
    $q = array_filter($params, fn($v) => $v !== '' && $v !== 'all' && $v !== null);
    return 'press.php' . ($q ? '?' . http_build_query($q) : '');
}

/* ── Database ─────────────────────────────────────────────────── */

try {
    $db   = new Db();
    $conn = $db->connect();
} catch (Exception $e) {
    $conn = null;
}

/* ── Routing ──────────────────────────────────────────────────── */

$current_article = null;
$blog_id         = null;

if (isset($_GET['id']) && $conn) {
    $blog_id = (int)$_GET['id'];
    $stmt = $conn->prepare(
        "SELECT * FROM blogs WHERE blog_id = ? AND (status = 'published' OR status IS NULL)"
    );
    $stmt->execute([$blog_id]);
    $current_article = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    if ($current_article) {
        // Increment view counter (graceful — views column may or may not exist)
        try {
            $conn->prepare("UPDATE blogs SET views = COALESCE(views,0)+1 WHERE blog_id = ?")
                 ->execute([$blog_id]);
            $current_article['views'] = ($current_article['views'] ?? 0) + 1;
        } catch (PDOException $e) {}
    }

} elseif (isset($_GET['article']) && $conn) {
    // Legacy ?article=N (0-based index) → permanent redirect to stable ?id=
    $idx      = max(0, (int)$_GET['article']);
    $id_stmt  = $conn->prepare(
        "SELECT blog_id FROM blogs WHERE (status='published' OR status IS NULL) ORDER BY upload_date DESC"
    );
    $id_stmt->execute();
    $all_ids = $id_stmt->fetchAll(PDO::FETCH_COLUMN);
    if (isset($all_ids[$idx])) {
        header("Location: press.php?id={$all_ids[$idx]}", true, 301);
        exit();
    }
}

/* ── List-view data ───────────────────────────────────────────── */

$posts              = [];
$total              = 0;
$total_pages        = 0;
$featured           = null;
$available_years    = [];
$available_cats     = [];
$search             = '';
$cat_filter         = 'all';
$year_filter        = 'all';
$page               = 1;

if (!$current_article && $conn) {
    $per_page    = 9;
    $page        = max(1, (int)($_GET['page'] ?? 1));
    $offset      = ($page - 1) * $per_page;
    $search      = trim($_GET['search'] ?? '');
    $cat_filter  = $_GET['cat']  ?? 'all';
    $year_filter = $_GET['year'] ?? 'all';

    $where  = ["(status = 'published' OR status IS NULL)"];
    $params = [];

    if ($search !== '') {
        $where[]  = "(blog_title LIKE ? OR summary LIKE ? OR blog_content LIKE ?)";
        $like     = "%{$search}%";
        $params[] = $like; $params[] = $like; $params[] = $like;
    }
    if ($cat_filter !== 'all' && $cat_filter !== '') {
        $where[]  = "category = ?";
        $params[] = $cat_filter;
    }
    if ($year_filter !== 'all' && $year_filter !== '') {
        $where[]  = "year = ?";
        $params[] = (int)$year_filter;
    }

    $wsql = "WHERE " . implode(" AND ", $where);

    try {
        $count_stmt = $conn->prepare("SELECT COUNT(*) FROM blogs {$wsql}");
        $count_stmt->execute($params);
        $total       = (int)$count_stmt->fetchColumn();
        $total_pages = (int)ceil($total / $per_page);

        $list_stmt = $conn->prepare(
            "SELECT * FROM blogs {$wsql} ORDER BY upload_date DESC LIMIT {$per_page} OFFSET {$offset}"
        );
        $list_stmt->execute($params);
        $posts = $list_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Featured article (first page, no filters active)
        if ($page === 1 && $search === '' && $cat_filter === 'all' && $year_filter === 'all') {
            try {
                $feat_stmt = $conn->prepare(
                    "SELECT * FROM blogs WHERE featured = 1 AND (status='published' OR status IS NULL)
                     ORDER BY upload_date DESC LIMIT 1"
                );
                $feat_stmt->execute();
                $featured = $feat_stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            } catch (PDOException $e) {}
            if (!$featured && !empty($posts)) $featured = $posts[0];
        }

        // Filter options
        $yrs = $conn->prepare(
            "SELECT DISTINCT year FROM blogs WHERE year IS NOT NULL AND (status='published' OR status IS NULL)
             ORDER BY year DESC"
        );
        $yrs->execute();
        $available_years = $yrs->fetchAll(PDO::FETCH_COLUMN);

        $cats = $conn->prepare(
            "SELECT DISTINCT category FROM blogs WHERE category IS NOT NULL AND (status='published' OR status IS NULL)"
        );
        $cats->execute();
        $available_cats = $cats->fetchAll(PDO::FETCH_COLUMN);

    } catch (PDOException $e) {
        $posts = [];
    }
}

/* ── Related articles ─────────────────────────────────────────── */

$related = [];
if ($current_article && $conn) {
    try {
        $cat     = $current_article['category'] ?? '';
        $rel_sql = "SELECT * FROM blogs WHERE blog_id != ? AND (status='published' OR status IS NULL)
                    ORDER BY " . ($cat ? "CASE WHEN category = '{$cat}' THEN 0 ELSE 1 END, " : '')
                    . "upload_date DESC LIMIT 3";
        $rel_stmt = $conn->prepare($rel_sql);
        $rel_stmt->execute([$current_article['blog_id']]);
        $related = $rel_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {}
}

/* ── Page metadata ────────────────────────────────────────────── */

$page_title = 'ATMABISWAS Newsroom — News & Media Center';
if ($current_article) {
    $seo_title = !empty($current_article['seo_title'])
        ? $current_article['seo_title']
        : $current_article['blog_title'];
    $page_title = htmlspecialchars($seo_title) . ' — ATMABISWAS';
}

$article_url = $current_article
    ? 'https://atmabiswas.org/press.php?id=' . ($current_article['blog_id'] ?? '')
    : 'https://atmabiswas.org/press.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <?php if ($current_article): ?>
    <?php
        $og_desc  = !empty($current_article['seo_description'])
            ? $current_article['seo_description']
            : mb_substr(strip_tags($current_article['summary'] ?? $current_article['blog_content'] ?? ''), 0, 160);
        $og_img   = !empty($current_article['social_image'])
            ? $current_article['social_image']
            : getThumbnail($current_article);
        $og_img   = $og_img ? 'https://atmabiswas.org/' . ltrim($og_img, '/') : 'https://atmabiswas.org/LOGO/NGO_logo_monogram.png';
    ?>
    <meta name="description" content="<?= htmlspecialchars($og_desc) ?>">
    <?php if (!empty($current_article['seo_keywords'])): ?>
    <meta name="keywords"    content="<?= htmlspecialchars($current_article['seo_keywords']) ?>">
    <?php endif; ?>
    <meta property="og:type"        content="article">
    <meta property="og:title"       content="<?= htmlspecialchars($seo_title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($og_desc) ?>">
    <meta property="og:image"       content="<?= $og_img ?>">
    <meta property="og:url"         content="<?= $article_url ?>">
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="<?= htmlspecialchars($seo_title) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($og_desc) ?>">
    <meta name="twitter:image"       content="<?= $og_img ?>">
    <link rel="canonical" href="<?= $article_url ?>">
    <?php else: ?>
    <?php include 'seo.php'; ?>
    <?php endif; ?>
    <link rel="icon" type="image/png" href="LOGO/NGO_logo_monogram.png">
    <link rel="stylesheet" href="press.css?v=<?php echo filemtime(__DIR__ . '/press.css'); ?>">
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-EZVV9DWWY7"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', 'G-EZVV9DWWY7');
    </script>
</head>
<body>

<?php include 'Navbar.php'; ?>

<main>
<?php if ($current_article):
/* ═══════════════════════════════════════════════════════════════
   ARTICLE VIEW
   ═══════════════════════════════════════════════════════════════ */
    $cat_label   = getCategoryLabel($current_article['category'] ?? 'news');
    $cat_class   = getCategoryClass($current_article['category'] ?? 'news');
    $yt_id       = extractYouTubeId($current_article['source_link'] ?? '');
    $thumb       = getThumbnail($current_article);
    $date_long   = !empty($current_article['upload_date'])
                    ? date('F j, Y', strtotime($current_article['upload_date'])) : '';
    $read_time   = !empty($current_article['reading_time'])
                    ? (int)$current_article['reading_time']
                    : calcReadingTime($current_article['blog_content'] ?? '');
    $views       = number_format((int)($current_article['views'] ?? 0));
    $tags        = !empty($current_article['tags'])
                    ? array_filter(array_map('trim', explode(',', $current_article['tags'])))
                    : [];
?>

<!-- Breadcrumb -->
<div class="pr-breadcrumb-bar">
    <nav class="pr-breadcrumb" aria-label="Breadcrumb">
        <a href="index.php"><i class="fas fa-home"></i> Home</a>
        <i class="fas fa-chevron-right"></i>
        <a href="press.php">News &amp; Media</a>
        <i class="fas fa-chevron-right"></i>
        <span><?= htmlspecialchars(mb_substr($current_article['blog_title'], 0, 60)) ?><?= mb_strlen($current_article['blog_title']) > 60 ? '…' : '' ?></span>
    </nav>
</div>

<!-- Article Hero -->
<div class="pr-hero pr-article-hero">
    <div class="pr-article-hero-inner">
        <a href="press.php" class="pr-back-btn">
            <i class="fas fa-arrow-left"></i> Back to Newsroom
        </a>
        <div>
            <span class="pr-article-cat"><?= htmlspecialchars($cat_label) ?></span>
        </div>
        <h1><?= htmlspecialchars($current_article['blog_title']) ?></h1>
        <div class="pr-article-meta">
            <?php if ($date_long): ?>
            <span><i class="far fa-calendar-alt"></i> <?= $date_long ?></span>
            <?php endif; ?>
            <?php if (!empty($current_article['blog_author'])): ?>
            <span><i class="far fa-user"></i> <?= htmlspecialchars($current_article['blog_author']) ?></span>
            <?php endif; ?>
            <span><i class="far fa-clock"></i> <?= $read_time ?> min read</span>
            <span><i class="far fa-eye"></i> <?= $views ?> views</span>
        </div>
    </div>
</div>

<!-- Article Content -->
<div class="pr-article-wrap">

    <?php if ($yt_id): ?>
    <div class="pr-article-video">
        <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($yt_id) ?>"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen></iframe>
    </div>
    <?php elseif (!empty($current_article['cover_img'])): ?>
    <img class="pr-article-img"
         src="<?= htmlspecialchars($current_article['cover_img']) ?>"
         alt="<?= htmlspecialchars($current_article['image_title'] ?? $current_article['blog_title']) ?>">
    <?php endif; ?>

    <div class="pr-article-body">
        <?= $current_article['blog_content'] ?>
    </div>

    <?php if (!empty($tags)): ?>
    <div class="pr-tags">
        <span class="pr-tags-label"><i class="fas fa-tags"></i> Tags:</span>
        <?php foreach ($tags as $tag): ?>
        <a href="press.php?search=<?= urlencode($tag) ?>" class="pr-tag"><?= htmlspecialchars($tag) ?></a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Social Share -->
    <?php
        $encoded_url   = urlencode($article_url);
        $encoded_title = urlencode($current_article['blog_title']);
    ?>
    <div class="pr-share">
        <span class="pr-share-label">Share:</span>
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $encoded_url ?>"
           target="_blank" rel="noopener" class="pr-share-btn pr-share-fb">
            <i class="fab fa-facebook-f"></i> Facebook
        </a>
        <a href="https://twitter.com/intent/tweet?url=<?= $encoded_url ?>&text=<?= $encoded_title ?>"
           target="_blank" rel="noopener" class="pr-share-btn pr-share-tw">
            <i class="fab fa-x-twitter"></i> X
        </a>
        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $encoded_url ?>"
           target="_blank" rel="noopener" class="pr-share-btn pr-share-li">
            <i class="fab fa-linkedin-in"></i> LinkedIn
        </a>
        <a href="https://wa.me/?text=<?= $encoded_title ?>%20<?= $encoded_url ?>"
           target="_blank" rel="noopener" class="pr-share-btn pr-share-wa">
            <i class="fab fa-whatsapp"></i> WhatsApp
        </a>
        <button class="pr-share-btn pr-share-copy" id="copyLinkBtn" onclick="copyArticleLink()">
            <i class="fas fa-link"></i> Copy Link
        </button>
        <button class="pr-share-btn pr-share-print" onclick="window.print()">
            <i class="fas fa-print"></i> Print
        </button>
    </div>

    <?php if (!empty($related)): ?>
    <div class="pr-related">
        <div class="pr-related-heading">More Press Updates</div>
        <div class="pr-related-grid">
            <?php foreach ($related as $rel):
                $rel_thumb = $rel['cover_img'] ?? '';
                $rel_cat   = getCategoryClass($rel['category'] ?? 'news');
                $rel_lbl   = getCategoryLabel($rel['category'] ?? 'news');
                $rel_date  = !empty($rel['upload_date']) ? date('M j, Y', strtotime($rel['upload_date'])) : '';
                $rel_time  = calcReadingTime($rel['blog_content'] ?? '');
            ?>
            <a href="press.php?id=<?= $rel['blog_id'] ?>" class="pr-card-link">
                <div class="pr-card">
                    <div class="pr-card-media">
                        <?php if ($rel_thumb): ?>
                        <img src="<?= htmlspecialchars($rel_thumb) ?>"
                             alt="<?= htmlspecialchars($rel['blog_title']) ?>" loading="lazy">
                        <?php else: ?>
                        <div class="pr-card-media-empty"><i class="far fa-newspaper"></i></div>
                        <?php endif; ?>
                    </div>
                    <div class="pr-card-body">
                        <div class="pr-card-top">
                            <span class="pr-cat pr-cat--<?= $rel_cat ?>"><?= $rel_lbl ?></span>
                            <span class="pr-card-date"><?= $rel_date ?></span>
                        </div>
                        <div class="pr-card-title"><?= htmlspecialchars($rel['blog_title']) ?></div>
                        <div class="pr-card-foot">
                            <span class="pr-read-time"><i class="far fa-clock"></i> <?= $rel_time ?> min</span>
                            <span class="pr-read-more">Read <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- Article JSON-LD Schema -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": <?= json_encode($current_article['blog_title']) ?>,
  "description": <?= json_encode(mb_substr(strip_tags($current_article['summary'] ?? $current_article['blog_content'] ?? ''), 0, 200)) ?>,
  <?php if ($thumb): ?>"image": <?= json_encode('https://atmabiswas.org/' . ltrim($thumb, '/')) ?>,<?php endif; ?>
  "datePublished": <?= json_encode(!empty($current_article['upload_date']) ? date('Y-m-d', strtotime($current_article['upload_date'])) : '') ?>,
  "dateModified": <?= json_encode(!empty($current_article['last_updated']) ? date('Y-m-d', strtotime($current_article['last_updated'])) : (!empty($current_article['upload_date']) ? date('Y-m-d', strtotime($current_article['upload_date'])) : '')) ?>,
  "author": {
    "@type": "Person",
    "name": <?= json_encode($current_article['blog_author'] ?? 'ATMABISWAS') ?>
  },
  "publisher": { "@id": "https://atmabiswas.org/#organization" },
  "url": <?= json_encode($article_url) ?>,
  "mainEntityOfPage": { "@type": "WebPage", "@id": <?= json_encode($article_url) ?> },
  "articleSection": <?= json_encode(getCategoryLabel($current_article['category'] ?? 'news')) ?>
}
</script>

<script>
function copyArticleLink() {
    var btn = document.getElementById('copyLinkBtn');
    if (!btn) return;
    navigator.clipboard.writeText(window.location.href).then(function () {
        var orig = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        setTimeout(function () { btn.innerHTML = orig; }, 2200);
    }).catch(function () {
        // Fallback for older browsers
        var ta = document.createElement('textarea');
        ta.value = window.location.href;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        setTimeout(function () { btn.innerHTML = '<i class="fas fa-link"></i> Copy Link'; }, 2200);
    });
}
</script>

<?php else:
/* ═══════════════════════════════════════════════════════════════
   LIST VIEW
   ═══════════════════════════════════════════════════════════════ */
?>

<!-- Hero -->
<div class="pr-hero">
    <h1>Press &amp; Media</h1>
    <p>Latest news, media coverage, announcements, and updates from ATMABISWAS.</p>
    <form class="pr-search" action="press.php" method="GET">
        <div class="pr-search-wrap">
            <i class="fas fa-search pr-search-icon"></i>
            <input type="text" name="search"
                   placeholder="Search press posts, announcements…"
                   value="<?= htmlspecialchars($search) ?>"
                   autocomplete="off">
            <?php if ($search): ?>
            <a href="press.php" class="pr-search-clear" title="Clear search"><i class="fas fa-times"></i></a>
            <?php endif; ?>
            <button type="submit">Search</button>
        </div>
    </form>
</div>

<?php if ($search !== '' || $cat_filter !== 'all' || $year_filter !== 'all'): ?>
<div class="pr-search-notice">
    <?php if ($search !== ''): ?>
    Showing <strong><?= $total ?></strong> result<?= $total !== 1 ? 's' : '' ?> for
    "<strong><?= htmlspecialchars($search) ?></strong>"
    <a href="press.php"><i class="fas fa-times-circle"></i> Clear all filters</a>
    <?php else: ?>
    Showing <strong><?= $total ?></strong> press post<?= $total !== 1 ? 's' : '' ?> —
    <a href="press.php">View all</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Featured Article -->
<?php if ($featured): ?>
<?php
    $feat_thumb   = $featured['cover_img'] ?? '';
    $feat_cat_lbl = getCategoryLabel($featured['category'] ?? 'news');
    $feat_cat_cls = getCategoryClass($featured['category'] ?? 'news');
    $feat_date    = !empty($featured['upload_date']) ? date('F j, Y', strtotime($featured['upload_date'])) : '';
    $feat_time    = calcReadingTime($featured['blog_content'] ?? '');
    $feat_excerpt = !empty($featured['summary'])
        ? mb_substr(strip_tags($featured['summary']), 0, 200)
        : mb_substr(strip_tags($featured['blog_content'] ?? ''), 0, 200);
?>
<div class="pr-featured-wrap">
    <a href="press.php?id=<?= $featured['blog_id'] ?>" class="pr-featured">
        <div class="pr-featured-img">
            <?php if ($feat_thumb): ?>
            <img src="<?= htmlspecialchars($feat_thumb) ?>"
                 alt="<?= htmlspecialchars($featured['blog_title']) ?>">
            <?php else: ?>
            <div class="pr-featured-img-empty"><i class="far fa-newspaper"></i></div>
            <?php endif; ?>
            <span class="pr-feat-badge">
                <?php if (!empty($featured['featured']) && $featured['featured'] == 1): ?>
                <i class="fas fa-star"></i> Featured
                <?php else: ?>
                Latest
                <?php endif; ?>
            </span>
        </div>
        <div class="pr-featured-body">
            <span class="pr-cat pr-cat--<?= $feat_cat_cls ?>"><?= $feat_cat_lbl ?></span>
            <h2 class="pr-featured-title"><?= htmlspecialchars($featured['blog_title']) ?></h2>
            <?php if ($feat_excerpt): ?>
            <p class="pr-featured-excerpt"><?= htmlspecialchars($feat_excerpt) ?>…</p>
            <?php endif; ?>
            <div class="pr-featured-meta">
                <?php if ($feat_date): ?>
                <span><i class="far fa-calendar-alt"></i> <?= $feat_date ?></span>
                <?php endif; ?>
                <?php if (!empty($featured['blog_author'])): ?>
                <span><i class="far fa-user"></i> <?= htmlspecialchars($featured['blog_author']) ?></span>
                <?php endif; ?>
                <span><i class="far fa-clock"></i> <?= $feat_time ?> min read</span>
            </div>
            <span class="pr-featured-cta">Read Article <i class="fas fa-arrow-right"></i></span>
        </div>
    </a>
</div>
<?php endif; ?>

<!-- Main Content -->
<div class="pr-main">

    <!-- Filter Bar -->
    <?php if (!empty($posts) || $cat_filter !== 'all' || $year_filter !== 'all'): ?>
    <div class="pr-filters">
        <?php if (count($available_cats) > 1): ?>
        <div class="pr-filter-group">
            <span class="pr-filter-label">Category</span>
            <div class="pr-filter-pills" id="catPills">
                <a href="<?= buildListUrl(['year'=>$year_filter,'search'=>$search]) ?>"
                   class="pr-pill <?= $cat_filter === 'all' ? 'active' : '' ?>">All</a>
                <?php foreach ($available_cats as $cat):
                    $lbl = getCategoryLabel($cat);
                ?>
                <a href="<?= buildListUrl(['cat'=>$cat,'year'=>$year_filter,'search'=>$search]) ?>"
                   class="pr-pill <?= $cat_filter === $cat ? 'active' : '' ?>"><?= htmlspecialchars($lbl) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php if (!empty($available_years)): ?>
        <div class="pr-filter-group">
            <span class="pr-filter-label">Year</span>
            <div class="pr-filter-pills">
                <a href="<?= buildListUrl(['cat'=>$cat_filter,'search'=>$search]) ?>"
                   class="pr-pill <?= $year_filter === 'all' ? 'active' : '' ?>">All</a>
                <?php foreach ($available_years as $yr): ?>
                <a href="<?= buildListUrl(['cat'=>$cat_filter,'year'=>$yr,'search'=>$search]) ?>"
                   class="pr-pill <?= (string)$year_filter === (string)$yr ? 'active' : '' ?>"><?= (int)$yr ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Grid -->
    <?php if (empty($posts)): ?>
    <div class="pr-empty">
        <i class="far fa-newspaper"></i>
        <?php if ($search !== '' || $cat_filter !== 'all' || $year_filter !== 'all'): ?>
        <p>No articles match your search. <a href="press.php" style="color:var(--primary)">Clear filters</a></p>
        <?php else: ?>
        <p>No articles published yet. Check back soon.</p>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="pr-grid">
        <?php foreach ($posts as $post):
            $thumb     = $post['cover_img'] ?? '';
            $cat_cls   = getCategoryClass($post['category'] ?? 'news');
            $cat_lbl   = getCategoryLabel($post['category'] ?? 'news');
            $date_fmt  = !empty($post['upload_date']) ? date('M j, Y', strtotime($post['upload_date'])) : '';
            $read_t    = calcReadingTime($post['blog_content'] ?? '');
            $excerpt   = !empty($post['summary'])
                ? mb_substr(strip_tags($post['summary']), 0, 130)
                : mb_substr(strip_tags($post['blog_content'] ?? ''), 0, 130);
        ?>
        <a href="press.php?id=<?= $post['blog_id'] ?>" class="pr-card-link">
            <article class="pr-card">
                <div class="pr-card-media">
                    <?php if ($thumb): ?>
                    <img src="<?= htmlspecialchars($thumb) ?>"
                         alt="<?= htmlspecialchars($post['blog_title']) ?>"
                         loading="lazy">
                    <?php else: ?>
                    <div class="pr-card-media-empty"><i class="far fa-newspaper"></i></div>
                    <?php endif; ?>
                </div>
                <div class="pr-card-body">
                    <div class="pr-card-top">
                        <span class="pr-cat pr-cat--<?= $cat_cls ?>"><?= $cat_lbl ?></span>
                        <?php if ($date_fmt): ?>
                        <span class="pr-card-date"><?= $date_fmt ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="pr-card-title"><?= htmlspecialchars($post['blog_title']) ?></div>
                    <?php if ($excerpt): ?>
                    <p class="pr-card-excerpt"><?= htmlspecialchars($excerpt) ?></p>
                    <?php endif; ?>
                    <div class="pr-card-foot">
                        <span class="pr-read-time"><i class="far fa-clock"></i> <?= $read_t ?> min read</span>
                        <span class="pr-read-more">Read Press <i class="fas fa-arrow-right"></i></span>
                    </div>
                </div>
            </article>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <nav class="pr-pagination" aria-label="Articles pagination">
        <?php
            $base = buildListUrl(['cat'=>$cat_filter,'year'=>$year_filter,'search'=>$search]);
            $sep  = strpos($base, '?') !== false ? '&' : '?';
        ?>
        <?php if ($page > 1): ?>
        <a href="<?= $base . $sep ?>page=<?= $page - 1 ?>" class="pr-page-btn">
            <i class="fas fa-chevron-left"></i> Prev
        </a>
        <?php endif; ?>
        <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
        <a href="<?= $base . $sep ?>page=<?= $i ?>"
           class="pr-page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $total_pages): ?>
        <a href="<?= $base . $sep ?>page=<?= $page + 1 ?>" class="pr-page-btn">
            Next <i class="fas fa-chevron-right"></i>
        </a>
        <?php endif; ?>
    </nav>
    <?php endif; ?>
    <?php endif; ?>

</div>
<?php endif; ?>

</main>
<?php include 'footer.php'; ?>
</body>
</html>
