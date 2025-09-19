<?php
// Start the session to manage user login status
session_start();

// Check if the user is logged in. If not, redirect to the login page.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include the database connection and functions files
include 'includes/db_connection.php';
include 'includes/functions.php';

// Fetch the user's reminders from the database
$user_id = $_SESSION['user_id'];
$reminders = get_user_reminders($user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Reminders - Green Leaf</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">

  <!-- Navbar -->
  <header>
    <?php include 'navbar.php'; ?>
  </header>

  <main class="max-w-7xl mx-auto px-6 py-10">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">My Plant Care Reminders 🌱</h1>
        <p class="text-gray-600">Stay on top of watering, fertilizing, and more!</p>
      </div>
      <button data-modal-target="addReminderModal" data-modal-toggle="addReminderModal"
              class="px-5 py-2 bg-green-600 text-white rounded-lg shadow hover:bg-green-700 transition">
        + Add New Reminder
      </button>
    </div>

    <!-- Reminders List -->
    <?php if (!empty($reminders)): ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($reminders as $reminder): ?>
          <div class="bg-white rounded-lg shadow hover:shadow-md transition p-5 flex flex-col">
            <h3 class="text-lg font-semibold text-green-700">
              <?php echo htmlspecialchars($reminder['plant_name']); ?>
            </h3>
            <span class="inline-block mt-1 px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded-full">
              <?php echo ucfirst(htmlspecialchars($reminder['type'])); ?>
            </span>
            <p class="text-gray-500 text-sm mt-3">
              📅 <?php echo date('F j, Y, g:i a', strtotime($reminder['reminder_date'])); ?>
            </p>
            <div class="mt-auto pt-4">
              <a href="delete_reminder.php?id=<?php echo htmlspecialchars($reminder['reminder_id']); ?>"
                 class="block w-full text-center px-3 py-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition"
                 onclick="return confirm('Delete this reminder?');">
                 Delete
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 text-center p-6 rounded-lg shadow-sm">
        You don’t have any reminders yet.  
        <strong>Click "Add New Reminder"</strong> to get started!
      </div>
    <?php endif; ?>
  </main>

  <!-- Add Reminder Modal -->
  <div id="addReminderModal" tabindex="-1" aria-hidden="true"
       class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
      <div class="flex justify-between items-center bg-green-600 text-white px-5 py-3 rounded-t-lg">
        <h5 class="text-lg font-semibold">Add a New Reminder</h5>
        <button type="button" class="text-white text-xl" data-modal-hide="addReminderModal">&times;</button>
      </div>
      <div class="p-6">
        <form action="add_reminder.php" method="post" class="space-y-4">
          <div>
            <label for="plant_id" class="block text-sm font-medium text-gray-700">Select Plant</label>
            <select id="plant_id" name="plant_id"
                    class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-green-500 focus:border-green-500" required>
              <option value="" disabled selected>Choose...</option>
              <?php
              $user_plants = get_user_plants($user_id);
              if (!empty($user_plants)) {
                foreach ($user_plants as $plant) {
                  echo '<option value="' . htmlspecialchars($plant['plant_id']) . '">' . htmlspecialchars($plant['name']) . '</option>';
                }
              }
              ?>
            </select>
          </div>
          <div>
            <label for="reminder_type" class="block text-sm font-medium text-gray-700">Reminder Type</label>
            <select id="reminder_type" name="type"
                    class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-green-500 focus:border-green-500" required>
              <option value="" disabled selected>Choose...</option>
              <option value="watering">Watering</option>
              <option value="fertilizing">Fertilizing</option>
              <option value="pruning">Pruning</option>
              <option value="repotting">Repotting</option>
              <option value="custom">Custom</option>
            </select>
          </div>
          <div>
            <label for="reminder_date" class="block text-sm font-medium text-gray-700">Date & Time</label>
            <input type="datetime-local" id="reminder_date" name="reminder_date"
                   class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-green-500 focus:border-green-500" required>
          </div>
          <div>
            <button type="submit"
                    class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
              Save Reminder
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-gray-900 text-gray-400 py-6 mt-10">
    <?php include 'footer.php'; ?>
  </footer>

  <script>
    // Modal toggle
    document.querySelectorAll('[data-modal-toggle]').forEach(btn => {
      btn.addEventListener('click', () => {
        const target = document.getElementById(btn.getAttribute('data-modal-target'));
        target.classList.remove('hidden');
      });
    });
    document.querySelectorAll('[data-modal-hide]').forEach(btn => {
      btn.addEventListener('click', () => {
        btn.closest('.fixed').classList.add('hidden');
      });
    });
  </script>
</body>
</html>
