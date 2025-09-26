<?php
// admin/dashboard_functions.php

/**
 * Fetches the total revenue from all completed orders.
 * @return float The total price of all 'Delivered' orders.
 */
function get_total_sales() {
    global $conn;
    $sql = "SELECT SUM(total_amount) AS total FROM orders WHERE status = 'Delivered'";
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        return floatval($row['total']);
    }
    return 0.00;
}

/**
 * Fetches the total number of orders.
 * @return int The total count of all orders.
 */
function get_total_orders() {
    global $conn;
    $sql = "SELECT COUNT(*) AS total FROM orders";
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        return intval($row['total']);
    }
    return 0;
}

/**
 * Fetches the most recent pending reviews.
 * @return array An array of the 5 most recent pending reviews.
 */
function get_latest_pending_reviews() {
    global $conn;
    $reviews = [];
    $sql = "SELECT r.review_id, r.rating, r.comment, r.created_at, u.username, p.name AS plant_name
            FROM reviews r
            LEFT JOIN users u ON r.user_id = u.user_id
            LEFT JOIN plants p ON r.plant_id = p.plant_id
            WHERE r.is_approved = 0
            ORDER BY r.created_at DESC
            LIMIT 5";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $reviews[] = $row;
        }
    }
    return $reviews;
}
?>