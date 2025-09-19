<?php
// add_to_cart.php - add a plant/item to the user's cart
require_once 'includes/db_connection.php';
require_once 'includes/csrf.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

if (!isset($_POST['csrf_token']) || !csrf_check($_POST['csrf_token'])) {
    http_response_code(400);
    echo "Invalid CSRF token";
    exit;
}

if (empty($_SESSION['user_id'])) {
    http_response_code(403);
    echo "You must be logged in to add items to cart.";
    exit;
}

$user_id = $_SESSION['user_id'];
$item_id = intval($_POST['item_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 1);
if ($item_id <= 0 || $quantity <= 0) {
    http_response_code(400);
    echo "Invalid input.";
    exit;
}

// Check if item already in cart -> update quantity, else insert
$stmt = $conn->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND item_id = ? LIMIT 1");
$stmt->bind_param("ii", $user_id, $item_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $stmt->close();
    $new_qty = $row['quantity'] + $quantity;
    $upd = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
    $upd->bind_param("ii", $new_qty, $row['cart_id']);
    if ($upd->execute()) {
        $upd->close();
        header("Location: cart.php");
        exit;
    } else {
        $upd->close();
        http_response_code(500);
        echo "Failed to update cart.";
        exit;
    }
} else {
    $stmt->close();
    $ins = $conn->prepare("INSERT INTO cart (user_id, item_id, quantity, created_at) VALUES (?, ?, ?, NOW())");
    $ins->bind_param("iii", $user_id, $item_id, $quantity);
    if ($ins->execute()) {
        $ins->close();
        header("Location: cart.php");
        exit;
    } else {
        $ins->close();
        http_response_code(500);
        echo "Failed to add to cart.";
        exit;
    }
}
?>
