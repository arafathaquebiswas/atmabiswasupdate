<?php
require_once __DIR__ . '/../image_optimize.php';
require_once __DIR__ . '/../storage.php';
session_start();

header('Content-Type: application/json');

/** Send a JSON reply and stop. Every exit path below goes through here. */
function press_reply(int $http, array $body): void
{
  http_response_code($http);
  echo json_encode($body);
  exit();
}

// Check if user is logged in
require_once __DIR__ . '/auth.php';

if (!is_logged_in()) {
  press_reply(401, [
    'status'  => 'error',
    'message' => 'Your session has expired. Sign in again in another tab, then click the button again — your text is still in the editor.'
  ]);
}

require_once __DIR__ . '/DashBoard/csrf_helper.php';
require_once __DIR__ . '/blogSanitizer.php';

/* A request larger than post_max_size reaches PHP with $_POST and $_FILES both
   empty, which used to surface as the misleading "expired session token". */
if (empty($_POST) && empty($_FILES) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
  error_log('Press publish: request body of ' . (int)$_SERVER['CONTENT_LENGTH']
    . ' bytes exceeded post_max_size (' . ini_get('post_max_size') . ')');
  press_reply(413, [
    'status'  => 'error',
    'message' => 'The post is larger than the server accepts. Use a smaller thumbnail or fewer pasted images and try again.'
  ]);
}

if (!csrf_verify()) {
  press_reply(403, [
    'status'  => 'error',
    'message' => 'Invalid or expired session token. Please reload the editor and try again.'
  ]);
}

/* Draft vs. publish must be an explicit choice. The editor used to fall back to
   "published" when the value was missing and to "draft" whenever anything other
   than exactly "published" arrived, so the outcome depended on which button the
   browser happened to report. */
$post_status = $_POST['post_status_action'] ?? '';
if (!in_array($post_status, ['published', 'draft'], true)) {
  press_reply(422, [
    'status'  => 'error',
    'message' => 'Click "Publish Press Post" or "Save Draft" to save the post.'
  ]);
}

/* Retries of the same editor submission (a double click, a timeout, the
   network dropping after the row was written) return the post that submission
   already created instead of inserting a second copy. The token comes from the
   editor page and is recorded only after a successful insert, so a submission
   that failed validation can be corrected and sent again. */
$submission_token = (string)($_POST['submission_token'] ?? '');
if (!preg_match('/^[a-f0-9]{32}$/', $submission_token)) {
  $submission_token = '';
}
if ($submission_token !== '' && isset($_SESSION['press_submissions'][$submission_token])) {
  $done = $_SESSION['press_submissions'][$submission_token];
  press_reply(200, [
    'status'      => 'success',
    'message'     => 'This post was already saved (#' . $done['post_id'] . '). Nothing was duplicated.',
    'post_id'     => $done['post_id'],
    'post_status' => $done['status'],
    'duplicate'   => true
  ]);
}

$dest = null;   // thumbnail written to disk, removed again if the insert fails

