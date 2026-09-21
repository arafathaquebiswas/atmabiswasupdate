<?php
/**
 * WebP delivery for uploaded images.
 *
 * The homepage ships about 1.7 MB of images, roughly 1.1 MB of it the four
 * slider photos. They are already cached correctly -- a year of max-age,
 * immutable, HIT at the edge -- so the remaining cost is the bytes themselves.
 * Cloudflare Polish would re-encode them at the edge, but that needs a Pro plan;
 * this does the same job in PHP, which the hosting already supports.
 *
 * ORIGINALS ARE NEVER TOUCHED. Every function here only ever writes a sibling
 * "<name>.<ext>.webp". Nothing is overwritten, resized in place, or deleted, so
 * the whole optimisation is undone by deleting the .webp files and reverting the
 * templates -- no image can be lost by running this.
 *
 * Delivery is via <picture>, not Accept-header negotiation. Negotiation would
 * need "Vary: Accept", and Cloudflare does not vary its cache on Accept -- it
 * would happily hand a WebP to a browser that cannot render it. Distinct URLs
 * avoid the question entirely.
 *
 * Callers must check img_picture_is_safe() first: some cards are styled with
 * ".card > img", a direct-child selector that a <picture> wrapper would break.
 * Those call sites keep a plain <img> and are deliberately left alone.
 */

/** WebP encoding needs GD compiled with WebP; older builds lack it. */
function img_webp_supported(): bool
{
    static $ok = null;
    if ($ok === null) {
        $ok = function_exists('imagewebp')
            && function_exists('gd_info')
            && !empty(gd_info()['WebP Support']);
    }
    return $ok;
}

/** Quality high enough that the result is visually indistinguishable at display size. */
const IMG_WEBP_QUALITY = 82;

/** Only genuinely oversized sources are scaled; nothing on the site today is. */
const IMG_MAX_EDGE = 2560;

/**
 * Create "<path>.webp" beside an image, if it does not already exist.
 *
 * Returns the absolute path of the .webp, or null when one could not be made
 * (unsupported build, unreadable source, animated GIF, encoder failure). Null is
 * an ordinary outcome, not an error: the caller simply serves the original.
 */
function img_make_webp(string $absSource): ?string
{
    if (!img_webp_supported() || !is_file($absSource) || !is_readable($absSource)) {
        return null;
    }

    $target = $absSource . '.webp';
    // Regenerate only when the source is newer, so reruns are cheap and safe.
    if (is_file($target) && filemtime($target) >= filemtime($absSource)) {
        return $target;
    }

    $info = @getimagesize($absSource);
    if ($info === false) {
        return null;
    }

    // Same guard as img_build_variants: refuse a source too large to decode
    // within the memory limit rather than dying halfway and returning an empty
    // response, which reaches the visitor as a Cloudflare 520.
    img_raise_limits();
    $limitBytes = img_memory_limit_bytes();
    if ($limitBytes > 0 && ($info[0] * $info[1] * 4 * 2) > ($limitBytes / 2)) {
        error_log(sprintf('img_make_webp: skipping %s (%dx%d too large for limit)',
            basename($absSource), $info[0], $info[1]));
        return null;
    }

    switch ($info[2]) {
        case IMAGETYPE_JPEG: $im = @imagecreatefromjpeg($absSource); break;
        case IMAGETYPE_PNG:  $im = @imagecreatefrompng($absSource);  break;
        default:             return null;   // GIF/SVG/anything else is left alone
    }
    if (!$im) {
        return null;
    }

    // PNGs may carry transparency; WebP keeps it, but only if GD is told to.
    if ($info[2] === IMAGETYPE_PNG) {
        imagepalettetotruecolor($im);
        imagealphablending($im, false);
        imagesavealpha($im, true);
    }

    // Aspect ratio is preserved exactly -- both edges scale by one factor.
    $w = imagesx($im);
    $h = imagesy($im);
    if (max($w, $h) > IMG_MAX_EDGE) {
        $scale  = IMG_MAX_EDGE / max($w, $h);
        $scaled = imagescale($im, (int) round($w * $scale), (int) round($h * $scale));
        if ($scaled) {
            $im = $scaled;
        }
    }

    // Write to a temp file first: a half-written .webp beside a good original
    // would be served as if it were complete.
    $tmp = $target . '.tmp';
    // No imagedestroy(): a no-op since PHP 8.0 and deprecated in 8.5, where it
    // would fill the error log on every upload. GD frees its own objects.
    $ok = @imagewebp($im, $tmp, IMG_WEBP_QUALITY);

    if (!$ok || !is_file($tmp)) {
        @unlink($tmp);
        return null;
    }
    // A .webp larger than its source is worth nothing; keep the original only.
    if (filesize($tmp) >= filesize($absSource)) {
        @unlink($tmp);
        return null;
    }
    if (!@rename($tmp, $target)) {
        @unlink($tmp);
        return null;
    }
    return $target;
}

