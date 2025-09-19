<?php
// Start the session
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'includes/db_connection.php';
include 'includes/functions.php';

$user_id = (int)$_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Handle add/remove plant
if (isset($_GET['add_plant'])) {
    $plant_id = (int)$_GET['add_plant'];
    if (add_plant_to_garden($user_id, $plant_id)) {
        $success_message = "Plant added to your garden!";
    } else {
        $error_message = "This plant is already in your garden.";
    }
}
if (isset($_GET['remove_plant'])) {
    $user_plant_id = (int)$_GET['remove_plant'];
    if (delete_user_plant($user_plant_id, $user_id)) {
        $success_message = "Plant removed successfully!";
    } else {
        $error_message = "Failed to remove plant.";
    }
}

// Handle reminders
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_reminder'])) {
    $plant_id = (int)$_POST['plant_id'];
    $reminder_type = trim($_POST['reminder_type']);
    $reminder_date = trim($_POST['reminder_date']);

    if (empty($reminder_date)) {
        $error_message = "Reminder date cannot be empty.";
    } elseif (add_reminder($user_id, $plant_id, $reminder_type, $reminder_date)) {
        $success_message = "Reminder added successfully!";
    } else {
        $error_message = "Failed to add reminder.";
    }
}
if (isset($_GET['delete_reminder'])) {
    $reminder_id = (int)$_GET['delete_reminder'];
    if (delete_reminder($user_id, $reminder_id)) {
        $success_message = "Reminder deleted!";
    } else {
        $error_message = "Failed to delete reminder.";
    }
}

// Fetch data
$my_plants = get_user_plants($user_id);
$my_reminders = get_user_reminders($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Garden - Green Leaf</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">
  <!-- Navbar -->
  <header>
    <?php include 'navbar.php'; ?>
  </header>

  <main class="max-w-7xl mx-auto px-4 py-8">
   <!-- Welcome section -->
    <h1 class="text-3xl font-bold text-gray-800 mb-2">
      Welcome to Your Garden, <?php echo htmlspecialchars($_SESSION['username']); ?>! 🌱
    </h1>
    <p class="text-gray-600 mb-6">
        Manage your plants, set reminders, and track their growth.
    </p>
 
   <!-- Action buttons -->
    <div class="flex flex-wrap gap-3 mb-6">
        <a href="edit_profile.php" class="h-8 px-2 py-1 rounded-lg text-gray-700 hover:bg-gray-100"> Edit Profile</a>
        <?php if ($success_message): ?>
            <div id="alert-success" class="mb-4 p-3 rounded bg-green-100 text-green-700">
               <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
          <div id="alert-error" class="mb-4 p-3 rounded bg-red-100 text-red-700">
            <?php echo htmlspecialchars($error_message); ?>
          </div>
        <?php endif; ?>
    </div>

    <!-- Garden Section -->
    <section class="mb-12">
      <h2 class="text-2xl font-semibold mb-4">Your Plants (<?php echo count($my_plants); ?>)</h2>
      <?php if (!empty($my_plants)): ?>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <?php foreach ($my_plants as $plant): ?>
              <div class="bg-white shadow rounded-lg overflow-hidden hover:shadow-lg transition">
    <img src="images/<?php echo htmlspecialchars($plant['image']); ?>" alt="<?php echo htmlspecialchars($plant['name']); ?>" class="w-full h-48 object-cover">
    <div class="p-4">
        <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($plant['name']); ?></h3>

        <!-- Display current notes -->
        <p class="text-sm text-gray-600 mt-1"><?php echo nl2br(htmlspecialchars($plant['notes'])); ?></p>

        <!-- Notes form -->
        <form action="update_notes.php" method="POST" class="mt-2 flex gap-2">
            <input type="hidden" name="user_plant_id" value="<?php echo $plant['user_plant_id']; ?>">
            <input type="text" name="notes" placeholder="Add or edit notes..." value="<?php echo htmlspecialchars($plant['notes']); ?>" class="flex-1 p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-400">
            <button type="submit" class="px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700">Save</button>
        </form>

        <div class="flex justify-between items-center mt-4">
            <a href="plant_details.php?id=<?php echo $plant['plant_id']; ?>" class="text-green-600 hover:underline">View Details</a>
            <a href="my_garden.php?remove_plant=<?php echo $plant['user_plant_id']; ?>" onclick="return confirm('Remove this plant?')" class="text-red-600 hover:underline">Remove</a>
        </div>
    </div>
</div>
<?php endforeach; ?>

        </div>
      <?php else: ?>
        <p class="text-gray-500">You haven’t added any plants yet. <a href="catalog.php" class="text-green-600 underline">Browse the catalog</a>.</p>
      <?php endif; ?>
    </section>

    <!-- Reminders Section -->
    <section>
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-semibold">Reminders</h2>
        <a href="reminders.php"
           class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">➕ Add Reminder</a>
      </div>

      <?php if (!empty($my_reminders)): ?>
        <ul class="space-y-3">
          <?php foreach ($my_reminders as $reminder): ?>
            <li class="bg-white shadow p-4 rounded-lg flex justify-between items-center">
              <div>
                <p class="font-medium"><?php echo htmlspecialchars($reminder['plant_name']); ?></p>
                <p class="text-sm text-gray-600">
                  <?php echo htmlspecialchars($reminder['type']); ?> • <?php echo date('F j, Y, g:i a', strtotime($reminder['reminder_date'])); ?>
                </p>
              </div>
              <a href="my_garden.php?delete_reminder=<?php echo $reminder['reminder_id']; ?>"
                 onclick="return confirm('Delete this reminder?')"
                 class="text-red-600 hover:underline">Delete</a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p class="text-gray-500">No reminders yet. Add one to stay on track 🌱</p>
      <?php endif; ?>
    </section>
  </main>

  <footer class="">
    <?php include 'footer.php'; ?>
  </footer>
</body>
</html>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const successAlert = document.getElementById("alert-success");
    const errorAlert = document.getElementById("alert-error");

    [successAlert, errorAlert].forEach(alert => {
      if (alert) {
        setTimeout(() => {
          alert.style.transition = "opacity 0.5s ease";
          alert.style.opacity = "0";
          setTimeout(() => alert.remove(), 500); // remove from DOM after fade
        }, 2500); // 2.5 seconds
      }
    });
  });
</script>
