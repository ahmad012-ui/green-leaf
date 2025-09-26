<?php
// admin/order_management_functions.php

/**
 * Fetches all orders from the database.
 * @return array An array of order data, or an empty array if none are found.
 */
function get_all_orders() {
    global $conn;
    $orders = [];
    $sql = "SELECT o.order_id, o.total_amount, o.status, o.created_at, u.username
            FROM orders o
            JOIN users u ON o.user_id = u.user_id
            ORDER BY o.created_at DESC";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
    }
    return $orders;
}

/**
 * Fetches a single order by its ID, along with the user's details.
 * @param int $order_id The ID of the order to retrieve.
 * @return array|null An associative array of order and user data, or null if not found.
 */
function get_order_by_id($order_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT o.*, u.username, u.email, u.address FROM orders o JOIN users u ON o.user_id = u.user_id WHERE o.order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();
    return $order;
}

/**
 * Fetches all items belonging to a specific order.
 * @param int $order_id The ID of the order to retrieve items for.
 * @return array An array of order item data, or an empty array if none are found.
 */
function get_order_items($order_id) {
    global $conn;
    $order_items = [];
    $stmt = $conn->prepare("SELECT oi.quantity, p.name, p.price, p.image
                            FROM order_items oi
                            JOIN plants p ON oi.plant_id = p.plant_id
                            WHERE oi.order_id = ?;");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $order_items[] = $row;
        }
    }
    $stmt->close();
    return $order_items;
}

/**
 * Updates the status of an order.
 * @param int $order_id The ID of the order to update.
 * @param string $status The new status for the order.
 * @return bool True on success, false on failure.
 */
function update_order_status($order_id, $status) {
    global $conn;
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $status, $order_id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}
?>