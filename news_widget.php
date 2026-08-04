<?php
/**
 * ATMABISWAS News Widget — Homepage Integration
 * Usage: <?php include 'news_widget.php'; ?>
 *
 * Self-contained: opens its own DB connection if one isn't available.
 * Shows 3 latest published articles with the press.php card style.
 */

// Open a dedicated connection so the widget doesn't conflict with
// any other variable names on the host page.
$_nw_posts = [];
try {
    if (!class_exists('Db')) {
        include __DIR__ . '/backend/Database/db.php';
    }
    $_nw_db   = new Db();
    $_nw_conn = $_nw_db->connect();
    $_nw_stmt = $_nw_conn->prepare(
        "SELECT blog_id, blog_title, summary, blog_content, category,
                cover_img, source_link, upload_date, blog_author
         FROM blogs
         WHERE (status = 'published' OR status IS NULL)
         ORDER BY upload_date DESC
         LIMIT 3"
    );
    $_nw_stmt->execute();
    $_nw_posts = $_nw_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $_nw_posts = [];
}

if (empty($_nw_posts)) return;

/* Helper: YouTube ID from URL */
function _nw_yt_id(string $url): string {
    if (strpos($url, 'youtu.be/') !== false) {
        $path = ltrim(parse_url($url, PHP_URL_PATH) ?? '', '/');
        return preg_replace('/[^a-zA-Z0-9_-]/', '', strtok($path, '?&'));
    }
    if (preg_match('/[?&]v=([a-zA-Z0-9_-]+)/', $url, $m)) return $m[1];
    return '';
}

function _nw_thumb(array $p): string {
    if (!empty($p['cover_img'])) return $p['cover_img'];
    $id = _nw_yt_id($p['source_link'] ?? '');
    return $id ? "https://img.youtube.com/vi/{$id}/hqdefault.jpg" : '';
}
?>

<?php if (!defined('ATMA_NW_CSS')): define('ATMA_NW_CSS', true); ?>
<style>
/* ── ATMABISWAS News Widget ──────────────────────────────────── */
.nw-section {
    padding: 3.5rem 1.5rem;
    background: #f5f7fa;
}
.nw-wrap { max-width: 1160px; margin: 0 auto; }
.nw-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 1.75rem;
    flex-wrap: wrap;
    gap: .75rem;
}
.nw-section-label {
    display: block;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: #0073e6;
    margin-bottom: .35rem;
}
.nw-title {
    font-size: 1.65rem;
    font-weight: 800;
    color: #1e3a5f;
    line-height: 1.2;
}
.nw-view-all {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    background: #0073e6;
    color: #fff;
    font-size: .85rem;
    font-weight: 700;
    padding: .55rem 1.2rem;
    border-radius: 6px;
    text-decoration: none;
    white-space: nowrap;
    flex-shrink: 0;
}
.nw-view-all:hover { background: #005bb5; }
.nw-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.25rem;
}
.nw-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,.08);
    overflow: hidden;
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
}
.nw-card-media {
    aspect-ratio: 16/9;
    overflow: hidden;
    background: #1e3a5f;
    flex-shrink: 0;
}
.nw-card-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.nw-card-media-empty {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #1e3a5f, #0073e6);
    display: flex;
    align-items: center;
    justify-content: center;
}
.nw-card-media-empty i { font-size: 2rem; color: rgba(255,255,255,.2); }
.nw-card-body {
    padding: 1.1rem 1.2rem 1.3rem;
    display: flex;
    flex-direction: column;
    flex: 1;
}
.nw-card-date  { font-size: .76rem; color: #9ca3af; margin-bottom: .3rem; }
.nw-card-title {
    font-size: .97rem;
    font-weight: 700;
    color: #1e3a5f;
    line-height: 1.4;
    margin-bottom: .5rem;
}
.nw-card-excerpt {
    font-size: .84rem;
    color: #6b7280;
    line-height: 1.55;
    flex: 1;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin-bottom: .8rem;
}
.nw-read-more {
    font-size: .8rem;
    font-weight: 700;
    color: #0073e6;
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    margin-top: auto;
}
@media (max-width: 1024px) { .nw-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 600px)  { .nw-grid { grid-template-columns: 1fr; } }
</style>
<?php endif; ?>

<section class="nw-section" aria-label="Latest News">
    <div class="nw-wrap">
        <div class="nw-header">
            <div>
                <span class="nw-section-label">ATMABISWAS Newsroom</span>
                <h2 class="nw-title">Latest News &amp; Updates</h2>
            </div>
            <a href="press.php" class="nw-view-all">
                View All News <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="nw-grid">
            <?php foreach ($_nw_posts as $_p):
                $_thumb   = _nw_thumb($_p);
                $_date    = !empty($_p['upload_date']) ? date('M j, Y', strtotime($_p['upload_date'])) : '';
                $_excerpt = !empty($_p['summary'])
                    ? mb_substr(strip_tags($_p['summary']), 0, 120)
                    : mb_substr(strip_tags($_p['blog_content'] ?? ''), 0, 120);
            ?>
            <a href="press.php?id=<?= $_p['blog_id'] ?>" class="nw-card">
                <div class="nw-card-media">
                    <?php if ($_thumb): ?>
                    <img src="<?= htmlspecialchars($_thumb) ?>"
                         alt="<?= htmlspecialchars($_p['blog_title']) ?>"
                         loading="lazy">
                    <?php else: ?>
                    <div class="nw-card-media-empty"><i class="far fa-newspaper"></i></div>
                    <?php endif; ?>
                </div>
                <div class="nw-card-body">
                    <div class="nw-card-date"><?= $_date ?></div>
                    <div class="nw-card-title"><?= htmlspecialchars($_p['blog_title']) ?></div>
                    <?php if ($_excerpt): ?>
                    <div class="nw-card-excerpt"><?= htmlspecialchars($_excerpt) ?>…</div>
                    <?php endif; ?>
                    <span class="nw-read-more">Read More <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
