-- Restricted "Sign in with Google" for the admin panel, plus login rate limiting.
--
-- Optional to run by hand: the code creates both tables the first time they
-- are needed (google_access_ensure_table() in backend/google_auth.php and
-- auth_throttle_ensure_table() in backend/auth.php). Neither changes or drops
-- any existing table; `admins` is untouched.

-- Which Google accounts may sign in, and as which admin account.
--   status     only 'approved' may sign in; 'pending' = tried but not approved,
--              'suspended' / 'revoked' = refused (both can be restored).
--   admin_id   the admins.adminId this Google account signs in as (its role
--              and permissions); NULL for a pending request.
--   google_sub Google's permanent account id, pinned on the first sign-in so
--              no other Google account can later use the same row.
CREATE TABLE IF NOT EXISTS admin_google_access (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Failed sign-ins, for rate limiting (5 per account / 30 per address per 15 min).
-- bucket is a SHA-256 hash, so neither email addresses nor IPs are stored.
CREATE TABLE IF NOT EXISTS auth_login_failures (
    id        BIGINT   NOT NULL AUTO_INCREMENT PRIMARY KEY,
    bucket    CHAR(64) NOT NULL,
    failed_at DATETIME NOT NULL,
    KEY bucket_time (bucket, failed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