/**
 * Render an <img>, wrapped in <picture> when a .webp sibling exists.
 *
 * $attrs is emitted verbatim on the <img>, so class, style, alt, width, height,
 * loading and everything else survive untouched and the rendered box is
 * identical either way -- same pixels, same dimensions, fewer bytes.
 */
function img_picture(string $docRelPath, string $attrs = ''): string
{
    $docRelPath = ltrim($docRelPath, '/');
    if ($docRelPath === '') {
        return '';
    }

    $src = htmlspecialchars($docRelPath, ENT_QUOTES, 'UTF-8');
    $img = '<img src="' . $src . '" ' . $attrs . '>';

    $webpAbs = __DIR__ . '/' . $docRelPath . '.webp';
    if (!is_file($webpAbs)) {
        return $img;   // nothing generated yet: behave exactly as before
    }

    $webpSrc = htmlspecialchars($docRelPath . '.webp', ENT_QUOTES, 'UTF-8');
    return '<picture>'
         . '<source srcset="' . $webpSrc . '" type="image/webp">'
         . $img
         . '</picture>';
}

/**
 * True when wrapping this image in <picture> cannot change its styling.
 *
 * pages.css styles team and founder photos with ".card > img" and
 * ".founder-card > img". Inserting a <picture> makes the img a grandchild and
 * those rules stop matching, which would visibly change the layout. Rather than
 * edit that CSS, those call sites keep a bare <img>: they are small logos and
 * portraits, not the megabyte-scale slider photos this is aimed at.
 */
function img_picture_is_safe(string $context): bool
{
    return !in_array($context, ['card', 'founder-card', 'othermembers', 'segment'], true);
}

/**
 * move_uploaded_file() plus a WebP sibling, as a drop-in replacement.
 *
 * Call sites swap one function name and keep their existing control flow, so
 * this adds no new failure path. WebP generation is best-effort on purpose: an
 * upload must never fail because the optimiser could not encode a variant, so
 * a null from img_make_webp() is ignored and the original is served as before.
 */
function img_store_uploaded(string $tmpName, string $target): bool
{
    if (!move_uploaded_file($tmpName, $target)) {
        return false;
    }

    // The move is all that happens while the request is still being decided.
    // Encoding used to run here, which put the most expensive work in the
    // request BEFORE the database row was written: when it exhausted memory on
    // a 6000x4000 photo the process died with the file already on disk and no
    // row referencing it, so the admin lost the upload and was left an orphan
    // file. Deferring it inverts that -- the row is committed first, and the
    // worst a failed encode can now cost is the variants, with the original
    // still served exactly as before.
    img_defer_optimization($target);
    return true;
}

/**
 * Run the encoding after the response has been handed back to the visitor.
 *
 * LiteSpeed and FPM can both close the connection and keep executing, which is
 * what makes this safe: the redirect has already been sent, so the admin sees
 * the result at normal speed no matter how large the photo is. Where neither
 * exists the work still runs at shutdown, after the insert and the redirect
 * header, so the ordering guarantee holds even without the early flush.
 */
function img_defer_optimization(string $absPath): void
{
    static $queued = [];
    if (isset($queued[$absPath])) {
        return;                       // one pass per file per request
    }
    $queued[$absPath] = true;

    register_shutdown_function(static function () use ($absPath) {
        if (function_exists('litespeed_finish_request')) {
            @litespeed_finish_request();
        } elseif (function_exists('fastcgi_finish_request')) {
            @fastcgi_finish_request();
        }
        // Best-effort by definition: a variant that cannot be produced leaves
        // the original in place, which is only slower, never broken.
        try {
            img_make_webp($absPath);
            img_build_variants($absPath);
        } catch (Throwable $e) {
            error_log('img_defer_optimization failed for ' . basename($absPath) . ': ' . $e->getMessage());
        }
    });
}

