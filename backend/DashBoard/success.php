<?php
$successType = $_GET['type'] ?? 'Success';
$successMsg = $successType === 'upload' ? 'File uploaded successfully!' : 'Registration completed successfully!';

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Success - ATMABISWAS Admin</title>
  <link rel="icon" type="image/png" href="../images/logo/logo.png">
  <link rel="stylesheet" href="css/success.css">
</head>

<body>
  <div class="success-container">
    <div class="checkmark-circle">
      <div class="background"></div>
      <div class="checkmark"></div>
    </div>
    <h1><?php echo htmlspecialchars($successType); ?></h1>
    <p><?php echo htmlspecialchars($successMsg); ?></p>
    <button onclick="goToDashboard()">Back to Dashboard</button>
  </div>

  <script src="js/success.js"></script>
</body>

</html>