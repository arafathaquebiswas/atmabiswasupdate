<?php
// Configuration file for ATMABISWAS website
// This ensures paths work correctly across different hosting environments

// Ensure output buffering is active early to allow session_start and headers anywhere
if (ob_get_level() === 0) {
    ob_start();
}

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @session_start();
}

// Belt-and-suspenders: PHP-level no-cache headers for all pages that include
// this file. The .htaccess covers the full server, but PHP headers guarantee
// these pages are never served from cache even if .htaccess is bypassed.
if (!headers_sent()) {
    header('Cache-Control: no-store, no-cache, must-revalidate, private');
    header('Pragma: no-cache');
    header('Expires: 0');
}

// Get the base directory of the website
$base_dir = dirname(__FILE__);

// Define base URL - always points to site root regardless of which script calls this.
// Using SCRIPT_NAME caused subdirectory pages (e.g. backend/career/*.php) to build
// wrong paths like https://site.com/backend/career/index.php instead of
// https://site.com/index.php. We use the host root directly instead.
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = rtrim($_SERVER['HTTP_HOST'], '/');

// SITE_ROOT is always the domain root — safe to use from any subdirectory
define('SITE_ROOT', $protocol . $host);

// BASE_URL kept as an alias for backward compatibility
define('BASE_URL', SITE_ROOT);

// Define common paths
define('LOGIN_PATH', BASE_URL . '/backend/login/prelogin.php');
define('DASHBOARD_PATH', BASE_URL . '/backend/DashBoard/dashboard.php');
define('UPDATE_BLOG_IMAGE_PATH', BASE_URL . '/backend/DashBoard/update_Blog_Image.php');
define('HOME_PATH', BASE_URL . '/index.php');

// Define main page paths
define('NOTICE_PATH', BASE_URL . '/notice.php');
define('CAREER_PATH', BASE_URL . '/career.php');
define('PRESS_PATH', BASE_URL . '/press.php');
define('ABOUTUS_PATH', BASE_URL . '/aboutus.php');
define('CONTACT_PATH', BASE_URL . '/contact.php');
define('EVENTS_PATH', BASE_URL . '/Events.php');
define('SOCIAL_PATH', BASE_URL . '/social.php');

// Define team pages
define('EVE_PATH', BASE_URL . '/eve.php');
define('GENERALBODY_PATH', BASE_URL . '/generalbody.php');
define('SENIOR_MANAGEMENT_PATH', BASE_URL . '/SeniorManagement.php');
define('FOUNDER_PATH', BASE_URL . '/founder.php');

// Define service pages
define('GREEN_ENERGY_PATH', BASE_URL . '/Green_Energy.php');
define('ENTERPRISE_PATH', BASE_URL . '/enterprice.php');
define('AGRICULTURAL_PATH', BASE_URL . '/Agricultural.php');
define('READYTOEAT_PATH', BASE_URL . '/readytoeat.php');
define('HEALTH_PATH', BASE_URL . '/health.php');

// For file includes (server paths)
define('BASE_DIR', $base_dir);
define('BACKEND_DIR', BASE_DIR . '/backend');
define('DATABASE_DIR', BACKEND_DIR . '/Database');

// WhatsApp number every share button posts to, digits only with the country
// code (880 = Bangladesh) and no +, spaces or dashes — the wa.me format.
// Displayed as +880 1714-812943. Defined once here so the number lives in a
// single place rather than being repeated in each template that shares a page.
define('WHATSAPP_SHARE_NUMBER', '8801714812943');
