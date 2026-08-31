<?php
require_once __DIR__ . '/../image_optimize.php';
require_once __DIR__ . '/../storage.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
  http_response_code(401);
  echo json_encode([
    'status' => 'error',
    'message' => 'Unauthorized access. Please login first.'
  ]);
  exit();
}

header('Content-Type: application/json');

require_once __DIR__ . '/DashBoard/csrf_helper.php';
require_once __DIR__ . '/blogSanitizer.php';

try {
  if (!csrf_verify()) {
    http_response_code(403);
    echo json_encode([
      'status'  => 'error',
      'message' => 'Invalid or expired session token. Please reload the editor and try again.'
    ]);
    exit();
  }

  // Use the centralized database connection
  require_once __DIR__ . '/Database/db.php';
  $db = new Db();
  $pdo = $db->connect();

  // Validate and sanitize input
  $title         = trim($_POST['blog_title']    ?? '');
  $content       = sanitize_blog_html($_POST['blog_content']    ?? '');
  $summary       = sanitize_blog_html($_POST['summary_content'] ?? '');
  $author        = $_SESSION['username']        ?? 'ATMABISWAS';
  $category      = trim($_POST['category']      ?? 'news');
  $source_link   = trim($_POST['source_link']   ?? '');
  $tags          = trim($_POST['tags']          ?? '');
  $seo_title     = trim($_POST['seo_title']     ?? '');
  $seo_desc      = trim($_POST['seo_description'] ?? '');
  $seo_keys      = trim($_POST['seo_keywords']  ?? '');
  $focus_keyword = trim($_POST['focus_keyword'] ?? '');
  $canonical_url = trim($_POST['canonical_url'] ?? '');
  $facebook_url  = trim($_POST['facebook_url']  ?? '');
  $instagram_url = trim($_POST['instagram_url'] ?? '');
  $social_img    = trim($_POST['social_image']  ?? '');
  $featured      = isset($_POST['featured']) ? 1 : 0;

  // Auto-generate slug from title if not provided
  $slug = trim($_POST['slug'] ?? '');
  if ($slug === '') {
      $slug = mb_strtolower($title, 'UTF-8');
      $slug = preg_replace('/[^a-z0-9\s-]/u', '', $slug);
      $slug = preg_replace('/\s+/', '-', trim($slug));
      $slug = preg_replace('/-+/', '-', $slug);
      $slug = trim($slug, '-');
  }
  // Ensure slug uniqueness by appending timestamp if needed
  if ($slug) $slug = $slug . '-' . time();

  // Calculate reading time (words ÷ 200 wpm)
  $reading_time = max(1, (int)ceil(str_word_count(strip_tags($content)) / 200));

  $allowed_categories = ['news', 'media', 'announcement', 'press'];
  if (!in_array($category, $allowed_categories, true)) $category = 'news';

  // Validation
  if (empty($title)) {
    throw new Exception('Press title is required.');
  }

  if (empty($content)) {
    throw new Exception('Press content is required.');
  }

  if (empty($summary)) {
    throw new Exception('Press summary is required.');
  }

  // Thumbnail is required
  if (!isset($_FILES['thumbnail']) || $_FILES['thumbnail']['error'] !== UPLOAD_ERR_OK) {
    throw new Exception('Press thumbnail is required.');
  }

  // Additional security: limit content length
  if (strlen($title) > 255) {
    throw new Exception('Title is too long (max 255 characters)');
  }

  if (strlen($content) > 50000) {
    throw new Exception('Content is too long (max 50,000 characters)');
  }

  if (strlen($summary) > 1000) {
    throw new Exception('Summary is too long (max 1,000 characters)');
  }

  // Process thumbnail upload
  $thumb_file = $_FILES['thumbnail'];
  $finfo      = new finfo(FILEINFO_MIME_TYPE);
  $mime       = $finfo->file($thumb_file['tmp_name']);

  // Maps a validated MIME type to the extension we save with — never
  // taken from the attacker-supplied filename.
  $allowedThumbTypes = ['image/jpeg' => 'jpg', 'image/jpg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
  if (!array_key_exists($mime, $allowedThumbTypes)) {
    throw new Exception('Thumbnail must be a JPG, PNG, or WebP image.');
  }

  if ($thumb_file['size'] > 3 * 1024 * 1024) {
    throw new Exception('Thumbnail must be under 3MB.');
  }

  $uploadDir = media_path('blog_imgs');
  if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

  $ext      = $allowedThumbTypes[$mime];
  $filename = 'PRESS_' . bin2hex(random_bytes(16)) . '.' . $ext;
  $dest     = $uploadDir . $filename;

  if (!img_store_uploaded($thumb_file['tmp_name'], $dest)) {
    throw new Exception('Failed to save thumbnail. Check upload directory permissions.');
  }

  $cover_img = media_dir('blog_imgs') . $filename;

  /* facebook_url and instagram_url arrived with a later migration. Naming them
     unconditionally would break post creation on any install that has not run
     it yet -- the same hardcoded-column mistake that silently broke every image
     upload -- so they join the statement only when the table really has them. */
  $blogCols = array_flip($pdo->query("SHOW COLUMNS FROM blogs")->fetchAll(PDO::FETCH_COLUMN));
  $optional = [];
  foreach (['facebook_url' => $facebook_url, 'instagram_url' => $instagram_url] as $col => $val) {
      if (isset($blogCols[$col])) {
          $optional[$col] = $val;
      }
  }
  $extraCols   = $optional ? ', ' . implode(', ', array_keys($optional)) : '';
  $extraParams = $optional ? ', :' . implode(', :', array_keys($optional)) : '';

  $stmt = $pdo->prepare("
        INSERT INTO blogs
            (blog_title, slug, blog_content, summary, blog_author, upload_date, year,
             category, source_link, tags, seo_title, seo_description, seo_keywords,
             focus_keyword, canonical_url,
             social_image, featured, reading_time, cover_img, status{$extraCols})
        VALUES
            (:title, :slug, :content, :summary, :author, NOW(), YEAR(NOW()),
             :category, :source_link, :tags, :seo_title, :seo_desc, :seo_keys,
             :focus_keyword, :canonical_url,
             :social_img, :featured, :reading_time, :cover_img, :status{$extraParams})
    ");

  /* bindValue, not bindParam: bindParam binds by reference, so binding a loop
     variable would leave every parameter pointing at the final iteration. */
  foreach ($optional as $col => $val) {
      $stmt->bindValue(':' . $col, $val, PDO::PARAM_STR);
  }

  $post_status = isset($_POST['post_status_action']) && $_POST['post_status_action'] === 'draft'
    ? 'draft' : 'published';

  $stmt->bindParam(':title',         $title,         PDO::PARAM_STR);
  $stmt->bindParam(':slug',          $slug,          PDO::PARAM_STR);
  $stmt->bindParam(':content',       $content,       PDO::PARAM_STR);
  $stmt->bindParam(':summary',       $summary,       PDO::PARAM_STR);
  $stmt->bindParam(':author',        $author,        PDO::PARAM_STR);
  $stmt->bindParam(':category',      $category,      PDO::PARAM_STR);
  $stmt->bindParam(':source_link',   $source_link,   PDO::PARAM_STR);
  $stmt->bindParam(':tags',          $tags,          PDO::PARAM_STR);
  $stmt->bindParam(':seo_title',     $seo_title,     PDO::PARAM_STR);
  $stmt->bindParam(':seo_desc',      $seo_desc,      PDO::PARAM_STR);
  $stmt->bindParam(':seo_keys',      $seo_keys,      PDO::PARAM_STR);
  $stmt->bindParam(':focus_keyword', $focus_keyword, PDO::PARAM_STR);
  $stmt->bindParam(':canonical_url', $canonical_url, PDO::PARAM_STR);
  $stmt->bindParam(':social_img',    $social_img,    PDO::PARAM_STR);
  $stmt->bindParam(':featured',      $featured,      PDO::PARAM_INT);
  $stmt->bindParam(':reading_time',  $reading_time,  PDO::PARAM_INT);
  $stmt->bindParam(':cover_img',     $cover_img,     PDO::PARAM_STR);
  $stmt->bindParam(':status',        $post_status,   PDO::PARAM_STR);

  if ($stmt->execute()) {
    echo json_encode([
      'status'  => 'success',
      'message' => 'Press post published!',
      'post_id' => $pdo->lastInsertId()
    ]);
  } else {
    echo json_encode([
      'status'  => 'error',
      'message' => 'Failed to save press post'
    ]);
  }
} catch (PDOException $e) {
  error_log('Database Error: ' . $e->getMessage());
  echo json_encode([
    'status'  => 'error',
    'message' => 'Database error occurred',
    'error'   => $e->getMessage()
  ]);
} catch (Exception $e) {
  error_log('General Error: ' . $e->getMessage());
  echo json_encode([
    'status'  => 'error',
    'message' => 'An error occurred',
    'error'   => $e->getMessage()
  ]);
}
