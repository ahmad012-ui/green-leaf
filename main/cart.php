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

// Fetch the user's cart items from the database
$user_id = $_SESSION['user_id'];
$cart_items = get_cart_items($user_id);

$total_price = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your Shopping Cart - Green Leaf</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">

  <!-- Navbar -->
  <header>
    <?php include 'navbar.php'; ?>
  </header>

  <!-- Main -->
  <main class="max-w-6xl mx-auto my-10 px-4">
    <!-- Title + My Orders link -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold">Your Shopping Cart</h1>
      <a href="my_orders.php" 
         class="px-4 py-2 rounded-lg text-green-600 hover:bg-green-600 hover:text-white transition text-sm font-medium">
        My Orders
      </a>
    </div>

    <?php if (!empty($cart_items)): ?>
      <div class="overflow-x-auto bg-white shadow rounded-lg">
        <table class="w-full text-left border-collapse">
          <thead class="bg-green-600 text-white">
            <tr>
              <th class="p-3">Image</th>
              <th class="p-3">Product</th>
              <th class="p-3">Price</th>
              <th class="p-3">Quantity</th>
              <th class="p-3">Subtotal</th>
              <th class="p-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($cart_items as $item): ?>
              <tr class="border-b hover:bg-gray-50">
                <td class="p-3">
                  <img src="images/<?php echo htmlspecialchars($item['image']); ?>" 
                       alt="<?php echo htmlspecialchars($item['name']); ?>" 
                       class="w-16 h-16 object-cover rounded">
                </td>
                <td class="p-3"><?php echo htmlspecialchars($item['name']); ?></td>
                <td class="p-3">$<?php echo htmlspecialchars($item['price']); ?></td>
                <td class="p-3">
                  <form action="update_cart.php" method="post" class="flex items-center gap-2">
                    <input type="hidden" name="cart_id" value="<?php echo htmlspecialchars($item['cart_id']); ?>">
                    <input type="number" 
                           name="quantity" 
                           value="<?php echo htmlspecialchars($item['quantity']); ?>" 
                           min="1" 
                           class="w-16 border rounded p-1 text-center">
                    <button type="submit" 
                            class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                      Update
                    </button>
                  </form>
                </td>
                <td class="p-3">
                  $<?php
                    $subtotal = $item['price'] * $item['quantity'];
                    $total_price += $subtotal;
                    echo number_format($subtotal, 2);
                  ?>
                </td>
                <td class="p-3">
                  <a href="remove_from_cart.php?cart_id=<?php echo htmlspecialchars($item['cart_id']); ?>" 
                     class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                    Remove
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot class="bg-gray-100">
            <tr>
              <td colspan="4" class="p-3 text-right font-semibold">Total:</td>
              <td class="p-3 font-bold">$<?php echo number_format($total_price, 2); ?></td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- Checkout button -->
      <div class="flex justify-end mt-6">
        <a href="checkout.php" 
           class="px-6 py-3 bg-green-600 text-white rounded-lg shadow hover:bg-green-700 transition">
          Proceed to Checkout
        </a>
      </div>

    <?php else: ?>
      <div class="text-center bg-white shadow rounded-lg p-6">
        <p class="text-gray-600">Your cart is empty.</p>
        <a href="catalog.php" class="text-green-600 hover:underline font-medium">Browse our plants</a>
      </div>
    <?php endif; ?>
  </main>

  <!-- Footer -->
    <footer >
      <?php include 'footer.php'; ?>
    </footer>

</body>
</html>
