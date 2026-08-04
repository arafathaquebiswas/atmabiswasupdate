<?php
/**
 * Auto-prepended to every PHP request via .htaccess (see the
 * "OUTPUT BUFFERING" section there for why this exists).
 *
 * Starts an explicit, unlimited output buffer before any page's own
 * code runs, so that a later session_start() call (e.g. in Navbar.php,
 * which typically runs after a page's own <head> HTML has already been
 * echoed) can still send its Set-Cookie header — headers can be sent
 * at any point up until the buffer is actually flushed, which normally
 * happens automatically at the end of the request.
 *
 * Also sets secure session cookie parameters here rather than in
 * Navbar.php — several endpoints (e.g. deleteblog.php, signup.php) call
 * session_start() directly without including Navbar.php first, so this
 * is the only place guaranteed to run before every session_start() call
 * site-wide. Must be called before session_start(), which is why it
 * lives here rather than in config.php (which several of those same
 * endpoints only include, if at all, after session_start() has run).
 *
 * This file must never produce any visible output itself.
 */
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}
