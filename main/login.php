<?php
// login.php
require_once 'includes/db_connection.php';
require_once 'includes/csrf.php';

// set session cookie params then start session
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

$error_message = '';

function login_user($conn, $email, $password) {
    $stmt = $conn->prepare("SELECT user_id, username, password_hash, is_admin FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password_hash'])) {
            $stmt->close();
            return $user;
        }
    }
    $stmt->close();
    return false;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || !csrf_check($_POST['csrf_token'])) {
        $error_message = "Invalid form submission (CSRF). Please try again.";
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = login_user($conn, $email, $password);

        if ($user) {
            // prevent session fixation
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = (bool)$user['is_admin'];

            if ($_SESSION['is_admin']) {
                header("Location: admin/index.php");
            } else {
                header("Location: my_garden.php");
            }
            exit();
        } else {
            $error_message = "Invalid email or password. Please try again.";
        }
    }
}

$csrf = csrf_token();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Login - Green Leaf</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
  <?php include 'navbar.php'; ?>
  <main class="flex items-center justify-center py-10">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
      <h2 class="text-2xl font-bold mb-4 text-center">Login</h2>
      <?php if (!empty($error_message)): ?>
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded"><?php echo htmlspecialchars($error_message, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); ?></div>
      <?php endif; ?>

      <form method="POST" action="login.php" class="space-y-4">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
        <div>
          <label class="block text-sm font-medium">Email</label>
          <input type="email" name="email" required class="w-full px-3 py-2 border rounded" />
        </div>
        <div>
          <label class="block text-sm font-medium">Password</label>
          <input type="password" name="password" required class="w-full px-3 py-2 border rounded" />
        </div>
        <button type="submit" class="w-full bg-green-600 text-white py-2 rounded">Login</button>
      </form>

      <p class="mt-4 text-sm text-center"><a href="forgot_password.php" class="text-green-600">Forgot password?</a></p>
    </div>
  </main>
  <?php include 'footer.php'; ?>
</body>
</html>
