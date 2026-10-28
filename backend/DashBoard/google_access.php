<?php
/**
 * Google Sign-in Access: which Google accounts may sign in to the admin panel,
 * and as which admin. Super admins only.
 *
 * Every Google address is tied to one admin account and signs in with that
 * account's role. Rules enforced here, on the server:
 *  - nobody changes the row tied to their own account (no self-approval);
 *  - an admin account has at most one Google address, and an address at most
 *    one admin account;
 *  - only active admin accounts can be linked;
 *  - a new account made here always gets the plain Admin role -- promoting it
 *    stays on Manage Admins, which already protects the last super admin;
 *  - nothing here deletes an admin account.
 * See ../google_auth.php for how sign-in uses these rows.
 */
require_once __DIR__ . '/../google_auth.php';
require_once __DIR__ . '/csrf_helper.php';

require_super_admin('manage Google sign-in access');

$me   = current_admin();
$conn = (new Db())->connect();
google_access_ensure_table($conn);

function ga_back(string $type, string $message): never
{
    $_SESSION['ga_flash'] = ['type' => $type, 'msg' => $message];
    header('Location: google_access.php');
    exit();
}

function ga_row(PDO $conn, int $id): ?array
{
    $st = $conn->prepare("SELECT * FROM admin_google_access WHERE id = ?");
    $st->execute([$id]);
    return $st->fetch(PDO::FETCH_ASSOC) ?: null;
}

function ga_existing_email(PDO $conn, string $email): bool
{
    $st = $conn->prepare("SELECT COUNT(*) FROM admin_google_access WHERE email = ?");
    $st->execute([$email]);
    return (int) $st->fetchColumn() > 0;
}

/**
 * The admin account a Google address should sign in as, from the form: an
 * existing active account (never the current user's, never one already tied
 * to another address), or a new plain Admin account. Returns the adminId, or
 * a message string when the choice is not allowed.
 */
