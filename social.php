<?php
require_once __DIR__ . '/social_cards.php';
// Text and images saved from the admin panel; without a database every card
// keeps its built-in text and image.
$socialSegments  = social_apply_texts(social_cards(), social_text_overrides());
$socialOverrides = social_image_overrides();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Development Programs – ATMABISWAS (আত্মবিশ্বাস) Bangladesh</title>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-EZVV9DWWY7"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', 'G-EZVV9DWWY7');
    </script>
    <?php include 'seo.php'; ?>
    <link rel="stylesheet" href="pages.css?v=<?php echo filemtime(__DIR__ . '/pages.css'); ?>">
</head>
<body>
    <?php include 'Navbar.php' ?>

    <main>
    <section class="page-hero">
        <div class="page-hero-inner">
            <h1>Social Development Programs</h1>
            <p>Empowering communities through vocational training, care programs, and social initiatives.</p>
        </div>
    </section>

    <div class="container">

        <?php foreach ($socialSegments as $segment): ?>
        <div class="segment">
            <div class="segment-header">
                <i class="<?= htmlspecialchars($segment['icon']) ?>"></i>
                <h2><?= htmlspecialchars($segment['title']) ?></h2>
            </div>
            <div class="card-grid">
                <?php foreach ($segment['cards'] as $card):
                    $image = social_card_image($card, $socialOverrides);
                    // Uploads get the narrower copies image_optimize.php makes, when they exist.
                    $set   = $image['source'] === 'uploaded' ? img_srcset($image['src']) : ['fallback' => ''];
                ?>
                <div class="card">
                    <img src="<?= htmlspecialchars($image['src']) ?>"<?php if ($set['fallback'] !== ''): ?> srcset="<?= $set['fallback'] ?>" sizes="(max-width: 640px) 100vw, 400px"<?php endif; ?> loading="lazy" alt="<?= htmlspecialchars($card['alt']) ?>"<?= $image['logo'] ? ' class="card-img-logo"' : '' ?>
                         onerror="this.onerror=null;this.removeAttribute('srcset');this.src='<?= htmlspecialchars(social_logo_src()) ?>';this.className='card-img-logo';">
                    <div class="card-content">
                        <h3><?= htmlspecialchars($card['title']) ?></h3>
                        <?php if ($card['desc'] !== ''): ?>
                        <p><?= htmlspecialchars($card['desc']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

    </div>

    </main>
    <?php include 'footer.php' ?>
</body>
</html>
