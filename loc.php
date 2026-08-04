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
    <title>Branch Locator – ATMABISWAS (আত্মবিশ্বাস) Bangladesh</title>
    <?php include 'seo.php'; ?>

    <link rel="icon" type="image/png" href="LOGO/NGO_logo_monogram.png">
    <link rel="stylesheet" href="locations.css?v=<?php echo filemtime(__DIR__ . '/locations.css'); ?>">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
</head>

<body>
    <?php include 'Navbar.php' ?>

    <main>
    <!-- Hero -->
    <div class="ct-hero">
        <i class="fas fa-map-marked-alt ct-hero-icon"></i>
        <h1>Branch Locator</h1>
        <p>Find an ATMABISWAS branch office near you, anywhere in Bangladesh</p>
    </div>

    <div class="ct-wrapper">
        <a href="<?= CONTACT_PATH ?>" class="ct-back-link">
            <i class="fas fa-arrow-left"></i> Back to Contact
        </a>

        <div class="ct-section ct-locator-section">
            <div class="ct-locator-heading">
                <span class="ct-toggle-left">
                    <i class="fas fa-code-branch"></i>
                    ATMABISWAS Branches
                </span>
                <span class="ct-locator-count" id="branchCount"></span>
            </div>

            <div class="ct-locator-body">
                <div class="ct-locator-list-col">
                    <div class="ct-locator-search">
                        <i class="fas fa-search"></i>
                        <input type="text" id="branchSearch" placeholder="Search by name, address or district…">
                    </div>
                    <div id="storeId" class="ct-locator-list"></div>
                </div>

                <div class="ct-locator-map-col">
                    <div class="ct-locator-map">
                        <div id="map"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </main>
    <?php include 'footer.php' ?>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="allLocations.js?v=<?php echo filemtime(__DIR__ . '/allLocations.js'); ?>"></script>
    <script>
        const map = L.map('map').setView([23.6487093, 88.8487908], 10);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        const marker = L.marker([23.6487093, 88.8487908]).addTo(map);

        function moveToLocation(lat, lng) {
            const target = L.latLng(lat, lng);
            map.flyTo(target, 13);
            marker.setLatLng(target);
        }

        // Client-side search filter over the rendered branch list
        const searchInput = document.getElementById('branchSearch');
        searchInput.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            document.querySelectorAll('#storeId .ct-locator-item').forEach(function (item) {
                item.style.display = item.dataset.search.includes(q) ? '' : 'none';
            });
        });
    </script>
</body>

</html>
