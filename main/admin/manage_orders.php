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

// Handle order status updates
$message = '';
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['status'];
    if (update_order_status($order_id, $new_status)) {
        $message = "Order status updated successfully!";
    } else {
        $message = "Error updating order status.";
    }
}

// Fetch all orders from the database
$orders = get_all_orders();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Green Leaf</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col md:flex-row">

    <?php include 'admin_sidebar.php'; ?>

    <main class="flex-1 p-4 md:p-8">
        <?php include 'admin_top_navbar.php'; ?>

        <div class="container mx-auto mt-4">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6">Manage Orders</h1>

            <?php if ($message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($message); ?></span>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-4 sm:p-6 border-b border-gray-200">
                    <h2 class="text-lg sm:text-xl font-semibold text-gray-800">All Orders</h2>
                </div>
                <div class="p-4 sm:p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                    <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                    <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (!empty($orders)): ?>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($order['order_id']); ?></td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($order['username']); ?></td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500">$<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php
                                                    switch ($order['status']) {
                                                        case 'Pending':
                                                            echo 'bg-yellow-100 text-yellow-800';
                                                            break;
                                                        case 'Processing':
                                                            echo 'bg-blue-100 text-blue-800';
                                                            break;
                                                        case 'Shipped':
                                                            echo 'bg-indigo-100 text-indigo-800';
                                                            break;
                                                        case 'Completed':
                                                            echo 'bg-green-100 text-green-800';
                                                            break;
                                                        case 'Cancelled':
                                                            echo 'bg-red-100 text-red-800';
                                                            break;
                                                        default:
                                                            echo 'bg-gray-100 text-gray-800';
                                                            break;
                                                        }
                                                    ?>">
                                                    <?php echo htmlspecialchars($order['status']); ?>
                                                </span>
                                            </td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars(date('M d, Y', strtotime($order['created_at']))); ?></td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="view_order.php?id=<?php echo htmlspecialchars($order['order_id']); ?>" class="text-indigo-600 hover:text-indigo-900 mr-2 sm:mr-4">View Details</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="px-3 sm:px-6 py-4 text-center text-sm text-gray-500">No orders found.</td>
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