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

$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$category = get_category_by_id($category_id);

// If the category doesn't exist, redirect back to the main management page
if (!$category) {
    header("Location: manage_categories.php");
    exit();
}

$message = '';

// Handle form submission for editing
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_category'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    if (update_category($category_id, $name, $description)) {
        $message = "Category updated successfully!";
        // Re-fetch category data to show the updated values
        $category = get_category_by_id($category_id);
        header("Location: manage_categories.php?message=updated");
        exit();
    } else {
        $message = "Error updating category.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category - Green Leaf</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col md:flex-row">

    <?php include 'admin_sidebar.php'; ?>

    <main class="flex-1 p-4 md:p-8">
        <?php include 'admin_top_navbar.php'; ?>

        <div class="container mx-auto mt-4">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6">Edit Category: <?php echo htmlspecialchars($category['category_name']); ?></h1>

            <?php if ($message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($message); ?></span>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 max-w-lg mx-auto">
                <form action="edit_category.php?id=<?php echo htmlspecialchars($category_id); ?>" method="POST">
                    <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($category['category_id']); ?>">
                    
                    <div class="mb-4">
                        <label for="name" class="block text-gray-700 text-sm font-medium mb-2">Category Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($category['category_name']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="description" class="block text-gray-700 text-sm font-medium mb-2">Description</label>
                        <textarea id="description" name="description" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"><?php echo htmlspecialchars($category['description']); ?></textarea>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between space-y-2 sm:space-y-0">
                        <button type="submit" name="edit_category" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition-colors focus:outline-none focus:shadow-outline">Update Category</button>
                        <a href="manage_categories.php" class="text-center sm:text-right font-medium text-gray-600 hover:text-gray-800">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>