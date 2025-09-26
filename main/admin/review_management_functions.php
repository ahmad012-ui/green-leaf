<?php
// admin/review_management_functions.php

// This file contains all the functions needed for managing reviews in the admin panel,
// including fetching reviews with a filter, and the ability to add, edit, approve, and delete them.

/**
 * Fetches reviews from the database with optional filters for status, search query, and rating.
 *
 * @param string $status_filter 'all', 'approved', or 'pending' to filter reviews.
 * @param string $search_query Optional search term for plant names.
 * @param int|null $rating_filter Optional rating to filter reviews by.
 * @return array An array of review data, or an empty array if none are found.
 */
function get_all_reviews($status_filter = 'all', $search_query = '', $rating_filter = null) {
    global $conn;
    $reviews = [];
    $params = [];
    $types = '';
    
    // Base SQL query to join reviews with users and plants
    $sql = "SELECT r.*, u.username, p.name AS plant_name
            FROM reviews r
            LEFT JOIN users u ON r.user_id = u.user_id
            LEFT JOIN plants p ON r.plant_id = p.plant_id";
    
    // Initialize an array for WHERE clauses
    $where_clauses = [];
    
    // Add WHERE clause based on the selected status filter
    if ($status_filter === 'approved') {
        $where_clauses[] = "r.is_approved = 1";
    } elseif ($status_filter === 'pending') {
        $where_clauses[] = "r.is_approved = 0";
    }
    
    // Add WHERE clause for plant name search if a query is provided
    if (!empty($search_query)) {
        $where_clauses[] = "p.name LIKE ?";
        $params[] = '%' . $search_query . '%';
        $types .= 's';
    }
    
    // Add WHERE clause for rating filter if a rating is selected
    if ($rating_filter !== null) {
        $where_clauses[] = "r.rating = ?";
        $params[] = $rating_filter;
        $types .= 'i';
    }
    
    // Combine all WHERE clauses with "AND"
    if (!empty($where_clauses)) {
        $sql .= " WHERE " . implode(" AND ", $where_clauses);
    }
    
    // Order the results by creation date, newest first
    $sql .= " ORDER BY r.created_at DESC";
    
    // Prepare the statement if we have parameters
    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    } else {
        // If no parameters, just run the query directly
        $result = $conn->query($sql);
    }
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $reviews[] = $row;
        }
    }
    return $reviews;
}

/**
 * Fetches a single review by its ID.
 * @param int $review_id The ID of the review to retrieve.
 * @return array|null An array of review data, or null if not found.
 */
function get_review_by_id($review_id) {
    global $conn;
    $sql = "SELECT * FROM reviews WHERE review_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $review_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $review = $result->fetch_assoc();
    $stmt->close();
    return $review;
}

/**
 * Approves a review.
 * @param int $review_id The ID of the review to approve.
 * @return bool True on success, false on failure.
 */
function approve_review($review_id) {
    global $conn;
    $stmt = $conn->prepare("UPDATE reviews SET is_approved = 1 WHERE review_id = ?");
    $stmt->bind_param("i", $review_id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Deletes a review from the database.
 * @param int $review_id The ID of the review to delete.
 * @return bool True on success, false on failure.
 */
function delete_review($review_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM reviews WHERE review_id = ?");
    $stmt->bind_param("i", $review_id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Adds a new review to the database from the admin panel.
 * @param int $user_id The ID of the user submitting the review.
 * @param int $plant_id The ID of the plant being reviewed.
 * @param int $rating The rating (1-5).
 * @param string $comment The review comment.
 * @param int $is_approved Whether the review should be approved immediately (1 or 0).
 * @return bool True on success, false on failure.
 */
function add_review_admin($user_id, $plant_id, $rating, $comment, $is_approved) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO reviews (user_id, plant_id, rating, comment, is_approved) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisi", $user_id, $plant_id, $rating, $comment, $is_approved);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Updates an existing review in the database.
 * @param int $review_id The ID of the review to update.
 * @param int $user_id The ID of the user submitting the review.
 * @param int $plant_id The ID of the plant being reviewed.
 * @param int $rating The rating (1-5).
 * @param string $comment The review comment.
 * @param int $is_approved Whether the review should be approved immediately (1 or 0).
 * @return bool True on success, false on failure.
 */
function update_review($review_id, $user_id, $plant_id, $rating, $comment, $is_approved) {
    global $conn;
    $stmt = $conn->prepare("UPDATE reviews SET user_id=?, plant_id=?, rating=?, comment=?, is_approved=? WHERE review_id=?");
    $stmt->bind_param("iiisii", $user_id, $plant_id, $rating, $comment, $is_approved, $review_id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}
?>