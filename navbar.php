<?php
$email = $_SESSION['email'] ?? 'Guest';
$role = $_SESSION['role'] ?? null;
?>

<nav>
    <div class="nav-left">
        <a href="index.php">Home</a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <div class="dropdown">
                <button class="dropbtn">Admin Tools ▾</button>
                <div class="dropdown-content">
                    <a href="modcomments.php">Moderate Comments</a>
                    <a href="manageusers.php">Manage Users</a>
                    <a href="categories.php">Manage Categories</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php if (isset($_SESSION['user_id'])): ?>
        <span>Welcome, <strong><?= htmlspecialchars($email) ?></strong></span>
    <?php endif; ?>
    <div class="nav-right">
    <form action="search.php" method="GET" id="search-form">
    <input type="text" name="q" placeholder="Search pages..." required>
    <button type="submit">Search</button>
</form>
    <button onclick="darkMode()">Toggle dark mode</button>
</div>
    <script>
function darkMode() {
    document.body.classList.toggle('dark-mode');
    if (document.body.classList.contains('dark-mode')) {
        localStorage.setItem('theme', 'dark');
    } else {
        localStorage.setItem('theme', 'light');
    }
}

window.onload = () => {
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-mode');
    }
};

    </script>
</nav>



