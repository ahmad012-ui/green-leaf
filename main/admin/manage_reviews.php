<?php
// admin/manage_reviews.php

// This file provides an interface for the administrator to manage product reviews.
// It displays a list of all reviews, allowing for filtering, moderation (approve, delete)
// and now, full content management (add new, edit existing).

// Start the session and check for admin login
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Include the database connection and the review management functions
include '../includes/db_connection.php';
include 'review_management_functions.php';

// Initialize variables for filtering and messages
// Retrieve filter values from the URL, or set defaults
$status_filter = isset($_GET['status_filter']) ? htmlspecialchars($_GET['status_filter']) : 'all';
$search_query = isset($_GET['search_query']) ? htmlspecialchars($_GET['search_query']) : '';
$rating_filter = isset($_GET['rating_filter']) && $_GET['rating_filter'] !== '' ? intval($_GET['rating_filter']) : null;
$message = '';
$alert_class = '';

// Handle actions (approve or delete) based on URL parameters
if (isset($_GET['action']) && isset($_GET['id'])) {
    $review_id = intval($_GET['id']);
    
    if ($_GET['action'] == 'approve') {
        if (approve_review($review_id)) {
            $message = "Review approved successfully!";
        } else {
            $message = "Error approving review.";
        }
    } elseif ($_GET['action'] == 'delete') {
        if (delete_review($review_id)) {
            $message = "Review deleted successfully!";
        } else {
            $message = "Error deleting review.";
        }
    }
    // Redirect to the same page with the message and all current filter values
    $redirect_url = "manage_reviews.php?message=" . urlencode($message) . "&status_filter=" . urlencode($status_filter) . "&search_query=" . urlencode($search_query) . "&rating_filter=" . urlencode($rating_filter);
    header("Location: " . $redirect_url);
    exit();
}

// Check for a message parameter from a redirect
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
    // Set the alert class based on the message content
    $alert_class = (strpos($message, 'Error') !== false) ? "bg-red-100 border-red-400 text-red-700" : "bg-green-100 border-green-400 text-green-700";
}

// Fetch reviews based on all selected filters
// This is where we call the updated function with all the parameters
$reviews = get_all_reviews($status_filter, $search_query, $rating_filter);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Management - Green Leaf</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col md:flex-row">

    <?php include 'admin_sidebar.php'; ?>

    <main class="flex-1 p-4 md:p-8">
        <?php include 'admin_top_navbar.php'; ?>

        <div class="container mx-auto mt-4">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6">Review Management</h1>

            <?php if ($message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($message); ?></span>
                </div>
            <?php endif; ?>

            <div class="flex flex-col md:flex-row justify-between items-center mb-4 space-y-4 md:space-y-0 md:space-x-4">
                <a href="add_edit_review.php" class="inline-block bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md transition-colors w-full md:w-auto text-center">
                    <i class="fa-solid fa-plus mr-2"></i>Add New Review
                </a>
                
                <form action="manage_reviews.php" method="GET" class="flex flex-col md:flex-row items-stretch md:items-center space-y-2 md:space-y-0 md:space-x-2 w-full md:w-auto">
                    <div class="relative w-full">
                        <input type="text" name="search_query" placeholder="Search by Plant Name" value="<?php echo htmlspecialchars($search_query); ?>" class="pl-10 pr-3 py-2 w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>

                    <select name="rating_filter" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                        <option value="" <?php echo ($rating_filter === null) ? 'selected' : ''; ?>>All Ratings</option>
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?php echo $i; ?>" <?php echo ($rating_filter == $i) ? 'selected' : ''; ?>>
                                <?php echo $i; ?> Stars
                            </option>
                        <?php endfor; ?>
                    </select>
                    
                    <select name="status_filter" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                        <option value="all" <?php echo ($status_filter == 'all') ? 'selected' : ''; ?>>All Statuses</option>
                        <option value="pending" <?php echo ($status_filter == 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo ($status_filter == 'approved') ? 'selected' : ''; ?>>Approved</option>
                    </select>

                    <button type="submit" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-md transition-colors w-full md:w-auto">Filter</button>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-4 sm:p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Review ID</th>
                                    <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plant</th>
                                    <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                                    <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (!empty($reviews)): ?>
                                    <?php foreach ($reviews as $review): ?>
                                        <tr>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($review['review_id']); ?></td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($review['username']); ?></td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($review['plant_name']); ?></td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($review['rating']); ?>/5</td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo ($review['is_approved']) ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                                    <?php echo ($review['is_approved']) ? 'Approved' : 'Pending'; ?>
                                                </span>
                                            </td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="add_edit_review.php?id=<?php echo htmlspecialchars($review['review_id']); ?>" class="text-indigo-600 hover:text-indigo-900 mr-2 sm:mr-4">Edit</a>
                                                <?php if (!$review['is_approved']): ?>
                                                    <a href="manage_reviews.php?action=approve&id=<?php echo htmlspecialchars($review['review_id']); ?>&status_filter=<?php echo htmlspecialchars($status_filter); ?>&search_query=<?php echo htmlspecialchars($search_query); ?>&rating_filter=<?php echo htmlspecialchars($rating_filter); ?>" class="text-green-600 hover:text-green-900 mr-2 sm:mr-4">Approve</a>
                                                <?php endif; ?>
                                                <a href="manage_reviews.php?action=delete&id=<?php echo htmlspecialchars($review['review_id']); ?>&status_filter=<?php echo htmlspecialchars($status_filter); ?>&search_query=<?php echo htmlspecialchars($search_query); ?>&rating_filter=<?php echo htmlspecialchars($rating_filter); ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this review?');">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="px-3 sm:px-6 py-4 text-center text-sm text-gray-500">No reviews found.</td>
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