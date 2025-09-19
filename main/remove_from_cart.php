<?php
// Start the session to manage user login status
session_start();

// Include the database connection and functions files
include 'includes/db_connection.php';
include 'includes/functions.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if a cart_id is provided in the URL
if (isset($_GET['cart_id'])) {
    $user_id = $_SESSION['user_id'];
    $cart_id = $_GET['cart_id'];

    // Call the function to remove the item
    if (remove_from_cart($user_id, $cart_id)) {
        header("Location: cart.php?success=removed");
        exit();
    } else {
        header("Location: cart.php?error=removal_failed");
        exit();
    }
} else {
    header("Location: cart.php");
    exit();
}
?>