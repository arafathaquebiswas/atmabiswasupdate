<?php
// Ends the session completely: data, server-side file and the browser cookie.
require_once __DIR__ . '/../auth.php';

auth_destroy_session();
header('Cache-Control: no-store');
header("Location: loging.php");
exit();
