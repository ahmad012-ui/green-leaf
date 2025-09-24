<?php
// reset_password.php
require_once 'includes/db_connection.php';
require_once 'includes/csrf.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

$message = '';
$message_type = '';
$token_valid = false;
$token = '';

// Validate token provided in GET
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $conn->prepare("SELECT user_id FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $token_valid = true;
    } else {
        $message = "Invalid or expired password reset token.";
        $message_type = 'error';
    }
    $stmt->close();
} else {
    $message = "No password reset token provided.";
    $message_type = 'error';
}

// Handle POST (set new password)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['token']) && isset($_POST['password'])) {
    if (!isset($_POST['csrf_token']) || !csrf_check($_POST['csrf_token'])) {
        $message = "Invalid form submission (CSRF).";
        $message_type = 'error';
        $token_valid = true;
    } else {
        $token = $_POST['token'];
        $new_password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'] ?? '';

        if ($new_password !== $confirm_password) {
            $message = "Passwords do not match.";
            $message_type = 'error';
            $token_valid = true;
        }
        // enforce strong password
        elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $new_password)) {
            $message = "Password must be at least 8 characters long and include one letter, one number, and one special character.";
            $message_type = 'error';
            $token_valid = true;
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("UPDATE users SET password_hash = ?, reset_token = NULL WHERE reset_token = ?");
            $stmt->bind_param("ss", $hashed_password, $token);

            if ($stmt->execute()) {
                $message = "Your password has been successfully reset. You can now log in with your new password.";
                $message_type = 'success';
                $token_valid = false;
            } else {
                $message = "Failed to reset password. Please try again.";
                $message_type = 'error';
                $token_valid = true;
            }
            $stmt->close();
        }
    }
}

$csrf = csrf_token();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Reset Password - Green Leaf</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
  <?php include 'navbar.php'; ?>
  <main class="flex items-center justify-center py-10">
    <div class="bg-white p-6 rounded shadow-md w-full max-w-md">
      <h2 class="text-2xl font-bold mb-4">Reset Your Password</h2>
      <?php if ($message): ?>
        <div class="mb-4 p-3 rounded <?php echo ($message_type === 'success') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
          <?php echo htmlspecialchars($message, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); ?>
        </div>
      <?php endif; ?>

      <?php if ($token_valid): ?>
        <form method="POST" action="reset_password.php" class="space-y-4">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
          <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
          <div>
            <label class="block">New Password</label>
            <input type="password" name="password" required class="w-full px-3 py-2 border rounded" />
          </div>
          <div>
            <label class="block">Confirm Password</label>
            <input type="password" name="confirm_password" required class="w-full px-3 py-2 border rounded" />
          </div>
          <button type="submit" class="w-full bg-green-600 text-white py-2 rounded">Set New Password</button>
        </form>
      <?php else: ?>
        <div class="text-center">
          <a href="forgot_password.php" class="text-green-600">Request a new password reset link</a>
        </div>
      <?php endif; ?>
    </div>
  </main>
  <?php include 'footer.php'; ?>
</body>
</html>
