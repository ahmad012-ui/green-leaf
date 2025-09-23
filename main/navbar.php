<?php
// navbar.php - Tailwind navbar with Cart slide-over and AJAX updates
if (session_status() == PHP_SESSION_NONE) session_start();

// PHP cart logic
include_once 'includes/functions.php';

if (isset($_SESSION['user_id'])) {
    $cart_items = get_cart_items($_SESSION['user_id']);
    $cart_count = count($cart_items);
} else {
    $cart_items = [];
    $cart_count = 0;
}

$cart_subtotal = 0;
foreach ($cart_items as $ci) {
    $cart_subtotal += ($ci['price'] * $ci['quantity']);
}
?>

<header class="bg-white shadow">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center py-4">
      <!-- Logo -->
      <div class="flex items-center gap-3">
        <a href="index.php" class="flex items-center gap-3">
          <span class="flex items-center space-x-2 text-3xl font-extrabold tracking-tight">
            <span class="text-green-700 drop-shadow-md">🌿</span>
            <span class="text-green-900">Green</span>
            <span class="text-lime-600 italic">Leaf</span>
          </span>
        </a>
      </div>

      <!-- Desktop nav -->
      <nav class="hidden md:flex items-center gap-6">
        <a href="catalog.php" class="text-gray-700 hover:text-green-700">Plant Catalog</a>
        <a href="blogs.php" class="text-gray-700 hover:text-green-700">Gardening Tips</a>
        <a href="about.php" class="text-gray-700 hover:text-green-700">About Us</a>
        <a href="contact.php" class="text-gray-700 hover:text-green-700">Contact</a>
      </nav>

      <!-- Desktop search bar -->
      <form action="search.php" method="GET" class="hidden md:flex items-center w-64 mx-4">
        <input type="text" name="q" placeholder="Search..."
          class="flex-1 border border-gray-300 rounded-l-md px-2 py-1 text-sm 
                 focus:outline-none focus:ring-1 focus:ring-green-600 focus:border-green-600">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <button type="submit" class="px-3 py-1 bg-green-600 text-white text-sm rounded-r-md hover:bg-green-700 flex items-center justify-center">
          <span class="material-icons text-sm">search</span>
        </button>
      </form>

      <!-- Right side -->
      <div class="flex items-center gap-4">
        <!-- Cart trigger -->
        <a href="cart.php" class="flex items-center gap-2 text-gray-700 hover:text-green-700">
          <span class="material-icons text-2xl">shopping_cart</span>
        <?php if ($cart_count > 0): ?>
          <span class="bg-red-600 text-white text-xs rounded-full px-2 py-0.5">
            <?php echo $cart_count; ?>
          </span>
        <?php endif; ?>
        </a>

        <!-- User links -->
        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="my_garden.php" class="hidden sm:inline text-gray-700 hover:text-green-700">My Garden</a>
          <a href="logout.php" class="text-gray-700 hover:text-green-700">Logout</a>
        <?php else: ?>
          <a href="login.php" class="text-gray-700 hover:text-green-700">Login</a>
          <a href="register.php" class="text-gray-700 hover:text-green-700">Register</a>
        <?php endif; ?>

        <!-- Mobile menu toggle -->
        <button id="menu-toggle" class="md:hidden text-gray-700 focus:outline-none ml-2">☰</button>
      </div>
    </div>
  </div>

  <!-- Mobile nav -->
  <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-200">
    <nav class="flex flex-col p-4 space-y-2">
      <a href="catalog.php" class="text-gray-700 hover:text-green-700">Plant Catalog</a>
      <a href="blogs.php" class="text-gray-700 hover:text-green-700">Gardening Tips</a>
      <a href="about.php" class="text-gray-700 hover:text-green-700">About Us</a>
      <a href="contact.php" class="text-gray-700 hover:text-green-700">Contact</a>

      <a href="cart.php" class="flex items-center gap-2 text-gray-700 hover:text-green-700">
        Cart
        <?php if ($cart_count > 0): ?>
          <span class="bg-red-600 text-white text-xs rounded-full px-2 py-0.5">
            <?php echo $cart_count; ?>
          </span>
        <?php endif; ?>
      </a>

      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="my_garden.php" class="text-gray-700 hover:text-green-700">My Garden</a>
        <a href="logout.php" class="text-gray-700 hover:text-green-700">Logout</a>
      <?php else: ?>
        <a href="login.php" class="text-gray-700 hover:text-green-700">Login</a>
        <a href="register.php" class="text-gray-700 hover:text-green-700">Register</a>
      <?php endif; ?>

      <!-- Mobile search bar -->
      <form action="search.php" method="GET" class="flex mt-2">
        <input type="text" name="q" placeholder="Search..." class="flex-1 border border-gray-300 rounded-l-md px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-green-600 focus:border-green-600">
        <button type="submit" class="px-3 bg-green-600 text-white text-sm rounded-r-md hover:bg-green-700 flex items-center justify-center">
          <span class="material-icons text-base">search</span>
        </button>
      </form>
    </nav>
  </div>
</header>

<!-- Toggle Script -->
<script>
  document.getElementById('menu-toggle').addEventListener('click', function () {
    const menu = document.getElementById('mobile-menu');
    menu.classList.toggle('hidden');
  });
</script>

