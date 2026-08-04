<?php
// Use centralized database connection
include '../Database/db.php';
$db = new Db();
$pdo = $db->connect();


$blog_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($blog_id) {
  try {

    $stmt = $pdo->prepare("SELECT * FROM blogs WHERE blog_id = ?");
    $stmt->execute([$blog_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    die("Error fetching post: " . $e->getMessage());
  }
} else {
  die("Invalid press post ID");
}
?>

<!DOCTYPE html>
<html>

<head>
  <title><?= htmlspecialchars($post['blog_title']) ?> - ATMABISWAS</title>
  <link rel="icon" type="image/png" href="../images/logo/logo.png">
  <link rel="stylesheet" href="css/blog_content.css">
</head>

<body>
  <div class="blog-content">
    <h1><?= htmlspecialchars($post['blog_title']) ?></h1>


    <div class="content-wrapper">
      <?php
      echo htmlspecialchars_decode($post['blog_content']);

      ?>
    </div>

    <div class="post-meta">
      <p>Author: <?= htmlspecialchars($post['blog_author']) ?></p>
      <p>Date: <?= date('F j, Y', strtotime($post['upload_date'])) ?></p>
    </div>
  </div>
</body>

</html>