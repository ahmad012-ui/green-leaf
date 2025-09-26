<?php
// Start the session to use session variables
session_start();

// Include the database connection and functions files
include 'includes/db_connection.php';
include 'includes/functions.php';

// Handle search and filter logic
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Fetch plants based on search and filter
$plants = get_plants($search_query, $category);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Plant Catalog - Green Leaf</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="bg-gray-50 text-gray-800 flex flex-col min-h-screen">
  <!-- Navbar -->
  <header>
    <?php include 'navbar.php'; ?>
  </header>

  <!-- Main Content -->
  <main class="flex-grow container mx-auto px-4 py-12">
    <h1 class="text-3xl font-extrabold text-green-700 mb-8 text-center">Our Plant Catalog</h1>

    <!-- Search & Filter -->
    <form action="catalog.php" method="GET" class="mb-8 flex flex-col md:flex-row gap-4 items-center justify-center">
      <input type="text" name="search" placeholder="Search plants by name" 
             value="<?php echo htmlspecialchars($search_query); ?>" 
             class="border border-gray-300 rounded-lg px-4 py-2 w-full md:w-1/2 focus:ring-2 focus:ring-green-500 focus:outline-none">

      <select name="category" 
              class="border border-gray-300 rounded-lg px-4 py-2 w-full md:w-1/4 focus:ring-2 focus:ring-green-500 focus:outline-none">
        <option value="">Filter by Category</option>
        <option value="Indoor" <?php if ($category == 'Indoor') echo 'selected'; ?>>Indoor</option>
        <option value="Outdoor" <?php if ($category == 'Outdoor') echo 'selected'; ?>>Outdoor</option>
        <option value="Flowering" <?php if ($category == 'Flowering') echo 'selected'; ?>>Flowering</option>
        <option value="Air-Purifying" <?php if ($category == 'Air-Purifying') echo 'selected'; ?>>Air-Purifying</option>
        <option value="Seasonal" <?php if ($category == 'Seasonal') echo 'selected'; ?>>Seasonal</option>
      </select>

      <button type="submit" 
              class="bg-green-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-green-700 transition w-full md:w-auto">
        Apply Filters
      </button>
    </form>

    <!-- Plant Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
      <?php if (!empty($plants)): ?>
        <?php foreach ($plants as $plant): ?>
          <div class="bg-white rounded-2xl shadow-md overflow-hidden flex flex-col">
            <img src="images/<?php echo htmlspecialchars($plant['image']); ?>" 
                 alt="<?php echo htmlspecialchars($plant['name']); ?>" 
                 class="w-full h-48 object-cover">

            <div class="p-4 flex flex-col flex-grow">
              <h3 class="text-lg font-semibold mb-2"><?php echo htmlspecialchars($plant['name']); ?></h3>
              <p class="text-green-600 font-bold text-xl mb-4">$<?php echo htmlspecialchars($plant['price']); ?></p>

              <a href="plant_details.php?id=<?php echo htmlspecialchars($plant['plant_id']); ?>" 
                 class="mt-auto bg-green-600 text-white py-2 px-4 rounded-lg text-center font-semibold hover:bg-green-700 transition">
                View Details
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="col-span-full text-center text-gray-500">No plants found matching your criteria.</p>
      <?php endif; ?>
    </div>
  </main>

  <!-- Footer -->
  <footer class="">
    <?php include 'footer.php'; ?>
  </footer>
</body>
</html>
