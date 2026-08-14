<?php
/**
 * Temporary diagnostic page — upload, visit once, then DELETE.
 *
 * Reports why manageAdmins.php returns HTTP 500. It deliberately has no
 * dependencies of its own, so it still renders when auth.php or the database
 * are the thing that is broken. It prints no admin data, only yes/no facts.
 */

// Surface errors on this page only; nothing else is affected.
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Must happen before any output, otherwise the headers are already sent.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: text/plain; charset=utf-8');

function row(string $label, $value): void
{
    printf("%-42s %s\n", $label, is_bool($value) ? ($value ? 'YES' : 'NO  <-- ') : $value);
}

echo "=== ATMABISWAS diagnostic ===\n\n";

echo "-- PHP --\n";
row('PHP version', PHP_VERSION);
row('PHP SAPI', PHP_SAPI);

echo "\n-- Database extensions (need pdo + pdo_mysql) --\n";
row('pdo loaded', extension_loaded('pdo'));
row('pdo_mysql loaded', extension_loaded('pdo_mysql'));
row('mysqli loaded', extension_loaded('mysqli'));
row('PDO drivers', class_exists('PDO') ? implode(', ', PDO::getAvailableDrivers()) : 'PDO class missing');

echo "\n-- Required files present --\n";
$root = dirname(__DIR__, 2);
foreach ([
    'backend/auth.php',
    'backend/Database/db.php',
    'backend/Database/migrate_roles.php',
    'backend/DashBoard/manageAdmins.php',
] as $rel) {
    row($rel, file_exists($root . '/' . $rel));
}

echo "\n-- Loading auth.php --\n";
try {
    require_once $root . '/backend/auth.php';
    row('auth.php loaded', true);
    row('require_super_admin() defined', function_exists('require_super_admin'));
    row('ROLE_SUPER_ADMIN constant', defined('ROLE_SUPER_ADMIN') ? ROLE_SUPER_ADMIN : 'MISSING');
} catch (Throwable $e) {
    echo "FAILED: " . get_class($e) . ': ' . $e->getMessage() . "\n";
    echo "  at " . $e->getFile() . ':' . $e->getLine() . "\n";
}

echo "\n-- Database connection --\n";
try {
    $conn = (new Db())->connect();
    row('connected', $conn instanceof PDO);

    $hasRole = $conn->query("SHOW COLUMNS FROM admins LIKE 'role'")->fetch() !== false;
    row('admins.role column exists', $hasRole);
    if (!$hasRole) {
        echo "  -> run backend/Database/migrate_roles.php\n";
    } else {
        $n = (int) $conn->query("SELECT COUNT(*) FROM admins WHERE role='super_admin'")->fetchColumn();
        row('super admins configured', $n > 0 ? $n : '0  <-- run migrate_roles.php');
    }
    row('total admin accounts', (int) $conn->query("SELECT COUNT(*) FROM admins")->fetchColumn());
} catch (Throwable $e) {
    echo "FAILED: " . get_class($e) . ': ' . $e->getMessage() . "\n";
    echo "  at " . $e->getFile() . ':' . $e->getLine() . "\n";
}

echo "\n-- Your session --\n";
row('logged in', isset($_SESSION['username']));
row('session role', $_SESSION['role'] ?? '(none - log out and back in)');

echo "\n=== end — delete this file when finished ===\n";
