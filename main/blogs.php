<?php
// Start the session to manage user login status
session_start();

// Include the database connection and functions files
include 'includes/db_connection.php';
include 'includes/functions.php';

// Fetch all blog posts from the database
$all_blogs = get_all_blogs();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gardening Tips & Blogs - Green Leaf</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex flex-col min-h-screen bg-gray-50 text-gray-900">

  <!-- Navbar -->
  <header>
    <?php include 'navbar.php'; ?>
  </header>

  <!-- Blog List -->
  <main class="container mx-auto flex-grow px-4 py-10">
    <h1 class="text-3xl font-bold text-center mb-3">Gardening Tips & Blogs</h1>
    <p class="text-lg text-gray-600 text-center mb-10">
      Read expert articles and seasonal guides to help your plants thrive.
    </p>

   <div class="grid gap-6 sm:grid-cols-2">
  <?php
  if (!empty($all_blogs)) {
    foreach ($all_blogs as $blog) {
      echo '<div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition p-6">';
      echo '  <h2 class="text-lg font-semibold mb-2">' . htmlspecialchars($blog['title']) . '</h2>';
      echo '  <p class="text-gray-500 text-sm mb-4">' . htmlspecialchars(substr($blog['content'], 0, 100)) . '...</p>';
      echo '  <a href="blog.php?id=' . htmlspecialchars($blog['blog_id']) . '" 
                 class="text-green-600 font-medium hover:underline inline-flex items-center">
                 Read More <span class="ml-1">→</span>
               </a>';
      echo '</div>';
    }
  } else {
    echo '<p class="text-center col-span-2 text-gray-600">No blog articles found yet.</p>';
  }
  ?>
</div>

  </main>

  <!-- Footer -->
  <?php include 'footer.php'; ?>

</body>
</html>
