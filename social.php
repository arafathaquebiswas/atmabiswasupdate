<?php
require_once __DIR__ . '/page_cards.php';
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

        <?php cards_render('social'); ?>

    </div>

    </main>
    <?php include 'footer.php' ?>
</body>
</html>
