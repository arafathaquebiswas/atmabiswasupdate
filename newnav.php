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
        }

        /* General Navbar Styles */
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

        /* Dropdown Styles */
        .dropdown {
            position: relative;
        }

        .dropdown .maindrop {
            display: flex;
            align-items: center;
            cursor: pointer;
            font-weight: bold;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .dropdown .maindrop:hover {
            background-color: #005bb5;
        }

        .dropdown .arrow {
            margin-left: 5px;
            transition: transform 0.3s;
        }

        .dropdown .dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: #ffffff;
            color: #0073e6;
            min-width: 200px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            z-index: 1000;
        }

        .dropdown .dropdown-content a {
            color: #0073e6;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
            transition: background-color 0.3s;
        }

        .dropdown .dropdown-content a:hover {
            background-color: #0073e6;
            color: white;
        }

        .dropdown.active .dropdown-content {
            display: block;
        }

        .dropdown.active .arrow {
            transform: rotate(180deg);
        }

        /* Sidebar Styles (Mobile Only) */
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

    <!-- Navbar -->
    <div class="navbar">
        <div class="logo">ATMA BISWAS</div>
        <div class="menu">
            <a href="#" class="active">Home</a>
            <a href="#">Notice</a>
            <a href="#">Career</a>
            <a href="#">Press</a>
            <div class="dropdown">
                <div class="maindrop">
                    What we do
                    <span class="arrow">&#9662;</span>
                </div>
                <div class="dropdown-content">
                    <a href="Pages/Founder.php">Green Energy</a>
                    <a href="SeniorManagement.php">Enterprise Development</a>
                    <a href="Pages/ExecutiveGeneralBody.php">Food and Agriculture</a>
                    <a href="Pages/ExecutiveGeneralBody.php">Ready to Eat</a>
                </div>
            </div>
            <div class="dropdown">
                <div class="maindrop">
                    Our Team
                    <span class="arrow">&#9662;</span>
                </div>
                <div class="dropdown-content">
                    <a href="Pages/Founder.php">Founder</a>
                    <a href="SeniorManagement.php">Senior Management</a>
                    <a href="Pages/ExecutiveGeneralBody.php">Executive and General Body</a>
                </div>
            </div>
            <a href="#">Contact</a>
        </div>
        <div class="menu-toggle" id="menu-toggle">&#9776;</div>
    </div>

    <!-- Mobile Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="close-btn" id="close-btn">&times;</div>
        <a href="#">Home</a>
        <a href="#">Notice</a>
        <a href="#">Career</a>
        <a href="#">Press</a>
        <div class="dropdown">
            <div class="maindrop">
                What we do
                <span class="arrow">&#9662;</span>
            </div>
            <div class="dropdown-content">
                <a href="Pages/Founder.php">Green Energy</a>
                <a href="SeniorManagement.php">Enterprise Development</a>
                <a href="Pages/ExecutiveGeneralBody.php">Food and Agriculture</a>
                <a href="Pages/ExecutiveGeneralBody.php">Ready to Eat</a>
            </div>
        </div>
        <div class="dropdown">
            <div class="maindrop">
                Our Team
                <span class="arrow">&#9662;</span>
            </div>
            <div class="dropdown-content">
                <a href="Pages/Founder.php">Founder</a>
                <a href="SeniorManagement.php">Senior Management</a>
                <a href="Pages/ExecutiveGeneralBody.php">Executive and General Body</a>
            </div>
        </div>
        <a href="#">Contact</a>
    </div>

    <!-- JavaScript -->
    <script>
        const sidebar = document.getElementById('sidebar');
        const menuToggle = document.getElementById('menu-toggle');
        const closeBtn = document.getElementById('close-btn');

        // Toggle Sidebar
        menuToggle.addEventListener('click', () => {
            sidebar.classList.add('active');
        });

        closeBtn.addEventListener('click', () => {
            sidebar.classList.remove('active');
        });

        // Toggle Dropdown Menus
        document.querySelectorAll('.dropdown .maindrop').forEach(drop => {
            drop.addEventListener('click', function () {
                const parent = this.parentElement;
                parent.classList.toggle('active');
            });
        });
    </script>
</body>
</html>
