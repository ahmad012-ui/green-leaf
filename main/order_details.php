<?php
// Start the session to access user data
session_start();

// Check if the user is logged in. If not, redirect to login page.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection and functions
include 'includes/db_connection.php';
include 'includes/functions.php';

// Check if an order ID is provided in the URL
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    header("Location: my_orders.php"); 
    exit();
}

$order_id = $_GET['order_id'];
$user_id = $_SESSION['user_id'];

// Get the order details
$order = get_order_details($order_id, $user_id);
if (!$order) {
    header("Location: my_orders.php"); 
    exit();
}

// Get the order items
$order_items = get_order_items($order_id);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Order #<?php echo htmlspecialchars($order['order_id']); ?> Details</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">
  <header>
    <?php include 'navbar.php'; ?>
  </header>

  <main class="flex-grow container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto bg-white shadow rounded-lg">
      <!-- Card Header -->
      <div class="bg-green-600 text-white px-6 py-4 rounded-t-lg">
        <h2 class="text-xl font-semibold">Order Details #<?php echo htmlspecialchars($order['order_id']); ?></h2>
      </div>

      <!-- Card Body -->
      <div class="p-6">
        <div class="grid md:grid-cols-2 gap-4 mb-4">
          <div>
            <p><span class="font-semibold">Order Date:</span> <?php echo date('F j, Y', strtotime($order['order_date'])); ?></p>
            <p><span class="font-semibold">Status:</span> 
              <span class="inline-block px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-sm">
                <?php echo htmlspecialchars(ucfirst($order['status'])); ?>
              </span>
            </p>
          </div>
          <div class="text-md-end md:text-right">
            <p><span class="font-semibold">Total Amount:</span> 
              <span class="font-bold text-green-600">$<?php echo number_format($order['total_amount'], 2); ?></span>
            </p>
          </div>
        </div>

        <hr class="my-4">

        <h4 class="text-lg font-semibold mb-3">Order Items</h4>
        <?php if (!empty($order_items)): ?>
          <ul class="divide-y divide-gray-200">
            <?php foreach ($order_items as $item): ?>
              <li class="flex justify-between items-center py-3">
                <div class="flex items-center">
                  <img src="images/<?php echo htmlspecialchars($item['image']); ?>" 
                       alt="<?php echo htmlspecialchars($item['name']); ?>" 
                       class="w-16 h-16 rounded object-cover mr-4 border">
                  <div>
                    <h6 class="font-medium"><?php echo htmlspecialchars($item['name']); ?></h6>
                    <p class="text-sm text-gray-500">$<?php echo htmlspecialchars($item['price']); ?> × <?php echo htmlspecialchars($item['quantity']); ?></p>
                  </div>
                </div>
                <span class="font-semibold">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <div class="bg-yellow-100 text-yellow-800 px-4 py-3 rounded mt-3">
            No items found for this order.
          </div>
        <?php endif; ?>
      </div>

      <!-- Card Footer -->
      <div class="px-6 py-4 bg-gray-50 rounded-b-lg text-right">
        <a href="my_orders.php" class="inline-block bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">
          &laquo; Back to My Orders
        </a>
      </div>
    </div>
  </main>

  <?php include 'footer.php'; ?>
</body>
</html>
