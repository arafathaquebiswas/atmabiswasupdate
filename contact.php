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
    <title>Contact ATMABISWAS (আত্মবিশ্বাস) – Chuadanga, Bangladesh</title>
    <?php include 'seo.php'; ?>

    <link rel="icon" type="image/png" href="LOGO/NGO_logo_monogram.png">
    <link rel="stylesheet" href="locations.css?v=<?php echo filemtime(__DIR__ . '/locations.css'); ?>">
</head>

<body>
    <?php include 'Navbar.php' ?>

    <main>
    <!-- Hero -->
    <div class="ct-hero">
        <i class="fas fa-hands-helping ct-hero-icon"></i>
        <h1>Contact ATMABISWAS</h1>
        <p>Reach out to us at any of our offices across Bangladesh</p>
    </div>

    <div class="ct-wrapper">

        <!-- ── Section 1: HQ & Liaison ── -->
        <div class="ct-section">
            <button class="ct-toggle" id="toggle-btn">
                <span class="ct-toggle-left">
                    <i class="fas fa-building"></i>
                    ATMABISWAS HQ &amp; Liaison Office
                </span>
                <i class="fas fa-chevron-down ct-chevron"></i>
            </button>

            <div class="ct-hq-panel" id="contactCard">
                <div class="ct-office-grid">

                    <!-- Head Office -->
                    <div class="ct-office">
                        <div class="ct-office-heading">
                            <i class="fas fa-landmark"></i>
                            <h2>Head Office</h2>
                        </div>
                        <ul class="ct-info-list">
                            <li>
                                <i class="fas fa-map-marker-alt"></i>
                                <span>Asma Palace, Court Para, Chuadanga-7200</span>
                            </li>
                            <li>
                                <i class="fas fa-phone"></i>
                                <span><a href="tel:+8801713302930">+880 1713 302930</a></span>
                            </li>
                            <li>
                                <i class="fas fa-envelope"></i>
                                <span><a href="mailto:atmabiswas_ngo@yahoo.com">atmabiswas_ngo@yahoo.com</a></span>
                            </li>
                        </ul>
                        <a href="loc.php" class="ct-branch-btn">
                            <i class="fas fa-code-branch"></i> Other Branches
                        </a>
                        <div class="ct-map">
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3654.990205551057!2d88.84420999999999!3d23.640521800000002!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39fecc6a98f15555%3A0x7237da8c2d53a42d!2sAtmabiswas!5e0!3m2!1sen!2sbd!4v1737729888492!5m2!1sen!2sbd"
                                loading="lazy"
                                allowfullscreen
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                    </div>

                    <!-- Liaison Office -->
                    <div class="ct-office">
                        <div class="ct-office-heading">
                            <i class="fas fa-city"></i>
                            <h2>Liaison Office</h2>
                        </div>
                        <ul class="ct-info-list">
                            <li>
                                <i class="fas fa-map-marker-alt"></i>
                                <span>59, Mia Tower, West Agargaon, BNP bazar, Dhaka - 1207</span>
                            </li>
                            <li>
                                <i class="fas fa-phone"></i>
                                <span><a href="tel:+8801713302930">+880 1713 302930</a></span>
                            </li>
                            <li>
                                <i class="fas fa-envelope"></i>
                                <span><a href="mailto:atmabiswas_ngo@yahoo.com">atmabiswas_ngo@yahoo.com</a></span>
                            </li>
                        </ul>
                        <div class="ct-map">
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3651.0861266058914!2d90.37095479999999!3d23.7799472!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3755c0b3f03cfd27%3A0xbec44bc7d01da1f!2sEl%20Dorado%20Mia%20Tower%2C%20House%23%2059%2C%20West%20Agargaon%2C%20Dhaka%201207!5e0!3m2!1sen!2sbd!4v1748789887571!5m2!1sen!2sbd"
                                loading="lazy"
                                allowfullscreen
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- ── Section 2: Regional Offices ── -->
        <div class="ct-section">
            <button class="ct-toggle" id="newcard">
                <span class="ct-toggle-left">
                    <i class="fas fa-map-marker-alt"></i>
                    ATMABISWAS Regional Offices
                </span>
                <i class="fas fa-chevron-down ct-chevron"></i>
            </button>

            <div class="ct-reg-panel" id="reg">
                <div class="ct-table-wrap">
                    <table class="ct-table">
                        <thead>
                            <tr>
                                <th>Region Name</th>
                                <th>Address</th>
                                <th>Designation</th>
                                <th>Phone</th>
                            </tr>
                        </thead>
                        <tbody id="newtable"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ── Section 3: Branches ── -->
        <div class="ct-section">
            <button class="ct-toggle" id="filterbutton">
                <span class="ct-toggle-left">
                    <i class="fas fa-code-branch"></i>
                    ATMABISWAS Branches
                </span>
                <i class="fas fa-chevron-down ct-chevron"></i>
            </button>

            <div class="ct-br-panel" id="filterbars">
                <div class="ct-filter">
                    <label for="divisionSelect">
                        <i class="fas fa-filter"></i> Select Division
                    </label>
                    <select name="division" id="divisionSelect">
                        <option value="">Loading divisions…</option>
                    </select>
                </div>

                <div class="ct-table-wrap">
                    <table class="ct-table">
                        <thead>
                            <tr>
                                <th>Branch Name</th>
                                <th>Address</th>
                                <th>Division</th>
                                <th>District</th>
                            </tr>
                        </thead>
                        <tbody id="table-body"></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div><!-- /.ct-wrapper -->

    </main>
    <?php include 'footer.php' ?>

    <script src="locations.js?v=<?= filemtime(__DIR__ . '/locations.js') ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <!-- Chevron rotation (supplements locations.js without modifying it) -->
    <script>
    (function () {
        ['toggle-btn', 'newcard', 'filterbutton'].forEach(function (id) {
            var btn = document.getElementById(id);
            if (!btn) return;
            btn.addEventListener('click', function () {
                this.classList.toggle('ct-open');
            });
        });
    }());
    </script>
</body>

</html>
