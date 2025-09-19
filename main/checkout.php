<?php
// Start the session to access user data
session_start();

// Check if the user is logged in. If not, redirect to login page.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection and functions
include 'includes/db_connection.php';
include 'includes/functions.php';

$user_id = $_SESSION['user_id'];

// Get the cart items for the logged-in user
$cart_items = get_cart_items($user_id);

if (empty($cart_items)) {
    // Redirect to cart if it's empty
    header("Location: cart.php?error=empty_cart");
    exit();
}

// Calculate the total amount
$total_amount = 0;
foreach ($cart_items as $item) {
    $total_amount += ($item['price'] * $item['quantity']);
}

// Create the order in the database and clear the cart
$order_id = create_order($user_id, $cart_items, $total_amount);

if ($order_id) {
    // Order successfully created, redirect to an order confirmation page
    header("Location: order_confirmation.php?order_id=" . $order_id);
    exit();
} else {
    // Failed to create order
    header("Location: cart.php?error=checkout_failed");
    exit();
}

?>