<?php
session_start();
include 'includes/db_connection.php';
include 'includes/functions.php';

$featured_plants = get_featured_plants();
$latest_blogs = get_latest_blogs();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Green Leaf - Care Made Simple</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">

  <!-- Navbar -->
  <header>
    <?php include 'navbar.php'; ?>
  </header>

  <!-- Hero Section -->
  <section class="bg-gradient-to-b from-white to-green-50 py-20">
    <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-2 items-center gap-12">
      
      <!-- Hero Text -->
      <div>
        <h1 class="text-4xl md:text-6xl font-extrabold leading-tight">
          Bring Home <span class="text-green-600">Green</span> —<br>
          Care Made Simple
        </h1>
        <p class="mt-6 text-lg text-gray-600">
          Shop premium indoor plants and get personalized reminders 
          to keep them thriving.
        </p>
        <div class="mt-8 flex flex-wrap gap-4">
          <a href="catalog.php" 
             class="bg-green-600 text-white px-6 py-3 rounded-lg shadow hover:bg-green-700 transition">
            🌱 Shop Plants
          </a>
          <a href="blogs.php" 
             class="border border-green-600 text-green-700 px-6 py-3 rounded-lg hover:bg-green-50 transition">
            How reminders work
          </a>
        </div>
      </div>

      <!-- Hero Image -->
      <div class="flex justify-center md:justify-end">
        <div class="w-full md:w-[500px] lg:w-[600px] h-80 md:h-[400px] lg:h-[450px] rounded-3xl flex items-center justify-center overflow-hidden shadow-lg">
          <img src="images/english-ivy.jpg" alt="Hero plant" class="w-full h-full object-cover">
        </div>
      </div>

    </div>
  </section>

  <!-- Featured Plants -->
  <section class="py-20">
    <div class="max-w-7xl mx-auto px-6">
      <h2 class="text-3xl font-bold text-center mb-12">Featured Plants</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
        <?php if (!empty($featured_plants)): ?>
          <?php foreach ($featured_plants as $plant): ?>
            <div class="bg-white rounded-2xl shadow hover:shadow-lg transition overflow-hidden">
              <img src="images/<?php echo htmlspecialchars($plant['image']); ?>" 
                   alt="<?php echo htmlspecialchars($plant['name']); ?>" 
                   class="w-full h-48 object-cover">
              <div class="p-5">
                <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($plant['name']); ?></h3>
                <p class="text-green-600 font-bold mt-2">$<?php echo htmlspecialchars($plant['price']); ?></p>
                <a href="plant_details.php?id=<?php echo htmlspecialchars($plant['plant_id']); ?>" 
                   class="mt-4 inline-block w-full text-center bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition">
                  View Details
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-center col-span-4">No featured plants found.</p>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- Gardening Tips -->
  <section class="py-20 bg-gray-100">
    <div class="max-w-7xl mx-auto px-6">
      <h2 class="text-3xl font-bold text-center mb-12">Latest Gardening Tips</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <?php if (!empty($latest_blogs)): ?>
          <?php foreach ($latest_blogs as $blog): ?>
            <div class="bg-white rounded-2xl shadow hover:shadow-lg transition p-6">
              <h3 class="text-xl font-semibold"><?php echo htmlspecialchars($blog['title']); ?></h3>
              <p class="text-gray-600 mt-3">
                <?php echo htmlspecialchars(substr($blog['content'], 0, 150)); ?>...
              </p>
              <a href="blog.php?id=<?php echo htmlspecialchars($blog['blog_id']); ?>" 
                 class="mt-4 inline-block text-green-600 font-semibold hover:underline">
                Read More →
              </a>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-center col-span-2">No blog articles found.</p>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    <?php include 'footer.php'; ?>
  </footer>

</body>
</html>
