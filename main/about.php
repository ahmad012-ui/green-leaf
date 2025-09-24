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
  <title>About Us - Green Leaf</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="bg-gray-50 text-gray-800 flex flex-col min-h-screen">
  <!-- Navbar -->
  <header>
    <?php include 'navbar.php'; ?>
  </header>

  <!-- Main -->
  <main class="flex-grow container mx-auto px-4 py-12">
    <!-- Intro -->
    <section class="text-center mb-12">
      <h1 class="text-4xl font-extrabold text-green-600 mb-3">About Green Leaf</h1>
      <p class="text-lg text-gray-600">Your companion for all things green 🌱</p>
    </section>

    <div class="max-w-4xl mx-auto bg-white shadow-md rounded-2xl p-6 mb-12">
      <p class="mb-4">
        Discover the joy of gardening with <span class="font-semibold">Green Leaf</span>, your all-in-one platform for plants and care. Whether you’re a beginner or a seasoned gardener, Green Leaf helps you explore a wide plant catalog, receive personalized care tips, and grow your green space with confidence.
      </p>
      <p class="mb-4">
        From plant recommendations to expert guides, Green Leaf makes gardening simple, accessible, and inspiring. Build your virtual garden, learn how to nurture your plants, and join a community passionate about nature.
      </p>
      <p class="mb-4 text-center">
        <span class="font-semibold text-center">Green Leaf — Where Every Home Can Bloom.</span>
      </p>
    </div>

    <!-- Mission & Vision -->
    <section class="grid md:grid-cols-2 gap-6 mb-12">
      <div class="bg-green-50 p-6 rounded-xl shadow text-center">
        <h3 class="text-xl font-bold text-green-700 mb-2">🌍 Our Mission</h3>
        <p>To empower individuals to cultivate greener lives by making plant care simple, smart, and sustainable.</p>
      </div>
      <div class="bg-green-50 p-6 rounded-xl shadow text-center">
        <h3 class="text-xl font-bold text-green-700 mb-2">✨ Our Vision</h3>
        <p>A world where every home has a thriving green space, powered by technology and community knowledge.</p>
      </div>
    </section>

    <!-- Features -->
    <section class="mb-12">
      <h2 class="text-2xl font-bold text-center text-green-600 mb-8">What Green Leaf Offers</h2>
      <div class="grid md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-xl shadow text-center">
          <h4 class="text-green-600 font-semibold mb-2">🌱 Plant Catalog</h4>
          <p>Browse a wide variety of plants with detailed care information tailored for your needs.</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow text-center">
          <h4 class="text-green-600 font-semibold mb-2">⏰ Smart Reminders</h4>
          <p>Never miss watering, fertilizing, or repotting again with automated care reminders.</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow text-center">
          <h4 class="text-green-600 font-semibold mb-2">📚 Community Tips</h4>
          <p>Learn from fellow plant lovers with shared experiences, articles, and advice.</p>
        </div>
      </div>
    </section>

    <!-- Team -->
    <!-- <section class="mb-12">
      <h2 class="text-2xl font-bold text-center text-green-600 mb-8">Meet Our Team</h2>
      <div class="grid sm:grid-cols-2 gap-6 max-w-3xl mx-auto">
        <div class="bg-white p-6 rounded-xl shadow text-center">
          <img src="images/team1.jpg" alt="Team Member" class="w-28 h-28 rounded-full mx-auto mb-4 object-cover border-4 border-green-500">
          <h5 class="font-bold">Ahmad Rehman</h5>
          <p class="text-gray-500">Full Stack Developer</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow text-center">
          <img src="images/team2.jpg" alt="Team Member" class="w-28 h-28 rounded-full mx-auto mb-4 object-cover border-4 border-green-500">
          <h5 class="font-bold">Farooq Daniyal</h5>
          <p class="text-gray-500">UI/UX Designer</p>
        </div>
      </div>
    </section> -->

    <!-- Impact -->
    <section class="bg-green-50 p-8 rounded-2xl shadow text-center mb-12">
      <h2 class="text-2xl font-bold text-green-700 mb-4">Our Growing Impact</h2>
      <p class="mb-8 text-gray-600">We’re making plant care easier every day 🌿</p>
      <div class="grid md:grid-cols-3 gap-6">
        <div>
          <h3 class="text-3xl font-extrabold text-green-600">500+</h3>
          <p class="text-gray-600">Plants in Catalog</p>
        </div>
        <div>
          <h3 class="text-3xl font-extrabold text-green-600">300+</h3>
          <p class="text-gray-600">Active Users</p>
        </div>
        <div>
          <h3 class="text-3xl font-extrabold text-green-600">50+</h3>
          <p class="text-gray-600">Community Articles</p>
        </div>
      </div>
    </section>

    <!-- Call to Action -->
    <section class="text-center">
      <h2 class="text-2xl font-bold text-green-600 mb-3">Join the Green Movement 🌱</h2>
      <p class="text-gray-600 mb-6">Start building your own virtual garden today and make plant care simple and joyful.</p>
      <a href="register.php" class="inline-block bg-green-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-green-700 transition">
        Get Started
      </a>
    </section>
  </main>

  <!-- Footer -->
  <footer class="">
    <?php include 'footer.php'; ?>
  </footer>
</body>
</html>
