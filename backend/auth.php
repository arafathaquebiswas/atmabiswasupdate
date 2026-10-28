<?php

/**
 * Role-based access control for the ATMABISWAS admin backend.
 *
 * Two roles exist:
 *   - super_admin : full control, including destructive actions and admin management
 *   - admin       : may create and edit content, but may not deactivate,
 *                   unpublish or delete anything, and may not touch super admins
 *
 * Usage in a protected page:
 *     require_once __DIR__ . '/auth.php';   // adjust depth per file
 *     require_login();
 *     require_super_admin('delete this blog post');   // destructive actions only
 *
 * Usage in a JSON endpoint:
 *     require_login(true);
 *     require_super_admin('delete this image', true);
 */

require_once __DIR__ . '/Database/db.php';

const ROLE_SUPER_ADMIN = 'super_admin';
const ROLE_ADMIN       = 'admin';

const VALID_ROLES = [ROLE_SUPER_ADMIN, ROLE_ADMIN];

/**
 * Start the session if it has not been started yet.
 */
function auth_boot(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Relative path prefix from the currently executing script back to the project
 * root, e.g. '' at the root, '../../' inside backend/DashBoard/.
 *
 * Derived from the entry script so that included files resolve correctly.
 */
function auth_root_prefix(): string
{
    static $prefix = null;
    if ($prefix !== null) {
        return $prefix;
    }

    $root   = realpath(dirname(__DIR__));
    $script = realpath($_SERVER['SCRIPT_FILENAME'] ?? '');

    if ($root === false || $script === false || strpos($script, $root) !== 0) {
        return $prefix = '';
    }

    $relative = trim(str_replace('\\', '/', substr($script, strlen($root))), '/');
    $depth    = substr_count($relative, '/');

    return $prefix = str_repeat('../', $depth);
}

function auth_login_url(): string
{
    return auth_root_prefix() . 'backend/login/loging.php';
}

function auth_dashboard_url(): string
{
    return auth_root_prefix() . 'backend/DashBoard/dashboard.php';
}

/**
 * Store the authenticated admin in the session. Called by the login pages.
 */
function auth_login_admin(array $admin, string $method = 'password', ?array $google = null): void
{
    auth_boot();

    // Prevent session fixation: the pre-login session id must not survive login.
    session_regenerate_id(true);

    $_SESSION['admin_id'] = (int) $admin['adminId'];
    $_SESSION['role']     = auth_normalize_role($admin['role'] ?? ROLE_ADMIN);
    // Legacy rows keep the address in `username` and leave email/fullname empty.
    $_SESSION['email']    = $admin['email'] ?? ($admin['username'] ?? '');
    // Retained under its original name: existing pages and Navbar.php read this.
    $_SESSION['username'] = $admin['fullname'] ?? ($admin['email'] ?? ($admin['username'] ?? ''));

    // A Google session stays valid only while that exact Google account's
    // access row is approved; see auth_session_still_valid().
    if ($method === 'google' && $google !== null) {
        $_SESSION['login_method']     = 'google';
        $_SESSION['google_access_id'] = (int) $google['id'];
        $_SESSION['google_sub']       = (string) $google['google_sub'];
    } else {
        $_SESSION['login_method'] = 'password';
        unset($_SESSION['google_access_id'], $_SESSION['google_sub']);
    }
}

/**
 * Whether an admins row may sign in at all, by either method. Rows from before
 * the status column carry no value and count as active; any other value
 * ('inactive', 'suspended', ...) shuts the account out.
 */
function auth_admin_is_active(array $admin): bool
{
    $status = strtolower(trim((string) ($admin['status'] ?? '')));
    return $status === '' || $status === 'active';
}

/** Shown for every refused password login, so it never says which part was wrong. */
const AUTH_MSG_INVALID   = 'Incorrect email or password. Please try again.';
const AUTH_MSG_THROTTLED = 'Too many sign-in attempts. Please wait 15 minutes and try again.';

/** Failed sign-ins allowed per account and per network address within the window. */
const AUTH_THROTTLE_WINDOW      = 900;
const AUTH_THROTTLE_PER_ACCOUNT = 5;
const AUTH_THROTTLE_PER_IP      = 30;

/**
 * Rate-limit buckets for a sign-in attempt. Keys are hashed, so the table
 * holds neither email addresses nor IP addresses.
 */
function auth_throttle_buckets(string $method, string $account = ''): array
{
    $buckets = ['ip' => hash('sha256', $method . '|ip|' . ($_SERVER['REMOTE_ADDR'] ?? ''))];
    $account = strtolower(trim($account));
    if ($account !== '') {
        $buckets['account'] = hash('sha256', $method . '|account|' . $account);
    }
    return $buckets;
}

function auth_throttle_ensure_table(PDO $conn): void
{
    $conn->exec(
        "CREATE TABLE IF NOT EXISTS auth_login_failures (
            id        BIGINT   NOT NULL AUTO_INCREMENT PRIMARY KEY,
            bucket    CHAR(64) NOT NULL,
            failed_at DATETIME NOT NULL,
            KEY bucket_time (bucket, failed_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

/** Whether recent failures from this account or address mean the attempt must be refused unchecked. */
function auth_login_throttled(PDO $conn, string $method, string $account = ''): bool
{
    try {
        auth_throttle_ensure_table($conn);
        $since = date('Y-m-d H:i:s', time() - AUTH_THROTTLE_WINDOW);
        $st    = $conn->prepare("SELECT COUNT(*) FROM auth_login_failures WHERE bucket = ? AND failed_at > ?");
        foreach (auth_throttle_buckets($method, $account) as $kind => $bucket) {
            $st->execute([$bucket, $since]);
            $limit = $kind === 'ip' ? AUTH_THROTTLE_PER_IP : AUTH_THROTTLE_PER_ACCOUNT;
            if ((int) $st->fetchColumn() >= $limit) {
                return true;
            }
        }
    } catch (Throwable $e) {
        // A broken limiter must not lock every admin out; the password check still applies.
        error_log('auth_login_throttled: ' . $e->getMessage());
    }
    return false;
}

function auth_note_login_failure(PDO $conn, string $method, string $account = ''): void
{
    try {
        auth_throttle_ensure_table($conn);
        $now = date('Y-m-d H:i:s');
        $st  = $conn->prepare("INSERT INTO auth_login_failures (bucket, failed_at) VALUES (?, ?)");
        foreach (auth_throttle_buckets($method, $account) as $bucket) {
            $st->execute([$bucket, $now]);
        }
        // Rows older than a day can no longer count; clear them now and then.
        if (random_int(1, 50) === 1) {
            $conn->prepare("DELETE FROM auth_login_failures WHERE failed_at < ?")
                 ->execute([date('Y-m-d H:i:s', time() - 86400)]);
        }
    } catch (Throwable $e) {
        error_log('auth_note_login_failure: ' . $e->getMessage());
    }
}

/** After a successful sign-in the account starts afresh; the address keeps its count. */
function auth_clear_login_failures(PDO $conn, string $method, string $account): void
{
    $buckets = auth_throttle_buckets($method, $account);
    if (!isset($buckets['account'])) {
        return;
    }
    try {
        $conn->prepare("DELETE FROM auth_login_failures WHERE bucket = ?")->execute([$buckets['account']]);
    } catch (Throwable $e) {
        error_log('auth_clear_login_failures: ' . $e->getMessage());
    }
}

/**
 * The password sign-in every login form uses: rate limited, and refused with
 * one generic message whatever was wrong. Returns the admin, or null with
 * $error set to the message to show.
 */
function auth_password_login(PDO $conn, string $login, string $password, ?string &$error): ?array
{
    $error = null;
    if (auth_login_throttled($conn, 'password', $login)) {
        $error = AUTH_MSG_THROTTLED;
        return null;
    }
    $admin = auth_attempt_login($conn, $login, $password);
    if ($admin === null) {
        auth_note_login_failure($conn, 'password', $login);
        $error = AUTH_MSG_INVALID;
        return null;
    }
    auth_clear_login_failures($conn, 'password', $login);
    return $admin;
}

/**
 * The admin a login form identifies, if the password is right; otherwise null.
 *
 * Every login page goes through here so they cannot disagree. Three kinds of
 * correct login used to be refused as "Invalid Credentials":
 *  - accounts from the retired newadmin.php page, which kept the address in
 *    `username` rather than `email`, and stored the password in plain text;
 *  - passwords with & ' " < >, because the login checked an HTML-escaped copy
 *    while signup and Change Credentials hash the password as typed.
 * The address is matched ignoring case and stray spaces. A password in an old
 * format is accepted once and replaced at once with a hash of the password as
 * typed, so the old formats disappear as people log in.
 */
function auth_attempt_login(PDO $conn, string $login, string $password): ?array
{
    $login = trim($login);
    if ($login === '' || $password === '') {
        return null;
    }

    $cols  = admin_table_columns($conn);
    $where = [];
    $args  = [];
    if (isset($cols['email'])) {
        $where[]       = 'LOWER(TRIM(email)) = LOWER(:email)';
        $args['email'] = $login;
    }
    if (isset($cols['username'])) {
        $where[]          = 'LOWER(TRIM(username)) = LOWER(:username)';
        $args['username'] = $login;
    }
    if (!$where) {
        return null;
    }

    $stmt = $conn->prepare('SELECT * FROM admins WHERE ' . implode(' OR ', $where));
    $stmt->execute($args);

    // What the old login actually compared: the password run through this filter.
    $escaped = (string) filter_var($password, FILTER_SANITIZE_SPECIAL_CHARS);

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $admin) {
        // The same rule as Google sign-in: a deactivated account cannot sign in.
        if (!auth_admin_is_active($admin)) {
            continue;
        }
        $stored = (string) ($admin['pswd'] ?? '');
        if ($stored === '') {
            continue;
        }
        $isHash = password_get_info($stored)['algoName'] !== 'unknown';

        if ($isHash && password_verify($password, $stored)) {
            $upgrade = password_needs_rehash($stored, PASSWORD_DEFAULT);
        } elseif ($isHash && $escaped !== $password && password_verify($escaped, $stored)) {
            $upgrade = true;
        } elseif (!$isHash && (hash_equals($stored, $password) || hash_equals($stored, $escaped))) {
            $upgrade = true;
        } else {
            continue;
        }

        if ($upgrade) {
            try {
                $conn->prepare('UPDATE admins SET pswd = ? WHERE adminId = ?')
                     ->execute([password_hash($password, PASSWORD_DEFAULT), $admin['adminId']]);
            } catch (Throwable $e) {
                // The login itself is still valid; only the upgrade waits for next time.
                error_log('auth_attempt_login: password upgrade failed: ' . $e->getMessage());
            }
        }
        return $admin;
    }
    return null;
}

/**
 * Coerce an arbitrary stored value into a known role, defaulting to the least
 * privileged one so an unrecognised value can never grant super admin.
 */
function auth_normalize_role(?string $role): string
{
    $role = strtolower(trim((string) $role));
    return in_array($role, VALID_ROLES, true) ? $role : ROLE_ADMIN;
}

/**
 * The current admin as ['id', 'fullname', 'email', 'role'], or null.
 *
 * Sessions created before roles existed carry no 'role' key; those are
 * rehydrated from the database so nobody is silently treated as an admin
 * when they are in fact a super admin (or vice versa).
 */
function current_admin(): ?array
{
    auth_boot();

    if (!isset($_SESSION['username'])) {
        return null;
    }

    if (!isset($_SESSION['role']) || !isset($_SESSION['admin_id'])) {
        if (!auth_rehydrate_session()) {
            return null;
        }
    }

    if (!auth_session_still_valid()) {
        return null;
    }

    return [
        'id'       => (int) ($_SESSION['admin_id'] ?? 0),
        'fullname' => (string) $_SESSION['username'],
        'email'    => (string) ($_SESSION['email'] ?? ''),
        'role'     => auth_normalize_role($_SESSION['role'] ?? null),
    ];
}

/**
 * Rebuild a legacy session from the database. Returns false when the account
 * can no longer be resolved, in which case the session is destroyed.
 */
function auth_rehydrate_session(): bool
{
    try {
        $conn = (new Db())->connect();
        if (!$conn) {
            return false;
        }

        auth_ensure_role_column($conn);

        if (!empty($_SESSION['admin_id'])) {
            $stmt = $conn->prepare(
                "SELECT adminId, fullname, email, role FROM admins WHERE adminId = ? LIMIT 1"
            );
            $stmt->execute([$_SESSION['admin_id']]);
        } else {
            $stmt = $conn->prepare(
                "SELECT adminId, fullname, email, role FROM admins WHERE fullname = ? LIMIT 1"
            );
            $stmt->execute([$_SESSION['username']]);
        }

        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$admin) {
            auth_destroy_session();
            return false;
        }

        $_SESSION['admin_id'] = (int) $admin['adminId'];
        $_SESSION['role']     = auth_normalize_role($admin['role'] ?? ROLE_ADMIN);
        $_SESSION['email']    = $admin['email'] ?? '';
        $_SESSION['username'] = $admin['fullname'];

        return true;
    } catch (Throwable $e) {
        error_log('Auth rehydrate failed: ' . $e->getMessage());
        // Fail closed: an unresolvable session must not keep any privileges.
        return false;
    }
}

/**
 * Whether the signed-in admin may still use the site, checked against the
 * database once per request: the account must still exist and be active, and
 * a Google sign-in must still be approved and tied to the same Google account.
 * Suspending, revoking or deactivating therefore takes effect on the next
 * page, not when the session happens to expire. The session's role and name
 * are refreshed at the same time, so a role change applies at once too.
 *
 * A database error refuses the request but keeps the session, so a brief
 * outage does not sign everybody out; a definite "no" ends the session.
 */
function auth_session_still_valid(): bool
{
    static $verdicts = [];
    $id  = (int) ($_SESSION['admin_id'] ?? 0);
    $key = session_id() . '|' . $id;
    if (isset($verdicts[$key])) {
        return $verdicts[$key];
    }

    try {
        $conn = (new Db())->connect();
        $st   = $conn->prepare("SELECT * FROM admins WHERE adminId = ? LIMIT 1");
        $st->execute([$id]);
        $admin = $st->fetch(PDO::FETCH_ASSOC);
        $valid = $admin !== false && auth_admin_is_active($admin);

        if ($valid && ($_SESSION['login_method'] ?? '') === 'google') {
            $st = $conn->prepare(
                "SELECT COUNT(*) FROM admin_google_access
                 WHERE id = ? AND admin_id = ? AND google_sub = ? AND status = 'approved'"
            );
            $st->execute([(int) ($_SESSION['google_access_id'] ?? 0), $id, (string) ($_SESSION['google_sub'] ?? '')]);
            $valid = (int) $st->fetchColumn() === 1;
        }
    } catch (Throwable $e) {
        error_log('auth_session_still_valid: ' . $e->getMessage());
        return false;
    }

    if (!$valid) {
        auth_destroy_session();
        return $verdicts[$key] = false;
    }

    $_SESSION['role']     = auth_normalize_role($admin['role'] ?? ROLE_ADMIN);
    $_SESSION['email']    = $admin['email'] ?? ($admin['username'] ?? '');
    $_SESSION['username'] = $admin['fullname'] ?? ($admin['email'] ?? ($admin['username'] ?? ''));
    return $verdicts[$key] = true;
}

function auth_destroy_session(): void
{
    auth_boot();
    $_SESSION = [];
    // Expire the cookie too, so the browser stops presenting the old id.
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 42000,
            'path'     => $p['path'],
            'domain'   => $p['domain'],
            'secure'   => $p['secure'],
            'httponly' => $p['httponly'],
            'samesite' => $p['samesite'] ?: 'Lax',
        ]);
    }
    session_destroy();
}

