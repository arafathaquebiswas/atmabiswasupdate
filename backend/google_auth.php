<?php
/**
 * Sign in with Google for the admin panel, restricted to approved addresses.
 *
 * How it fits the existing login: a Google sign-in never creates its own kind
 * of user. Each approved address is tied to one row in `admins`, and signing
 * in with Google signs in as that admin -- same role, same pages, same
 * permissions as their password login. Approval lives in admin_google_access:
 *
 *   pending    someone signed in with Google but nobody has approved them
 *   approved   may sign in, as the admin the row is tied to
 *   suspended  temporarily refused; can be restored
 *   revoked    refused; can be restored by a super admin
 *
 * Only `approved` signs in; everything else, and every address not in the
 * table, is refused. Rows are created and changed only by super admins on
 * google_access.php, never by signing in (an unknown address is recorded as
 * `pending` so it can be approved, but that never grants anything).
 *
 * The Google account is identified by its `sub` (Google's permanent account
 * id), pinned on the first successful sign-in, so a different Google account
 * that later appears under the same address cannot take the row over.
 *
 * ID tokens are verified on the server with firebase/php-jwt (maintained by
 * Google; it is what Google's own PHP client uses): RS256 signature against
 * Google's published keys, issuer, audience (our client id), expiry, and
 * email_verified. Nothing the browser says about the address is trusted.
 */
require_once __DIR__ . '/auth.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;

const GOOGLE_ISSUERS   = ['accounts.google.com', 'https://accounts.google.com'];
const GOOGLE_JWKS_URL  = 'https://www.googleapis.com/oauth2/v3/certs';
const GOOGLE_STATUSES  = ['pending', 'approved', 'suspended', 'revoked'];

/** Unknown addresses recorded as pending requests, at most; beyond this they are only refused. */
const GOOGLE_PENDING_MAX = 100;

/** Shown for every refused Google sign-in, whatever the reason. */
const GOOGLE_MSG_DENIED = 'This Google account is not allowed to sign in here. If you need access, ask an ATMABISWAS administrator.';
const GOOGLE_MSG_FAILED = 'Google sign-in could not be completed. Please try again.';

/**
 * The OAuth Web client id, or '' when Google sign-in is not set up (the
 * button is then hidden and the endpoint refuses everything).
 *
 * Read from the GOOGLE_CLIENT_ID environment variable, else from a config
 * file outside the web root: <folder above public_html>/private/auth.config.php
 * returning ['google_client_id' => '....apps.googleusercontent.com'].
 * The client id is not a secret (it is in the login page's HTML); no client
 * secret is needed or used anywhere.
 */
function google_client_id(): string
{
    static $id = null;
    if ($id !== null) {
        return $id;
    }
    $value = (string) getenv('GOOGLE_CLIENT_ID');
    if ($value === '') {
        $file = dirname(__DIR__, 2) . '/private/auth.config.php';
        if (is_file($file)) {
            $config = include $file;
            $value  = is_array($config) ? (string) ($config['google_client_id'] ?? '') : '';
        }
    }
    $value = trim($value);
    return $id = preg_match('/^[0-9]+-[a-z0-9]+\.apps\.googleusercontent\.com$/', $value) ? $value : '';
}

/**
 * Google's current signing keys, cached on disk for as long as Google says
 * (Cache-Control max-age) so a sign-in does not usually fetch them.
 * $refresh forces a fetch, for a token signed with a key we have not seen.
 */
function google_signing_keys(bool $refresh = false): array
{
    $cache = sys_get_temp_dir() . '/atmabiswas_google_jwks.json';
    if (!$refresh && is_file($cache)) {
        $saved = json_decode((string) file_get_contents($cache), true);
        if (is_array($saved) && ($saved['expires'] ?? 0) > time() && isset($saved['jwks']['keys'])) {
            return JWK::parseKeySet($saved['jwks'], 'RS256');
        }
    }

    $ch = curl_init(GOOGLE_JWKS_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);
    $response = curl_exec($ch);
    $status   = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $hsize    = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    if ($response === false || $status !== 200) {
        throw new RuntimeException('Could not fetch Google signing keys (HTTP ' . $status . ')');
    }
    $headers = substr($response, 0, $hsize);
    $jwks    = json_decode(substr($response, $hsize), true);
    if (!is_array($jwks) || empty($jwks['keys'])) {
        throw new RuntimeException('Google signing keys were not valid JSON');
    }
    $maxAge = preg_match('/max-age=(\d+)/i', $headers, $m) ? (int) $m[1] : 3600;
    $maxAge = max(300, min($maxAge, 86400));
    @file_put_contents($cache, json_encode(['expires' => time() + $maxAge, 'jwks' => $jwks]), LOCK_EX);

    return JWK::parseKeySet($jwks, 'RS256');
}

/**
 * The verified identity in a Google ID token -- ['sub', 'email', 'name'] --
 * or null when the token is not a valid, current, Google-signed token for
 * this site with a verified address.
 */
