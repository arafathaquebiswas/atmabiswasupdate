<?php
$errorCode    = "401";
$errorTitle   = "Sign In Required";
$errorHeading = "You Need to Sign In to Continue";
$errorMessage = "This page is reserved for logged-in users. Please sign in with your ATMABISWAS account to continue.";
$errorIcon    = "fa-key";
$errorButtons = ['login', 'home', 'back'];
$showSearch   = false;
$autoRedirect = false; // never auto-redirect away from a sign-in prompt

include __DIR__ . '/error-shell.php';
