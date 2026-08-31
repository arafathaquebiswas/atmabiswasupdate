<?php
/**
 * One-off backfill: generate WebP siblings for images already on the server.
 *
 * New uploads get their .webp automatically (see img_store_uploaded), but the
 * images already in media/ and uploads/ predate that and are the ones costing
 * real bytes today -- the four slider photos alone are about 1.1 MB. Those
 * directories are git-ignored and exist only on the server, so they cannot be
 * optimised from a checkout; this has to run here.
 *
 * SAFETY: this only ever CREATES "<name>.<ext>.webp". No original is renamed,
 * resized, overwritten or deleted, and a source whose .webp would be no smaller
 * is skipped entirely. To undo the whole thing, delete the .webp files.
 *
 * Dry run by default. Nothing is written until ?run=1 is passed explicitly.
 */
require_once __DIR__ . '/../../image_optimize.php';
require_once __DIR__ . '/../auth.php';

require_login();

@set_time_limit(300);

$root    = realpath(__DIR__ . '/../../');
$dirs    = ['media', 'uploads'];
$doWrite = isset($_GET['run']) && $_GET['run'] === '1';

$rows = [];
$origTotal = $webpTotal = $skipped = 0;

foreach ($dirs as $d) {
    $base = $root . DIRECTORY_SEPARATOR . $d;
    if (!is_dir($base)) {
        continue;
    }
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($it as $file) {
        if (!$file->isFile()) {
            continue;
        }
        $path = $file->getPathname();
        $ext  = strtolower($file->getExtension());
        if (!in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
            continue;   // .webp siblings and everything else are ignored
        }

        $before  = $file->getSize();
        $existing = is_file($path . '.webp');

        if ($doWrite) {
            $made = img_make_webp($path);
            $after = $made ? filesize($made) : null;
        } else {
            // Dry run: report what already exists, promise nothing about the rest.
            $after = $existing ? filesize($path . '.webp') : null;
        }

        $origTotal += $before;
        if ($after !== null && $after < $before) {
            $webpTotal += $after;
            $rows[] = [str_replace($root . '/', '', $path), $before, $after];
        } else {
            $webpTotal += $before;   // original still served
            $skipped++;
        }
    }
}

$saved = $origTotal - $webpTotal;
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Image Optimisation — ATMABISWAS Admin</title>
  <style>
    body{font-family:system-ui,-apple-system,'Segoe UI',sans-serif;margin:2rem;color:#1f2937;background:#f9fafb}
    h1{font-size:1.25rem;margin:0 0 .25rem}
    .sub{color:#6b7280;font-size:.85rem;margin-bottom:1.25rem}
    .box{background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:1rem;margin-bottom:1rem}
    table{border-collapse:collapse;width:100%;font-size:.8rem}
    th,td{text-align:left;padding:.35rem .5rem;border-bottom:1px solid #f0f0f0}
    th{color:#6b7280;font-weight:600}
    td.num{text-align:right;font-variant-numeric:tabular-nums}
    .big{font-size:1.5rem;font-weight:700;color:#059669}
    a.btn{display:inline-block;background:#2563eb;color:#fff;text-decoration:none;padding:.5rem .9rem;border-radius:6px;font-size:.85rem}
    a.back{color:#6b7280;font-size:.85rem}
    .warn{background:#fffbeb;border-color:#fcd34d}
  </style>
</head>
<body>
  <h1>Image optimisation<?= $doWrite ? '' : ' — dry run' ?></h1>
  <div class="sub">
    Creates a WebP copy beside each JPEG/PNG. Originals are never modified or deleted,
    and browsers without WebP keep receiving them.
  </div>

  <?php if (!img_webp_supported()): ?>
    <div class="box warn">
      <strong>WebP is unavailable on this server.</strong>
      GD is present but built without WebP support, so no variants can be produced.
      Nothing has been changed. The site continues to serve the original images.
    </div>
  <?php else: ?>

  <div class="box">
    <div class="big"><?= number_format($saved / 1024, 0) ?> KB<?= $doWrite ? ' saved' : ' available' ?></div>
    <div class="sub" style="margin:0">
      <?= count($rows) ?> image<?= count($rows) === 1 ? '' : 's' ?> optimised,
      <?= $skipped ?> left as-is (WebP would not have been smaller).
      Total <?= number_format($origTotal / 1024) ?> KB &rarr; <?= number_format($webpTotal / 1024) ?> KB.
    </div>
  </div>

  <?php if (!$doWrite): ?>
    <div class="box">
      This was a dry run &mdash; nothing has been written.
      <p><a class="btn" href="?run=1">Generate WebP files now</a></p>
    </div>
  <?php endif; ?>

  <?php if ($rows): ?>
  <div class="box">
    <table>
      <tr><th>File</th><th class="num">Original</th><th class="num">WebP</th><th class="num">Saved</th></tr>
      <?php foreach ($rows as [$f, $b, $a]): ?>
      <tr>
        <td><?= htmlspecialchars($f) ?></td>
        <td class="num"><?= number_format($b / 1024) ?> KB</td>
        <td class="num"><?= number_format($a / 1024) ?> KB</td>
        <td class="num"><?= round((1 - $a / $b) * 100) ?>%</td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
  <?php endif; ?>

  <?php endif; ?>
  <p><a class="back" href="dashboard.php">&larr; Back to dashboard</a></p>
</body>
</html>
