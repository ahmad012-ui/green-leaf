<?php
session_start();
include 'includes/db_connection.php';
include 'includes/functions.php';

header('Content-Type: application/json');

$response = ['success' => false];

if (isset($_SESSION['user_id']) && isset($_POST['cart_id'])) {
    $cart_id = intval($_POST['cart_id']);

    // Case 1: Increment
    if (isset($_POST['increment'])) {
        $sql = "UPDATE cart SET quantity = quantity + 1 WHERE cart_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $cart_id);
        $stmt->execute();
        $stmt->close();

    // Case 2: Decrement
    } elseif (isset($_POST['decrement'])) {
        $sql = "UPDATE cart SET quantity = GREATEST(quantity - 1, 1) WHERE cart_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $cart_id);
        $stmt->execute();
        $stmt->close();

    // Case 3: Manual quantity update (your Update button)
    } elseif (isset($_POST['quantity'])) {
        $quantity = max(1, intval($_POST['quantity'])); // never less than 1
        $sql = "UPDATE cart SET quantity = ? WHERE cart_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $quantity, $cart_id);
        $stmt->execute();
        $stmt->close();

    } else {
        echo json_encode(['success' => false, 'message' => 'No action specified']);
        exit;
    }

    // Fetch updated cart item
    $stmt = $conn->prepare("SELECT quantity, price FROM cart JOIN plants ON cart.plant_id = plants.plant_id WHERE cart_id = ?");
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($result) {
        // Recalculate subtotal
        $cart_items = get_cart_items($_SESSION['user_id']);
        $cart_total = 0;
        foreach ($cart_items as $ci) {
            $cart_total += $ci['price'] * $ci['quantity'];
        }

        $response = [
            'success'    => true,
            'quantity'   => (int) $result['quantity'],
            'line_total' => number_format($result['price'] * $result['quantity'], 2),
            'cart_total' => number_format($cart_total, 2),
            'cart_count' => count($cart_items)
        ];
    }
}

echo json_encode($response);
exit;
