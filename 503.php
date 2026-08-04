<?php
$errorCode    = "503";
$errorTitle   = "Maintenance in Progress";
$errorHeading = "We'll Be Right Back";
$errorMessage = "ATMABISWAS is currently undergoing scheduled maintenance. We'll be back online shortly — thank you for your patience.";
$errorIcon    = "fa-screwdriver-wrench";
$errorButtons = ['retry', 'home', 'contact'];
$showSearch   = false;
$autoRedirect = true;

// Tells well-behaved crawlers/clients when to check back — safe to send
// whether this was reached via ErrorDocument or the maintenance-mode rewrite.
header('Retry-After: 3600');

include __DIR__ . '/error-shell.php';
