<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Signup Successful - ATMABISWAS</title>
    <link rel="icon" type="image/png" href="../images/logo/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
    body {
        background: linear-gradient(135deg, #2c3e50, #4ca1af);
        font-family: 'Segoe UI', sans-serif;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100vh;
        margin: 0;
        color: white;
        text-align: center;
    }

    .message-box {
        background: #1e1e1e;
        padding: 40px;
        border-radius: 16px;
        box-shadow: 0 12px 24px rgba(255, 255, 255, 0.05);
        width: 320px;
    }

    /* SVG container */
    .checkmark {
        width: 100px;
        height: 100px;
        margin: 0 auto 20px auto;
        stroke: #4CAF50;
        stroke-width: 8;
        stroke-linecap: round;
        stroke-linejoin: round;
        fill: none;
    }

    /* Circle animation */
    .checkmark__circle {
        stroke-dasharray: 314;
        stroke-dashoffset: 314;
        animation: dashCircle 0.6s ease forwards;
    }

    /* Checkmark animation */
    .checkmark__check {
        stroke-dasharray: 48;
        stroke-dashoffset: 48;
        animation: dashCheck 0.4s ease forwards;
        animation-delay: 0.6s;
    }

    @keyframes dashCircle {
        to {
            stroke-dashoffset: 0;
        }
    }

    @keyframes dashCheck {
        to {
            stroke-dashoffset: 0;
        }
    }

    a.btn-success {
        background-color: #4CAF50;
        border-color: #4CAF50;
        font-weight: 600;
        width: 100%;
        border-radius: 25px;
        padding: 10px 20px;
        margin-top: 20px;
        transition: background-color 0.3s ease;
    }

    a.btn-success:hover {
        background-color: #66bb6a;
        border-color: #66bb6a;
        color: white;
        text-decoration: none;
    }
    </style>
</head>

<body>
    <div class="message-box">
        <!-- SVG Checkmark -->
        <svg class="checkmark" viewBox="0 0 100 100">
            <circle class="checkmark__circle" cx="50" cy="50" r="50" />
            <path class="checkmark__check" d="M30 52 L43 65 L70 38" />
        </svg>

        <h2>Successfully Registered!</h2>
        <p>You are now registered as a new admin.</p>
        <a href="../login/loging.php" class="btn btn-success">Go to Login</a>
    </div>
</body>

</html>