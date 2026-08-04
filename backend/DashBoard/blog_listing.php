<?php
include '../Database/db.php';
$db = new Db();
$pdo = $db->connect();



$stmt = $pdo->query("SELECT blog_id, blog_title, upload_date FROM blogs ORDER BY upload_date DESC");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="blog-list">
  <?php foreach ($posts as $post): ?>
    <div class="blog-preview">
      <h2>
        <a href="blog_content.php?id=<?= $post['blog_id'] ?>">
          <?= htmlspecialchars($post['blog_title']) ?>
        </a>
      </h2>
      <p class="post-date">
        <?= date('M j, Y', strtotime($post['upload_date'])) ?>
      </p>
    </div>
  <?php endforeach; ?>
</div>