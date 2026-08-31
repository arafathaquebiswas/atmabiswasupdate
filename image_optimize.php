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
    img_make_webp($target);
    return true;
}