function ga_target_admin(PDO $conn, array $me, string $email, string $googleName, ?int $forRow): int|string
{
    $link = (string) ($_POST['link'] ?? '');

    if ($link === 'new') {
        $name = trim((string) ($_POST['new_name'] ?? '')) ?: ($googleName !== '' ? $googleName : strstr($email, '@', true));
        $name = mb_substr($name, 0, 100);
        $dup  = $conn->prepare("SELECT COUNT(*) FROM admins WHERE LOWER(TRIM(email)) = ? OR LOWER(TRIM(username)) = ?");
        $dup->execute([$email, $email]);
        if ((int) $dup->fetchColumn() > 0) {
            return 'An admin account with that address already exists. Choose it from the list instead.';
        }
        $cols   = admin_table_columns($conn);
        $values = [
            'email'    => $email,
            'fullname' => $name,
            'username' => $email,
            // Random and never shown: this account signs in with Google only.
            'pswd'     => password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT),
            'role'     => ROLE_ADMIN,
            'status'   => 'active',
        ];
        $values = array_intersect_key($values, $cols);
        $conn->prepare('INSERT INTO admins (' . implode(',', array_keys($values)) . ') VALUES ('
                       . implode(',', array_fill(0, count($values), '?')) . ')')
             ->execute(array_values($values));
        return (int) $conn->lastInsertId();
    }

    $adminId = (int) $link;
    if ($adminId === (int) $me['id']) {
        return 'You cannot give your own account Google access. Ask another super admin to do it.';
    }
    $st = $conn->prepare("SELECT * FROM admins WHERE adminId = ?");
    $st->execute([$adminId]);
    $admin = $st->fetch(PDO::FETCH_ASSOC);
    if (!$admin || !auth_admin_is_active($admin)) {
        return 'Choose an active admin account for this address to sign in as.';
    }
    $st = $conn->prepare("SELECT COUNT(*) FROM admin_google_access WHERE admin_id = ? AND id <> ?");
    $st->execute([$adminId, (int) $forRow]);
    if ((int) $st->fetchColumn() > 0) {
        return 'That admin account already has a Google address. Each account can have only one.';
    }
    return $adminId;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        ga_back('error', 'Your session expired. Please reload the page and try again. Nothing was changed.');
    }
    $action = (string) ($_POST['action'] ?? '');
    $now    = date('Y-m-d H:i:s');

    try {
        if ($action === 'add') {
            $email = strtolower(trim((string) ($_POST['email'] ?? '')));
            if (strlen($email) > 254 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                ga_back('error', 'Enter a valid Google email address.');
            }
            if (ga_existing_email($conn, $email)) {
                ga_back('error', 'That address is already in the list below. Change its status there.');
            }
            $conn->beginTransaction();
            $target = ga_target_admin($conn, $me, $email, '', null);
            if (is_string($target)) {
                $conn->rollBack();
                ga_back('error', $target . ' Nothing was changed.');
            }
            $conn->prepare("INSERT INTO admin_google_access (email, admin_id, status, changed_by, changed_at) VALUES (?, ?, 'approved', ?, ?)")
                 ->execute([$email, $target, $me['id'], $now]);
            $conn->commit();
            ga_back('success', "{$email} is approved. It can sign in with Google now.");
        }

        if (!in_array($action, ['approve', 'suspend', 'revoke', 'restore'], true)) {
            ga_back('error', 'Unknown action. Nothing was changed.');
        }
        $row = ga_row($conn, (int) ($_POST['id'] ?? 0));
        if (!$row) {
            ga_back('error', 'That entry no longer exists. Nothing was changed.');
        }
        if ($row['admin_id'] !== null && (int) $row['admin_id'] === (int) $me['id']) {
            ga_back('error', 'You cannot change your own Google sign-in. Ask another super admin. Nothing was changed.');
        }

        $from = $row['status'];
        $allowed = [
            'approve' => ['pending', 'revoked'],
            'suspend' => ['approved'],
            'revoke'  => ['pending', 'approved', 'suspended'],
            'restore' => ['suspended', 'revoked'],
        ][$action];
        if (!in_array($from, $allowed, true)) {
            ga_back('error', "That entry is {$from}, so it cannot be changed that way. Nothing was changed.");
        }

        $conn->beginTransaction();
        $adminId = $row['admin_id'] === null ? null : (int) $row['admin_id'];
        if ($action === 'approve' || ($action === 'restore' && $adminId === null)) {
            $target = ga_target_admin($conn, $me, $row['email'], (string) $row['google_name'], (int) $row['id']);
            if (is_string($target)) {
                $conn->rollBack();
                ga_back('error', $target . ' Nothing was changed.');
            }
            $adminId = $target;
        } elseif ($action === 'restore') {
            // Restoring signs in as the same account as before; it must still be usable.
            $target = $conn->prepare("SELECT * FROM admins WHERE adminId = ?");
            $target->execute([$adminId]);
            $admin = $target->fetch(PDO::FETCH_ASSOC);
            if (!$admin || !auth_admin_is_active($admin)) {
                $conn->rollBack();
                ga_back('error', 'The admin account this address signed in as is no longer active. Approve it again and choose an account. Nothing was changed.');
            }
        }
        $to = ['approve' => 'approved', 'suspend' => 'suspended', 'revoke' => 'revoked', 'restore' => 'approved'][$action];
        $conn->prepare("UPDATE admin_google_access SET status = ?, admin_id = ?, changed_by = ?, changed_at = ? WHERE id = ? AND status = ?")
             ->execute([$to, $adminId, $me['id'], $now, (int) $row['id'], $from]);
        $conn->commit();

        $done = [
            'approved'  => 'is approved. It can sign in with Google now.',
            'suspended' => 'is suspended. It is signed out and cannot sign in until restored.',
            'revoked'   => 'is revoked. It is signed out and cannot sign in.',
        ][$to];
        ga_back('success', "{$row['email']} {$done}");
    } catch (Throwable $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log('google_access: ' . $action . ' failed: ' . $e->getMessage());
        ga_back('error', 'Could not save the change. Nothing was changed.');
    }
}

