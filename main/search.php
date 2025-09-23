<?php
// search.php
session_start();
include 'includes/db_connection.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$plants = [];
$blogs = [];

if ($q !== '') {
     // Remove words like 'plant', 'plants', 'tree', etc.
    $searchTerm = preg_replace('/\b(plants?|tree|trees|flower|flowers)\b/i', '', $q);
    $searchTerm = trim($searchTerm);

    $like = "%$searchTerm%";

    // 🔍 Search Plants (name, botanical_name, description, category)
    $stmt = $conn->prepare("
      SELECT p.plant_id, p.name, p.price, p.image, p.description, c.category_name
      FROM plants p
      LEFT JOIN categories c ON p.category_id = c.category_id
      WHERE p.name LIKE ? 
        OR p.botanical_name LIKE ? 
        OR p.description LIKE ? 
        OR c.category_name LIKE ?
      ORDER BY p.name ASC");
   $stmt->bind_param("ssss", $like, $like, $like, $like);
   $stmt->execute();
   $plants = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
   $stmt->close();

  // Add "type" => "plant"
  foreach ($plants as &$plant) {
    $plant['type'] = 'plant';
    $plant['id']   = $plant['plant_id'];
  }
  unset($plant);

    // 🔍 Search Blogs (title, content)
    $stmt = $conn->prepare("
        SELECT blog_id AS id, title, image, content, 'blog' AS type
        FROM blogs 
        WHERE title LIKE ? OR content LIKE ?
        ORDER BY title ASC");
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $blogs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Merge results
$results = array_merge($plants, $blogs);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Search Results - Green Leaf</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <style> html, body { height: 100%; margin: 0; padding: 0; } body { display: flex; flex-direction: column; } main { flex: 1 0 auto; } </style>
</head>
<body class="bg-gray-50">
<?php include 'navbar.php'; ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <h1 class="text-2xl font-bold mb-6">
    Search Results for: <span class="text-green-700"><?php echo htmlspecialchars($q); ?></span>
  </h1>

  <?php if ($q === ''): ?>
    <p class="text-gray-600">Please enter a keyword to search.</p>
  <?php elseif (empty($results)): ?>
    <p class="text-gray-600">No results found.</p>
  <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <?php foreach ($results as $item): ?>
        <div class="bg-white shadow rounded-lg overflow-hidden">
          <?php if ($item['type'] === 'plant'): ?>
            <img src="images/<?php echo htmlspecialchars($item['image']); ?>" 
                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                 class="w-full h-40 object-cover">
            <div class="p-4">
              <h2 class="text-lg font-semibold text-gray-800">
                <?php echo htmlspecialchars($item['name']); ?>
              </h2>
              <p class="text-sm text-gray-500 mb-2 line-clamp-2">
                <?php echo htmlspecialchars($item['description']); ?>
              </p>
              <p class="text-green-700 font-bold mb-3">
                $<?php echo number_format($item['price'], 2); ?>
              </p>
              <a href="plant_details.php?id=<?php echo $item['id']; ?>" 
                 class="block w-full text-center bg-green-600 text-white py-2 rounded hover:bg-green-700">
                View Plant
              </a>
            </div>
          <?php elseif ($item['type'] === 'blog'): ?>
            <img src="images/<?php echo htmlspecialchars($item['image']); ?>" 
                 alt="<?php echo htmlspecialchars($item['title']); ?>" 
                 class="w-full h-40 object-cover">
            <div class="p-4">
              <h2 class="text-lg font-semibold text-gray-800">
                <?php echo htmlspecialchars($item['title']); ?>
              </h2>
              <p class="text-sm text-gray-500 mb-2 line-clamp-2">
                <?php echo htmlspecialchars(substr($item['content'], 0, 100)); ?>...
              </p>
              <a href="blog_details.php?id=<?php echo $item['id']; ?>" 
                 class="block w-full text-center bg-green-600 text-white py-2 rounded hover:bg-green-700">
                Read Blog
              </a>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<?php include 'footer.php'; ?>
</body>
</html>
