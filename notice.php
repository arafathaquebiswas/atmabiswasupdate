<?php
include 'backend/Database/db.php';

$database = new Db();
$conn = $database->connect();

$sql = "SELECT * FROM pdsfiles";
$stmt = $conn->prepare($sql);
$stmt->execute();
$pdfs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-EZVV9DWWY7"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-EZVV9DWWY7');
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Notices – ATMABISWAS (আত্মবিশ্বাস) NGO Bangladesh</title>
    <?php include 'seo.php'; ?>
    <link rel="stylesheet" href="notice.css?v=<?php echo filemtime(__DIR__ . '/notice.css'); ?>">
    <link rel="icon" type="image/png" href="LOGO/NGO_logo_monogram.png">
</head>
<body>
    <?php include 'Navbar.php'; ?>

    <main>
    <section class="notice-hero">
        <div class="notice-hero-inner">
            <div class="notice-hero-icon">
                <i class="fa-solid fa-bullhorn"></i>
            </div>
            <h1>Official Notice Board</h1>
            <p>Stay informed with the latest announcements, circulars, and publications from ATMABISWAS.</p>
        </div>
    </section>

    <div class="container">
        <?php if (count($pdfs) === 0): ?>
            <div class="grid">
                <div class="empty-state">
                    <i class="fa-regular fa-folder-open"></i>
                    <h3>No Notices Available</h3>
                    <p>Check back later for updates and announcements.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($pdfs as $pdf): ?>
                    <a href="<?php echo htmlspecialchars($pdf['pdf_path']); ?>" target="_blank" class="card-link">
                        <div class="card-embed-wrap">
                            <embed class="card-embed" src="<?php echo htmlspecialchars($pdf['pdf_path']); ?>">
                        </div>
                        <div class="card-footer">
                            <h2><?php echo htmlspecialchars($pdf['pdf_title']); ?></h2>
                            <div class="card-meta">
                                <span class="card-date">
                                    <i class="fa-regular fa-calendar"></i>
                                    <?php echo explode(' ', $pdf['upload_date'])[0]; ?>
                                </span>
                                <span class="card-badge">View Notice</span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    </main>
    <?php include 'footer.php'; ?>
</body>
</html>
