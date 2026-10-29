<?php
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Server-side sanitizer for blog/press rich-text HTML (summary + content).
 * Client-side stripping in the editor is a UX convenience only — this is
 * the actual security boundary, since a request can bypass the browser
 * entirely (curl, modified fetch, disabled JS).
 */
function sanitize_blog_html(string $dirty): string
{
    $config = HTMLPurifier_Config::createDefault();
    $config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
    $config->set('Core.Encoding', 'UTF-8');

    // This HTMLPurifier version's bundled definition has no HTML5 module,
    // so figure/figcaption/mark/input aren't available — captions and
    // checklists below are built from span/div/ul/li with CSS classes
    // instead of those tags.
    $config->set('HTML.Allowed',
        'h1,h2,h3,h4,h5,h6,p,br,hr,' .
        'strong,b,em,i,u,s,strike,sup,sub,span[class],div[class],' .
        'a[href|title|target|rel],' .
        'ul[class],ol,li[class],blockquote,pre[class],code[class],' .
        'table,thead,tbody,tr,th[colspan|rowspan],td[colspan|rowspan],' .
        'img[src|alt|title|width|height|style|class]'
    );

    // Inline styles are how TinyMCE encodes font family/size/color and
    // alignment — allow only the specific properties the editor emits.
    $config->set('CSS.AllowedProperties', [
        'color', 'background-color',
        'text-align', 'text-decoration',
        'font-family', 'font-size', 'font-weight', 'font-style',
        'width', 'height', 'max-width',
    ]);

    $config->set('Attr.AllowedFrameTargets', ['_blank']);
    $config->set('Attr.EnableID', false);
    // http(s)/mailto only — no "data:" URIs, closing off the inline-base64
    // image path the old editor used to accept.
    $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
    $config->set('HTML.SafeIframe', false);

    $purifier = new HTMLPurifier($config);
    return $purifier->purify($dirty);
}

/* ── Press input validation (shared by create and edit) ─────────────────
   Lengths used to be measured with strlen(), which counts BYTES. Bangla takes
   three bytes per character in UTF-8, so a 74-word Bangla summary (540
   characters) failed the "max 1,000 characters" rule and an 86-character
   Bangla title failed "max 255" -- posts that looked fine in the editor were
   refused, and the editor only ever showed "An error occurred". Limits below
   are counted the way an editor reads them: characters, not bytes. */

const PRESS_TITLE_MAX_CHARS   = 255;    // blogs.blog_title is VARCHAR(255), counted in characters
const PRESS_SUMMARY_MAX_CHARS = 1000;   // visible text, markup excluded
const PRESS_TEXT_COLUMN_BYTES = 65535;  // blogs.summary / blog_content are TEXT, counted in bytes

/** Visible length of rich-text HTML: tags stripped, entities decoded, in characters. */
function press_visible_length(string $html): int
{
    return mb_strlen(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'), 'UTF-8');
}

/**
 * The first problem with a Press post's title, summary and content (already
 * sanitized), as a message the editor can show, or null when all three are fine.
 */
function press_validate_text(string $title, string $summary, string $content): ?string
{
    if ($title === '')                        return 'Press title is required.';
    if (press_visible_length($summary) === 0) return 'Press summary is required.';
    if (press_visible_length($content) === 0 && stripos($content, '<img') === false) {
        return 'Press content is required.';
    }

    $titleLen = mb_strlen($title, 'UTF-8');
    if ($titleLen > PRESS_TITLE_MAX_CHARS) {
        return "Press title is too long ({$titleLen} characters; the limit is " . PRESS_TITLE_MAX_CHARS . ').';
    }

    $summaryLen = press_visible_length($summary);
    if ($summaryLen > PRESS_SUMMARY_MAX_CHARS) {
        return "Press summary is too long ({$summaryLen} characters; the limit is "
            . number_format(PRESS_SUMMARY_MAX_CHARS) . '). Shorten it and try again.';
    }
    if (strlen($summary) > PRESS_TEXT_COLUMN_BYTES) {
        return 'Press summary has too much formatting to save. Remove some formatting and try again.';
    }

    if (strlen($content) > PRESS_TEXT_COLUMN_BYTES) {
        return sprintf(
            'Press content is too long to save (%d KB; the limit is 64 KB). Shorten it or split it into two posts.',
            (int) ceil(strlen($content) / 1024)
        );
    }

    return null;
}

/**
 * Validate an optional link to a post on one platform (YouTube, Facebook,
 * Instagram). Returns '' for an empty field, the trimmed URL when it is an
 * http(s) address on one of $hosts (or a subdomain of one), and false
 * otherwise. The scheme check keeps javascript: URLs out of the public page's
 * href attributes; the host check catches a link pasted into the wrong box.
 */
function press_platform_url(string $raw, array $hosts): string|false
{
    $url = trim($raw);
    if ($url === '') {
        return '';
    }
    if (strlen($url) > 500 || preg_match('/\s/', $url)) {
        return false;
    }
    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
    $host   = strtolower((string) parse_url($url, PHP_URL_HOST));
    if (!in_array($scheme, ['http', 'https'], true) || $host === '') {
        return false;
    }
    foreach ($hosts as $allowed) {
        if ($host === $allowed || str_ends_with($host, '.' . $allowed)) {
            return $url;
        }
    }
    return false;
}

const PRESS_URL_FIELDS = [
    'source_link'   => ['label' => 'YouTube URL',   'hosts' => ['youtube.com', 'youtu.be']],
    'facebook_url'  => ['label' => 'Facebook URL',  'hosts' => ['facebook.com', 'fb.com', 'fb.watch']],
    'instagram_url' => ['label' => 'Instagram URL', 'hosts' => ['instagram.com', 'instagr.am']],
];

/**
 * Read and validate the YouTube / Facebook / Instagram fields from $post.
 *
 * Returns [values, error]: values maps each field to its validated string
 * ('' when blank), error is the first problem or null. A value equal to
 * $current[field] is accepted as-is, so editing a post that predates this
 * validation never fails on a link nobody touched.
 */
function press_read_urls(array $post, array $current = []): array
{
    $values = [];
    foreach (PRESS_URL_FIELDS as $field => $spec) {
        $raw = trim((string) ($post[$field] ?? ''));
        if ($raw !== '' && $raw === trim((string) ($current[$field] ?? ''))) {
            $values[$field] = $raw;
            continue;
        }
        $url = press_platform_url($raw, $spec['hosts']);
        if ($url === false) {
            return [$values, "{$spec['label']} must be a full https:// link to "
                . $spec['hosts'][0] . ' (or leave it blank).'];
        }
        $values[$field] = $url;
    }
    return [$values, null];
}
