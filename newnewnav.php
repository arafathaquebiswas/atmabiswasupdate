<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responsive Navbar with Dropdowns</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            overflow-x: hidden;
        }

        /* Desktop Navbar */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #0073e6;
            color: white;
            padding: 10px 20px;
        }

        .navbar .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .navbar .menu {
            display: flex;
            gap: 15px;
        }

        .navbar .menu a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .navbar .menu a:hover {
            background-color: #005bb5;
        }

        /* Mobile Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: -250px;
            width: 250px;
            height: 100vh;
            background-color: #0073e6;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            padding: 20px;
            transition: left 0.3s ease;
            z-index: 1000;
        }

        .sidebar.active {
            left: 0;
        }

        .sidebar .close-btn {
            align-self: flex-end;
            font-size: 1.5rem;
            cursor: pointer;
            color: white;
            margin-bottom: 20px;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            margin: 10px 0;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .sidebar a:hover {
            background-color: #005bb5;
        }

        .dropdown {
            width: 100%;
        }

        .dropdown .maindrop {
            display: block;
            font-weight: bold;
            padding: 10px 15px;
            background-color: #005bb5;
            color: #ffffff;
            cursor: pointer;
            border-radius: 5px;
        }

        .dropdown-content {
            display: none;
            flex-direction: column;
            margin-left: 1rem;
        }

        .dropdown.active .dropdown-content {
            display: flex;
        }

        .menu-toggle {
            display: none;
            font-size: 2rem;
            cursor: pointer;
            color: white;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .navbar .menu {
                display: none;
            }

            .menu-toggle {
                display: block;
            }
        }
    </style>
</head>
<body>

    <!-- Desktop Navbar -->
    <div class="navbar">
        <div class="logo">ATMA BISWAS</div>
        <div class="menu">
            <a href="#" class="active">Home</a>
            <a href="#">Notice</a>
            <a href="#">Career</a>
            <a href="#">Press</a>
            <a href="aboutus.php">About Us</a>
                    <div class="dropdown">
            <div class="maindrop">What we do</div>
            <div class="dropdown-content">
                <a href="Pages/Founder.php">Green Energy</a>
                <a href="SeniorManagement.php">Enterprise Development</a>
                <a href="Pages/ExecutiveGeneralBody.php">Food and Agriculture</a>
                <a href="Pages/ExecutiveGeneralBody.php">Ready to Eat</a>
            </div>
        </div>

        <div class="dropdown">
            <div class="maindrop">Our Team</div>
            <div class="dropdown-content">
                <a href="Pages/Founder.php">Founder</a>
                <a href="SeniorManagement.php">Senior Management</a>
                <a href="Pages/ExecutiveGeneralBody.php">Executive and General Body</a>
            </div>
        </div>            
        <a href="Contact.php">Events</a>
        <a href="Contact.php">Contact</a>
        <a href="#" id="login-btn">Login</a>
            <a href="#">Contact</a>
        </div>
        <div class="menu-toggle" id="menu-toggle">&#9776;</div>
    </div>

    <!-- Mobile Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="close-btn" id="close-btn">&times;</div>
        <a href="#" class="active">Home</a>
        <a href="#">Notice</a>
        <a href="#">Career</a>
        <a href="#">Press</a>
        <a href="aboutus.php">About Us</a>

        <div class="dropdown">
            <div class="maindrop">What we do</div>
            <div class="dropdown-content">
                <a href="Pages/Founder.php">Green Energy</a>
                <a href="SeniorManagement.php">Enterprise Development</a>
                <a href="Pages/ExecutiveGeneralBody.php">Food and Agriculture</a>
                <a href="Pages/ExecutiveGeneralBody.php">Ready to Eat</a>
            </div>
        </div>

        <div class="dropdown">
            <div class="maindrop">Our Team</div>
            <div class="dropdown-content">
                <a href="Pages/Founder.php">Founder</a>
                <a href="SeniorManagement.php">Senior Management</a>
                <a href="Pages/ExecutiveGeneralBody.php">Executive and General Body</a>
            </div>
        </div>            
        <a href="Contact.php">Events</a>
        <a href="Contact.php">Contact</a>
        <a href="#" id="login-btn">Login</a>
    </div>

    <!-- JavaScript -->
    <script>
        const sidebar = document.getElementById('sidebar');
        const menuToggle = document.getElementById('menu-toggle');
        const closeBtn = document.getElementById('close-btn');

        // Toggle sidebar
        menuToggle.addEventListener('click', () => {
            sidebar.classList.add('active');
        });

        // Close sidebar
        closeBtn.addEventListener('click', () => {
            sidebar.classList.remove('active');
        });

        // Toggle dropdown menus
        document.querySelectorAll('.dropdown .maindrop').forEach(drop => {
            drop.addEventListener('click', function () {
                this.parentElement.classList.toggle('active');
            });
        });
    </script>
</body>
</html>
