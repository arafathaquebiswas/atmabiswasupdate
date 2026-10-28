<?php
/**
 * Receives the Google ID token from the "Sign in with Google" button on
 * loging.php and signs in the admin it belongs to, if that Google account is
 * approved (see google_auth.php). Answers JSON: {"redirect": "..."} on
 * success, {"error": "..."} otherwise, always with the same wording for any
 * refusal so it reveals nothing about which accounts exist or their status.
 */
require_once __DIR__ . '/../google_auth.php';
require_once __DIR__ . '/../DashBoard/csrf_helper.php';

header('Content-Type: application/json');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function google_login_reply(int $status, array $body): never
{
    http_response_code($status);
    echo json_encode($body);
    exit();
}

auth_boot();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    google_login_reply(405, ['error' => GOOGLE_MSG_FAILED]);
}
if (!csrf_verify()) {
    google_login_reply(403, ['error' => 'Your session expired. Please reload the page and try again.']);
}
$clientId = google_client_id();
if ($clientId === '') {
    google_login_reply(503, ['error' => 'Google sign-in is not available right now.']);
}

try {
    $conn = (new Db())->connect();
} catch (Throwable $e) {
    error_log('google login: database unavailable: ' . $e->getMessage());
    google_login_reply(503, ['error' => GOOGLE_MSG_FAILED]);
}

if (auth_login_throttled($conn, 'google')) {
    google_login_reply(429, ['error' => AUTH_MSG_THROTTLED]);
}

$identity = google_verify_id_token((string) ($_POST['credential'] ?? ''), $clientId);
if ($identity === null) {
    auth_note_login_failure($conn, 'google');
    google_login_reply(401, ['error' => GOOGLE_MSG_FAILED]);
}

try {
    $granted = google_access_resolve($conn, $identity);
} catch (Throwable $e) {
    error_log('google login: access check failed: ' . $e->getMessage());
    google_login_reply(503, ['error' => GOOGLE_MSG_FAILED]);
}
if ($granted === null) {
    auth_note_login_failure($conn, 'google');
    google_login_reply(403, ['error' => GOOGLE_MSG_DENIED]);
}

auth_login_admin($granted['admin'], 'google', $granted['access']);
google_login_reply(200, ['redirect' => '../DashBoard/dashboard.php']);
