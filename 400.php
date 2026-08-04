<?php
$errorCode    = "400";
$errorTitle   = "Bad Request";
$errorHeading = "Oops! Your Request Couldn't Be Understood";
$errorMessage = "Something about that request looks off — maybe a broken link or a mistyped address. Let's get you back on track.";
$errorIcon    = "fa-paper-plane";
$errorButtons = ['home', 'back', 'contact'];
$showSearch   = false;
$autoRedirect = true;

include __DIR__ . '/error-shell.php';
