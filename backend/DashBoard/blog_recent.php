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

    // Get recent posts (last 10)
    $stmt = $pdo->prepare("
        SELECT 
            blog_id, 
            blog_title, 
            upload_date, 
            COALESCE(status, 'published') as status,
            blog_author
        FROM blogs 
        ORDER BY upload_date DESC 
        LIMIT 10
    ");

    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($posts);
} catch (Exception $e) {
    error_log('Blog recent posts error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
