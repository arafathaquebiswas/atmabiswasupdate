<?php
// TEMPLATE ONLY — safe to commit (contains no real secrets).
//
// Copy this file to "db.config.php" in this same folder ON THE SERVER and put
// the REAL database credentials there. db.config.php is git-ignored, so the
// real password never enters the public repository or a deployment.
//
// On Hostinger:
//   hPanel -> Files -> File Manager -> public_html/backend/Database/
//   Create a new file named  db.config.php  with your real values below.
//
// db.php loads this automatically if it exists; environment variables
// (DB_HOST/DB_USER/DB_PASS/DB_NAME) still override it when set.

return [
    'host' => 'localhost',
    'user' => 'your_db_user',
    'pass' => 'your_db_password',
    'name' => 'your_db_name',
];
