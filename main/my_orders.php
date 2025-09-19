<?php
// Start the session to manage user login status
session_start();

// Check if the user is logged in. If not, redirect to the login page.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include the database connection and functions files
include 'includes/db_connection.php';
include 'includes/functions.php';

// Fetch the user's orders from the database
$user_id = $_SESSION['user_id'];
$user_orders = get_user_orders($user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Orders - Green Leaf</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
  <!-- Navbar -->
  <header>
    <?php include 'navbar.php'; ?>
  </header>

  <main class="max-w-5xl mx-auto px-4 py-10">
    <h1 class="text-3xl font-bold mb-4">My Orders</h1>
    <p class="text-lg text-gray-600 mb-8">Here is a list of your past and pending orders.</p>

    <?php if (!empty($user_orders)): ?>
      <div class="space-y-4">
        <?php foreach ($user_orders as $order): ?>
          <a href="order_details.php?order_id=<?php echo htmlspecialchars($order['order_id']); ?>"
             class="flex items-center justify-between p-5 bg-white border border-gray-200 rounded-lg shadow hover:shadow-md transition">
            <div>
              <h2 class="text-lg font-semibold text-gray-800">
                Order #<?php echo htmlspecialchars($order['order_id']); ?>
              </h2>
              <p class="text-sm text-gray-500">
                Placed on: <?php echo date('F j, Y', strtotime($order['order_date'])); ?>
              </p>
            </div>
            <span class="text-indigo-600 font-bold text-lg">
              $<?php echo number_format($order['total_amount'], 2); ?>
            </span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="text-center p-6 bg-blue-50 border border-blue-200 rounded-lg">
        <p class="text-blue-700 font-medium">You have not placed any orders yet.</p>
      </div>
    <?php endif; ?>
  </main>

  <footer class="">
    <?php include 'footer.php'; ?>
  </footer>
</body>
</html>