function google_verify_id_token(string $jwt, string $clientId): ?array
{
    if ($clientId === '' || strlen($jwt) > 8192 || substr_count($jwt, '.') !== 2) {
        error_log('google_verify_id_token: malformed token');
        return null;
    }
    $header = json_decode((string) JWT::urlsafeB64Decode(explode('.', $jwt)[0]), true);
    $kid    = is_array($header) ? (string) ($header['kid'] ?? '') : '';
    if ($kid === '' || ($header['alg'] ?? '') !== 'RS256') {
        error_log('google_verify_id_token: unsupported token header');
        return null;
    }

    try {
        $keys = google_signing_keys();
        if (!isset($keys[$kid])) {
            // Google rotates keys; a token signed with a new one needs a fresh set.
            $keys = google_signing_keys(true);
        }
        JWT::$leeway = 60;
        $claims = (array) JWT::decode($jwt, $keys);
    } catch (Throwable $e) {
        // php-jwt's messages name the failed check only, never token contents.
        error_log('google_verify_id_token: rejected (' . get_class($e) . ': ' . $e->getMessage() . ')');
        return null;
    }

    $verified = $claims['email_verified'] ?? false;
    $email    = strtolower(trim((string) ($claims['email'] ?? '')));
    $sub      = (string) ($claims['sub'] ?? '');

    $ok = in_array($claims['iss'] ?? '', GOOGLE_ISSUERS, true)
        && ($claims['aud'] ?? '') === $clientId
        && (!isset($claims['azp']) || $claims['azp'] === $clientId)
        && isset($claims['exp']) && (int) $claims['exp'] > time() - JWT::$leeway
        && ($verified === true || $verified === 'true')
        && filter_var($email, FILTER_VALIDATE_EMAIL) !== false
        && preg_match('/^[0-9A-Za-z]{1,255}$/', $sub);
    if (!$ok) {
        error_log('google_verify_id_token: claims refused');
        return null;
    }

    return ['sub' => $sub, 'email' => $email, 'name' => mb_substr(trim((string) ($claims['name'] ?? '')), 0, 255)];
}

function google_access_ensure_table(PDO $conn): void
{
    $conn->exec(
        "CREATE TABLE IF NOT EXISTS admin_google_access (
            id            INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
            email         VARCHAR(254) NOT NULL,
            google_sub    VARCHAR(255) NULL,
            google_name   VARCHAR(255) NULL,
            admin_id      INT          NULL,
            status        ENUM('pending','approved','suspended','revoked') NOT NULL DEFAULT 'pending',
            changed_by    INT          NULL,
            changed_at    DATETIME     NULL,
            last_login_at DATETIME     NULL,
            created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY email (email),
            UNIQUE KEY google_sub (google_sub),
            UNIQUE KEY admin_id (admin_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

/**
 * Decide a verified Google identity: the admins row to sign in as, plus the
 * access row, or null when it must be refused. Pins the Google account on the
 * first sign-in and records unknown addresses as pending requests.
 */
function google_access_resolve(PDO $conn, array $identity): ?array
{
    google_access_ensure_table($conn);

    $st = $conn->prepare("SELECT * FROM admin_google_access WHERE email = ? LIMIT 1");
    $st->execute([$identity['email']]);
    $row = $st->fetch(PDO::FETCH_ASSOC);

    if ($row === false) {
        // Unknown: refuse, but keep a pending request a super admin can approve.
        $pending = (int) $conn->query("SELECT COUNT(*) FROM admin_google_access WHERE status = 'pending'")->fetchColumn();
        if ($pending < GOOGLE_PENDING_MAX) {
            try {
                $conn->prepare("INSERT INTO admin_google_access (email, google_sub, google_name, status) VALUES (?, ?, ?, 'pending')")
                     ->execute([$identity['email'], $identity['sub'], $identity['name']]);
            } catch (PDOException $e) {
                // Another request recorded it first, or this Google account is already listed
                // under another address; either way the answer is still no.
            }
        }
        error_log('google sign-in refused: not listed');
        return null;
    }

    // A pinned row only ever accepts the Google account it was pinned to.
    if ($row['google_sub'] !== null && !hash_equals((string) $row['google_sub'], $identity['sub'])) {
        error_log('google sign-in refused: account mismatch on access #' . (int) $row['id']);
        return null;
    }
    if ($row['status'] !== 'approved' || $row['admin_id'] === null) {
        error_log('google sign-in refused: status ' . $row['status'] . ' on access #' . (int) $row['id']);
        return null;
    }

    $st = $conn->prepare("SELECT * FROM admins WHERE adminId = ? LIMIT 1");
    $st->execute([(int) $row['admin_id']]);
    $admin = $st->fetch(PDO::FETCH_ASSOC);
    if ($admin === false || !auth_admin_is_active($admin)) {
        error_log('google sign-in refused: admin account inactive for access #' . (int) $row['id']);
        return null;
    }

    if ($row['google_sub'] === null) {
        // First sign-in: tie the row to this Google account for good. The
        // condition makes a simultaneous second sign-in unable to re-pin it.
        $pin = $conn->prepare("UPDATE admin_google_access SET google_sub = ? WHERE id = ? AND google_sub IS NULL");
        $pin->execute([$identity['sub'], (int) $row['id']]);
        if ($pin->rowCount() !== 1) {
            return null;
        }
        $row['google_sub'] = $identity['sub'];
    }
    $conn->prepare("UPDATE admin_google_access SET last_login_at = NOW(), google_name = ? WHERE id = ?")
         ->execute([$identity['name'], (int) $row['id']]);

    return ['admin' => $admin, 'access' => $row];
}
