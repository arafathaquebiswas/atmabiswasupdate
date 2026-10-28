<?php require_once __DIR__ . '/page_cards.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-EZVV9DWWY7"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', 'G-EZVV9DWWY7');
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events &amp; Activities – ATMABISWAS (আত্মবিশ্বাস) Bangladesh</title>
    <?php include 'seo.php'; ?>
    <link rel="stylesheet" href="pages.css?v=<?php echo filemtime(__DIR__ . '/pages.css'); ?>">
</head>
<body>
    <?php include 'Navbar.php' ?>

    <main>
    <section class="page-hero">
        <div class="page-hero-inner">
            <h1>Events &amp; Activities</h1>
            <p>Celebrating milestones, inspiring change, and building community across Bangladesh.</p>
        </div>
    </section>

    <div class="container">
        <?php cards_render('events'); ?>
    </div>
    </main>
    <?php include 'footer.php' ?>
</body>
</html>
