<?php
require_once __DIR__ . '/brand_icons.php';
// ── Load editable content from the database ───────────────────────
// Fallback values are used if the DB table doesn't exist yet.
// To set up the table run: backend/Database/about_us_migration.sql

$about_image     = 'office_pic/office_pic.jpg';
$about_image_alt = 'ATMABISWAS Office';
$about_text      = 'ATMABISWAS is a non-governmental, non-profit, voluntary, and development-focused organization committed to creating meaningful social change and fostering sustainable development. Established in January 1991 under the Department of Social Welfare, ATMABISWAS has dedicated over three decades to empowering communities across Bangladesh. The organization primarily focuses on serving the disadvantaged populations, striving to uplift their living standards and enhance their access to essential resources and opportunities.

Since its inception, ATMABISWAS has worked tirelessly to support marginalized individuals and communities, with an initial emphasis on the district of Chuadanga. Through a range of social welfare programs, development projects, and micro-credit initiatives, the organization has impacted thousands of lives, enabling beneficiaries to break the cycle of poverty and build a better future.

Headquartered in Chuadanga within Khulna Division, ATMABISWAS also maintains a liaison office in Dhaka and extends its community development work into Rajshahi Division, with programs spanning education, agriculture, health, women\'s empowerment, and environmental sustainability across the regions it serves.

আত্মবিশ্বাস বাংলাদেশের একটি বেসরকারি উন্নয়ন সংস্থা (এনজিও), যা ১৯৯১ সাল থেকে শিক্ষা, নারীর ক্ষমতায়ন, কৃষি উন্নয়ন, পরিবেশ সংরক্ষণ, সমাজকল্যাণ এবং কমিউনিটি উন্নয়নে কাজ করে আসছে। চুয়াডাঙ্গায় (খুলনা বিভাগ) সদর দপ্তর ছাড়াও ঢাকায় একটি লিয়াজোঁ অফিস এবং রাজশাহী বিভাগে সম্প্রসারিত কার্যক্রম পরিচালনা করে থাকে।';

$team_image      = 'office_pic/00000.jpg';
$team_image_alt  = 'ATMABISWAS Team with PKSF';
$team_text       = 'Our team consists of dedicated professionals who are passionate about making a difference. We collaborate to create a positive impact and support each other in our mission to empower communities and foster sustainable development.

Our team members come from diverse backgrounds, bringing a wealth of experience and expertise to the organization. We are united by our shared commitment to social justice, equality, and sustainable development. Each member of our team plays a crucial role in driving our mission forward — from field workers to administrative staff, project managers, and volunteers. Together, we strive to create a positive and lasting impact on the communities we serve.';

try {
    require_once 'backend/Database/db.php';
    $db   = new Db();
    $conn = $db->connect();
    $stmt = $conn->query("SELECT section_key, image_path, image_alt, text_content FROM about_us_content");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['section_key'] === 'about_us') {
            if ($row['image_path']) $about_image     = $row['image_path'];
            if ($row['image_alt'])  $about_image_alt = $row['image_alt'];
            if ($row['text_content']) $about_text    = $row['text_content'];
        } elseif ($row['section_key'] === 'our_team') {
            if ($row['image_path']) $team_image     = $row['image_path'];
            if ($row['image_alt'])  $team_image_alt = $row['image_alt'];
            if ($row['text_content']) $team_text    = $row['text_content'];
        }
    }
} catch (Exception $e) {
    // DB unavailable or table not yet created — fallback values remain active
}
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
    <title>About ATMABISWAS (আত্মবিশ্বাস) – Bangladesh NGO | Mission &amp; Vision</title>
    <?php include 'seo.php'; ?>
    <link rel="stylesheet" href="aboutus.css?v=<?php echo filemtime(__DIR__ . '/aboutus.css'); ?>">
