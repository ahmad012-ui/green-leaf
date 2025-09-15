<?php
// register.php
// Server-side registration with CSRF and validation
// Keep your register_user($conn,...) function as in original (using prepared statements)

require_once 'includes/db_connection.php';
require_once 'includes/csrf.php';

// Set secure session cookie params (only affects new sessions)
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '', // set in production
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

function register_user($conn, $username, $email, $password_hash) {
    // Keep original behavior: check existing email then insert
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        return false; // Email already exists
    }
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password_hash);

    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        $stmt->close();
        return false;
    }
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || !csrf_check($_POST['csrf_token'])) {
        $error = "Invalid form submission (CSRF). Please try again.";
    } else {
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($email) || empty($password)) {
            $error = "Please fill in all fields.";
        } 
        // Username: only letters, numbers, underscores, 3–20 chars
        elseif (!preg_match("/^[a-zA-Z0-9_]{3,20}$/", $username)) {
            $error = "Username must be 3–20 characters and contain only letters, numbers, and underscores.";
        } 
        // Email
        elseif (!preg_match("/^[\w\.\-]+@([\w\-]+\.)+[a-zA-Z]{2,}$/", $email)) {
            $error = "Please enter a valid email address.";
        } 
        // Password: at least 8 chars, one upper, one lower, one number
        elseif (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/", $password)) {
            $error = "Password must be at least 8 characters long and include upper, lower, and a number.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            if (register_user($conn, $username, $email, $password_hash)) {
                header("Location: login.php?registration=success");
                exit();
            } else {
                $error = "Registration failed. This email may already be in use.";
            }
        }
    }
}

$csrf = csrf_token();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Register - Green Leaf</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
  <?php include 'navbar.php'; ?>
  <main class="flex items-center justify-center py-10">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
      <h2 class="text-2xl font-bold mb-4 text-center">Create an Account</h2>
      <?php if (!empty($error)): ?>
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded"><?php echo htmlspecialchars($error, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); ?></div>
      <?php endif; ?>

      <form method="POST" action="register.php" class="space-y-4">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
        <div>
          <label class="block text-sm font-medium">Username</label>
          <input name="username" required class="w-full px-3 py-2 border rounded" />
        </div>
        <div>
          <label class="block text-sm font-medium">Email</label>
          <input type="email" name="email" required class="w-full px-3 py-2 border rounded" />
        </div>
        <div>
          <label class="block text-sm font-medium">Password</label>
          <input type="password" name="password" required class="w-full px-3 py-2 border rounded" />
        </div>
        <button type="submit" class="w-full bg-green-600 text-white py-2 rounded">Register</button>
      </form>

      <p class="mt-4 text-sm text-center">Already have an account? <a href="login.php" class="text-green-600">Login</a></p>
    </div>
  </main>
  <?php include 'footer.php'; ?>
</body>
</html>
