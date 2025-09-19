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

      <!-- Right side -->
      <div class="flex items-center gap-4">
        <!-- Cart trigger -->
        <button id="cart-open" aria-controls="cart-drawer" aria-expanded="false"
                class="relative inline-flex items-center gap-2 text-gray-700 hover:text-green-700 focus:outline-none">
          <span class="text-2xl">🛒</span>
          <?php if ($cart_count > 0): ?>
            <span id="cart-badge" class="absolute -top-2 -right-3 bg-red-600 text-white text-xs rounded-full px-2 py-0.5">
              <?php echo $cart_count; ?>
            </span>
          <?php endif; ?>
        </button>

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
          <span class="bg-red-600 text-white text-xs rounded-full px-2 py-0.5"><?php echo $cart_count; ?></span>
        <?php endif; ?>
      </a>

      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="my_garden.php" class="text-gray-700 hover:text-green-700">My Garden</a>
        <a href="logout.php" class="text-gray-700 hover:text-green-700">Logout</a>
      <?php else: ?>
        <a href="login.php" class="text-gray-700 hover:text-green-700">Login</a>
        <a href="register.php" class="text-gray-700 hover:text-green-700">Register</a>
      <?php endif; ?>
    </nav>
  </div>

  <!-- Cart Overlay + Drawer -->
  <div id="cart-overlay" class="fixed inset-0 bg-black bg-opacity-40 hidden z-40"></div>

  <aside id="cart-drawer" class="fixed right-0 top-0 h-full w-full sm:w-96 transform translate-x-full transition-transform duration-300 bg-white shadow-lg z-50" aria-hidden="true">
    <div class="flex flex-col h-full">
      <div class="flex items-center justify-between p-4 border-b">
        <h3 class="text-lg font-semibold">Your Cart</h3>
        <button id="cart-close" class="text-gray-600 hover:text-gray-800">✕</button>
      </div>

      <div class="p-4 overflow-y-auto flex-1">
        <?php if (empty($cart_items)): ?>
          <div class="text-center text-gray-500 mt-8">Your cart is empty.</div>
        <?php else: ?>
          <ul class="space-y-4">
            <?php foreach ($cart_items as $item): ?>
              <li class="flex items-center gap-3">
                <img src="images/<?php echo htmlspecialchars($item['image']); ?>"
                     alt="<?php echo htmlspecialchars($item['name']); ?>"
                     class="w-16 h-16 object-cover rounded-md">
                <div class="flex-1">
                  <div class="font-medium text-gray-800"><?php echo htmlspecialchars($item['name']); ?></div>
                  <div class="text-sm text-gray-500 flex items-center gap-2 mt-1">
                    <button data-action="decrement" data-cart-id="<?php echo $item['cart_id']; ?>" class="px-2 py-1 bg-gray-200 hover:bg-gray-300 rounded">-</button>
                    <input type="number" readonly id="qty-<?php echo $item['cart_id']; ?>" value="<?php echo (int) $item['quantity']; ?>" class="w-12 text-center border rounded bg-gray-50">
                    <button data-action="increment" data-cart-id="<?php echo $item['cart_id']; ?>" class="px-2 py-1 bg-gray-200 hover:bg-gray-300 rounded">+</button>
                  </div>
                  <div class="text-xs text-gray-500">$<?php echo number_format($item['price'], 2); ?> each</div>
                </div>
                <div id="line-<?php echo $item['cart_id']; ?>" class="font-semibold text-gray-800">
                  $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>

      <div class="p-4 border-t">
        <div class="flex items-center justify-between mb-4">
          <div class="text-sm text-gray-500">Subtotal</div>
          <div id="cart-subtotal" class="text-lg font-bold text-gray-800">$<?php echo number_format($cart_subtotal, 2); ?></div>
        </div>
        <div class="space-y-2">
          <a href="cart.php" class="block text-center w-full py-2 border rounded text-gray-700 hover:bg-gray-50">View Cart</a>
          <a href="checkout.php" class="block text-center w-full py-2 bg-green-600 text-white rounded hover:bg-green-700">Proceed to Checkout</a>
        </div>
      </div>
    </div>
  </aside>
</header>

<script>
document.addEventListener("DOMContentLoaded", () => {
  // Mobile menu toggle
  const menuToggle = document.getElementById('menu-toggle');
  const mobileMenu = document.getElementById('mobile-menu');
  if (menuToggle) {
    menuToggle.addEventListener('click', () => mobileMenu.classList.toggle('hidden'));
  }

  // Cart drawer
  const cartOpen = document.getElementById('cart-open');
  const cartClose = document.getElementById('cart-close');
  const cartDrawer = document.getElementById('cart-drawer');
  const cartOverlay = document.getElementById('cart-overlay');

  function openCart() {
    cartDrawer.classList.remove('translate-x-full');
    cartDrawer.classList.add('translate-x-0');
    cartDrawer.setAttribute('aria-hidden', 'false');
    cartOverlay.classList.remove('hidden');
  }
  function closeCart() {
    cartDrawer.classList.remove('translate-x-0');
    cartDrawer.classList.add('translate-x-full');
    cartDrawer.setAttribute('aria-hidden', 'true');
    cartOverlay.classList.add('hidden');
  }

  cartOpen && cartOpen.addEventListener('click', openCart);
  cartClose && cartClose.addEventListener('click', closeCart);
  cartOverlay && cartOverlay.addEventListener('click', closeCart);
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeCart(); });

  // AJAX quantity update
  document.querySelectorAll("[data-action]").forEach(btn => {
    btn.addEventListener("click", () => {
      const cartId = btn.dataset.cartId;
      const action = btn.dataset.action;

      fetch("update_cart.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `cart_id=${cartId}&${action}=1`
      })
     .then(res => res.json())
     .then(data => {
       if (data.success) {
         document.getElementById(`qty-${cartId}`).value = data.quantity;
         document.getElementById(`line-${cartId}`).innerText = `$${data.line_total}`;
         document.getElementById("cart-subtotal").innerText = `$${data.cart_total}`;
         const badge = document.getElementById("cart-badge");
         if (badge) badge.innerText = data.cart_count;
       }
     });
    });
  });
});
</script>
