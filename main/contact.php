<?php
session_start();
include 'includes/db_connection.php';
include 'includes/functions.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Us - Green Leaf</title>
  <script src="https://cdn.tailwindcss.com"></script>
  

</head>
<body class="bg-gray-50 text-gray-800">

  <!-- Navbar -->
  <header>
    <?php include 'navbar.php'; ?>
  </header>

  <main class="container mx-auto my-12 px-4">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-8">

      <h1 class="text-3xl font-bold text-center mb-4">Contact Us</h1>
      <p class="text-center text-gray-600 mb-6">Have a question or suggestion? We'd love to hear from you!</p>

      <!-- Alerts -->
      <?php if (isset($_SESSION['contact_success'])): ?>
        <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-4">
          <?= $_SESSION['contact_success']; unset($_SESSION['contact_success']); ?>
        </div>
      <?php endif; ?>

      <?php if (isset($_SESSION['contact_error'])): ?>
        <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-4">
          <?= $_SESSION['contact_error']; unset($_SESSION['contact_error']); ?>
        </div>
      <?php endif; ?>

      <!-- Contact Form -->
      <form action="contact_process.php" method="POST" class="space-y-4">
        <div>
          <label for="name" class="block font-medium mb-1">Your Name</label>
          <input type="text" name="name" id="name" 
                 class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400"
                 value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
        </div>

        <div>
          <label for="email" class="block font-medium mb-1">Email Address</label>
          <input type="email" name="email" id="email" 
                 class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>

        <div>
          <label for="subject" class="block font-medium mb-1">Subject</label>
          <input type="text" name="subject" id="subject" 
                 class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400"
                 value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" required>
        </div>

        <div>
          <label for="message" class="block font-medium mb-1">Message</label>
          <textarea name="message" id="message" rows="5"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400"
                    required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="w-full bg-green-600 text-white font-semibold px-4 py-3 rounded hover:bg-green-700 transition">
          Send Message
        </button>
      </form>

      <hr class="my-6 border-gray-200">

      <!-- Direct Contact Info -->
      <div class="text-center text-gray-700">
        <h5 class="font-semibold mb-2">Or reach us directly at:</h5>
        <p><strong>Email:</strong> support@greenleaf.com</p>
        <p><strong>Phone:</strong> +1 (555) 123-4567</p>
      </div>

    </div>
  </main>

  <footer class="">
    <?php include 'footer.php'; ?>
  </footer>

</body>
</html>
