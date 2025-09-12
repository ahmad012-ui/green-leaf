<?php
// includes/functions.php

/**
 * Fetches a list of featured plants from the database.
 *
 * @return array An array of plant data, or an empty array if none are found.
 */
function get_featured_plants() {
    global $conn;
    $plants = [];
    $sql = "SELECT plant_id, name, price, image FROM plants ORDER BY plant_id DESC LIMIT 8";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $plants[] = $row;
        }
    }
    return $plants;
}

/**
 * Fetches a list of all plants from the database with optional search and filter.
 *
 * @param string $search_query Optional search term for plant names.
 * @param string $category_name Optional category name to filter by.
 * @return array An array of plant data, or an empty array if none are found.
 */
function get_plants($search_query = '', $category_name = '') {
    global $conn;
    $plants = [];
    $sql = "SELECT 
                p.plant_id, 
                p.name, 
                p.description,
                p.price, 
                p.image, 
                p.care_level,
                p.light_requirement,
                p.watering_schedule,
                c.category_name 
            FROM plants p
            LEFT JOIN categories c ON p.category_id = c.category_id
            WHERE 1=1";
    $params = [];
    $param_types = '';

    if (!empty($search_query)) {
        $sql .= " AND p.name LIKE ?";
        $params[] = "%" . $search_query . "%";
        $param_types .= 's';
    }

    if (!empty($category_name)) {
        $sql .= " AND c.category_name = ?";
        $params[] = $category_name;
        $param_types .= 's';
    }

    $stmt = $conn->prepare($sql);

    if (!empty($param_types)) {
        $stmt->bind_param($param_types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $plants[] = $row;
        }
    }
    $stmt->close();
    return $plants;
}

/**
 * Fetches a single plant by its ID, including the category name.
 *
 * @param int $plant_id The ID of the plant to retrieve.
 * @return array|null An array of plant data, or null if the plant is not found.
 */
function get_plant_by_id($plant_id) {
    global $conn;
    $sql = "SELECT p.*, c.category_name 
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
 * Fetches a list of the latest blog articles.
 *
 * @return array An array of blog data, or an empty array if none are found.
 */
function get_latest_blogs() {
    global $conn;
    $blogs = [];
    $sql = "SELECT blog_id, title, content FROM blogs ORDER BY created_at DESC LIMIT 4";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $blogs[] = $row;
        }
    }
    return $blogs;
}

/**
 * Fetches a list of all blog articles from the database.
 *
 * @return array An array of blog data, or an empty array if none are found.
 */
function get_all_blogs() {
    global $conn;
    $blogs = [];
    $sql = "SELECT blog_id, title, content, created_at FROM blogs ORDER BY created_at DESC";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $blogs[] = $row;
        }
    }
    return $blogs;
}

/**
 * Fetches details for a single blog post based on its ID.
 *
 * @param int $blog_id The ID of the blog post to retrieve.
 * @return array|null An array of blog data on success, or null if not found.
 */
function get_blog_by_id($blog_id) {
    global $conn;
    $blog = null;

    $stmt = $conn->prepare("SELECT * FROM blogs WHERE blog_id = ?");
    $stmt->bind_param("i", $blog_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $blog = $result->fetch_assoc();
    }
    $stmt->close();
    
    return $blog;
}

/**
 * Fetches a user's details by their ID.
 *
 * @param int $user_id The ID of the user.
 * @return array|null An array of user data, or null if not found.
 */
function get_user_details($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT user_id, username, email, profile_image FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user;
}

/**
 * Updates a user's profile information.
 *
 * @param int $user_id The ID of the user to update.
 * @param string $username The new username.
 * @param string $email The new email.
 * @param string|null $profile_image_path The file path of the new profile image, or null if not provided.
 * @return bool True on success, false on failure.
 */
