<?php
$errorCode    = "500";
$errorTitle   = "Internal Server Error";
$errorHeading = "Something Went Wrong on Our Server";
$errorMessage = "We're experiencing a technical issue on our end. Our team has already been notified — please try again shortly.";
$errorIcon    = "fa-server";
$errorButtons = ['retry', 'home', 'contact'];
$showSearch   = false;
$autoRedirect = true;

include __DIR__ . '/error-shell.php';
