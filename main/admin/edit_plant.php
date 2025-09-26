<?php
// Start the session and check for admin login
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Include the database connection and functions files
include '../includes/db_connection.php';
include 'plant_management_functions.php';

// Check if a plant ID is provided in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_plants.php");
    exit();
}

$plant_id = intval($_GET['id']);
$plant = get_plant_by_id_admin($plant_id);

// If no plant is found with that ID, redirect back
if (!$plant) {
    header("Location: manage_plants.php");
    exit();
}

// Handle form submission for updating a plant
$update_success = false;
$update_error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_plant'])) {
    $image_filename = $plant['image'] ?? ''; // Default to current image
    $original_image = $plant['image'] ?? '';

    // Handle image upload if a new one is selected
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_filename = basename($_FILES["image"]["name"]);
        } else {
            $update_error = "Error uploading new image.";
        }
    }

    if (empty($update_error)) {
        $plant_data = [
            'name' => trim($_POST['name']),
            'botanical_name' => trim($_POST['botanical_name']),
            'description' => trim($_POST['description']),
            'price' => floatval($_POST['price']),
            'image' => $image_filename,
            'care_level' => trim($_POST['care_level']),
            'light_requirement' => trim($_POST['light_requirement']),
            'watering_schedule' => trim($_POST['watering_schedule']),
            'category_id' => intval($_POST['category_id'])
        ];

        if (update_plant($plant_id, $plant_data)) {
            $update_success = true;
            // Re-fetch plant data to display updated info on the page
            $plant = get_plant_by_id_admin($plant_id);
        } else {
            $update_error = "Error updating plant in the database.";
        }
    }
}

// Fetch all categories for the dropdown menu
$categories = get_all_categories();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Plant - Green Leaf</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col md:flex-row">
    
    <?php include 'admin_sidebar.php'; ?>

    <main class="flex-1 p-4 md:p-8">
        <?php include 'admin_top_navbar.php'; ?>

        <div class="container mx-auto mt-4">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6">Edit Plant: <?php echo htmlspecialchars($plant['name'] ?? ''); ?></h1>

            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-8">
                <?php if ($update_success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">Plant updated successfully!</span>
                    </div>
                <?php endif; ?>
                <?php if ($update_error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?php echo htmlspecialchars($update_error); ?></span>
                    </div>
                <?php endif; ?>
                <form action="manage_plants.php?id=<?php echo htmlspecialchars($plant_id); ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="update_plant" value="1">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Plant Name</label>
                            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($plant['name'] ?? ''); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label for="botanical_name" class="block text-sm font-medium text-gray-700">Botanical Name</label>
                            <input type="text" name="botanical_name" id="botanical_name" value="<?php echo htmlspecialchars($plant['botanical_name'] ?? ''); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
                            <input type="number" step="0.01" name="price" id="price" value="<?php echo htmlspecialchars($plant['price'] ?? ''); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                            <select name="category_id" id="category_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50">
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo htmlspecialchars($category['category_id']); ?>" <?php if (($plant['category_id'] ?? '') == $category['category_id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="care_level" class="block text-sm font-medium text-gray-700">Care Level</label>
                            <input type="text" name="care_level" id="care_level" value="<?php echo htmlspecialchars($plant['care_level'] ?? ''); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label for="light_requirement" class="block text-sm font-medium text-gray-700">Light Requirement</label>
                            <input type="text" name="light_requirement" id="light_requirement" value="<?php echo htmlspecialchars($plant['light_requirement'] ?? ''); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label for="watering_schedule" class="block text-sm font-medium text-gray-700">Watering Schedule</label>
                            <input type="text" name="watering_schedule" id="watering_schedule" value="<?php echo htmlspecialchars($plant['watering_schedule'] ?? ''); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50">
                        </div>
                        <div class="md:col-span-2 lg:col-span-3">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50"><?php echo htmlspecialchars($plant['description'] ?? ''); ?></textarea>
                        </div>
                        <div class="md:col-span-2 lg:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Current Image</label>
                            <img src="../uploads/<?php echo htmlspecialchars($plant['image'] ?? ''); ?>" alt="<?php echo htmlspecialchars($plant['name'] ?? ''); ?>" class="h-40 w-40 object-cover rounded-md mb-4">
                            
                            <label for="image" class="block text-sm font-medium text-gray-700">Update Image</label>
                            <input type="file" name="image" id="image" class="mt-1 block w-full">
                            <p class="text-sm text-gray-500 mt-1">Leave this field blank to keep the current image.</p>
                        </div>
                    </div>
                    <div class="mt-6 flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md transition-colors">Update Plant</button>
                        <a href="manage_plants.php" class="text-gray-600 hover:text-gray-800">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>