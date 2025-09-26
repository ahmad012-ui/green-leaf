<?php
// admin/category_management_functions.php

/**
 * Fetches all categories from the database.
 * @return array An array of category data, or an empty array if none are found.
 */
function get_all_categories() {
    global $conn;
    $categories = [];
    $sql = "SELECT category_id, category_name FROM categories ORDER BY category_name ASC";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    return $categories;
}

/**
 * Adds a new category to the database.
 * @param string $name The name of the new category.
 * @param string $description The description of the new category.
 * @return bool True on success, false on failure.
 */
function add_category($name, $description) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO categories (category_name, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $description);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Fetches a single category by its ID.
 * @param int $category_id The ID of the category to retrieve.
 * @return array|null An array of category data, or null if not found.
 */
function get_category_by_id($category_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM categories WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $category = $result->fetch_assoc();
    $stmt->close();
    return $category;
}

/**
 * Updates an existing category in the database.
 * @param int $category_id The ID of the category to update.
 * @param string $name The new name for the category.
 * @param string $description The new description for the category.
 * @return bool True on success, false on failure.
 */
function update_category($category_id, $name, $description) {
    global $conn;
    $stmt = $conn->prepare("UPDATE categories SET category_name=?, description=? WHERE category_id=?");
    $stmt->bind_param("ssi", $name, $description, $category_id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Deletes a category from the database.
 * @param int $category_id The ID of the category to delete.
 * @return bool True on success, false on failure.
 */
function delete_category($category_id) {
    global $conn;
    // Set category_id to NULL for all plants in this category before deleting the category
    $stmt = $conn->prepare("UPDATE plants SET category_id = NULL WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}
?>