<?php
session_start();
date_default_timezone_set("Asia/Karachi");
include 'includes/db_connection.php';
include 'includes/functions.php';
include 'includes/csrf.php';    

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function timeAgo($time) {
    $timestamp = strtotime($time);
    $diff = time() - $timestamp;

    if ($diff < 0) {
        $diff = abs($diff); // fix negative values
    }

    if ($diff < 60) {
        return $diff . "s";
    } elseif ($diff < 3600) {
        return floor($diff / 60) . "m";
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . "h";
    } else {
        return floor($diff / 86400) . "d";
    }
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: catalog.php");
    exit();
}

$plant_id = $_GET['id'];
$plant = get_plant_by_id($plant_id);

if (!$plant) {
    header("Location: catalog.php");
    exit();
}

// Handle new comment submission
$comment_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    if (!isset($_SESSION['user_id'])) {
        $comment_error = "You must be logged in to comment.";
    } else {
        $comment_text = trim($_POST['comment']);
        if (!empty($comment_text)) {
            add_plant_comment($_SESSION['user_id'], $plant_id, $comment_text);
        } else {
            $comment_error = "Comment cannot be empty.";
        }
    }
}

// Handle Like toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_like'])) {
    if (isset($_SESSION['user_id'])) {
        toggle_plant_like($_SESSION['user_id'], $plant_id);
        // Update likes immediately for display
        $user_liked = user_liked_plant($_SESSION['user_id'], $plant_id);
        $total_likes = count_plant_likes($plant_id);
    }
}

// Fetch comments
$comments = get_plant_comments($plant_id);

// Likes
$user_liked = isset($_SESSION['user_id']) ? user_liked_plant($_SESSION['user_id'], $plant_id) : false;
$total_likes = count_plant_likes($plant_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($plant['name']) ?> - Green Leaf</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="bg-gray-50">

<header>
<?php include 'navbar.php'; ?>
</header>

<main class="max-w-6xl mx-auto my-8 px-4">

<div class="flex flex-col md:flex-row gap-8 bg-white shadow-md rounded-lg p-6">
    <!-- Plant Image -->
    <div class="md:w-1/2">
        <img src="images/<?= htmlspecialchars($plant['image']) ?>" alt="<?= htmlspecialchars($plant['name']) ?>" class="rounded-lg w-full h-80 object-cover">
    </div>

    <!-- Plant Info -->
<div class="md:w-1/2 flex flex-col justify-between">
    <div>
        <h1 class="text-3xl font-bold mb-2"><?= htmlspecialchars($plant['name']) ?></h1>
        <p class="text-gray-500 uppercase tracking-wide mb-2"><?= htmlspecialchars($plant['category_name']) ?></p>
        <p class="text-green-600 text-2xl font-semibold mb-4">$<?= htmlspecialchars($plant['price']) ?></p>

        <hr class="my-4">

        <h2 class="font-semibold text-lg mb-1">Description</h2>
        <p class="text-gray-700 mb-4"><?= nl2br(htmlspecialchars($plant['description'])) ?></p>

        <h2 class="font-semibold text-lg mb-1">Care Requirements</h2>
        <ul class="list-disc ml-5 text-gray-700 mb-4">
            <li>Care Level: <?= htmlspecialchars($plant['care_level']) ?></li>
            <li>Light: <?= htmlspecialchars($plant['light_requirement']) ?></li>
            <li>Watering: <?= htmlspecialchars($plant['watering_schedule']) ?></li>
        </ul>
    </div>

   <!-- Button Group -->
<div class="mt-6 grid grid-cols-4 gap-3 ">
    <!-- Like Button -->
    <form method="POST">
        <button type="submit" name="toggle_like" class="w-full px-4 py-2 bg-green-600 text-white font-semibold rounded hover:bg-green-700 transition">
            <?= $user_liked ? '❤️ ' : '🤍 ' ?> <?= $total_likes ?>
        </button>
    </form>

    <!-- Add to Cart -->
    <form action="add_to_cart.php" method="POST">
        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="id" value="<?= $plant_id ?>">
        <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white font-semibold rounded hover:bg-green-700 transition">🛒 Add to Cart</button>
    </form>

    <!-- Add to My Garden -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <form action="my_garden.php" method="GET">
        <input type="hidden" name="add_plant" value="<?= $plant_id ?>">
        <button type="submit" class="w-full px-4 py-2 bg-purple-600 text-white font-semibold rounded hover:bg-purple-700 transition">🌱 Add to My Garden</button>
    </form>
    <?php endif; ?>

    <!-- Share Button -->
    <button id="shareBtn" class="w-full px-4 py-2 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700 transition">🔗 Share</button>
</div>
</div>
</div>

<!-- Comments Section -->
<section class="mt-10">
<h2 class="text-2xl font-semibold mb-4">Comments</h2>

<?php if ($comment_error): ?>
<p class="text-red-500 mb-2"><?= htmlspecialchars($comment_error) ?></p>
<?php endif; ?>

<!-- Comment Form -->
<?php if (isset($_SESSION['user_id'])): ?>
<form action="plant_details.php?id=<?= $plant_id ?>" method="POST" class="flex gap-2 mb-6">
    <input type="text" name="comment" placeholder="Write a comment..." class="flex-1 p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-400">
    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Publish</button>
</form>
<?php else: ?>
<p class="text-gray-500 mb-4">Log in to comment.</p>
<?php endif; ?>

<!-- Display Comments -->
<div class="space-y-4">
<?php foreach ($comments as $c): ?>
<div class="flex items-start gap-3 bg-gray-100 p-3 rounded">
    <?php if (!empty($c['profile_image'])): ?>
    <img src="<?= htmlspecialchars($c['profile_image']) ?>" alt="User Image" class="w-10 h-10 rounded-full object-cover">
    <?php else: ?>
    <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center text-gray-600">👤</div>
    <?php endif; ?>
    <div>
        <p class="font-semibold"><?= htmlspecialchars($c['username']) ?></p>
        <p class="text-gray-700"><?= nl2br(htmlspecialchars($c['comment'])) ?></p>
        <p class="text-gray-400 text-sm"><?= timeAgo($c['created_at']) ?></p>
    </div>
</div>
<?php endforeach; ?>
</div>

</section>
</main>

<?php include 'footer.php'; ?>

<script>
// Simple share button
document.getElementById('shareBtn').addEventListener('click', () => {
    const url = window.location.href;
    navigator.clipboard.writeText(url);
    alert('Link copied to clipboard!');
});
</script>

</body>
</html>