function is_logged_in(): bool
{
    return current_admin() !== null;
}

function is_super_admin(): bool
{
    $admin = current_admin();
    return $admin !== null && $admin['role'] === ROLE_SUPER_ADMIN;
}

function current_role(): string
{
    $admin = current_admin();
    return $admin === null ? ROLE_ADMIN : $admin['role'];
}

function role_label(string $role): string
{
    return auth_normalize_role($role) === ROLE_SUPER_ADMIN ? 'Super Admin' : 'Admin';
}

/**
 * Require any authenticated admin. Redirects to the login page, or emits a
 * 403 JSON body when $json is true.
 */
function require_login(bool $json = false): void
{
    if (is_logged_in()) {
        return;
    }

    if ($json) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Unauthorized access. Please login first.']);
        exit();
    }

    header('Location: ' . auth_login_url());
    exit();
}

/**
 * Require the super_admin role. Every deactivate, unpublish and delete path
 * goes through here.
 *
 * $action is folded into the message shown to a denied admin, e.g.
 * require_super_admin('delete this blog post').
 */
function require_super_admin(string $action = 'perform this action', bool $json = false): void
{
    require_login($json);

    if (is_super_admin()) {
        return;
    }

    $admin = current_admin();
    error_log(sprintf(
        'Denied super-admin action "%s" for admin #%d (%s)',
        $action,
        $admin['id'] ?? 0,
        $admin['fullname'] ?? 'unknown'
    ));

    $message = 'Super admin permission is required to ' . $action . '.';

    if ($json) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
        exit();
    }

    auth_render_denied($message);
}

