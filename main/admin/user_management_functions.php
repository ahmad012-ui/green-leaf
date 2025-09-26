<?php
// admin/user_management_functions.php

/**
 * Fetches all users from the database.
 * @return array An array of user data, or an empty array if none are found.
 */
function get_all_users() {
    global $conn;
    $users = [];
    $sql = "SELECT user_id, username, email, is_admin, created_at FROM users ORDER BY created_at DESC";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    return $users;
}

/**
 * Updates a user's admin status.
 * @param int $user_id The ID of the user to update.
 * @param bool $is_admin The new admin status (true or false).
 * @return bool True on success, false on failure.
 */
function update_user_admin_status($user_id, $is_admin) {
    global $conn;
    $stmt = $conn->prepare("UPDATE users SET is_admin = ? WHERE user_id = ?");
    $stmt->bind_param("ii", $is_admin, $user_id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Deletes a user from the database.
 * @param int $user_id The ID of the user to delete.
 * @return bool True on success, false on failure.
 */
function delete_user($user_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}
?>