<?php
if(!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1){
    echo "Access denied."; exit;
}
?>

<div class="admin-nav">
    <h2>ComicHub Admin</h2>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="manage_comics.php">Comics</a></li>
        <li><a href="manage_universes.php">Universes</a></li>
        <li><a href="manage_users.php">Users</a></li>
        <li><a href="manage_reviews.php">Reviews</a></li>
        <li><a href="manage_purchases.php">Purchases</a></li>
        <li><a href="login.php">Admin Login</a></li>
        <li><a href="signup.php">Admin Signup</a></li>
        <li><a href="../index.php">Back to Site</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>