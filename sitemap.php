<?php
/**
 * sitemap.xml, generated on request.
 *
 * The sitemap used to be a hand-written file. It listed the twenty static
 * pages and nothing else, so no press article had ever appeared in it, and its
 * newest lastmod was three months stale -- telling Google the site had not
 * changed since July while posts were being published. Publishing more simply
 * did not register.
 *
 * Static pages keep the priorities and change frequencies the previous file
 * declared. Press articles are appended from the database, so a post published
 * today is in the sitemap the moment Google next fetches it, with a real
 * lastmod taken from the row rather than a date somebody remembered to edit.
 *
 * Served at /sitemap.xml through a rewrite, so the URL Google already knows and
 * robots.txt already declares does not change.
 */
require_once __DIR__ . '/backend/Database/db.php';

header('Content-Type: application/xml; charset=UTF-8');

const SITEMAP_BASE = 'https://atmabiswas.org';

/** Static pages: path => [changefreq, priority] — as previously declared. */
const SITEMAP_STATIC = [
    '/'                     => ['weekly',  '1.0'],
    '/aboutus.php'          => ['monthly', '0.9'],
    '/contact.php'          => ['monthly', '0.8'],
    '/career.php'           => ['weekly',  '0.8'],
    '/press.php'            => ['weekly',  '0.8'],
    '/health.php'           => ['monthly', '0.8'],
    '/Green_Energy.php'     => ['monthly', '0.8'],
    '/enterprice.php'       => ['monthly', '0.8'],
    '/Agricultural.php'     => ['monthly', '0.8'],
    '/readytoeat.php'       => ['monthly', '0.7'],
    '/Events.php'           => ['weekly',  '0.7'],
    '/notice.php'           => ['weekly',  '0.7'],
    '/social.php'           => ['monthly', '0.7'],
    '/storelocation.php'    => ['monthly', '0.6'],
    '/loc.php'              => ['monthly', '0.6'],
    '/generalbody.php'      => ['yearly',  '0.6'],
    '/SeniorManagement.php' => ['yearly',  '0.6'],
    '/OurTeam.php'          => ['yearly',  '0.6'],
    '/eve.php'              => ['yearly',  '0.6'],
    '/founder.php'          => ['yearly',  '0.6'],
];

/** Newest published article date, so the listing pages carry a true lastmod. */
$newestPost = null;
$articles   = [];

try {
    $conn = (new Db())->connect();
    if ($conn) {
        $cols = array_flip($conn->query("SHOW COLUMNS FROM blogs")->fetchAll(PDO::FETCH_COLUMN));
        // last_updated where the install has it, upload_date otherwise.
        $dateCol = isset($cols['last_updated']) ? 'last_updated' : 'upload_date';
        $stmt = $conn->query(
            "SELECT blog_id, `$dateCol` AS changed
               FROM blogs
              WHERE status = 'published' OR status IS NULL
              ORDER BY `$dateCol` DESC"
        );
        foreach ($stmt as $row) {
            $date = !empty($row['changed']) ? date('Y-m-d', strtotime($row['changed'])) : date('Y-m-d');
            $articles[] = ['id' => (int) $row['blog_id'], 'lastmod' => $date];
            if ($newestPost === null) {
                $newestPost = $date;
            }
        }
    }
} catch (Throwable $e) {
    // A sitemap listing only the static pages is still a valid sitemap; an
    // error page in its place is not, and Google would treat it as a fetch
    // failure for the whole site.
    error_log('sitemap: article query failed: ' . $e->getMessage());
}

$today = date('Y-m-d');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

foreach (SITEMAP_STATIC as $path => [$freq, $priority]) {
    // The homepage and the newsroom both change whenever a post is published.
    $lastmod = in_array($path, ['/', '/press.php'], true) && $newestPost
        ? $newestPost
        : $today;
    printf(
        "  <url>\n    <loc>%s</loc>\n    <lastmod>%s</lastmod>\n    <changefreq>%s</changefreq>\n    <priority>%s</priority>\n  </url>\n",
        htmlspecialchars(SITEMAP_BASE . $path, ENT_XML1, 'UTF-8'),
        $lastmod,
        $freq,
        $priority
    );
}

foreach ($articles as $a) {
    printf(
        "  <url>\n    <loc>%s</loc>\n    <lastmod>%s</lastmod>\n    <changefreq>monthly</changefreq>\n    <priority>0.7</priority>\n  </url>\n",
        htmlspecialchars(SITEMAP_BASE . '/press.php?id=' . $a['id'], ENT_XML1, 'UTF-8'),
        $a['lastmod']
    );
}

echo '</urlset>' . "\n";
