<!-- Mobile Header with Toggle -->
<div class="md:hidden flex items-center justify-between bg-gray-900 text-white p-4">
  <div class="flex items-center space-x-2">
    <i class="fa-solid fa-leaf text-green-400 text-xl"></i>
    <span class="text-xl font-bold">Green Leaf</span>
  </div>
  <button id="sidebarToggle" class="text-white focus:outline-none">
    <i class="fa-solid fa-bars text-2xl"></i>
  </button>
</div>

<!-- Sidebar -->
<div id="sidebar"
     class="fixed md:static top-0 left-0 w-64 bg-gray-800 text-white min-h-screen p-4 shadow-xl transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out z-50">

  <!-- Logo -->
  <div class="flex items-center space-x-2 mb-8">
    <i class="fa-solid fa-leaf text-green-400 text-2xl"></i>
    <span class="text-2xl font-bold">Green Leaf</span>
  </div>

  <!-- Nav -->
  <nav>
    <ul class="space-y-2">
       <li><a href="index.php" class="flex items-center space-x-2 p-3 rounded-lg hover:bg-green-700 transition-colors"><i class="fa-solid fa-chart-line"></i><span>Dashboard</span></a></li>
       <li><a href="manage_plants.php" class="flex items-center space-x-2 p-3 rounded-lg hover:bg-green-700 transition-colors"><i class="fa-solid fa-seedling"></i><span>Plant Management</span></a></li>
       <li><a href="manage_categories.php" class="flex items-center space-x-2 p-3 rounded-lg hover:bg-green-700 transition-colors"><i class="fa-solid fa-list-ul"></i><span>Category Management</span></a></li>
       <li><a href="manage_blogs.php" class="flex items-center space-x-2 p-3 rounded-lg hover:bg-green-700 transition-colors"><i class="fa-solid fa-blog"></i><span>Blog Management</span></a></li>
       <li><a href="manage_orders.php" class="flex items-center space-x-2 p-3 rounded-lg hover:bg-green-700 transition-colors"><i class="fa-solid fa-box-open"></i><span>Order Management</span></a></li>
       <li><a href="manage_reviews.php" class="flex items-center space-x-2 p-3 rounded-lg hover:bg-green-700 transition-colors"><i class="fa-solid fa-star"></i><span>Review Moderation</span></a></li>
       <li><a href="manage_users.php" class="flex items-center space-x-2 p-3 rounded-lg hover:bg-green-700 transition-colors"><i class="fa-solid fa-users"></i><span>User Management</span></a></li>
       <li><a href="../logout.php" class="flex items-center space-x-2 p-3 rounded-lg hover:bg-green-700 transition-colors"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></a></li>
    </ul>
  </nav>
</div>

<!-- JS Toggle -->
<script>
  const toggleBtn = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');

  toggleBtn.addEventListener('click', () => {
    // Slide sidebar in/out on mobile
    sidebar.classList.toggle('-translate-x-full');
  });
</script>
