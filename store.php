<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Branch Locations - ATMABISWAS</title>
    <link rel="icon" type="image/png" href="LOGO/NGO_logo_monogram.png">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #1a1a2e;
            color: #fff;
            margin: 0;
            padding: 0;
            height: 100vh;
        }

        .container-fluid {
            height: 100%;
            padding: 20px;
        }

        .store-list {
            max-height: 80vh;
            overflow-y: auto;
            border-radius: 8px;
        }

        .map-container {
            height: 80vh;
            border-radius: 8px;
            overflow: hidden;
        }

        #map {
            width: 100%;
            height: 100%;
        }

        /* Media Queries for Better Responsiveness */
        @media (max-width: 992px) {
            .map-container {
                height: 50vh;
            }

            .store-list {
                max-height: 50vh;
            }
        }

        @media (max-width: 768px) {
            .map-container {
                height: 40vh;
            }

            .store-list {
                max-height: 40vh;
            }
        }

        @media (max-width: 576px) {
            .store-list {
                max-height: 35vh;
            }

            .map-container {
                height: 45vh;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <a href="Contact.php" class="btn btn-secondary">Go back to Home</a>
        <h2 class="text-center mb-4 mt-2">ATMABISWAS Branchs</h2>
        <div class="row">
            <div class="col-lg-4 col-md-5 col-sm-12 mb-4">
                <div class="store-list bg-light text-dark p-3">
                    <h4>Store Locations</h4>
                    <div class="mb-3">
                        <input type="text" class="form-control" placeholder="Search">
                    </div>
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-action" data-lat="23.8123" data-lng="90.4143">
                            <h5 class="mb-1">Gadget & Gear - Uttara, Sector 11</h5>
                            <p class="mb-1">36, Khawja Gareeb-e-Newaz Avenue, Sector-11, Uttara, Dhaka 1230</p>
                            <small>Phone: 0967-8666793, 01322-883302</small><br>
                            <small>Opening Hours: 10:00 AM - 9:00 PM (Opens Everyday)</small>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" data-lat="23.7945" data-lng="90.4078">
                            <h5 class="mb-1">Gadget & Gear - Gulshan Avenue</h5>
                            <p class="mb-1">Shop 14, Ground Floor, Rupayan Golden Age, 99 Gulshan Avenue, Dhaka 1212</p>
                            <small>Phone: 0967-8666779, 01611-010101</small><br>
                            <small>Opening Hours: 10:00 AM - 8:30 PM - Sunday Closed</small>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" data-lat="23.7892" data-lng="90.4086">
                            <h5 class="mb-1">Gadget Studio by G&G - Gulshan Avenue</h5>
                            <p class="mb-1">Ground Floor, Bti Landmark, 16 Gulshan Avenue, Dhaka 1212</p>
                            <small>Phone: 0967-8666796, 01322-883308</small><br>
                            <small>Opening Hours: 10:00 AM - 8:30 PM - Sunday Closed</small>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-8 col-md-7 col-sm-12">
                <div class="map-container">
                    <div id="map"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        // Initialize the map
        const map = L.map('map').setView([22.7115673, 89.0605441], 10);


        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);


        const marker = L.marker([22.7115673, 89.0605441]).addTo(map);

        // Function to move to a specific location
        function moveToLocation(lat, lng) {
            const target = L.latLng(lat, lng);
            map.flyTo(target, 11); 
            marker.setLatLng(target); 
        }

        // Attach event listeners to list items
        document.querySelectorAll('.list-group-item').forEach((item) => {
            item.addEventListener('click', function (event) {
                event.preventDefault(); // Prevent default link behavior
                const lat = parseFloat(this.getAttribute('data-lat'));
                const lng = parseFloat(this.getAttribute('data-lng'));
                moveToLocation(lat, lng);
            });
        });
    </script>
</body>
</html>
