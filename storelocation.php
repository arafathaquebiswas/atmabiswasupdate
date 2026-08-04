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
    <title>Branch Locations – ATMABISWAS (আত্মবিশ্বাস) Bangladesh</title>
    <link rel="icon" type="image/png" href="LOGO/NGO_logo_monogram.png">
    <?php include 'seo.php'; ?>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            min-height: 100vh;
            overflow-x: hidden;
        }
        .page-title {
            text-align: center;
            padding: 20px;
            background-color: #0d47a1;
            color: white;
        }
        .container {
            display: flex;
            height: 600px;
        }
        .store-list {
            width: 30%;
            overflow-y: auto;
            background-color: white;
            border-right: 1px solid #ddd;
            padding: 20px;
        }
        .store-item {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .store-item h3 {
            margin: 0;
            font-size: 18px;
        }
        .store-item p {
            margin: 5px 0;
            color: #555;
            font-size: 14px;
        }
        .store-item button {
            margin-top: 10px;
            padding: 8px 12px;
            background-color: #0d47a1;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .store-item button:hover {
            background-color: #0a3880;
        }
        #map {
            width: 70%;
            height: 100%;
        }
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                height: auto;
            }
            .store-list {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid #ddd;
            }
            #map {
                width: 100%;
                height: 400px;
            }
        }
        @media (max-width: 480px) {
            .page-title { padding: 14px; }
            .page-title h1 { font-size: 1.35rem; }
            .store-list { padding: 14px; }
            .store-item { padding: 12px; }
            #map { height: 300px; }
        }
        @media (max-width: 380px) {
            .page-title h1 { font-size: 1.15rem; }
            .store-item h3 { font-size: 16px; }
            .store-item p { font-size: 13px; }
            #map { height: 260px; }
        }
    </style>
</head>
<body>
    <?php include 'Navbar.php'; ?>
    <main>
    <div class="page-title">
        <h1>Branch Locations</h1>
    </div>
    <div class="container">
        <div class="store-list" id="store-list">
            <!-- Store items will be dynamically added here -->
        </div>
        <div id="map"></div>
    </div>

    <script>
        const stores = [
            { name: "Gadget & Gear - Uttara, North Tower", location: { lat: 23.8751, lng: 90.3854 }, address: "Shop 506, 5th Floor, North Tower, Uttara, Dhaka 1230", phone: "0967-8666709", hours: "10:00 AM - 9:00 PM", distance: "144.93 km" },
            { name: "Gadget & Gear - Banani, Road 11", location: { lat: 23.7934, lng: 90.4044 }, address: "ANZ Huq Eleven Square, Plot 01, Block H, Banani, Dhaka 1213", phone: "0967-8666785", hours: "10:00 AM - 9:00 PM", distance: "138.08 km" },
            { name: "Gadget & Gear - Level 6, Bashundhara City", location: { lat: 23.7491, lng: 90.3768 }, address: "Shop 75-76, Block D, Level 6, Bashundhara City", phone: "01717-151515", hours: "10:00 AM - 9:00 PM", distance: "125.50 km" }
        ];

        function initMap() {
            const map = new google.maps.Map(document.getElementById("map"), {
                center: { lat: 23.8103, lng: 90.4125 }, // Centered on Dhaka
                zoom: 12,
            });

            stores.forEach((store, index) => {
                const marker = new google.maps.Marker({
                    position: store.location,
                    map,
                    title: store.name,
                });

                const infoWindow = new google.maps.InfoWindow({
                    content: `
                        <div>
                            <h3>${store.name}</h3>
                            <p><strong>Address:</strong> ${store.address}</p>
                            <p><strong>Phone:</strong> ${store.phone}</p>
                            <p><strong>Hours:</strong> ${store.hours}</p>
                            <button onclick="alert('Navigating to ${store.name}')">Get Directions</button>
                        </div>
                    `,
                });

                marker.addListener("click", () => {
                    infoWindow.open(map, marker);
                });

                // Add to store list
                const storeList = document.getElementById("store-list");
                const storeItem = document.createElement("div");
                storeItem.className = "store-item";
                storeItem.innerHTML = `
                    <h3>${store.name}</h3>
                    <p><strong>Address:</strong> ${store.address}</p>
                    <p><strong>Phone:</strong> ${store.phone}</p>
                    <p><strong>Hours:</strong> ${store.hours}</p>
                    <p><strong>Distance:</strong> ${store.distance}</p>
                    <button onclick="alert('Navigating to ${store.name}')">Get Directions</button>
                `;
                storeList.appendChild(storeItem);
            });
        }
    </script>
    <script async defer src="https://maps.gomaps.pro/maps/api/js?key=AlzaSy0QJPP-rfRIayVVb2TT8I1Zc8XOqYFcN9h&callback=initMap"></script>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>
