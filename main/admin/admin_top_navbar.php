<nav class="bg-white shadow-md p-4 mb-6 flex justify-between items-center">
    <div class="text-xl font-semibold text-gray-800">
        Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>
    </div>
    <div>
        <a href="../logout.php" class="text-gray-600 hover:text-red-500 font-medium transition-colors">
            <i class="fa-solid fa-right-from-bracket mr-1"></i> Logout
        </a>
    </div>
</nav>