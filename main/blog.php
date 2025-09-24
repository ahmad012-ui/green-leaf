<?php
// Start the session to manage user login status
session_start();
// Set default timezone
date_default_timezone_set("Asia/Karachi");

// Include the database connection and functions files
include 'includes/db_connection.php';
include 'includes/functions.php';
include 'includes/csrf.php';

/**
 * Convert timestamp into "time ago" format
 * Examples: 10s, 5m, 2h, 3d
 */
function timeAgo($time) {
    $timestamp = strtotime($time);
    $diff = time() - $timestamp;

    if ($diff < 0) {
        $diff = abs($diff); // Fix negative values (future timestamps)
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

// -------------------- Get Blog --------------------

// Get blog ID from URL (?id=...)
$blog_id = intval($_GET['id'] ?? 0);
if ($blog_id <= 0) {
    // Redirect if invalid blog ID
    header("Location: blogs.php");
    exit;
}

// Fetch blog data from database
$blog = get_blog_by_id($blog_id);
if (!$blog) {
    // Redirect if blog not found
    header("Location: blogs.php");
    exit;
}

// Get current logged-in user ID (if any)
$user_id = $_SESSION['user_id'] ?? null;

// -------------------- Likes --------------------

// Total likes for this blog
$total_likes = 0;
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM blog_likes WHERE blog_id = ?");
$stmt->bind_param("i", $blog_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $row = $res->fetch_assoc()) {
    $total_likes = (int)$row['total'];
}
$stmt->close();

// Check if current user already liked this blog
$user_liked = false;
if ($user_id) {
    $stmt = $conn->prepare("SELECT 1 FROM blog_likes WHERE user_id = ? AND blog_id = ? LIMIT 1");
    $stmt->bind_param("ii", $user_id, $blog_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) $user_liked = true;
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($blog['title']); ?> - Green Leaf Blog</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="flex flex-col min-h-screen bg-gray-50 text-gray-800">

    <!-- Navbar -->
    <header>
        <?php include 'navbar.php'; ?>
    </header>

    <!-- Blog Content -->
    <main class="container mx-auto flex-grow px-4 py-8">
        <article class="bg-white rounded-lg shadow-md p-6">
            
            <!-- Blog Title & Date -->
            <h1 class="text-3xl font-bold mb-3">
                <?php echo htmlspecialchars($blog['title']); ?>
            </h1>
            <p class="text-sm text-gray-500 mb-4">
                Published on: <?php echo date('F j, Y', strtotime($blog['created_at'])); ?>
            </p>
            <hr class="my-4">

            <!-- Blog Image -->
            <?php if (!empty($blog['image'])): ?>
                <img src="images/<?php echo htmlspecialchars($blog['image']); ?>" 
                     alt="Blog Image" 
                     class="w-full h-64 md:h-80 lg:h-96 object-cover rounded-lg mb-6">
            <?php endif; ?>

            <!-- Blog Content -->
            <div class="prose max-w-none">
                <p><?php echo nl2br(htmlspecialchars($blog['content'])); ?></p>
            </div>

            <!-- Like, Share & Comments Section -->
            <div class="mt-8 space-y-8">

                <!-- Like & Share Buttons -->
                <div class="flex items-center space-x-6">
                    
                    <!-- Like Form (with JS + Fallback) -->
                    <form id="likeForm" action="like_blog.php" method="POST" class="inline">
                        <!-- CSRF Protection -->
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                        <input type="hidden" name="blog_id" value="<?php echo $blog_id; ?>">

                        <!-- Like Button -->
                        <button type="submit" id="likeBtn" class="px-4 py-2 font-semibold rounded transition <?= $user_liked ? 'text-green' : 'text-green' ?>">
                            <!-- Heart icon -->
                            <span id="likeIcon"><?= $user_liked ? '❤️' : '🤍' ?></span>
                            <!-- text -->
                            <!-- <span id="likeText"><?= $user_liked ? 'Liked' : 'Like' ?></span> -->
                            <!-- Total likes -->
                            <span id="likeCount" class="text-green-600 hover:text-green-600 hover:underline transition">
                                <?= $total_likes ?>
                            </span>
                        </button>
                    </form>

                    <!-- Share Button -->
                    <button onclick="shareBlog()" 
                            class="text-green-600 hover:text-green-600 hover:underline transition">
                        📤 Share
                    </button>
                </div>

                <!-- Comment Form -->
                <div class="bg-gray-100 p-6 rounded-lg shadow">
                    <h2 class="text-lg font-semibold mb-4">Review</h2>
                    <form action="add_comment.php" method="POST" class="space-y-4">
                        <!-- CSRF Protection -->
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                        <input type="hidden" name="blog_id" value="<?php echo $blog['blog_id']; ?>">

                        <!-- Comment Textarea -->
                        <div>
                            <label for="comment" class="block text-sm font-medium">Write your thoughts on this product</label>
                            <textarea id="comment" name="comment" rows="4" required 
                                      class="w-full px-4 py-2 border rounded-lg focus:outline-none 
                                             focus:ring-2 focus:ring-green-500"></textarea>
                        </div>

                        <!-- Submit Comment -->
                        <button type="submit" 
                                class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                            💬 Post
                        </button>
                    </form>
                </div>

                <!-- Display Comments -->
                <div class="mt-6">
                    <h3 class="text-lg font-semibold mb-3">Reviews</h3>
                    <?php
                    // Fetch comments for this blog
                    $stmt = $conn->prepare("
                        SELECT c.comment, c.created_at, u.username, u.profile_image
                        FROM comments c
                        JOIN users u ON c.user_id = u.user_id
                        WHERE c.blog_id = ?
                        ORDER BY c.created_at DESC
                    ");
                    $stmt->bind_param("i", $blog['blog_id']);
                    $stmt->execute();
                    $comments = $stmt->get_result();

                    // Loop through comments
                    if ($comments->num_rows > 0) {
                        while ($c = $comments->fetch_assoc()) {
                            echo '<div class="flex space-x-3 mb-4 p-4 bg-white rounded-lg shadow">';

                            // Show profile image (or default avatar)
                            if (!empty($c['profile_image'])) {
                                echo '<img src="uploads/' . htmlspecialchars($c['profile_image']) . '" 
                                         alt="User Image" 
                                         class="w-10 h-10 rounded-full object-cover">';
                            } else {
                                echo '<div class="w-10 h-10 rounded-full bg-gray-300 flex items-center 
                                             justify-center text-gray-600">👤</div>';
                            }

                            // Show username + time ago + comment
                            echo '<div>';
                                echo '<p class="text-sm text-gray-700">';
                                    echo '<strong>' . htmlspecialchars($c['username']) . '</strong>';
                                    echo ' <span class="text-gray-500 text-xs">(' . timeAgo($c['created_at']) . ')</span>';
                                echo '</p>';
                                echo '<p class="mt-2 text-gray-800">' . htmlspecialchars($c['comment']) . '</p>';
                            echo '</div>';

                            echo '</div>';
                        }
                    } else {
                        // No comments found
                        echo '<p class="text-gray-500">No reviews yet. Be the first!</p>';
                    }
                    ?>
                </div>
            </div>

            <!-- Back Button -->
            <a href="blogs.php" 
               class="inline-block mt-6 px-4 py-2 bg-gray-700 text-white rounded-lg shadow hover:bg-gray-900 transition">
               &laquo; Back to All Blogs
            </a>
        </article>
    </main>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- Scripts -->
    <script>
        // Blog share function (Web Share API)
        function shareBlog() {
            if (navigator.share) {
                navigator.share({
                    title: "<?php echo htmlspecialchars($blog['title']); ?>",
                    text: "Check out this blog!",
                    url: window.location.href
                }).catch(err => console.log(err));
            } else {
                alert("Sharing not supported in this browser.");
            }
        }

        // Like button AJAX handler
        document.getElementById("likeForm").addEventListener("submit", function (e) {
            e.preventDefault(); // Prevent reload if JS works

            // Grab form values
            let blogId = this.querySelector("input[name='blog_id']").value;
            let csrfToken = this.querySelector("input[name='csrf_token']").value;

            // Send like request via fetch()
            fetch("like_blog.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "blog_id=" + blogId + "&csrf_token=" + encodeURIComponent(csrfToken)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Update like button UI
                    document.getElementById("likeIcon").textContent = data.liked ? "❤️" : "🤍";
                    // document.getElementById("likeText").textContent = data.liked ? "Liked" : "Like";
                    document.getElementById("likeCount").textContent = data.total;

                    // animation
                    // let btn = document.getElementById("likeBtn");
                    // btn.classList.add("scale-110");
                    // setTimeout(() => btn.classList.remove("scale-110"), 200);

                }
            })
            .catch(() => {
                // If fetch fails → fallback: submit form normally
                this.submit();
            });
        });
    </script>

</body>
</html>
