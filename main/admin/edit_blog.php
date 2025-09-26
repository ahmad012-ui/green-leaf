<?php
// Start the session and check for admin login
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Include the database connection and functions files
include '../includes/db_connection.php';
include 'blog_management_functions.php';

// Check if a blog ID is provided in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_blogs.php");
    exit();
}

$blog_id = intval($_GET['id']);
$blog = get_blog_by_id_admin($blog_id);

// If no blog is found with that ID, redirect back
if (!$blog) {
    header("Location: manage_blogs.php");
    exit();
}

// Handle form submission for updating a blog
$update_success = false;
$update_error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_blog'])) {
    $image_filename = $blog['image'] ?? ''; // Default to current image
    $original_image = $blog['image'] ?? '';

    // Handle image upload if a new one is selected
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../images/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_filename = basename($_FILES["image"]["name"]);
        } else {
            $update_error = "Error uploading new image.";
        }
    }

    if (empty($update_error)) {
        $blog_data = [
            'title' => trim($_POST['title']),
            'content' => trim($_POST['content']),
            'author_id' => $_SESSION['user_id'],
            'image' => $image_filename
        ];

        if (update_blog($blog_id, $blog_data)) {
            $update_success = true;
            // Re-fetch blog data to display updated info on the page
            $blog = get_blog_by_id_admin($blog_id);
        } else {
            $update_error = "Error updating blog post in the database.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Blog Post - Green Leaf</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col md:flex-row">
    
    <?php include 'admin_sidebar.php'; ?>

    <main class="flex-1 p-4 md:p-8">
        <?php include 'admin_top_navbar.php'; ?>

        <div class="container mx-auto mt-4">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6">Edit Blog Post: <?php echo htmlspecialchars($blog['title'] ?? ''); ?></h1>

            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-8">
                <?php if ($update_success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">Blog post updated successfully!</span>
                    </div>
                <?php endif; ?>
                <?php if ($update_error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?php echo htmlspecialchars($update_error); ?></span>
                    </div>
                <?php endif; ?>
                <form action="edit_blog.php?id=<?php echo htmlspecialchars($blog_id); ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="update_blog" value="1">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="md:col-span-2 lg:col-span-3">
                            <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                            <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($blog['title'] ?? ''); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50">
                        </div>
                        <div class="md:col-span-2 lg:col-span-3">
                            <label for="content" class="block text-sm font-medium text-gray-700">Content</label>
                            <textarea name="content" id="content" rows="6" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50"><?php echo htmlspecialchars($blog['content'] ?? ''); ?></textarea>
                        </div>
                        <div class="md:col-span-2 lg:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Current Image</label>
                            <?php if (!empty($blog['image'])): ?>
                                <img src="../images/<?php echo htmlspecialchars($blog['image']); ?>" alt="<?php echo htmlspecialchars($blog['title']); ?>" class="h-40 w-40 object-cover rounded-md mb-4">
                            <?php else: ?>
                                <p class="text-sm text-gray-500 mb-4">No image uploaded.</p>
                            <?php endif; ?>
                            
                            <label for="image" class="block text-sm font-medium text-gray-700">Update Image</label>
                            <input type="file" name="image" id="image" class="mt-1 block w-full">
                            <p class="text-sm text-gray-500 mt-1">Leave this field blank to keep the current image.</p>
                        </div>
                    </div>
                    <div class="mt-6 flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md transition-colors">Update Blog Post</button>
                        <a href="manage_blogs.php" class="text-gray-600 hover:text-gray-800">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>