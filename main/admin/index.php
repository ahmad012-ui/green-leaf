<?php
// Start the session and check for admin login
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Include the database connection and functions files
include '../includes/db_connection.php';
include 'dashboard_functions.php';

// Fetch data for the dashboard
$total_sales = get_total_sales();
$total_orders = get_total_orders();
$latest_reviews = get_latest_pending_reviews();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Green Leaf</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col md:flex-row">

  <?php include 'admin_sidebar.php'; ?>

  <main class="flex-1 p-4 md:p-8">
    <?php include 'admin_top_navbar.php'; ?>

    <div class="container mx-auto mt-4">
      <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6">Dashboard</h1>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 flex items-center justify-between">
          <div>
            <p class="text-gray-500 text-sm sm:text-base font-medium">Total Sales</p>
            <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-green-600 mt-1">
              $<?php echo number_format($total_sales, 2); ?>
            </h2>
          </div>
          <i class="fa-solid fa-dollar-sign text-2xl sm:text-3xl md:text-4xl text-green-300"></i>
        </div>

        <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 flex items-center justify-between">
          <div>
            <p class="text-gray-500 text-sm sm:text-base font-medium">Total Orders</p>
            <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-blue-600 mt-1">
              <?php echo htmlspecialchars($total_orders); ?>
            </h2>
          </div>
          <i class="fa-solid fa-box text-2xl sm:text-3xl md:text-4xl text-blue-300"></i>
        </div>

        <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 flex items-center justify-between">
          <div>
            <p class="text-gray-500 text-sm sm:text-base font-medium">Pending Reviews</p>
            <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-yellow-600 mt-1">
              <?php echo count($latest_reviews); ?>
            </h2>
          </div>
          <i class="fa-solid fa-star text-2xl sm:text-3xl md:text-4xl text-yellow-300"></i>
        </div>
      </div>

      <!-- Latest Pending Reviews -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-gray-200">
          <h2 class="text-lg sm:text-xl font-bold text-gray-800">Latest Pending Reviews</h2>
        </div>
        <div class="p-4 sm:p-6">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                  <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plant</th>
                  <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                  <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comment</th>
                  <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                  <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <?php if (!empty($latest_reviews)): ?>
                  <?php foreach ($latest_reviews as $review): ?>
                    <tr>
                      <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-gray-500"><?php echo htmlspecialchars($review['username'] ?? 'N/A'); ?></td>
                      <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-gray-500"><?php echo htmlspecialchars($review['plant_name'] ?? 'N/A'); ?></td>
                      <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-gray-500"><?php echo htmlspecialchars($review['rating']); ?>/5</td>
                      <td class="px-3 sm:px-6 py-4 text-gray-500 max-w-[150px] sm:max-w-xs truncate"><?php echo htmlspecialchars(substr($review['comment'], 0, 50)); ?>...</td>
                      <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-gray-500"><?php echo htmlspecialchars(date('M d, Y', strtotime($review['created_at']))); ?></td>
                      <td class="px-3 sm:px-6 py-4 whitespace-nowrap font-medium">
                        <a href="manage_reviews.php?action=approve&id=<?php echo htmlspecialchars($review['review_id']); ?>" class="text-green-600 hover:text-green-900 mr-2 sm:mr-4">Approve</a>
                        <a href="manage_reviews.php?action=delete&id=<?php echo htmlspecialchars($review['review_id']); ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this review?');">Delete</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="6" class="px-3 sm:px-6 py-4 text-center text-gray-500">No pending reviews found.</td>
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
