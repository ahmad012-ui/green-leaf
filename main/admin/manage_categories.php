<?php
// Start the session and check for admin login
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Include the database connection and functions files
include '../includes/db_connection.php';
include 'category_management_functions.php';

$message = '';

// Handle form submission for adding/editing categories
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_category'])) {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        if (add_category($name, $description)) {
            $message = "Category added successfully!";
        } else {
            $message = "Error adding category.";
        }
    } elseif (isset($_POST['edit_category'])) {
        $category_id = intval($_POST['category_id']);
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        if (update_category($category_id, $name, $description)) {
            $message = "Category updated successfully!";
        } else {
            $message = "Error updating category.";
        }
    }
}

// Handle deletion
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $category_id = intval($_GET['id']);
    if (delete_category($category_id)) {
        $message = "Category deleted successfully!";
    } else {
        $message = "Error deleting category.";
    }
    // Redirect to prevent form resubmission
    header("Location: manage_categories.php");
    exit();
}

// Fetch all categories
$categories = get_all_categories();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Green Leaf</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col md:flex-row">

    <?php include 'admin_sidebar.php'; ?>

    <main class="flex-1 p-4 md:p-8">
        <?php include 'admin_top_navbar.php'; ?>

        <div class="container mx-auto mt-4">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6">Manage Plant Categories</h1>

            <?php if ($message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($message); ?></span>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                    <h2 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4">Add New Category</h2>
                    <form action="manage_categories.php" method="POST">
                        <div class="mb-4">
                            <label for="name" class="block text-gray-700 text-sm font-medium mb-2">Category Name</label>
                            <input type="text" id="name" name="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        </div>
                        <div class="mb-4">
                            <label for="description" class="block text-gray-700 text-sm font-medium mb-2">Description</label>
                            <textarea id="description" name="description" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                        </div>
                        <button type="submit" name="add_category" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition-colors focus:outline-none focus:shadow-outline">Add Category</button>
                    </form>
                </div>

                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-4 sm:p-6 border-b border-gray-200">
                      <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Existing Categories</h2>
                    </div>
                    <div class="overflow-x-auto p-4 sm:p-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($category['category_id']); ?></td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($category['category_name']); ?></td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="edit_category.php?id=<?php echo htmlspecialchars($category['category_id']); ?>" class="text-indigo-600 hover:text-indigo-900 mr-2 sm:mr-4">Edit</a>
                                                <a href="manage_categories.php?action=delete&id=<?php echo htmlspecialchars($category['category_id']); ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this category? All plants in this category will become unassigned.');">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="px-3 sm:px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">No categories found.</td>
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