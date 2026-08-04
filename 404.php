<?php
$errorCode    = "404";
$errorTitle   = "Page Not Found";
$errorHeading = "Oops! We Couldn't Find That Page";
$errorMessage = "The page you're looking for doesn't exist. It might have been moved, renamed, or never existed in the first place.";
$errorIcon    = "fa-compass";
$errorButtons = ['home', 'back', 'contact'];
$showSearch   = true;
$autoRedirect = true;

include __DIR__ . '/error-shell.php';
