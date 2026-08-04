<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    // Use centralized database connection
    include '../Database/db.php';
    $db = new Db();
    $pdo = $db->connect();

    // Get total posts
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM blogs");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get published posts
    $stmt = $pdo->query("SELECT COUNT(*) as published FROM blogs WHERE status = 'published' OR status IS NULL");
    $published = $stmt->fetch(PDO::FETCH_ASSOC)['published'];

    // Get draft posts
    $stmt = $pdo->query("SELECT COUNT(*) as drafts FROM blogs WHERE status = 'draft'");
    $drafts = $stmt->fetch(PDO::FETCH_ASSOC)['drafts'];

    // Get total views (if views column exists)
    try {
        $stmt = $pdo->query("SELECT SUM(views) as total_views FROM blogs");
        $views = $stmt->fetch(PDO::FETCH_ASSOC)['total_views'] ?? 0;
    } catch (Exception $e) {
        $views = 0; // Column might not exist yet
    }

    echo json_encode([
        'total' => (int)$total,
        'published' => (int)$published,
        'drafts' => (int)$drafts,
        'views' => (int)$views
    ]);
} catch (Exception $e) {
    error_log('Blog stats error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
