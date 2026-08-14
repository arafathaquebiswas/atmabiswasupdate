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
function auth_login_admin(array $admin): void
{
    auth_boot();

    // Prevent session fixation: the pre-login session id must not survive login.
    session_regenerate_id(true);

    $_SESSION['admin_id'] = (int) $admin['adminId'];
    $_SESSION['role']     = auth_normalize_role($admin['role'] ?? ROLE_ADMIN);
    $_SESSION['email']    = $admin['email'] ?? '';
    // Retained under its original name: existing pages and Navbar.php read this.
    $_SESSION['username'] = $admin['fullname'] ?? ($admin['email'] ?? '');
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

function auth_destroy_session(): void
{
    auth_boot();
    $_SESSION = [];
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
 * Number of super admins in the system, used to block removing the last one.
 */
function count_super_admins(PDO $conn): int
{
    $stmt = $conn->prepare("SELECT COUNT(*) FROM admins WHERE role = ?");
    $stmt->execute([ROLE_SUPER_ADMIN]);
    return (int) $stmt->fetchColumn();
}
