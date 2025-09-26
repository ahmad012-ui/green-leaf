<?php
// Start the session and check for admin login
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Include the database connection and functions files
include '../includes/db_connection.php';
include 'order_management_functions.php';

// Get order ID from URL
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$order = get_order_by_id($order_id);
$order_items = get_order_items($order_id);

// Handle order status update
$message = '';
if (isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    if (update_order_status($order_id, $new_status)) {
        $message = "Order status updated successfully!";
        // Re-fetch the order to show the updated status
        $order = get_order_by_id($order_id);
        header("Location: manage_orders.php?id=" . $order_id . "&message=updated");
    } else {
        $message = "Error updating order status.";
    }
}

// If order is not found, redirect back to the order list
if (!$order) {
    header("Location: manage_orders.php");
    exit();
}

$status_options = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo htmlspecialchars($order['order_id']); ?> - Green Leaf</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col md:flex-row">

    <?php include 'admin_sidebar.php'; ?>

    <main class="flex-1 p-4 md:p-8">
        <?php include 'admin_top_navbar.php'; ?>

        <div class="container mx-auto mt-4">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6">Order Details: #<?php echo htmlspecialchars($order['order_id']); ?></h1>

            <?php if ($message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($message); ?></span>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-2 bg-white rounded-lg shadow-md overflow-hidden p-4 sm:p-6">
                    <h2 class="text-lg sm:text-xl font-bold text-gray-800 mb-4">Order Summary</h2>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <i class="fa-solid fa-calendar-check text-green-500 mr-2"></i>
                            <p class="text-gray-600">Order Date: <span class="font-semibold"><?php echo htmlspecialchars(date('F j, Y', strtotime($order['created_at']))); ?></span></p>
                        </div>
                        <div class="flex items-center">
                            <i class="fa-solid fa-dollar-sign text-green-500 mr-2"></i>
                            <p class="text-gray-600">Total: <span class="font-semibold text-xl">$<?php echo number_format(htmlspecialchars($order['total_amount']), 2); ?></span></p>
                        </div>
                        <div class="flex items-center">
                            <i class="fa-solid fa-circle-info text-green-500 mr-2"></i>
                            <p class="text-gray-600">Status: <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                <?php
                                    switch ($order['status']) {
                                        case 'Pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                        case 'Processing': echo 'bg-blue-100 text-blue-800'; break;
                                        case 'Shipped': echo 'bg-indigo-100 text-indigo-800'; break;
                                        case 'Delivered': echo 'bg-green-100 text-green-800'; break;
                                        case 'Cancelled': echo 'bg-red-100 text-red-800'; break;
                                        default: echo 'bg-gray-100 text-gray-800'; break;
                                    }
                                ?>">
                                <?php echo htmlspecialchars($order['status']); ?>
                            </span></p>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold text-gray-800 mt-6 mb-4">Order Items</h3>
                    <div class="space-y-4">
                        <?php if (!empty($order_items)): ?>
                            <?php foreach ($order_items as $item): ?>
                                <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-4 bg-gray-50 p-4 rounded-lg">
                                    <div class="flex-shrink-0">
                                        <img src="../images/<?php echo htmlspecialchars($item['image']); ?>" class="w-20 h-20 object-cover rounded" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-md font-semibold text-gray-800"><?php echo htmlspecialchars($item['name']); ?></h4>
                                        <p class="text-sm text-gray-600">Quantity: <?php echo htmlspecialchars($item['quantity']); ?></p>
                                    </div>
                                    <span class="text-md font-semibold text-gray-800 mt-2 sm:mt-0">$<?php echo number_format(htmlspecialchars($item['price']), 2); ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-center text-gray-500">No items found for this order.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md overflow-hidden p-4 sm:p-6 h-fit">
                    <h2 class="text-lg sm:text-xl font-bold text-gray-800 mb-4">Customer Details</h2>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <i class="fa-solid fa-user text-green-500 mr-2"></i>
                            <p class="text-gray-600">User: <span class="font-semibold"><?php echo htmlspecialchars($order['username']); ?></span></p>
                        </div>
                        <div class="flex items-center">
                            <i class="fa-solid fa-envelope text-green-500 mr-2"></i>
                            <p class="text-gray-600">Email: <span class="font-semibold"><?php echo htmlspecialchars($order['email']); ?></span></p>
                        </div>
                        <div class="flex items-start">
                            <i class="fa-solid fa-location-dot text-green-500 mr-2 mt-1"></i>
                            <p class="text-gray-600">Address: <span class="font-semibold"><?php echo htmlspecialchars($order['address']); ?></span></p>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold text-gray-800 mt-6 mb-4">Update Status</h3>
                    <form action="view_order.php?id=<?php echo htmlspecialchars($order['order_id']); ?>" method="POST">
                        <div class="mb-4">
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Select New Status</label>
                            <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm rounded-md">
                                <?php foreach ($status_options as $option): ?>
                                    <option value="<?php echo htmlspecialchars($option); ?>" <?php echo ($order['status'] == $option) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($option); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" name="update_status" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Update Status
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>