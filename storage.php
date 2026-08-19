<?php
/**
 * Persistent storage for admin-uploaded content.
 *
 * Uploads used to be written into uploads/, which is tracked in git. Every
 * Hostinger deployment re-asserts the repository's snapshot of tracked paths
 * over public_html, so admin-uploaded media placed there did not survive a
 * deploy while the database rows referencing it did — posts appeared to vanish.
 *
 * New uploads go to media/ instead. That directory is in .gitignore and is
 * never committed, so the deployment has no opinion about it at all. Paths the
 * repository has never contained are demonstrably left alone on this server:
 * .DS_Store, cow/ and fuel.atmabiswas/ have all survived every deploy to date.
 *
 * The directory is created at runtime by PHP, never by the deployment, and the
 * .htaccess guard is written programmatically rather than committed — committing
 * it would make media/ a repository path again and reintroduce the problem.
 *
 * Reading is unchanged: both uploads/... and media/... are stored in the
 * database as document-root-relative paths and resolve identically, so existing
 * posts keep working and nothing has to be re-uploaded or migrated.
 */

/** Same protection the tracked uploads/.htaccess provides: never execute uploads. */
const MEDIA_HTACCESS = <<<'HTA'
# Uploaded content is never trusted to execute as code — only ever served as
# static files. Written by storage.php rather than committed, so that media/
# stays outside the git repository and survives deployments.
php_flag engine off

<FilesMatch "\.(php|phtml|php\d|pht|phar)$">
    Require all denied
</FilesMatch>
HTA;

/** Absolute filesystem path of a media subdirectory, created if absent. */
function media_path(string $sub): string
{
    $root = __DIR__ . DIRECTORY_SEPARATOR . 'media';
    $dir  = $root . DIRECTORY_SEPARATOR . trim($sub, '/');

    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $guard = $root . DIRECTORY_SEPARATOR . '.htaccess';
    if (!file_exists($guard)) {
        file_put_contents($guard, MEDIA_HTACCESS);
    }

    return $dir . DIRECTORY_SEPARATOR;
}

/**
 * Document-root-relative path of a media subdirectory, with trailing slash.
 * This is the form stored in the database and used in URLs.
 */
function media_dir(string $sub): string
{
    media_path($sub);                      // ensure it exists + is guarded
    return 'media/' . trim($sub, '/') . '/';
}

/**
 * Resolve a stored media path to one that actually exists on disk.
 *
 * Values in the database are document-root-relative and come from two eras:
 * older rows say "uploads/blog_imgs/x.png", newer ones "media/blog_imgs/x.png".
 * Both are served directly, so normally the stored value is returned unchanged.
 *
 * If the stored file is missing, the same sub-path is tried under the other
 * prefix, so a file that moves between the two locations keeps rendering with
 * no database change. When neither exists an empty string is returned, letting
 * callers hide the image instead of emitting a broken <img>.
 *
 * Remote URLs (YouTube thumbnails, etc.) are passed through untouched.
 */
function media_resolve(?string $stored): string
{
    $stored = trim((string) $stored);
    if ($stored === '') {
        return '';
    }
    if (preg_match('~^https?://~i', $stored)) {
        return $stored;
    }

    $rel = ltrim($stored, '/');
    $candidates = [$rel];

    if (strpos($rel, 'uploads/') === 0) {
        $candidates[] = 'media/' . substr($rel, strlen('uploads/'));
    } elseif (strpos($rel, 'media/') === 0) {
        $candidates[] = 'uploads/' . substr($rel, strlen('media/'));
    }

    foreach ($candidates as $candidate) {
        if (is_file(__DIR__ . DIRECTORY_SEPARATOR . $candidate)) {
            return $candidate;
        }
    }
    return '';
}