try {
  // Use the centralized database connection
  require_once __DIR__ . '/Database/db.php';
  $db = new Db();
  $pdo = $db->connect();

  // Validate and sanitize input
  $title    = trim($_POST['blog_title']    ?? '');
  $content  = sanitize_blog_html($_POST['blog_content']    ?? '');
  $summary  = sanitize_blog_html($_POST['summary_content'] ?? '');
  $author   = $_SESSION['username']        ?? 'ATMABISWAS';
  $category = trim($_POST['category']      ?? 'news');
  $tags     = trim($_POST['tags']          ?? '');
  $featured = isset($_POST['featured']) ? 1 : 0;

  // Slug is derived from the title; the editor no longer exposes it.
  $slug = mb_strtolower($title, 'UTF-8');
  $slug = preg_replace('/[^a-z0-9\s-]/u', '', $slug);
  $slug = preg_replace('/\s+/', '-', trim($slug));
  $slug = preg_replace('/-+/', '-', $slug);
  $slug = trim($slug, '-');
  // Ensure slug uniqueness by appending timestamp if needed
  if ($slug) $slug = $slug . '-' . time();

  // Calculate reading time (words ÷ 200 wpm)
  $reading_time = max(1, (int)ceil(str_word_count(strip_tags($content)) / 200));

  $allowed_categories = ['news', 'media', 'announcement', 'press'];
  if (!in_array($category, $allowed_categories, true)) $category = 'news';

  // Validation
  $problem = press_validate_text($title, $summary, $content);
  if ($problem === null) {
    [$urls, $problem] = press_read_urls($_POST);
  }
  if ($problem !== null) {
    press_reply(422, ['status' => 'error', 'message' => $problem]);
  }

  // Thumbnail is required
  $thumb_error = $_FILES['thumbnail']['error'] ?? UPLOAD_ERR_NO_FILE;
  if ($thumb_error !== UPLOAD_ERR_OK) {
    $upload_messages = [
      UPLOAD_ERR_NO_FILE   => 'Press thumbnail is required.',
      UPLOAD_ERR_INI_SIZE  => 'The thumbnail is larger than the server accepts. Use an image under 3 MB.',
      UPLOAD_ERR_FORM_SIZE => 'The thumbnail is larger than the server accepts. Use an image under 3 MB.',
      UPLOAD_ERR_PARTIAL   => 'The thumbnail upload was interrupted. Please try again.',
    ];
    if (!isset($upload_messages[$thumb_error])) {
      error_log('Press publish: thumbnail upload failed with PHP upload error ' . $thumb_error);
    }
    press_reply(422, [
      'status'  => 'error',
      'message' => $upload_messages[$thumb_error] ?? 'The server could not receive the thumbnail. Please try again.'
    ]);
  }

  // Process thumbnail upload
  $thumb_file = $_FILES['thumbnail'];
  $finfo      = new finfo(FILEINFO_MIME_TYPE);
  $mime       = $finfo->file($thumb_file['tmp_name']);

  // Maps a validated MIME type to the extension we save with — never
  // taken from the attacker-supplied filename.
  $allowedThumbTypes = ['image/jpeg' => 'jpg', 'image/jpg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
  if (!array_key_exists($mime, $allowedThumbTypes)) {
    press_reply(422, ['status' => 'error', 'message' => 'Thumbnail must be a JPG, PNG, or WebP image.']);
  }

  if ($thumb_file['size'] > 3 * 1024 * 1024) {
    press_reply(422, ['status' => 'error', 'message' => 'Thumbnail must be under 3MB.']);
  }

  $uploadDir = media_path('blog_imgs');
  if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

  $ext      = $allowedThumbTypes[$mime];
  $filename = 'PRESS_' . bin2hex(random_bytes(16)) . '.' . $ext;

  if (!img_store_uploaded($thumb_file['tmp_name'], $uploadDir . $filename)) {
    throw new RuntimeException('move_uploaded_file failed for ' . $uploadDir . $filename);
  }
  $dest = $uploadDir . $filename;

  $cover_img = media_dir('blog_imgs') . $filename;

  /* facebook_url and instagram_url arrived with a later migration. Naming them
     unconditionally would break post creation on any install that has not run
     it yet -- the same hardcoded-column mistake that silently broke every image
     upload -- so they join the statement only when the table really has them.
     The SEO columns are no longer written: new posts leave them NULL and
     press.php falls back to the title, summary and thumbnail, while posts that
     already carry SEO values keep them. */
  $blogCols = array_flip($pdo->query("SHOW COLUMNS FROM blogs")->fetchAll(PDO::FETCH_COLUMN));
  $optional = [];
  foreach (['facebook_url', 'instagram_url'] as $col) {
      if (isset($blogCols[$col])) {
          $optional[$col] = $urls[$col];
      }
  }
  $extraCols   = $optional ? ', ' . implode(', ', array_keys($optional)) : '';
  $extraParams = $optional ? ', :' . implode(', :', array_keys($optional)) : '';

  $stmt = $pdo->prepare("
        INSERT INTO blogs
            (blog_title, slug, blog_content, summary, blog_author, upload_date, year,
             category, source_link, tags,
             featured, reading_time, cover_img, status{$extraCols})
        VALUES
            (:title, :slug, :content, :summary, :author, NOW(), YEAR(NOW()),
             :category, :source_link, :tags,
             :featured, :reading_time, :cover_img, :status{$extraParams})
    ");

  /* bindValue, not bindParam: bindParam binds by reference, so binding a loop
     variable would leave every parameter pointing at the final iteration. */
  foreach ($optional as $col => $val) {
      $stmt->bindValue(':' . $col, $val, PDO::PARAM_STR);
  }

  $stmt->bindValue(':title',        $title,               PDO::PARAM_STR);
  $stmt->bindValue(':slug',         $slug,                PDO::PARAM_STR);
  $stmt->bindValue(':content',      $content,             PDO::PARAM_STR);
  $stmt->bindValue(':summary',      $summary,             PDO::PARAM_STR);
  $stmt->bindValue(':author',       $author,              PDO::PARAM_STR);
  $stmt->bindValue(':category',     $category,            PDO::PARAM_STR);
  $stmt->bindValue(':source_link',  $urls['source_link'], PDO::PARAM_STR);
  $stmt->bindValue(':tags',         $tags,                PDO::PARAM_STR);
  $stmt->bindValue(':featured',     $featured,            PDO::PARAM_INT);
  $stmt->bindValue(':reading_time', $reading_time,        PDO::PARAM_INT);
  $stmt->bindValue(':cover_img',    $cover_img,           PDO::PARAM_STR);
  $stmt->bindValue(':status',       $post_status,         PDO::PARAM_STR);

  $stmt->execute();
  $post_id = (int)$pdo->lastInsertId();

  /* Report success only for a row that is really there with the status the
     admin chose -- the public page shows exactly the rows this confirms. */
  $check = $pdo->prepare("SELECT status FROM blogs WHERE blog_id = ?");
  $check->execute([$post_id]);
  $saved_status = $check->fetchColumn();
  if ($post_id <= 0 || $saved_status !== $post_status) {
    throw new RuntimeException("insert reported id {$post_id} but the row reads back as "
      . var_export($saved_status, true) . " instead of '{$post_status}'");
  }

  if ($submission_token !== '') {
    $_SESSION['press_submissions'][$submission_token] = ['post_id' => $post_id, 'status' => $post_status];
    // Bounded: only recent submissions can still be retried.
    $_SESSION['press_submissions'] = array_slice($_SESSION['press_submissions'], -20, null, true);
  }

  press_reply(200, [
    'status'      => 'success',
    'message'     => $post_status === 'published'
      ? 'Press post published! It is now live on the Press page.'
      : 'Draft saved. It is not on the public Press page until you publish it.',
    'post_id'     => $post_id,
    'post_status' => $post_status
  ]);
} catch (Throwable $e) {
  // Technical detail goes to the server log only; the admin gets a clear
  // statement that nothing was published.
  error_log('Press publish failed: ' . get_class($e) . ': ' . $e->getMessage());
  if ($dest !== null && is_file($dest)) {
    @unlink($dest);
  }
  press_reply(500, [
    'status'  => 'error',
    'message' => $e instanceof PDOException
      ? 'The database could not save the post, so nothing was published. Please try again in a minute; if it keeps failing, contact the site administrator.'
      : 'The server could not save the post, so nothing was published. Please try again; if it keeps failing, contact the site administrator.'
  ]);
}