function update_user_profile($user_id, $username, $email, $profile_image_path = null) {
    global $conn;
    if ($profile_image_path) {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, profile_image = ? WHERE user_id = ?");
        $stmt->bind_param("sssi", $username, $email, $profile_image_path, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE user_id = ?");
        $stmt->bind_param("ssi", $username, $email, $user_id);
    }
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Checks if a plant is already in the user's garden.
 *
 * @param int $user_id The ID of the user.
 * @param int $plant_id The ID of the plant.
 * @return bool True if the plant is in the garden, false otherwise.
 */
function is_plant_in_garden($user_id, $plant_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT plant_id FROM user_plants WHERE user_id = ? AND plant_id = ?");
    $stmt->bind_param("ii", $user_id, $plant_id);
    $stmt->execute();
    $stmt->store_result();
    $count = $stmt->num_rows;
    $stmt->close();
    return $count > 0;
}

/**
 * Adds a plant to a user's garden only if it's not already there.
 *
 * @param int $user_id The ID of the user.
 * @param int $plant_id The ID of the plant to add.
 * @return bool True on success, false on failure (including if the plant is already in the garden).
 */
function add_plant_to_garden($user_id, $plant_id) {
    global $conn;

    // Check if the plant is already in the garden
    if (is_plant_in_garden($user_id, $plant_id)) {
        return false; // Already exists
    }

    $stmt = $conn->prepare("INSERT INTO user_plants (user_id, plant_id) VALUES (?, ?)");
    if (!$stmt) return false; // failed to prepare

    $stmt->bind_param("ii", $user_id, $plant_id);
    $success = $stmt->execute();
    $stmt->close();

    return $success;
}

/**
 * Fetches the plants in a user's garden, including custom notes and images.
 *
 * @param int $user_id The ID of the user.
 * @return array An array of plant data, or an empty array if none are found.
 */
function get_user_plants($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT up.id AS user_plant_id, up.user_id, up.plant_id, up.notes, up.image_path,
                                   p.name, p.image
                            FROM user_plants up
                            JOIN plants p ON up.plant_id = p.plant_id
                            WHERE up.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $plants = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $plants;
}

/**
 * Updates a user's custom notes and image for a plant in their garden.
 *
 * @param int $user_plant_id The ID of the user's plant entry.
 * @param string $notes The new notes for the plant.
 * @param string|null $image_path The new image path, or null if not updated.
 * @return bool True on success, false on failure.
 */
function update_user_plant_notes($user_plant_id, $notes, $user_id) {
    global $conn;
    $stmt = $conn->prepare("UPDATE user_plants SET notes = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $notes, $user_plant_id, $user_id);
    $stmt->execute();
    $stmt->close();
}

/**
 * Deletes a plant from a user's garden.
 *
 * @param int $user_plant_id The ID of the user's plant entry to delete.
 * @param int $user_id The ID of the user to verify ownership.
 * @return bool True on success, false on failure.
 */
function delete_user_plant($user_plant_id, $user_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM user_plants WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $user_plant_id, $user_id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Fetches all items in a user's shopping cart.
 *
 * @param int $user_id The ID of the logged-in user.
 * @return array An array of cart item data, or an empty array if none are found.
 */
function get_cart_items($user_id) {
    global $conn;
    $cart_items = [];
    $sql = "SELECT c.cart_id, c.quantity, p.plant_id, p.name, p.price, p.image 
            FROM cart c 
            JOIN plants p ON c.plant_id = p.plant_id 
            WHERE c.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $cart_items[] = $row;
    }

    $stmt->close();
    return $cart_items;
}

/**
 * Creates a new order in the database and clears the user's cart.
 *
 * @param int $user_id The ID of the logged-in user.
 * @param float $total_amount The total price of the order.
 * @return int|false The ID of the newly created order on success, or false on failure.
 */
function create_order($user_id, $cart_items, $total_price) {
    global $conn;

    // Step 1: Insert into orders
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, order_date) 
                            VALUES (?, ?, 'Pending', NOW())");
    $stmt->bind_param("id", $user_id, $total_price);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // Step 2: Insert each cart item into order_items
    foreach ($cart_items as $item) {
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, plant_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $order_id, $item['plant_id'], $item['quantity']);
        $stmt->execute();
    }
    $stmt->close();

    // Step 3: Clear user’s cart
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    return $order_id;
}

/**
 * Fetches the details of a specific order for a given user.
 *
 * @param int $order_id The ID of the order to retrieve.
 * @param int $user_id The ID of the user who owns the order.
 * @return array|null An array of order details on success, or null if not found.
 */
function get_order_details($order_id, $user_id) {
    global $conn;
    $order = null;

    $stmt = $conn->prepare("
        SELECT o.*, u.username, u.email, u.address 
        FROM orders o
        JOIN users u ON o.user_id = u.user_id
        WHERE o.order_id = ? AND o.user_id = ?
    ");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
    }

    $stmt->close();
    return $order;
}

/**
 * Fetches all orders placed by a specific user.
 *
 * @param int $user_id The ID of the user whose orders to retrieve.
 * @return array An array of order data, or an empty array if none are found.
 */
function get_user_orders($user_id) {
    global $conn;
    $orders = [];
    $sql = "SELECT order_id, total_amount, order_date FROM orders WHERE user_id = ? ORDER BY order_date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
    }
    
    $stmt->close();
    return $orders;
}

/**
 * Adds a plant to a user's cart or updates the quantity if it already exists.
 *
 * @param int $user_id The ID of the logged-in user.
 * @param int $plant_id The ID of the plant to add.
 * @param int $quantity The quantity to add.
 * @return bool True on success, false on failure.
 */
