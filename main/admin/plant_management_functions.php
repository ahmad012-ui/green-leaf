<?php
// admin/plant_management_functions.php

// This file contains all the functions needed for managing plants in the admin panel,
// including adding, editing, and deleting plants, and fetching a list of categories for forms.

/**
 * Adds a new plant to the database.
 *
 * @param array $plant_data An associative array containing plant details.
 * @return bool True on success, false on failure.
 */
function add_plant($plant_data) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO plants (name, botanical_name, description, price, image, care_level, light_requirement, watering_schedule, category_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdssssi", $plant_data['name'], $plant_data['botanical_name'], $plant_data['description'], $plant_data['price'], $plant_data['image'], $plant_data['care_level'], $plant_data['light_requirement'], $plant_data['watering_schedule'], $plant_data['category_id']);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Fetches a single plant by its ID, including the category name.
 *
 * @param int $plant_id The ID of the plant to retrieve.
 * @return array|null An array of plant data, or null if the plant is not found.
 */
function get_plant_by_id_admin($plant_id) {
    global $conn;
    // The query now joins the plants and categories table to get the category name.
    // It correctly selects `c.category_name` to match your database structure.
    $sql = "SELECT p.*, c.category_name AS category_name 
            FROM plants p
            LEFT JOIN categories c ON p.category_id = c.category_id
            WHERE p.plant_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $plant_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $plant = $result->fetch_assoc();
    $stmt->close();
    return $plant;
}

/**
 * Updates an existing plant in the database.
 *
 * @param int $plant_id The ID of the plant to update.
 * @param array $plant_data An associative array containing the updated plant details.
 * @return bool True on success, false on failure.
 */
function update_plant($plant_id, $plant_data) {
    global $conn;
    $stmt = $conn->prepare("UPDATE plants SET name=?, botanical_name=?, description=?, price=?, image=?, care_level=?, light_requirement=?, watering_schedule=?, category_id=? WHERE plant_id=?");
    $stmt->bind_param("sssdssssii", $plant_data['name'], $plant_data['botanical_name'], $plant_data['description'], $plant_data['price'], $plant_data['image'], $plant_data['care_level'], $plant_data['light_requirement'], $plant_data['watering_schedule'], $plant_data['category_id'], $plant_id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Deletes a plant from the database.
 *
 * @param int $plant_id The ID of the plant to delete.
 * @return bool True on success, false on failure.
 */
function delete_plant($plant_id) {
    global $conn;

    // Delete dependent rows first
    $tables = ['cart', 'comments', 'orders', 'order_items', 'plant_likes', 'reminders', 'reviews', 'user_plants'];
    foreach ($tables as $table) {
        $stmt = $conn->prepare("DELETE FROM $table WHERE plant_id = ?");
        $stmt->bind_param("i", $plant_id);
        $stmt->execute();
        $stmt->close();
    }

    // Finally delete from plants
    $stmt = $conn->prepare("DELETE FROM plants WHERE plant_id = ?");
    $stmt->bind_param("i", $plant_id);
    $success = $stmt->execute();
    $stmt->close();

    return $success;
}

/**
 * Fetches all categories from the database.
 *
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
 * Fetches a list of all plants from the database for the admin panel.
 *
 * @return array An array of plant data, or an empty array if none are found.
 */
function get_all_plants_admin() {
    global $conn;
    $plants = [];
    // The query now joins the plants and categories table to get the category name.
    // It correctly selects `c.category_name` to match your database structure.
    $sql = "SELECT p.plant_id, p.name, p.price, p.image, c.category_name 
            FROM plants p
            LEFT JOIN categories c ON p.category_id = c.category_id
            ORDER BY p.name ASC";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $plants[] = $row;
        }
    }
    return $plants;
}
?>