<?php
// Start the session to access user data
session_start();

// Check if the user is logged in. If not, redirect to login page.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include the database connection and functions files
include 'includes/db_connection.php';
include 'includes/functions.php';

// Check if an order ID is provided in the URL
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    header("Location: index.php"); // Redirect if no order ID is found
    exit();
}

$order_id = $_GET['order_id'];
$order = get_order_details($order_id, $_SESSION['user_id']);

// If the order details are not found or don't belong to the logged-in user
if (!$order) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed - Green Leaf</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="flex flex-col min-h-screen bg-gray-50">
    <header>
        <?php include 'navbar.php'; ?>
    </header>

    <main class="flex-grow container mx-auto px-4 py-10">
        <div class="flex justify-center">
            <div class="bg-white shadow-lg rounded-xl w-full max-w-2xl text-center">
                <div class="bg-green-600 text-white rounded-t-xl py-4">
                    <h2 class="text-2xl font-bold">Order Confirmed!</h2>
                </div>
                <div class="p-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-6 text-green-600" width="80" height="80" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                    </svg>
                    <p class="text-lg">Thank you for your purchase, <span class="font-semibold"><?php echo htmlspecialchars($_SESSION['username']); ?></span>!</p>
                    <p class="text-gray-600">Your order #<?php echo htmlspecialchars($order['order_id']); ?> has been placed successfully.</p>
                    <hr class="my-6 border-gray-200">

                    <h4 class="text-xl font-semibold mb-4">Order Summary</h4>
                    <ul class="divide-y divide-gray-200 text-left">
                        <li class="flex justify-between py-3">
                            <span class="font-medium">Order Date:</span>
                            <span><?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></span>
                        </li>
                        <li class="flex justify-between py-3">
                            <span class="font-medium">Total Amount:</span>
                            <span class="text-green-600 font-bold">$<?php echo number_format($order['total_amount'], 2); ?></span>
                        </li>
                        <li class="flex justify-between py-3">
                            <span class="font-medium">Status:</span>
                            <span class="px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-700">
                                <?php echo htmlspecialchars(ucfirst($order['status'])); ?>
                            </span>
                        </li>
                    </ul>

                    <div class="mt-6 flex justify-center gap-4">
                        <a href="catalog.php" class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Continue Shopping</a>
                        <a href="my_orders.php" class="px-5 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition">View My Orders</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include 'footer.php'; ?>

    
</body>
</html>