</head>
<body>
    <?php include 'Navbar.php'; ?>

    <main>
    <!-- Hero -->
    <section class="about-hero">
        <div class="about-hero-inner">
            <h1>Welcome To ATMABISWAS</h1>
            <p>Empowering individuals and fostering self-belief across Bangladesh since 1991.</p>
        </div>
    </section>

    <div class="ab-container">

        <!-- About Us -->
        <div class="ab-card">
            <div class="ab-card-header">
                <i class="fa-solid fa-building-ngo"></i>
                <h2>About Us</h2>
            </div>
            <div class="ab-image-text">
                <img src="<?= htmlspecialchars($about_image) ?>" loading="lazy" alt="<?= htmlspecialchars($about_image_alt) ?>">
                <div>
                    <?php foreach (array_filter(explode("\n\n", trim($about_text))) as $para): ?>
                        <p style="margin-bottom:1.1rem;"><?= htmlspecialchars($para) ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Vision -->
        <div class="ab-card">
            <div class="ab-card-header">
                <i class="fa-solid fa-eye"></i>
                <h2>Our Vision</h2>
            </div>
            <p>Our vision is to create a society where poverty, inequality, and injustice are eradicated, and ecological balance is maintained. We strive for a world where every individual has the opportunity to thrive and contribute to sustainable development.</p>
        </div>

        <!-- Mission -->
        <div class="ab-card">
            <div class="ab-card-header">
                <i class="fa-solid fa-bullseye"></i>
                <h2>Our Mission</h2>
            </div>
            <ul class="ab-icon-grid">
                <li>
                    <i class="fa-solid fa-hand-holding-heart"></i>
                    <div>
                        <strong>Empowerment</strong>
                        <span>Providing resources and opportunities for personal and professional growth.</span>
                    </div>
                </li>
                <li>
                    <i class="fa-solid fa-people-carry-box"></i>
                    <div>
                        <strong>Support</strong>
                        <span>Offering guidance and assistance to individuals in need.</span>
                    </div>
                </li>
                <li>
                    <i class="fa-solid fa-users"></i>
                    <div>
                        <strong>Community</strong>
                        <span>Creating a supportive network that fosters collaboration and mutual growth.</span>
                    </div>
                </li>
                <li>
                    <i class="fa-solid fa-scale-balanced"></i>
                    <div>
                        <strong>Social Transformation</strong>
                        <span>Working towards a society that values harmony, peace, justice, and ecological balance.</span>
                    </div>
                </li>
            </ul>
        </div>

        <!-- Values -->
        <div class="ab-card">
            <div class="ab-card-header">
                <i class="fa-solid fa-gem"></i>
                <h2>Our Values</h2>
            </div>
            <ul class="ab-icon-grid values">
                <li>
                    <i class="fa-solid fa-shield-halved"></i>
                    <div>
                        <strong>Integrity</strong>
                        <span>We uphold the highest standards of integrity in all our actions.</span>
                    </div>
                </li>
                <li>
                    <i class="fa-solid fa-handshake"></i>
                    <div>
                        <strong>Respect</strong>
                        <span>We value each individual and treat everyone with respect and dignity.</span>
                    </div>
                </li>
                <li>
                    <i class="fa-solid fa-lightbulb"></i>
                    <div>
                        <strong>Innovation</strong>
                        <span>We embrace change and constantly seek new ways to achieve our mission.</span>
                    </div>
                </li>
            </ul>
        </div>

        <!-- Our Team -->
        <div class="ab-card">
            <div class="ab-card-header">
                <i class="fa-solid fa-people-group"></i>
                <h2>Our Team</h2>
            </div>
            <div class="ab-image-text">
                <img src="<?= htmlspecialchars($team_image) ?>" loading="lazy" alt="<?= htmlspecialchars($team_image_alt) ?>">
                <div>
                    <?php foreach (array_filter(explode("\n\n", trim($team_text))) as $para): ?>
                        <p style="margin-bottom:1.1rem;"><?= htmlspecialchars($para) ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Get Involved -->
        <div class="ab-card">
            <div class="ab-card-header">
                <i class="fa-solid fa-hands-holding-circle"></i>
                <h2>Get Involved</h2>
            </div>
            <ul class="ab-icon-grid get-involved">
                <li>
                    <i class="fa-solid fa-person-walking"></i>
                    <div>
                        <strong>Volunteer</strong>
                        <span>Share your skills and time to make a difference.</span>
                    </div>
                </li>
                <li>
                    <i class="fa-solid fa-circle-dollar-to-slot"></i>
                    <div>
                        <strong>Donate</strong>
                        <span>Support our initiatives with your generous contributions.</span>
                    </div>
                </li>
                <li>
                    <i class="fa-solid fa-link"></i>
                    <div>
                        <strong>Partner</strong>
                        <span>Collaborate with us to create impactful programs and events.</span>
                    </div>
                </li>
                <li>
                    <i class="fa-solid fa-briefcase"></i>
                    <div>
                        <strong>Career</strong>
                        <span>Join our team and contribute to our mission professionally.</span>
                    </div>
                </li>
            </ul>
        </div>

    </div><!-- /.ab-container -->

    <!-- Our Work in Action — full-bleed dark section -->
    <section class="work-section">
        <div class="work-inner">
            <div class="work-header">
                <span class="work-tag">
                    <?= brand_icon('youtube') ?> Our Stories
                </span>
                <h2>Our Work in Action</h2>
                <p>Watch stories of resilience, community empowerment, and meaningful change from across Bangladesh.</p>
            </div>
            <div class="work-grid">
                <div class="work-video-card">
                    <div class="work-video-frame">
                        <iframe src="https://www.youtube.com/embed/-rmQDVb3s4k" allowfullscreen loading="lazy"></iframe>
                    </div>
                    <div class="work-video-bar">
                        <?= brand_icon('youtube') ?>
                        <span>Watch Video</span>
                    </div>
                </div>
                <div class="work-video-card">
                    <div class="work-video-frame">
                        <iframe src="https://www.youtube.com/embed/i0UxCHapj40" allowfullscreen loading="lazy"></iframe>
                    </div>
                    <div class="work-video-bar">
                        <?= brand_icon('youtube') ?>
                        <span>Watch Video</span>
                    </div>
                </div>
                <div class="work-video-card">
                    <div class="work-video-frame">
                        <iframe src="https://www.youtube.com/embed/6xb-rN_9j24" allowfullscreen loading="lazy"></iframe>
                    </div>
                    <div class="work-video-bar">
                        <?= brand_icon('youtube') ?>
                        <span>Watch Video</span>
                    </div>
                </div>
                <div class="work-video-card">
                    <div class="work-video-frame">
                        <iframe src="https://www.youtube.com/embed/JS15JTafAv4" allowfullscreen loading="lazy"></iframe>
                    </div>
                    <div class="work-video-bar">
                        <?= brand_icon('youtube') ?>
                        <span>Watch Video</span>
                    </div>
                </div>
                <div class="work-video-card">
                    <div class="work-video-frame">
                        <iframe src="https://www.youtube.com/embed/eDMq_ispQYI" allowfullscreen loading="lazy"></iframe>
                    </div>
                    <div class="work-video-bar">
                        <?= brand_icon('youtube') ?>
                        <span>Watch Video</span>
                    </div>
                </div>
                <div class="work-video-card">
                    <div class="work-video-frame">
                        <iframe src="https://www.youtube.com/embed/nxDIwvOqTVg" allowfullscreen loading="lazy"></iframe>
                    </div>
                    <div class="work-video-bar">
                        <?= brand_icon('youtube') ?>
                        <span>Watch Video</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    </main>
    <?php include 'footer.php'; ?>
</body>
</html>
