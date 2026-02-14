<?php
session_start();
include '../includes/db.php';
if(!isset($_SESSION['user_id']) || $_SESSION['is_admin']!=1){
    echo "Access denied."; exit;
}

// Delete Review
if(isset($_GET['delete'])){
    $del_id=intval($_GET['delete']);
    $conn->query("DELETE FROM reviews WHERE review_id=$del_id");
    header("Location: manage_reviews.php"); exit;
}

$reviews=$conn->query("SELECT reviews.*, users.username, comics.title FROM reviews LEFT JOIN users ON reviews.user_id=users.user_id LEFT JOIN comics ON reviews.comic_id=comics.comic_id ORDER BY review_id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin - Manage Reviews</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'admin_nav.php';?>
    <div class="admin-content">
<h2>Manage Reviews</h2>
<table border="1" cellpadding="5" cellspacing="0">
<tr><th>ID</th><th>User</th><th>Comic</th><th>Rating</th><th>Comment</th><th>Actions</th></tr>
<?php while($r=$reviews->fetch_assoc()): ?>
<tr>
<td><?php echo $r['review_id']; ?></td>
<td><?php echo $r['username']; ?></td>
<td><?php echo $r['title']; ?></td>
<td><?php echo $r['rating']; ?></td>
<td><?php echo $r['comment']; ?></td>
<td><a href="manage_reviews.php?delete=<?php echo $r['review_id']; ?>" onclick="return confirm('Delete?')">Delete</a></td>
</tr>
<?php endwhile; ?>
</table>
</div>
</body>
</html>