<?php
// edit_profile.php - update user profile (username, email, address, profile_image optional)
require_once 'includes/db_connection.php';
require_once 'includes/csrf.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !csrf_check($_POST['csrf_token'])) {
        $error = "Invalid CSRF token";
    } else {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if ($username === '' || $email === '') {
            $error = "Username and email are required.";
        } elseif (!preg_match("/^[a-zA-Z0-9_]{3,20}$/", $username)) {
            $error = "Invalid username format.";
        } elseif (!preg_match("/^[\w\.\-]+@([\w\-]+\.)+[a-zA-Z]{2,}$/", $email)) {
            $error = "Invalid email address.";
        } else {
            // update profile (prepared stmt)
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, address = ? WHERE user_id = ?");
            $stmt->bind_param("sssi", $username, $email, $address, $user_id);
            if ($stmt->execute()) {
                $stmt->close();
                $success = "Profile updated successfully.";
                $_SESSION['username'] = $username; // reflect change in session
            } else {
                $stmt->close();
                $error = "Failed to update profile.";
            }
        }
    }
}

// Fetch current profile for display
$stmt = $conn->prepare("SELECT username, email, address FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$profile = $res->fetch_assoc();
$stmt->close();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Edit Profile - Green Leaf</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
  <?php include 'navbar.php'; ?>
  <main class="flex items-center justify-center py-10">
    <div class="bg-white p-6 rounded shadow-md w-full max-w-md">
      <h2 class="text-2xl font-bold mb-4">Edit Profile</h2>
      <?php if ($error): ?>
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded"><?php echo htmlspecialchars($error, ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded"><?php echo htmlspecialchars($success, ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); ?></div>
      <?php endif; ?>

      <form method="POST" action="my_garden.php" class="space-y-4">
        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
        <div><label class="block">Username</label><input name="username" value="<?php echo htmlspecialchars($profile['username'] ?? '', ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); ?>" class="w-full px-3 py-2 border rounded" /></div>
        <div><label class="block">Email</label><input type="email" name="email" value="<?php echo htmlspecialchars($profile['email'] ?? '', ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); ?>" class="w-full px-3 py-2 border rounded" /></div>
        <div><label class="block">Address</label><input name="address" value="<?php echo htmlspecialchars($profile['address'] ?? '', ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); ?>" class="w-full px-3 py-2 border rounded" /></div>
        <button type="submit" class="w-full bg-green-600 text-white py-2 rounded">Save</button>
      </form>
    </div>
  </main>
  <?php include 'footer.php'; ?>
</body>
</html>
