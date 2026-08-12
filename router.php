<?php
/**
 * Router script for local development using PHP built-in web server (php -S)
 * Usage: php -S localhost:8000 router.php
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$filePath = __DIR__ . $uri;

// Clean URL rewrite: /status -> status.php
if ($uri === '/status' || $uri === '/status/') {
    include __DIR__ . '/status.php';
    exit;
}

// Serve existing files (images, css, js, php files) directly
if ($uri !== '/' && file_exists($filePath) && !is_dir($filePath)) {
    return false; // Handled directly by PHP built-in server
}

// Serve root index.php
if ($uri === '/' || $uri === '/index.php') {
    include __DIR__ . '/index.php';
    exit;
}

// Serve directory index if present
if (is_dir($filePath) && file_exists(rtrim($filePath, '/') . '/index.php')) {
    include rtrim($filePath, '/') . '/index.php';
    exit;
}

// Custom 404 Page for non-existent routes
http_response_code(404);
include __DIR__ . '/404.php';
