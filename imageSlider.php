<?php
/**
 * Homepage image slider.
 *
 * Slides come from the img_upload table, managed at
 * backend/DashBoard/uploadimg.php. Rows are selected by img_type='img_slider'
 * — the exact value the upload form posts (uploadimg_process.php) — and
 * ordered by display_order, matching the "lower numbers appear first" hint
 * shown to the admin.
 *
 * img_path is stored relative to the document root ("uploads/images/x.jpg").
 * This file is included from index.php at the root, so the stored value is
 * used as-is; the admin page prefixes ../../ only because it sits two
 * directories down.
 *
 * If the table is empty or unreachable, the original built-in images are used
 * so the homepage never renders an empty or broken slider.
 */

/**
 * Most slides the homepage will ever show. Extra slider images are kept in the
 * database and stay visible in the admin dashboard — this caps display only,
 * and never deletes or recategorises anything.
 */
$sliderMax = 6;

$sliderItems = [];

try {
    include_once __DIR__ . '/backend/Database/db.php';
    $sliderConn = (new Db())->connect();

    if ($sliderConn) {
        // This table has drifted between installs: some carry img_title and
        // img_description, others only img_name, and display_order is added on
        // demand by uploadimg_process.php. Select only what is actually there.
        $sliderCols = array_flip(
            $sliderConn->query("SHOW COLUMNS FROM img_upload")->fetchAll(PDO::FETCH_COLUMN)
        );

        $select = ['img_path'];
        foreach (['img_title', 'img_description', 'img_name'] as $optional) {
            if (isset($sliderCols[$optional])) {
                $select[] = $optional;
            }
        }

        $orderBy = isset($sliderCols['display_order'])
            ? 'ORDER BY display_order ASC, img_id DESC'
            : 'ORDER BY img_id DESC';

        // Only published/active rows. No such column exists today — being in the
        // table as img_slider is what "published" means here — so this stays
        // empty unless one is added later.
        $publishedWhere = '';
        foreach (['status', 'is_active', 'published'] as $flag) {
            if (isset($sliderCols[$flag])) {
                $publishedWhere = $flag === 'status'
                    ? " AND (status = 'published' OR status = 'active' OR status IS NULL)"
                    : " AND ({$flag} = 1 OR {$flag} IS NULL)";
                break;
            }
        }

        // Fetch a buffer beyond the cap so that rows whose file has gone
        // missing from disk do not shrink the slider below the cap; the hard
        // cap is applied after that filtering, below.
        $stmt = $sliderConn->prepare(
            'SELECT ' . implode(', ', $select) . '
               FROM img_upload
              WHERE img_type = :type' . $publishedWhere . '
              ' . $orderBy . '
              LIMIT ' . ($sliderMax * 3)
        );
        // 'img_slider' is the exact value the upload form posts; the UI label
        // says "Image Slider" but the stored value is img_slider.
        $stmt->execute([':type' => 'img_slider']);

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $path = ltrim((string) $row['img_path'], '/');
            if ($path === '' || !is_file(__DIR__ . '/' . $path)) {
                continue; // row points at a file that is no longer on disk
            }

            $alt = '';
            foreach (['img_description', 'img_title', 'img_name'] as $field) {
                if (!empty($row[$field])) {
                    $alt = trim((string) $row[$field]);
                    break;
                }
            }

            $sliderItems[] = [
                'path' => $path,
                'alt'  => $alt !== '' ? $alt : 'ATMABISWAS programme photograph',
            ];
        }
    }
} catch (Throwable $e) {
    error_log('Homepage slider query failed: ' . $e->getMessage());
}

// Hard cap. Applied after the missing-file filter so a broken row cannot let a
// seventh slide through, and after ordering so this is the lowest Display Order
// values that survive.
if (count($sliderItems) > $sliderMax) {
    $sliderItems = array_slice($sliderItems, 0, $sliderMax);
}

if (empty($sliderItems)) {
    $sliderItems = [
        ['path' => 'toilet/toiletpic1.jpg',    'alt' => 'ATMABISWAS sanitation program – community toilet facility in rural Bangladesh'],
        ['path' => 'FISH/fish_pic6.jpg',       'alt' => 'ATMABISWAS fish farming project – sustainable aquaculture in Bangladesh'],
        ['path' => 'Awarness/awarness_pic6.jpg', 'alt' => 'ATMABISWAS awareness campaign – community health education program'],
        ['path' => 'Awarness/awarness_pic7.jpg', 'alt' => 'ATMABISWAS awareness program – rural community outreach in Bangladesh'],
        ['path' => 'FISH/fish_pic4.jpg',       'alt' => 'ATMABISWAS fish farming initiative – rural livelihood project in Bangladesh'],
    ];
}
?>
<link rel="stylesheet" href="imageSlider.css?v=<?php echo filemtime(__DIR__ . '/imageSlider.css'); ?>">
<div class="slider">
    <div class="list">
        <?php foreach ($sliderItems as $i => $slide): ?>
        <div class="item">
            <img src="<?= htmlspecialchars($slide['path'], ENT_QUOTES, 'UTF-8') ?>"
                 <?= $i === 0 ? '' : 'loading="lazy"' ?>
                 alt="<?= htmlspecialchars($slide['alt'], ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <?php endforeach; ?>
    </div>
    <div class="buttons">
        <button id="prev">&#10094;</button>
        <button id="next">&#10095;</button>
    </div>
    <ul class="dots">
        <?php // imageSlider.js indexes dots[active], so there must be exactly one per slide. ?>
        <?php foreach ($sliderItems as $i => $slide): ?>
        <li<?= $i === 0 ? ' class="active"' : '' ?>></li>
        <?php endforeach; ?>
    </ul>
</div>
<script src="imageSlider.js?v=<?php echo filemtime(__DIR__ . '/imageSlider.js'); ?>"></script>
