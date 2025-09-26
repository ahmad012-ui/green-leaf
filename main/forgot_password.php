<?php
// Start the session to manage user login status
session_start();

// Include the database connection and functions files
include 'includes/db_connection.php';
include 'includes/functions.php';
include 'includes/csrf.php';

$message = '';
$message_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!csrf_check($_POST['csrf_token'] ?? '')) {
      die("Invalid CSRF token.");
    } 
    $email = trim($_POST['email']);

    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $user_id = $user['user_id'];
            
            $token = bin2hex(random_bytes(32));
            
            $update_stmt = $conn->prepare("UPDATE users SET reset_token = ? WHERE user_id = ?");
            $update_stmt->bind_param("si", $token, $user_id);
            
            if ($update_stmt->execute()) {
                $message = "A password reset link has been generated. Check your email for instructions.";
                $message_type = 'success';
                $message .= "<br>Your reset link is: <a href='reset_password.php?token=" . htmlspecialchars($token) . "' class='text-blue-600 underline'>Reset Password</a>";
            } else {
                $message = "Failed to generate a reset link. Please try again.";
                $message_type = 'danger';
            }
            $update_stmt->close();
        } else {
            $message = "No account found with that email address.";
            $message_type = 'danger';
        }
        $stmt->close();
    } else {
        $message = "Please enter a valid email address.";
        $message_type = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password - Green Leaf</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">
  <header>
    <?php include 'navbar.php'; ?>
  </header>

  <main class="flex-grow flex items-center justify-center py-10">
    <div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-md">
      <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Forgot Password</h2>

      <?php if ($message): ?>
        <div class="mb-4 px-4 py-3 rounded-lg 
          <?php echo $message_type === 'success' ? 'bg-green-100 text-green-700 border border-green-300' : 'bg-red-100 text-red-700 border border-red-300'; ?>">
          <?php echo $message; ?>
        </div>
      <?php endif; ?>

      <p class="text-center text-sm text-gray-600 mb-4">
        Enter your email address to receive a password reset link.
      </p>

      <form action="forgot_password.php" method="POST" class="space-y-4">
        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
          <input type="email" id="email" name="email" required
            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm 
                   focus:outline-none focus:ring focus:ring-blue-400 focus:border-blue-400">
        </div>

        <button type="submit"
          class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition duration-200">
          Reset Password
        </button>

        <div class="text-center mt-3">
          <a href="login.php" class="text-sm text-blue-600 hover:underline">Back to Login</a>
        </div>
      </form>
    </div>
  </main>

  <footer>
        <?php include 'footer.php'; ?>
  </footer>
</body>
</html>
