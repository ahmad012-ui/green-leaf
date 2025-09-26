<?php
// admin/blog_management_functions.php

/**
 * Fetches all active blogs from the database.
 * @return array An array of blog data, or an empty array if none are found.
 */
function get_all_blogs_admin() {
    global $conn;
    $blogs = [];
    $sql = "SELECT b.blog_id, b.title, b.created_at, b.image, u.username 
            FROM blogs b
            LEFT JOIN users u ON b.author_id = u.user_id
            WHERE b.is_deleted = 0
            ORDER BY b.created_at DESC";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $blogs[] = $row;
        }
    }
    return $blogs;
}

/**
 * Adds a new blog post to the database.
 * @param array $blog_data An associative array containing blog details.
 * @return bool True on success, false on failure.
 */
function add_blog($blog_data) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO blogs (title, content, author_id, image) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $blog_data['title'], $blog_data['content'], $blog_data['author_id'], $blog_data['image']);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Fetches a single blog by its ID.
 * @param int $blog_id The ID of the blog to retrieve.
 * @return array|null An array of blog data, or null if not found.
 */
function get_blog_by_id_admin($blog_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM blogs WHERE blog_id = ?");
    $stmt->bind_param("i", $blog_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $blog = $result->fetch_assoc();
    $stmt->close();
    return $blog;
}

/**
 * Updates an existing blog post in the database.
 * @param int $blog_id The ID of the blog to update.
 * @param array $blog_data An associative array containing the updated blog details.
 * @return bool True on success, false on failure.
 */
function update_blog($blog_id, $blog_data) {
    global $conn;
    $stmt = $conn->prepare("UPDATE blogs SET title=?, content=?, author_id=?, image=? WHERE blog_id=?");
    $stmt->bind_param("ssisi", $blog_data['title'], $blog_data['content'], $blog_data['author_id'], $blog_data['image'], $blog_id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Permanently deletes a blog from the database (hard delete).
 * @param int $blog_id The ID of the blog to delete.
 * @return bool True on success, false on failure.
 */
function delete_blog($blog_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM blogs WHERE blog_id = ?");
    $stmt->bind_param("i", $blog_id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}
?>