$flash = $_SESSION['ga_flash'] ?? null;
unset($_SESSION['ga_flash']);

$rows = $conn->query(
    "SELECT g.*, a.fullname AS admin_name, a.email AS admin_email, a.role AS admin_role
     FROM admin_google_access g LEFT JOIN admins a ON a.adminId = g.admin_id
     ORDER BY FIELD(g.status, 'pending', 'approved', 'suspended', 'revoked'), g.email"
)->fetchAll(PDO::FETCH_ASSOC);

// Accounts a Google address may be tied to: active, not the current user, not already tied.
$linkable = array_filter(
    $conn->query(
        "SELECT a.* FROM admins a LEFT JOIN admin_google_access g ON g.admin_id = a.adminId
         WHERE g.id IS NULL ORDER BY a.fullname"
    )->fetchAll(PDO::FETCH_ASSOC),
    fn($a) => auth_admin_is_active($a) && (int) $a['adminId'] !== (int) $me['id']
);

$configured = google_client_id() !== '';
$badge = [
    'pending'   => ['Pending',   'muted'],
    'approved'  => ['Approved',  'ok'],
    'suspended' => ['Suspended', 'warn'],
    'revoked'   => ['Revoked',   'bad'],
];

/** The "signs in as" choice shared by the add form and the approve forms. */
function ga_link_select(array $linkable, string $suggestEmail = '', string $id = ''): string
{
    $html = '<select name="link"' . ($id !== '' ? ' id="' . $id . '"' : '') . ' required><option value="">Signs in as…</option>';
    foreach ($linkable as $a) {
        $label = ($a['fullname'] ?: $a['email']) . ' — ' . ($a['email'] ?: $a['username']) . ' (' . role_label((string) $a['role']) . ')';
        $same  = $suggestEmail !== '' && strtolower(trim((string) $a['email'])) === $suggestEmail;
        $html .= '<option value="' . (int) $a['adminId'] . '"' . ($same ? ' selected' : '') . '>' . htmlspecialchars($label) . '</option>';
    }
    return $html . '<option value="new">Create a new admin account (role: Admin)</option></select>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Sign-in Access - ATMABISWAS</title>
    <link rel="stylesheet" href="css/uploadfile.css">
    <link rel="stylesheet" href="css/admin-sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="../images/logo/logo.png">
    <style>
        .ga-wrap { max-width: 1100px; margin: 1.5rem auto 3rem; padding: 0 2rem; }
        .ga-wrap h1 { margin: 0 0 .35rem; font-size: 1.4rem; color: #0f172a; }
        .ga-sub { color: #64748b; font-size: .88rem; margin: 0 0 1.25rem; line-height: 1.55; max-width: 780px; }
        .ga-msg { padding: .8rem 1rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: .9rem; }
        .ga-msg.success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .ga-msg.error   { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .ga-msg.warn    { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
        .ga-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; margin-bottom: 1.5rem;
                   box-shadow: 0 4px 16px rgba(15,23,42,.05); }
        .ga-card h2 { margin: 0; padding: .85rem 1.25rem; font-size: 1.05rem; color: #fff; display: flex; gap: .6rem; align-items: center;
                      background: linear-gradient(135deg,#1e3a5f 0%,#0073e6 100%); }
        .ga-body { padding: 1.25rem; }
        .ga-add { display: grid; grid-template-columns: 1.2fr 1.4fr 1fr auto; gap: .6rem; align-items: end; }
        .ga-add label { display: block; font-size: .75rem; font-weight: 600; color: #334155; margin-bottom: .3rem; }
        .ga-wrap input[type=email], .ga-wrap input[type=text], .ga-wrap select {
            width: 100%; box-sizing: border-box; font: inherit; font-size: .85rem; padding: .5rem .6rem;
            border: 1px solid #cbd5e1; border-radius: 7px; background: #fff; color: #0f172a; }
        .ga-btn { border: none; border-radius: 7px; padding: .5rem .85rem; font-size: .8rem; font-weight: 600; cursor: pointer;
                  font-family: inherit; display: inline-flex; align-items: center; gap: .35rem; white-space: nowrap; }
        .ga-primary { background: #2563eb; color: #fff; } .ga-primary:hover { background: #1d4ed8; }
        .ga-plain   { background: #fff; color: #334155; border: 1px solid #cbd5e1; } .ga-plain:hover { background: #f8fafc; }
        .ga-danger  { background: #fff; color: #b91c1c; border: 1px solid #fecaca; } .ga-danger:hover { background: #fef2f2; }
        table.ga-table { width: 100%; border-collapse: collapse; font-size: .84rem; }
        .ga-table th { text-align: left; font-size: .72rem; text-transform: uppercase; letter-spacing: .5px; color: #64748b;
                       padding: .6rem .75rem; border-bottom: 1px solid #e2e8f0; }
        .ga-table td { padding: .7rem .75rem; border-bottom: 1px solid #f1f5f9; vertical-align: top; color: #0f172a; }
        .ga-small { color: #64748b; font-size: .76rem; }
        .ga-badge { font-size: .7rem; font-weight: 700; padding: .2rem .55rem; border-radius: 999px; }
        .ga-badge.ok { background: #dcfce7; color: #166534; } .ga-badge.muted { background: #f1f5f9; color: #475569; }
        .ga-badge.warn { background: #fef3c7; color: #92400e; } .ga-badge.bad { background: #fee2e2; color: #991b1b; }
        .ga-actions { display: flex; flex-wrap: wrap; gap: .4rem; align-items: center; }
        .ga-actions form { display: flex; gap: .4rem; align-items: center; flex-wrap: wrap; }
        .ga-actions select { width: auto; max-width: 260px; }
        @media (max-width: 860px) { .ga-add { grid-template-columns: 1fr; } .ga-wrap { padding: 0 1rem; }
            .ga-table thead { display: none; } .ga-table td { display: block; border: none; padding: .35rem .75rem; }
            .ga-table tr { display: block; border-bottom: 1px solid #e2e8f0; padding: .5rem 0; } }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar-container">
            <?php include 'sidebar.php'; ?>
        </div>
        <div class="main-content">
            <div class="ga-wrap">
                <h1><i class="fab fa-google"></i> Google Sign-in Access</h1>
                <p class="ga-sub">
                    Only the Google accounts approved here can use “Sign in with Google”, and each signs in as the admin
                    account you choose, with that account's role. Anyone else who tries is refused and appears below as
                    <strong>Pending</strong> — nothing is approved automatically. Suspending or revoking signs the person
                    out on their next click. Password sign-in is not affected.
                </p>

                <?php if ($flash): ?>
                    <div class="ga-msg <?= htmlspecialchars($flash['type']) ?>" role="status"><?= htmlspecialchars($flash['msg']) ?></div>
                <?php endif; ?>
                <?php if (!$configured): ?>
                    <div class="ga-msg warn">Google sign-in is not configured on this server yet, so the button is hidden on the
                        login page. You can still prepare the list; it takes effect once the Google client ID is set.</div>
                <?php endif; ?>

                <section class="ga-card">
                    <h2><i class="fas fa-user-plus"></i> Approve a Google address</h2>
                    <div class="ga-body">
                        <form method="POST" class="ga-add">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="add">
                            <div>
                                <label for="ga-email">Google email address</label>
                                <input type="email" id="ga-email" name="email" maxlength="254" required placeholder="name@gmail.com">
                            </div>
                            <div>
                                <label for="ga-link">Signs in as</label>
                                <?= ga_link_select($linkable, '', 'ga-link') ?>
                            </div>
                            <div>
                                <label for="ga-name">Name (only for a new account)</label>
                                <input type="text" id="ga-name" name="new_name" maxlength="100">
                            </div>
                            <button type="submit" class="ga-btn ga-primary"><i class="fas fa-check"></i> Approve</button>
                        </form>
                    </div>
                </section>

                <section class="ga-card">
                    <h2><i class="fas fa-list"></i> Google accounts (<?= count($rows) ?>)</h2>
                    <div class="ga-body">
                        <?php if (!$rows): ?>
                            <p class="ga-small">No Google accounts yet. Approve one above.</p>
                        <?php else: ?>
                        <table class="ga-table">
                            <thead><tr><th>Google account</th><th>Signs in as</th><th>Status</th><th>Last sign-in</th><th>Actions</th></tr></thead>
                            <tbody>
                            <?php foreach ($rows as $r):
                                [$bText, $bClass] = $badge[$r['status']];
                                $mine = $r['admin_id'] !== null && (int) $r['admin_id'] === (int) $me['id'];
                            ?>
                                <tr id="ga-<?= (int) $r['id'] ?>">
                                    <td>
                                        <strong><?= htmlspecialchars($r['email']) ?></strong>
                                        <?php if ($r['google_name']): ?><div class="ga-small"><?= htmlspecialchars($r['google_name']) ?></div><?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($r['admin_id'] !== null && $r['admin_name'] !== null): ?>
                                            <?= htmlspecialchars($r['admin_name'] ?: $r['admin_email']) ?>
                                            <div class="ga-small"><?= htmlspecialchars(role_label((string) $r['admin_role'])) ?></div>
                                        <?php else: ?>
                                            <span class="ga-small">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="ga-badge <?= $bClass ?>"><?= $bText ?></span></td>
                                    <td class="ga-small"><?= $r['last_login_at'] ? htmlspecialchars($r['last_login_at']) : 'Never' ?></td>
                                    <td>
                                        <?php if ($mine): ?>
                                            <span class="ga-small">Your own sign-in — another super admin must change it.</span>
                                        <?php else: ?>
                                        <div class="ga-actions">
                                            <?php if ($r['status'] === 'pending' || ($r['status'] === 'revoked' && $r['admin_id'] === null)): ?>
                                            <form method="POST">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <?= ga_link_select($linkable, $r['email']) ?>
                                                <button type="submit" class="ga-btn ga-primary"><i class="fas fa-check"></i> Approve</button>
                                            </form>
                                            <?php endif; ?>
                                            <?php foreach (['suspend' => ['approved'], 'restore' => ['suspended', 'revoked'], 'revoke' => ['pending', 'approved', 'suspended']] as $act => $from):
                                                if (!in_array($r['status'], $from, true) || ($act === 'restore' && $r['admin_id'] === null)) continue;
                                                [$icon, $cls, $label, $confirm] = [
                                                    'suspend' => ['fa-pause', 'ga-plain', 'Suspend', 'Suspend this Google sign-in? The person is signed out at once.'],
                                                    'restore' => ['fa-undo', 'ga-plain', 'Restore', 'Restore this Google sign-in?'],
                                                    'revoke'  => ['fa-ban', 'ga-danger', $r['status'] === 'pending' ? 'Reject' : 'Revoke', 'Refuse this Google account? It cannot sign in.'],
                                                ][$act];
                                            ?>
                                            <form method="POST" onsubmit="return confirm(<?= htmlspecialchars(json_encode($confirm)) ?>);">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                                <input type="hidden" name="action" value="<?= $act ?>">
                                                <button type="submit" class="ga-btn <?= $cls ?>"><i class="fas <?= $icon ?>"></i> <?= $label ?></button>
                                            </form>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </div>
    </div>
</body>
</html>