/* ─────────────────────────────────────────────────────────────────────────
   Responsive variants
   ─────────────────────────────────────────────────────────────────────────
   The slider ships 1600px photographs into a box that measures 390x300 on a
   phone -- roughly ten times the pixels the display can use, and on Slow 4G the
   five slides together came to 1034 KB, which is 63% of the page. Narrower
   copies are generated once, at upload or during the backfill, and the browser
   picks one via srcset.

   Generation NEVER happens during a page render: img_srcset() only stats files
   that already exist, so a request costs a handful of is_file() calls and never
   an encode.

   Sources are only ever scaled DOWN. A 900px original yields 480 and 768 and
   stops there -- upscaling invents detail and costs bytes for nothing. Height
   follows from one scale factor, so the aspect ratio is exact and nothing is
   ever cropped. */
const IMG_WIDTHS = [480, 768, 1200, 1600];

/** Variant path for a width: "photo.jpg" -> "photo.jpg.768.webp" */
function img_variant_path(string $abs, int $w, string $ext): string
{
    return $abs . '.' . $w . '.' . $ext;
}

/**
 * Build every responsive variant for one source image.
 *
 * Returns a map of width => [webp?, fallback?] describing what now exists on
 * disk. Widths at or above the source width are skipped so nothing is upscaled.
 */
function img_build_variants(string $absSource): array
{
    if (!is_file($absSource) || !is_readable($absSource)) {
        return [];
    }
    $info = @getimagesize($absSource);
    if ($info === false) {
        return [];
    }
    [$srcW, $srcH] = $info;

    switch ($info[2]) {
        case IMAGETYPE_JPEG: $loader = 'imagecreatefromjpeg'; $fallbackExt = 'jpg'; break;
        case IMAGETYPE_PNG:  $loader = 'imagecreatefrompng';  $fallbackExt = 'png'; break;
        default: return [];
    }

    // Widths smaller than the source, largest first. Nothing is ever upscaled.
    $widths = array_values(array_filter(IMG_WIDTHS, fn($w) => $w < $srcW));
    if (!$widths) {
        return [];
    }
    rsort($widths);

    // Everything that still needs making, so an upload that already has its
    // variants does no decoding at all.
    $todo = [];
    foreach ($widths as $w) {
        $webpPath = img_variant_path($absSource, $w, 'webp');
        if (!is_file($webpPath) || filemtime($webpPath) < filemtime($absSource)) {
            $todo[] = $w;
        }
    }
    if (!$todo) {
        $made = [];
        foreach ($widths as $w) {
            $made[$w] = [
                'webp'     => img_variant_path($absSource, $w, 'webp'),
                'fallback' => is_file(img_variant_path($absSource, $w, $fallbackExt))
                    ? img_variant_path($absSource, $w, $fallbackExt) : null,
            ];
        }
        return $made;
    }

    // A 6000x4000 upload is about 92 MB as a GD truecolor image. Decoding it
    // once per width -- which this did -- meant four of those in a single
    // request, and the process died partway through: PHP returned nothing, and
    // Cloudflare reported that as a 520 to anyone uploading a large photo.
    //
    // The source is decoded once now and each width is scaled from the previous,
    // larger result rather than from the original, so after the first step the
    // working image is already small. Peak memory is one source plus one scaled
    // copy instead of eight, and the arithmetic is a fraction of the work.
    img_raise_limits();

    // Refuse sources too large to decode within the limit rather than dying
    // halfway. 4 bytes per pixel, doubled for the working copy, and only half
    // the limit is spent so the rest of the request still has room.
    $limitBytes = img_memory_limit_bytes();
    if ($limitBytes > 0 && ($srcW * $srcH * 4 * 2) > ($limitBytes / 2)) {
        error_log(sprintf(
            'img_build_variants: skipping %s (%dx%d needs ~%dMB, limit %dMB)',
            basename($absSource), $srcW, $srcH,
            (int) ($srcW * $srcH * 4 * 2 / 1048576), (int) ($limitBytes / 1048576)
        ));
        return [];
    }

    $im = @$loader($absSource);
    if (!$im) {
        return [];
    }
    if ($info[2] === IMAGETYPE_PNG) {
        imagepalettetotruecolor($im);
        imagealphablending($im, false);
        imagesavealpha($im, true);
    }

    $made    = [];
    $current = $im;
    $curW    = $srcW;
    $curH    = $srcH;

    foreach ($widths as $w) {
        // One scale factor drives both axes, taken from the ORIGINAL dimensions,
        // so repeated steps cannot let rounding drift the aspect ratio.
        $h       = (int) round($srcH * ($w / $srcW));
        $resized = imagescale($current, $w, $h);
        if (!$resized) {
            break;
        }
        if ($current !== $im) {
            unset($current);            // release the previous intermediate
        }
        $current = $resized;
        $curW = $w;
        $curH = $h;

        if (!in_array($w, $todo, true)) {
            continue;                   // already on disk; still needed as a step
        }

        $entry    = [];
        $webpPath = img_variant_path($absSource, $w, 'webp');
        $fbPath   = img_variant_path($absSource, $w, $fallbackExt);

        $tmp = $webpPath . '.tmp';
        if (img_webp_supported() && @imagewebp($resized, $tmp, IMG_WEBP_QUALITY) && is_file($tmp)) {
            @rename($tmp, $webpPath);
            $entry['webp'] = $webpPath;
        } else {
            @unlink($tmp);
        }

        $tmp2 = $fbPath . '.tmp';
        $ok2  = $fallbackExt === 'jpg'
            ? @imagejpeg($resized, $tmp2, 82)
            : @imagepng($resized, $tmp2, 6);
        if ($ok2 && is_file($tmp2)) {
            @rename($tmp2, $fbPath);
            $entry['fallback'] = $fbPath;
        } else {
            @unlink($tmp2);
        }

        if ($entry) {
            $made[$w] = $entry;
        }
    }

    return $made;
}

