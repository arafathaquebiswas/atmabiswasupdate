<?php
/**
 * Shared premium error-page shell.
 * Each numbered error file (400.php, 401.php, ...) sets the variables
 * below, then includes this file. Keeps all six pages visually and
 * structurally consistent without duplicating markup six times.
 *
 * Expected variables:
 *   $errorCode     string  e.g. "404"
 *   $errorTitle    string  <title> tag text
 *   $errorHeading  string  main on-page heading (h1)
 *   $errorMessage  string  supporting paragraph
 *   $errorIcon     string  Font Awesome icon class, e.g. "fa-compass"
 *   $errorButtons  array   subset of: 'home', 'back', 'contact', 'login', 'retry'
 *   $showSearch    bool    show the quick-search box (404 only)
 *   $autoRedirect  bool    auto-redirect to Home after 30s (all except 401)
 */

http_response_code((int) $errorCode);

// Loaded directly (not just via Navbar.php) so SITE_ROOT/HOME_PATH/etc. are
// available for the absolute asset paths below — error pages can be served
// for a broken URL at any depth, so relative paths would resolve wrong.
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($errorTitle) ?> &ndash; ATMABISWAS (আত্মবিশ্বাস) Bangladesh</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/png" href="<?= SITE_ROOT ?>/LOGO/NGO_logo_monogram.png">
    <link rel="stylesheet" href="<?= SITE_ROOT ?>/pages.css?v=<?php echo filemtime(__DIR__ . '/pages.css'); ?>">
    <link rel="stylesheet" href="<?= SITE_ROOT ?>/error.css?v=<?php echo filemtime(__DIR__ . '/error.css'); ?>">
</head>
<body>
    <?php
    try {
        include __DIR__ . '/Navbar.php';
    } catch (Throwable $e) {
        error_log("Navbar include error on error page: " . $e->getMessage());
    }
    ?>

    <main>
    <div class="error-page">
        <div class="err-watermark" aria-hidden="true"><?= htmlspecialchars($errorCode) ?></div>
        <div class="err-orb err-orb-1" aria-hidden="true"></div>
        <div class="err-orb err-orb-2" aria-hidden="true"></div>
        <div class="err-orb err-orb-3" aria-hidden="true"></div>

        <div class="err-card">
            <nav class="err-breadcrumb" aria-label="Breadcrumb">
                <a href="<?= HOME_PATH ?>">Home</a>
                <span aria-hidden="true">/</span>
                <span aria-current="page">Error <?= htmlspecialchars($errorCode) ?></span>
            </nav>

            <div class="err-icon-wrap">
                <span class="err-icon-glow" aria-hidden="true"></span>
                <span class="err-icon-badge" aria-hidden="true"><i class="fas <?= htmlspecialchars($errorIcon) ?>"></i></span>
            </div>

            <p class="err-code"><?= htmlspecialchars($errorCode) ?></p>
            <h1 class="err-title"><?= htmlspecialchars($errorHeading) ?></h1>
            <p class="err-message"><?= htmlspecialchars($errorMessage) ?></p>

            <p class="err-quote" id="errQuote"></p>

            <?php if (!empty($showSearch)): ?>
            <form class="err-search" role="search" aria-label="Search ATMABISWAS">
                <i class="fas fa-search" aria-hidden="true"></i>
                <label for="errSearchInput" class="sr-only">Search the site</label>
                <input type="text" id="errSearchInput" placeholder="Search the site&hellip;" autocomplete="off">
                <button type="submit">Go</button>
            </form>
            <?php endif; ?>

            <div class="err-actions">
                <?php foreach ($errorButtons as $btn): switch ($btn):
                    case 'home': ?>
                        <a href="<?= HOME_PATH ?>" class="err-btn err-btn-primary">
                            <i class="fas fa-house" aria-hidden="true"></i> Home
                        </a>
                    <?php break;

                    case 'back': ?>
                        <button type="button" class="err-btn err-btn-secondary"
                            onclick="if (history.length > 1) { history.back(); } else { window.location.href = '<?= HOME_PATH ?>'; }">
                            <i class="fas fa-arrow-left" aria-hidden="true"></i> Go Back
                        </button>
                    <?php break;

                    case 'contact': ?>
                        <a href="<?= CONTACT_PATH ?>" class="err-btn err-btn-secondary">
                            <i class="fas fa-envelope" aria-hidden="true"></i> Contact Us
                        </a>
                    <?php break;

                    case 'login': ?>
                        <a href="<?= LOGIN_PATH ?>" class="err-btn err-btn-primary">
                            <i class="fas fa-right-to-bracket" aria-hidden="true"></i> Login
                        </a>
                    <?php break;

                    case 'retry': ?>
                        <button type="button" class="err-btn err-btn-primary" onclick="window.location.reload()">
                            <i class="fas fa-rotate-right" aria-hidden="true"></i> Retry
                        </button>
                    <?php break;
                endswitch; endforeach; ?>
            </div>

            <?php if (!empty($autoRedirect)): ?>
            <p class="err-countdown" data-countdown="30" data-redirect-url="<?= HOME_PATH ?>">
                Redirecting to Home in <span class="err-countdown-num">30</span>s &middot;
                <button type="button" class="err-cancel">Cancel</button>
            </p>
            <?php endif; ?>

            <p class="err-report">
                <a href="mailto:atmabiswas_ngo@yahoo.com?subject=<?= rawurlencode('Website issue: Error ' . $errorCode) ?>">
                    <i class="fas fa-flag" aria-hidden="true"></i> Report this problem
                </a>
            </p>
        </div>
    </div>
    </main>

    <?php
    try {
        include __DIR__ . '/footer.php';
    } catch (Throwable $e) {
        error_log("Footer include error on error page: " . $e->getMessage());
    }
    ?>

    <script src="<?= SITE_ROOT ?>/error.js?v=<?php echo filemtime(__DIR__ . '/error.js'); ?>"></script>
</body>
</html>
