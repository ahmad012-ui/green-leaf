<?php
// like_blog.php - toggles like/unlike and returns JSON
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once 'includes/db_connection.php';
require_once 'includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false, 'message'=>'Method not allowed']);
    exit;
}

// CSRF check
$token = $_POST['csrf_token'] ?? '';
if (!csrf_check($token)) {
    http_response_code(400);
    echo json_encode(['success'=>false, 'message'=>'Invalid CSRF token']);
    exit;
}

// Auth check
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success'=>false, 'message'=>'Login required']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$blog_id = isset($_POST['blog_id']) ? intval($_POST['blog_id']) : 0;
if ($blog_id <= 0) {
    http_response_code(400);
    echo json_encode(['success'=>false, 'message'=>'Invalid blog id']);
    exit;
}

try {
    // check existing like
    $stmt = $conn->prepare("SELECT id FROM blog_likes WHERE user_id = ? AND blog_id = ? LIMIT 1");
    $stmt->bind_param("ii", $user_id, $blog_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows > 0) {
        // unlike
        $row = $res->fetch_assoc();
        $stmt->close();

        $del = $conn->prepare("DELETE FROM blog_likes WHERE id = ?");
        $del->bind_param("i", $row['id']);
        $del->execute();
        $del->close();

        $liked = false;
    } else {
        $stmt->close();
        // like
        $ins = $conn->prepare("INSERT INTO blog_likes (user_id, blog_id, created_at) VALUES (?, ?, NOW())");
        $ins->bind_param("ii", $user_id, $blog_id);
        $ins->execute();
        $ins->close();

        $liked = true;
    }

    // return new count
    $cntStmt = $conn->prepare("SELECT COUNT(*) AS total FROM blog_likes WHERE blog_id = ?");
    $cntStmt->bind_param("i", $blog_id);
    $cntStmt->execute();
    $cntRes = $cntStmt->get_result();
    $total = ($cntRes && ($r = $cntRes->fetch_assoc())) ? (int)$r['total'] : 0;
    $cntStmt->close();

    echo json_encode(['success'=>true, 'liked'=>$liked, 'total'=>$total]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success'=>false, 'message'=>'Server error']);
    exit;
}
?>