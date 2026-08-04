<?php
$errorCode    = "403";
$errorTitle   = "Access Forbidden";
$errorHeading = "Sorry, You Don't Have Permission";
$errorMessage = "You don't have access to this page. If you believe this is a mistake, please reach out to our team.";
$errorIcon    = "fa-shield-halved";
$errorButtons = ['home', 'back', 'contact'];
$showSearch   = false;
$autoRedirect = true;

include __DIR__ . '/error-shell.php';
