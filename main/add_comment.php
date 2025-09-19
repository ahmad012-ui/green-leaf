<?php
// add_comment.php
require_once 'includes/db_connection.php';
require_once 'includes/csrf.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !csrf_check($_POST['csrf_token'])) {
        http_response_code(400);
        echo "Invalid CSRF token";
        exit;
    }

    if (empty($_SESSION['user_id'])) {
        http_response_code(403);
        echo "You must be logged in to post comments.";
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $blog_id = intval($_POST['blog_id'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if ($blog_id <= 0 || $comment === '') {
        http_response_code(400);
        echo "Invalid input.";
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO comments (user_id, blog_id, comment, created_at) 
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->bind_param("iis", $user_id, $blog_id, $comment);

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: blog.php?id=" . $blog_id);
        exit();
    } else {
        $stmt->close();
        http_response_code(500);
        echo "Failed to post comment.";
        exit;
    }
}
?>