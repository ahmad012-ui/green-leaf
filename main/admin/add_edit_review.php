<?php
// Start the session and check for admin login
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Include the database connection and functions files
include '../includes/db_connection.php';
include 'review_management_functions.php';
include 'plant_management_functions.php'; // Required to get plant list
include 'user_management_functions.php';   // Required to get user list

$review_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$review = null;
$page_title = "Add New Review";
$message = '';

if ($review_id > 0) {
    $review = get_review_by_id($review_id);
    if (!$review) {
        header("Location: manage_reviews.php");
        exit();
    }
    $page_title = "Edit Review: #" . htmlspecialchars($review['review_id']);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = intval($_POST['user_id']);
    $plant_id = intval($_POST['plant_id']);
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);
    $is_approved = isset($_POST['is_approved']) ? 1 : 0;

    if ($review_id > 0) {
        // This is an update
        if (update_review($review_id, $user_id, $plant_id, $rating, $comment, $is_approved)) {
            // After a successful update, redirect to the main review page
            // The message parameter is used to display a success alert on the next page
            header("Location: manage_reviews.php?message=updated");
            exit();
        } else {
            $message = "Error updating review.";
        }
    } else {
        // This is a new review
        if (add_review_admin($user_id, $plant_id, $rating, $comment, $is_approved)) {
            // After a successful addition, redirect to the main review page
            // The message parameter is used to display a success alert on the next page
            header("Location: manage_reviews.php?message=added");
            exit();
        } else {
            $message = "Error adding new review.";
        }
    }
}

// Fetch all plants and users for the dropdowns
$plants = get_all_plants_admin();
$users = get_all_users();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col md:flex-row">
    
    <?php include 'admin_sidebar.php'; ?>

    <main class="flex-1 p-4 md:p-8">
        <?php include 'admin_top_navbar.php'; ?>

        <div class="container mx-auto mt-4">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6"><?php echo htmlspecialchars($page_title); ?></h1>

            <?php if ($message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($message); ?></span>
                </div>
            <?php endif; ?>
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                <form action="add_edit_review.php?id=<?php echo htmlspecialchars($review_id); ?>" method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700">Reviewer</label>
                            <select id="user_id" name="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50" required>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?php echo htmlspecialchars($user['user_id']); ?>" <?php echo ($review && $review['user_id'] == $user['user_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($user['username']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="plant_id" class="block text-sm font-medium text-gray-700">Plant</label>
                            <select id="plant_id" name="plant_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50" required>
                                <?php foreach ($plants as $plant): ?>
                                    <option value="<?php echo htmlspecialchars($plant['plant_id']); ?>" <?php echo ($review && $review['plant_id'] == $plant['plant_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($plant['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="rating" class="block text-sm font-medium text-gray-700">Rating</label>
                            <select id="rating" name="rating" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50" required>
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <option value="<?php echo $i; ?>" <?php echo ($review && $review['rating'] == $i) ? 'selected' : ''; ?>>
                                        <?php echo $i; ?> Stars
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="md:col-span-2 lg:col-span-3">
                            <label for="comment" class="block text-sm font-medium text-gray-700">Comment</label>
                            <textarea id="comment" name="comment" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50" required><?php echo ($review) ? htmlspecialchars($review['comment']) : ''; ?></textarea>
                        </div>
                    </div>
                    
                    <div class="mt-4 flex items-center">
                        <input type="checkbox" id="is_approved" name="is_approved" class="h-4 w-4 text-green-600 border-gray-300 rounded focus:ring-green-500" <?php echo ($review && $review['is_approved']) ? 'checked' : ''; ?>>
                        <label for="is_approved" class="ml-2 block text-sm text-gray-900">Approve Review</label>
                    </div>

                    <div class="mt-6 flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md transition-colors w-full sm:w-auto">
                            <?php echo ($review) ? 'Update Review' : 'Add Review'; ?>
                        </button>
                        <a href="manage_reviews.php" class="text-gray-600 hover:text-gray-800 w-full sm:w-auto text-center sm:text-left">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>