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

// Handle plant deletion
$delete_success = false;
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $plant_id = intval($_GET['id']);
    if (delete_plant($plant_id)) {
        $delete_success = true;
    }
}

// Handle adding a new plant
$add_success = false;
$add_error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_plant'])) {
    $image_filename = '';
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../images/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_filename = basename($_FILES["image"]["name"]);
        } else {
            $add_error = "Error uploading image.";
        }
    }

    if (empty($add_error)) {
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

        if (add_plant($plant_data)) {
            $add_success = true;
        } else {
            $add_error = "Error adding plant to the database.";
        }
    }
}

// Fetch categories for filters
$categories = get_all_categories();

// Handle filters and search
$search = $_GET['search'] ?? '';
$filter_category = $_GET['category'] ?? '';
$filter_price = $_GET['price'] ?? '';
$filter_name = $_GET['name'] ?? '';

// Build query with filters
$sql = "SELECT p.plant_id, p.name, p.price, p.image, c.category_name 
        FROM plants p
        LEFT JOIN categories c ON p.category_id = c.category_id
        WHERE 1";

if (!empty($search)) {
    $sql .= " AND (p.name LIKE '%" . $conn->real_escape_string($search) . "%' 
                 OR c.category_name LIKE '%" . $conn->real_escape_string($search) . "%')";
}
if (!empty($filter_category)) {
    $sql .= " AND p.category_id = " . intval($filter_category);
}
if (!empty($filter_price)) {
    if ($filter_price == 'low') {
        $sql .= " AND p.price < 50";
    } elseif ($filter_price == 'mid') {
        $sql .= " AND p.price BETWEEN 50 AND 100";
    } elseif ($filter_price == 'high') {
        $sql .= " AND p.price > 100";
    }
}
if (!empty($filter_name)) {
    $sql .= " AND p.name LIKE '%" . $conn->real_escape_string($filter_name) . "%'";
}

$sql .= " ORDER BY p.name ASC";
$result = $conn->query($sql);
$plants = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $plants[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Plants - Green Leaf</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col md:flex-row">
    
    <?php include 'admin_sidebar.php'; ?>

    <main class="flex-1 p-4 md:p-8">
        <?php include 'admin_top_navbar.php'; ?>

        <div class="container mx-auto mt-4">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6">Manage Plants</h1>

            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-8">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4">Add New Plant</h2>
                <?php if ($add_success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">New plant added successfully!</span>
                    </div>
                <?php endif; ?>
                <?php if ($add_error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?php echo htmlspecialchars($add_error); ?></span>
                    </div>
                <?php endif; ?>
                <form action="manage_plants.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="add_plant" value="1">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Plant Name</label>
                            <input type="text" name="name" id="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label for="botanical_name" class="block text-sm font-medium text-gray-700">Botanical Name</label>
                            <input type="text" name="botanical_name" id="botanical_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
                            <input type="number" step="0.01" name="price" id="price" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                            <select name="category_id" id="category_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50">
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo htmlspecialchars($category['category_id']); ?>"><?php echo htmlspecialchars($category['category_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="care_level" class="block text-sm font-medium text-gray-700">Care Level</label>
                            <input type="text" name="care_level" id="care_level" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label for="light_requirement" class="block text-sm font-medium text-gray-700">Light Requirement</label>
                            <input type="text" name="light_requirement" id="light_requirement" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label for="watering_schedule" class="block text-sm font-medium text-gray-700">Watering Schedule</label>
                            <input type="text" name="watering_schedule" id="watering_schedule" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50">
                        </div>
                        <div class="md:col-span-2 lg:col-span-3">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50"></textarea>
                        </div>
                        <div class="md:col-span-2 lg:col-span-3">
                            <label for="image" class="block text-sm font-medium text-gray-700">Image</label>
                            <input type="file" name="image" id="image" required class="mt-1 block w-full">
                        </div>
                    </div>
                    <div class="mt-6">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md transition-colors">Add Plant</button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-6">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4">Filter Plants</h2>
                <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Search</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Search by name or category" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Category</label>
                        <select name="category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500">
                            <option value="">All</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['category_id']; ?>" <?php if ($filter_category == $cat['category_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($cat['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Price</label>
                        <select name="price" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500">
                            <option value="">All</option>
                            <option value="low" <?php if ($filter_price == 'low') echo 'selected'; ?>>Below $50</option>
                            <option value="mid" <?php if ($filter_price == 'mid') echo 'selected'; ?>>$50 - $100</option>
                            <option value="high" <?php if ($filter_price == 'high') echo 'selected'; ?>>Above $100</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name Filter</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($filter_name); ?>" 
                               placeholder="Filter by name" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500">
                    </div>

                    <div class="sm:col-span-2 md:col-span-4 mt-2">
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">
                            Apply Filters
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-4 sm:p-6 border-b border-gray-200">
                    <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Current Plants</h2>
                </div>
                <?php if ($delete_success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative my-4 mx-6" role="alert">
                        <span class="block sm:inline">Plant deleted successfully!</span>
                    </div>
                <?php endif; ?>
                <div class="p-4 sm:p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                                    <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                    <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (!empty($plants)): ?>
                                    <?php foreach ($plants as $plant): ?>
                                        <tr>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                <img src="../images/<?php echo htmlspecialchars($plant['image']); ?>" alt="<?php echo htmlspecialchars($plant['name']); ?>" class="h-12 w-12 object-cover rounded-md">
                                            </td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($plant['name']); ?></td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($plant['category_name']); ?></td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500">$<?php echo htmlspecialchars(number_format($plant['price'], 2)); ?></td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="edit_plant.php?id=<?php echo htmlspecialchars($plant['plant_id']); ?>" class="text-indigo-600 hover:text-indigo-900 mr-2 sm:mr-4">Edit</a>
                                                <a href="manage_plants.php?action=delete&id=<?php echo htmlspecialchars($plant['plant_id']); ?>" onclick="return confirm('Are you sure you want to delete this plant?');" class="text-red-600 hover:text-red-900">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="px-3 sm:px-6 py-4 text-center text-sm text-gray-500">No plants found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>