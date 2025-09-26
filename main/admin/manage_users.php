<?php
// admin/manage_users.php
require_once '../includes/db_connection.php';
require_once '../includes/csrf.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Ensure admin access
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../login.php");
    exit();
}

$message = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    if (!isset($_POST['csrf_token']) || !csrf_check($_POST['csrf_token'])) {
        $message = "Invalid CSRF token.";
    } else {
        $user_id = intval($_POST['id']);

        if ($user_id === $_SESSION['user_id']) {
            $message = "You cannot modify your own user account.";
        } else {
            $action = $_POST['action'];

            if ($action === 'promote') {
                $stmt = $conn->prepare("UPDATE users SET is_admin = 1 WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $message = $stmt->execute() ? "User promoted successfully!" : "Error promoting user.";
                $stmt->close();
            } elseif ($action === 'demote') {
                $stmt = $conn->prepare("UPDATE users SET is_admin = 0 WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $message = $stmt->execute() ? "User demoted successfully!" : "Error demoting user.";
                $stmt->close();
            } elseif ($action === 'delete') {
                $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $message = $stmt->execute() ? "User deleted successfully!" : "Error deleting user.";
                $stmt->close();
            }
        }
    }
}

// Fetch users
$users = [];
$res = $conn->query("SELECT user_id, username, email, is_admin, created_at FROM users ORDER BY user_id DESC");
if ($res) {
    while ($r = $res->fetch_assoc()) $users[] = $r;
}

$csrf = csrf_token();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Users - Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col md:flex-row">

  <?php include 'admin_sidebar.php'; ?>

  <main class="flex-1 p-4 md:p-8">
    <?php include 'admin_top_navbar.php'; ?>

    <div class="container mx-auto mt-4">
      <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6">Manage Users</h1>

      <?php if ($message): ?>
        <div class="mb-4 p-3 bg-blue-100 text-blue-700 rounded">
          <?php echo htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
        </div>
      <?php endif; ?>

      <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-gray-200">
          <h2 class="text-lg sm:text-xl font-bold text-green-600">All Registered Users</h2>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered</th>
                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                  <tr>
                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      <?php echo htmlspecialchars($user['user_id']); ?>
                    </td>
                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                      <?php echo htmlspecialchars($user['username']); ?>
                    </td>
                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                      <?php echo htmlspecialchars($user['email']); ?>
                    </td>
                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm">
                      <?php if ($user['is_admin']): ?>
                        <span class="px-2 inline-flex text-xs font-semibold rounded-full bg-green-100 text-green-800">Admin</span>
                      <?php else: ?>
                        <span class="px-2 inline-flex text-xs font-semibold rounded-full bg-gray-100 text-gray-800">User</span>
                      <?php endif; ?>
                    </td>
                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                      <?php echo htmlspecialchars(date('M d, Y', strtotime($user['created_at']))); ?>
                    </td>
                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                      <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                        <?php if ($user['is_admin']): ?>
                          <form method="POST" class="inline" action="manage_users.php" onsubmit="return confirm('Demote this admin?');">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['user_id']); ?>">
                            <input type="hidden" name="action" value="demote">
                            <button type="submit" class="text-yellow-600 hover:text-yellow-900">Demote</button>
                          </form>
                        <?php else: ?>
                          <form method="POST" class="inline" action="manage_users.php">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['user_id']); ?>">
                            <input type="hidden" name="action" value="promote">
                            <button type="submit" class="text-green-600 hover:text-green-900">Promote</button>
                          </form>
                        <?php endif; ?>
                        <form method="POST" class="inline" action="manage_users.php" onsubmit="return confirm('Delete this user? This cannot be undone.');">
                          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
                          <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['user_id']); ?>">
                          <input type="hidden" name="action" value="delete">
                          <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                        </form>
                      <?php else: ?>
                        <span class="text-gray-500">Current User</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" class="px-3 sm:px-6 py-4 text-center text-sm text-gray-500">
                    No users found.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</body>
</html>