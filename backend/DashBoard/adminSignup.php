<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../login/loging.php");
    exit();
}



?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Signup - ATMABISWAS</title>
    <link rel="icon" type="image/png" href="../images/logo/logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* ===== Base Styles ===== */
        body {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            /* Dark mode by default */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: background 0.3s ease, color 0.3s ease;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin: 0;
            padding: 0;
        }

        /* ===== Light Mode ===== */
        body.light-mode {
            background: linear-gradient(135deg, #74ebd5, #acb6e5);
            color: #333;
        }

        body.light-mode .signup-card {
            background: white;
            color: #333;
        }

        /* ===== Card Styling ===== */
        .signup-card {
            max-width: 420px;
            width: 90%;
            padding: 35px;
            background: #2c2c2c;
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transition: background 0.3s ease, color 0.3s ease;
        }

        body.light-mode .signup-card {
            background: white;
        }

        /* ===== Primary Button ===== */
        .btn-primary {
            width: 100%;
            border-radius: 25px;
            font-weight: 500;
            padding: 10px 20px;
            background-color: #4e54c8;
            border-color: #4e54c8;
        }

        .btn-primary:hover {
            background-color: #6c63ff;
            border-color: #6c63ff;
        }

        /* ===== Password Rules ===== */
        ul#password-rules {
            list-style: none;
            padding-left: 0;
            margin-top: 0.5rem;
        }

        ul#password-rules li::before {
            content: "• ";
            font-weight: bold;
            margin-right: 4px;
        }

        /* ===== Top Buttons (Dashboard + Theme Toggle) ===== */
        .top-buttons {
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
        }

        .top-buttons a,
        .top-buttons button {
            font-size: 14px;
            padding: 6px 14px;
            border-radius: 20px;
        }

        /* Button Styles */
        .btn-outline-light,
        .btn-outline-dark {
            border: 1px solid white;
            background: transparent;
            color: white;
            transition: background 0.3s ease, color 0.3s ease, border 0.3s ease;
        }

        .btn-outline-light:hover {
            background-color: white;
            color: black;
        }

        body.light-mode .btn-outline-light {
            border-color: #444;
            color: #444;
        }

        body.light-mode .btn-outline-light:hover {
            background-color: #444;
            color: white;
        }

        /* Fix for toggle float */
        #darkModeToggle {
            border: none;
            background: transparent;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
        }

        body.light-mode #darkModeToggle {
            color: #333;
        }

        /* ===== Responsive Design ===== */
        @media (max-width: 600px) {
            .signup-card {
                padding: 25px 20px;
            }

            .top-buttons {
                flex-direction: row;
                justify-content: space-between;
            }

            .top-buttons a,
            .top-buttons button {
                font-size: 13px;
                padding: 5px 10px;
            }
        }
    </style>
</head>

<body>

    <div class="top-buttons">
        <a href="dashboard.php" class="btn btn-outline-light">← Dashboard</a>

    </div>

    <div class="signup-card">
        <h3 class="text-center mb-4">Admin Registration</h3>
        <form action="signup.php" method="POST" id="signupForm">
            <div class="mb-3">
                <label class="form-label">Full name</label>
                <input type="text" name="fullname" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email address</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" id="password" required>
                <ul class="mt-2 small" id="password-rules">
                    <li id="length" class="text-danger">➤ At least 8 characters</li>
                    <li id="uppercase" class="text-danger">➤ One uppercase letter</li>
                    <li id="lowercase" class="text-danger">➤ One lowercase letter</li>
                    <li id="number" class="text-danger">➤ One number</li>
                    <li id="special" class="text-danger">➤ One special character (@, $, !, %, *, ?, &)</li>
                </ul>
            </div>

            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" id="confirm_password" required>
                <div id="match-message" class="small mt-1 text-danger d-none">Passwords do not match.</div>
            </div>

            <button type="submit" class="btn btn-primary">Sign Up</button>
        </form>
    </div>

    <script>
        const passwordField = document.getElementById('password');
        const confirmField = document.getElementById('confirm_password');
        const matchMessage = document.getElementById('match-message');

        const rules = {
            length: document.getElementById('length'),
            uppercase: document.getElementById('uppercase'),
            lowercase: document.getElementById('lowercase'),
            number: document.getElementById('number'),
            special: document.getElementById('special'),
        };

        function validatePassword(val) {
            rules.length.className = val.length >= 8 ? "text-success" : "text-danger";
            rules.uppercase.className = /[A-Z]/.test(val) ? "text-success" : "text-danger";
            rules.lowercase.className = /[a-z]/.test(val) ? "text-success" : "text-danger";
            rules.number.className = /\d/.test(val) ? "text-success" : "text-danger";
            rules.special.className = /[@$!%*?&]/.test(val) ? "text-success" : "text-danger";
        }

        passwordField.addEventListener('input', () => {
            validatePassword(passwordField.value);
            checkPasswordMatch();
        });

        confirmField.addEventListener('input', checkPasswordMatch);

        function checkPasswordMatch() {
            if (confirmField.value !== passwordField.value) {
                matchMessage.classList.remove('d-none');
            } else {
                matchMessage.classList.add('d-none');
            }
        }

        document.getElementById("signupForm").addEventListener("submit", function(e) {
            const val = passwordField.value;
            if (
                val.length < 8 ||
                !/[A-Z]/.test(val) ||
                !/[a-z]/.test(val) ||
                !/\d/.test(val) ||
                !/[@$!%*?&]/.test(val)
            ) {
                alert("Password does not meet all requirements.");
                e.preventDefault();
                return;
            }

            if (passwordField.value !== confirmField.value) {
                alert("Passwords do not match.");
                e.preventDefault();
                return;
            }
        });
    </script>


</body>

</html>