<?php
session_start();
include '../includes/db.php';
if(!isset($_SESSION['user_id']) || $_SESSION['is_admin']!=1){
    echo "Access denied."; exit;
}

// Delete User
if(isset($_GET['delete'])){
    $del_id=intval($_GET['delete']);
    $conn->query("DELETE FROM users WHERE user_id=$del_id");
    header("Location: manage_users.php"); exit;
}

$users=$conn->query("SELECT * FROM users ORDER BY user_id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin - Manage Users</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'admin_nav.php';?>
    <div class="admin-content">
<h2>Manage Users</h2>
<table border="1" cellpadding="5" cellspacing="0">
<tr><th>ID</th><th>Username</th><th>Full Name</th><th>Email</th><th>Admin</th><th>Actions</th></tr>
<?php while($u=$users->fetch_assoc()): ?>
<tr>
<td><?php echo $u['user_id']; ?></td>
<td><?php echo $u['username']; ?></td>
<td><?php echo $u['full_name']; ?></td>
<td><?php echo $u['email']; ?></td>
<td><?php echo $u['is_admin'] ? 'Yes':'No'; ?></td>
<td><a href="manage_users.php?delete=<?php echo $u['user_id']; ?>" onclick="return confirm('Delete?')">Delete</a></td>
</tr>
<?php endwhile; ?>
</table>
</div>
</body>
</html>