/**
 * Raise the memory ceiling only where it is currently lower.
 *
 * This used to set 512M unconditionally, which on this host LOWERED it: the
 * account runs with memory_limit 2048M and max_execution_time 0, so the
 * "safety" call was cutting the available memory to a quarter and capping a
 * runtime that had no cap. A guard that makes the situation worse on the
 * machine it is guarding is not a guard.
 *
 * set_time_limit is left alone entirely: 0 means no limit, and anything this
 * would set is a reduction.
 */
function img_raise_limits(): void
{
    $current = img_memory_limit_bytes();
    $wanted  = 512 * 1024 * 1024;
    // 0 means unlimited, which is already better than anything requested here.
    if ($current !== 0 && $current < $wanted) {
        @ini_set('memory_limit', '512M');
    }
}

/** PHP's memory_limit in bytes; 0 when unlimited or unreadable. */
function img_memory_limit_bytes(): int
{
    $raw = trim((string) ini_get('memory_limit'));
    if ($raw === '' || $raw === '-1') {
        return 0;
    }
    $unit = strtolower(substr($raw, -1));
    $num  = (int) $raw;
    return match ($unit) {
        'g' => $num * 1024 * 1024 * 1024,
        'm' => $num * 1024 * 1024,
        'k' => $num * 1024,
        default => $num,
    };
}

/**
 * srcset/fallback data for a document-root-relative image, from files on disk.
 *
 * Pure lookup -- it never encodes anything, so it is safe on a hot page render.
 */
