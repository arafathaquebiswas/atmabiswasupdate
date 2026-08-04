<?php if (!isset($_SESSION)) { session_start(); } ?>
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
    <title>Popup Offer Advertisement</title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        .popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.8);
            width: 90%;
            max-width: 400px;
            background: white;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            display: none;
            text-align: center;
            z-index: 1000;
            opacity: 0;
            transition: transform 0.3s ease-out, opacity 0.3s ease-out;
        }
        .popup h2 {
            margin: 0 0 10px;
        }
        .popup p {
            font-size: 16px;
            margin-bottom: 15px;
        }
        .popup .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
        }
        .popup button {
            background: #ff5722;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 5px;
        }
        .popup .close-btn{
            background-color: #ff5722;
        }
        .popup .close-btn:hover{
            background-color: #e64a19;
        }
        .popup button:hover {
            background: #e64a19;
        }
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            z-index: 999;
        }
        @media (max-width: 600px) {
            .popup {
                width: 95%;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="overlay" id="overlay"></div>
    <div class="popup" id="popup">
        <button class="close-btn" onclick="closePopup()">&times;</button>
        <h2>Special Offer!</h2>
        <p>Get 30% off on your first purchase. Limited time only!</p>
        <button onclick="closePopup()">Claim Offer</button>
    </div>
    <script>
        function showPopup() {
            const popup = document.getElementById("popup");
            const overlay = document.getElementById("overlay");
            overlay.style.display = "block";
            popup.style.display = "block";
            setTimeout(() => {
                popup.style.opacity = "1";
                popup.style.transform = "translate(-50%, -50%) scale(1)";
            }, 50);
        }
        function closePopup() {
            const popup = document.getElementById("popup");
            const overlay = document.getElementById("overlay");
            popup.style.opacity = "0";
            popup.style.transform = "translate(-50%, -50%) scale(0.8)";
            setTimeout(() => {
                popup.style.display = "none";
                overlay.style.display = "none";
            }, 300);
            fetch('set_session.php');
        }
        setTimeout(showPopup, 3000);
    </script>
</body>
</html>
<?php $_SESSION['popupShown'] = true; ?>
