<?php
// Custom error message
$errorType = $_GET['type'] ?? 'Error';
$errorMsg = $errorType === 'upload' ? 'File upload failed.' : 'Something went wrong.';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Error - ATMABISWAS Admin</title>
  <link rel="icon" type="image/png" href="../images/logo/logo.png">
  <link rel="stylesheet" href="css/error.css">
</head>

<body>
  <div class="error-container">
    <div class="cross-circle">
      <div class="circle-bg"></div>
      <div class="cross">
        <div class="line1"></div>
        <div class="line2"></div>
      </div>
    </div>
    <h1><?php echo htmlspecialchars($errorType); ?></h1>
    <p><?php echo htmlspecialchars($errorMsg); ?></p>
    <button onclick="goToDashboard()">Back to Dashboard</button>
  </div>

  <script src="js/error.js"></script>
</body>

</html>