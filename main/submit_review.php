<?php
session_start();
include 'includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        die("User not logged in.");
    }

    $user_id = $_SESSION['user_id'];
    $plant_id = isset($_POST['plant_id']) ? intval($_POST['plant_id']) : 0;
    $rating   = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $comment  = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;

    if ($plant_id <= 0 || $rating <= 0) {
        die("Invalid data provided.");
    }

    $sql = "INSERT INTO reviews (user_id, plant_id, blog_id, rating, comment, is_approved, created_at)
            VALUES (?, ?, NULL, ?, ?, 0, NOW())";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("SQL error: " . $conn->error);
    }

    $stmt->bind_param("iiis", $user_id, $plant_id, $rating, $comment);

    if ($stmt->execute()) {
        // ✅ If called via fetch() you can just echo success
        echo "success";
        exit;
    } else {
        die("Insert failed: " . $stmt->error);
    }
}
?>