function img_srcset(string $docRel): array
{
    $docRel = ltrim($docRel, '/');
    $abs    = __DIR__ . '/' . $docRel;
    if ($docRel === '' || !is_file($abs)) {
        return ['webp' => '', 'fallback' => '', 'width' => 0];
    }
    $ext = strtolower(pathinfo($docRel, PATHINFO_EXTENSION));
    $fb  = $ext === 'png' ? 'png' : 'jpg';

    $webp = $fallback = [];
    foreach (IMG_WIDTHS as $w) {
        if (is_file(img_variant_path($abs, $w, 'webp'))) {
            $webp[] = htmlspecialchars($docRel, ENT_QUOTES, 'UTF-8') . '.' . $w . '.webp ' . $w . 'w';
        }
        if (is_file(img_variant_path($abs, $w, $fb))) {
            $fallback[] = htmlspecialchars($docRel, ENT_QUOTES, 'UTF-8') . '.' . $w . '.' . $fb . ' ' . $w . 'w';
        }
    }
    // The full-size original closes both lists so large screens still get it.
    $size = @getimagesize($abs);
    $ow   = $size ? (int) $size[0] : 0;
    if ($ow) {
        if (is_file($abs . '.webp')) {
            $webp[] = htmlspecialchars($docRel, ENT_QUOTES, 'UTF-8') . '.webp ' . $ow . 'w';
        }
        $fallback[] = htmlspecialchars($docRel, ENT_QUOTES, 'UTF-8') . ' ' . $ow . 'w';
    }
    return ['webp' => implode(', ', $webp), 'fallback' => implode(', ', $fallback), 'width' => $ow];
}

/**
 * Responsive <picture> for a normal (non-slider) image.
 *
 * $sizes tells the browser how wide the image will be laid out, which is what
 * lets it choose a narrow variant before any CSS has been applied.
 */
function img_picture_responsive(string $docRel, string $attrs = '', string $sizes = '100vw'): string
{
    $set = img_srcset($docRel);
    if ($set['fallback'] === '') {
        return img_picture($docRel, $attrs);   // no variants yet: previous behaviour
    }
    $src = htmlspecialchars(ltrim($docRel, '/'), ENT_QUOTES, 'UTF-8');
    $out = '<picture>';
    if ($set['webp'] !== '') {
        $out .= '<source type="image/webp" srcset="' . $set['webp'] . '" sizes="' . $sizes . '">';
    }
    $out .= '<source srcset="' . $set['fallback'] . '" sizes="' . $sizes . '">';
    $out .= '<img src="' . $src . '" ' . $attrs . '>';
    return $out . '</picture>';
}

/**
 * One slider slide.
 *
 * Only the first slide is a real image. It carries fetchpriority="high" and no
 * lazy attribute, because it is the LCP element and every millisecond before
 * its request starts is visible to the user.
 *
 * Later slides are emitted with data-src/data-srcset and no src at all, so the
 * browser cannot request them during the initial load. loading="lazy" was
 * already on them and did nothing: they sit inside the viewport container at the
 * top of the page, so Chrome considered them in-view and fetched all five at
 * once. Those four downloads were competing with the first slide for bandwidth,
 * which is why the visible image took 8.9s to arrive on Slow 4G despite being
 * requested first. imageSlider.js promotes data-src to src ahead of time.
 *
 * Markup shape is unchanged -- still one <img> inside the .item div, with the
 * same attributes -- so ".slider .list .item img" matches exactly as before and
 * nothing about the layout, sizing or animation moves.
 */
function img_slide(string $docRel, string $alt, bool $isFirst, string $sizes = '100vw'): string
{
    $set    = img_srcset($docRel);
    $src    = htmlspecialchars(ltrim($docRel, '/'), ENT_QUOTES, 'UTF-8');
    $altEsc = htmlspecialchars($alt, ENT_QUOTES, 'UTF-8');

    if ($isFirst) {
        $out = '<picture>';
        if ($set['webp'] !== '') {
            $out .= '<source type="image/webp" srcset="' . $set['webp'] . '" sizes="' . $sizes . '">';
        }
        if ($set['fallback'] !== '') {
            $out .= '<source srcset="' . $set['fallback'] . '" sizes="' . $sizes . '">';
        }
        $out .= '<img src="' . $src . '" alt="' . $altEsc . '" fetchpriority="high" decoding="async">';
        return $out . '</picture>';
    }

    // No src, no srcset: nothing for the preload scanner to fetch.
    $data = ' data-src="' . $src . '"';
    if ($set['webp'] !== '') {
        $data .= ' data-srcset-webp="' . $set['webp'] . '"';
    }
    if ($set['fallback'] !== '') {
        $data .= ' data-srcset="' . $set['fallback'] . '"';
    }
    return '<img alt="' . $altEsc . '" loading="lazy" decoding="async" data-sizes="'
         . htmlspecialchars($sizes, ENT_QUOTES, 'UTF-8') . '"' . $data . '>';
}