function add_to_cart($user_id, $plant_id, $quantity) {
    global $conn;

    // Check if the item is already in the cart
    $stmt = $conn->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND plant_id = ?");
    $stmt->bind_param("ii", $user_id, $plant_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Item exists, update the quantity
        $row = $result->fetch_assoc();
        $new_quantity = $row['quantity'] + $quantity;
        $stmt->close();

        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND plant_id = ?");
        $stmt->bind_param("iii", $new_quantity, $user_id, $plant_id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    } else {
        // Item does not exist, insert new item
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO cart (user_id, plant_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $user_id, $plant_id, $quantity);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
}

/**
 * Updates the quantity of a specific cart item.
 *
 * @param int $user_id The ID of the logged-in user.
 * @param int $cart_id The ID of the cart item to update.
 * @param int $quantity The new quantity.
 * @return bool True on success, false on failure.
 */
function update_cart_item($user_id, $cart_id, $quantity) {
    global $conn;
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
    $stmt->bind_param("iii", $quantity, $cart_id, $user_id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Removes a specific item from a user's cart.
 *
 * @param int $user_id The ID of the logged-in user.
 * @param int $cart_id The ID of the cart item to remove.
 * @return bool True on success, false on failure.
 */
function remove_from_cart($user_id, $cart_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cart_id, $user_id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Fetches all reminders for a specific user.
 *
 * @param int $user_id The ID of the logged-in user.
 * @return array An array of reminder data, or an empty array if none are found.
 */
function get_user_reminders($user_id) {
    global $conn;
    $reminders = [];
    $sql = "SELECT r.reminder_id, r.reminder_date, r.type, p.name AS plant_name 
            FROM reminders r
            JOIN plants p ON r.plant_id = p.plant_id
            WHERE r.user_id = ?
            ORDER BY r.reminder_date ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $reminders[] = $row;
        }
    }
    
    $stmt->close();
    return $reminders;
}

/**
 * Adds a new reminder to the user's garden.
 *
 * @param int $user_id The ID of the user.
 * @param int $plant_id The ID of the plant to add a reminder for.
 * @param string $type The type of reminder (e.g., watering, fertilizing).
 * @param string $reminder_date The date and time for the reminder.
 * @return bool True on success, false on failure.
 */
function add_reminder($user_id, $plant_id, $type, $reminder_date) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO reminders (user_id, plant_id, type, reminder_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $user_id, $plant_id, $type, $reminder_date);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Deletes a reminder from the database.
 *
 * @param int $user_id The ID of the user who owns the reminder.
 * @param int $reminder_id The ID of the reminder to delete.
 * @return bool True on success, false on failure.
 */
function delete_reminder($user_id, $reminder_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM reminders WHERE reminder_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $reminder_id, $user_id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Fetches all items belonging to a specific order.
 *
 * @param int $order_id The ID of the order to retrieve items for.
 * @return array An array of order item data, or an empty array if none are found.
 */
function get_order_items($order_id) {
    global $conn;
    $order_items = [];
    $sql = "SELECT oi.quantity, p.name, p.price, p.image 
            FROM order_items oi 
            JOIN plants p ON oi.plant_id = p.plant_id 
            WHERE oi.order_id = ?";
    $stmt = $conn->prepare($sql);
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

// -------------------- Plant Comments & Likes Functions --------------------

/**
 * Fetches all approved comments for a specific plant.
 *
 * @param int $plant_id The ID of the plant.
 * @return array An array of approved comments with user info.
 */
function get_plant_comments($plant_id) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT c.comment_id, c.user_id, c.comment, c.created_at, u.username, u.profile_image
        FROM comments c
        JOIN users u ON c.user_id = u.user_id
        WHERE c.plant_id = ? AND c.is_approved = 1
        ORDER BY c.created_at DESC
    ");
    $stmt->bind_param("i", $plant_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $comments = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $comments;
}

/**
 * Adds a new comment for a plant.
 *
 * @param int $user_id The ID of the user posting the comment.
 * @param int $plant_id The ID of the plant being commented on.
 * @param string $comment The comment text.
 * @return bool True on success, false on failure.
 */
function add_plant_comment($user_id, $plant_id, $comment) {
    global $conn;
    $stmt = $conn->prepare("
        INSERT INTO comments (user_id, plant_id, comment, is_approved, created_at)
        VALUES (?, ?, ?, 1, NOW())
    ");
    $stmt->bind_param("iis", $user_id, $plant_id, $comment);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Checks if a user has liked a specific plant.
 *
 * @param int $user_id The ID of the user.
 * @param int $plant_id The ID of the plant.
 * @return bool True if user liked, false otherwise.
 */
function user_liked_plant($user_id, $plant_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM plant_likes WHERE user_id = ? AND plant_id = ?");
    $stmt->bind_param("ii", $user_id, $plant_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $liked = $result->num_rows > 0;
    $stmt->close();
    return $liked;
}

/**
 * Counts total likes for a specific plant.
 *
 * @param int $plant_id The ID of the plant.
 * @return int Number of likes.
 */
function count_plant_likes($plant_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM plant_likes WHERE plant_id = ?");
    $stmt->bind_param("i", $plant_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['total'] ?? 0;
}

/**
 * Toggles like/unlike for a plant by a user.
 *
 * @param int $user_id The ID of the user.
 * @param int $plant_id The ID of the plant.
 * @return bool True on success, false on failure.
 */
function toggle_plant_like($user_id, $plant_id) {
    global $conn;
    if (user_liked_plant($user_id, $plant_id)) {
        $stmt = $conn->prepare("DELETE FROM plant_likes WHERE user_id = ? AND plant_id = ?");
        $stmt->bind_param("ii", $user_id, $plant_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO plant_likes (user_id, plant_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $plant_id);
    }
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}


?>