/**
 * Full-page 403 for a denied non-JSON request.
 */
function auth_render_denied(string $message): void
{
    http_response_code(403);
    $dashboard = htmlspecialchars(auth_dashboard_url(), ENT_QUOTES, 'UTF-8');
    $safe      = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permission Required - ATMABISWAS</title>
    <style>
        body { margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center;
               background:#f8fafc; font-family:'Inter',system-ui,-apple-system,sans-serif; color:#1e293b; }
        .card { background:#fff; max-width:460px; padding:2.5rem; border-radius:16px; text-align:center;
                box-shadow:0 10px 40px rgba(15,23,42,.08); border:1px solid #e2e8f0; }
        .icon { width:64px; height:64px; margin:0 auto 1.25rem; border-radius:50%; background:#fef2f2;
                display:flex; align-items:center; justify-content:center; font-size:1.75rem; }
        h1 { font-size:1.35rem; margin:0 0 .75rem; }
        p { color:#64748b; line-height:1.6; margin:0 0 1.75rem; }
        a { display:inline-block; background:#0f766e; color:#fff; text-decoration:none;
            padding:.7rem 1.5rem; border-radius:8px; font-weight:600; }
        a:hover { background:#115e59; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">&#128274;</div>
        <h1>Permission Required</h1>
        <p>{$safe}</p>
        <a href="{$dashboard}">Back to Dashboard</a>
    </div>
</body>
</html>
HTML;
    exit();
}

/**
 * True when the current admin may modify (delete, or change the role of) the
 * given target admin row. Enforces two rules:
 *   - only a super admin may manage accounts at all
 *   - nobody may act on their own account here
 */
function can_manage_admin(array $target): bool
{
    $admin = current_admin();
    if ($admin === null || $admin['role'] !== ROLE_SUPER_ADMIN) {
        return false;
    }

    return (int) $target['adminId'] !== $admin['id'];
}

/**
 * Make sure admins.role exists, adding it the first time it is needed.
 *
 * blog_manager.php migrates the blogs table the same way, so this matches how
 * the rest of the project heals its own schema. It means the role system works
 * on a database that has never had migrate_roles.php run against it.
 *
 * Returns true when the column is available to query.
 */
function auth_ensure_role_column(PDO $conn): bool
{
    static $ready = null;
    if ($ready !== null) {
        return $ready;
    }

    try {
        if ($conn->query("SHOW COLUMNS FROM admins LIKE 'role'")->fetch() !== false) {
            return $ready = true;
        }

        $conn->exec("ALTER TABLE admins ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'admin'");

        // Guarantee one super admin, otherwise every destructive action would be
        // locked out for everybody. MySQL cannot read the table it updates, so
        // the subquery is wrapped in a derived table.
        $conn->exec(
            "UPDATE admins SET role = 'super_admin'
             WHERE adminId = (SELECT adminId FROM (SELECT MIN(adminId) AS adminId FROM admins) AS first_admin)"
        );

        return $ready = true;
    } catch (Throwable $e) {
        error_log('auth_ensure_role_column failed: ' . $e->getMessage());
        return $ready = false;
    }
}

/**
 * Column names present on the admins table, as a lookup map.
 *
 * The table has drifted between installs: some carry a legacy NOT NULL
 * `username` alongside `email`, some do not. Callers build their statements
 * from this rather than assuming one shape.
 */
function admin_table_columns(PDO $conn): array
{
    static $columns = null;
    if ($columns !== null) {
        return $columns;
    }

    try {
        $names = $conn->query("SHOW COLUMNS FROM admins")->fetchAll(PDO::FETCH_COLUMN);
        return $columns = array_flip($names);
    } catch (Throwable $e) {
        error_log('admin_table_columns failed: ' . $e->getMessage());
        return $columns = [];
    }
}

/**
 * Number of super admins in the system, used to block removing the last one.
 */
function count_super_admins(PDO $conn): int
{
    $stmt = $conn->prepare("SELECT COUNT(*) FROM admins WHERE role = ?");
    $stmt->execute([ROLE_SUPER_ADMIN]);
    return (int) $stmt->fetchColumn();
}
