<header class="header">
    <a href="#">Student Dashboard</a>
    <div class="username">Welcome, <?php echo htmlspecialchars($fullName); ?></div>
    <div class="logout">
        <a href="logout.php">Logout</a>
    </div>
</header>

<aside>
    <ul>
        <li><a href="my_courses.php">My Courses</a></li>
        <li><a href="my_results.php">My Results</a></li>
        <li><a href="profile.php">Profile</a></li>
        <li><a href="settings.php">Settings</a></li>
    </ul>
</aside>
