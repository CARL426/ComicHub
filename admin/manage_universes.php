<?php
session_start();
include '../includes/db.php';
if(!isset($_SESSION['user_id']) || $_SESSION['is_admin']!=1){
    echo "Access denied."; exit;
}

$msg='';
if(isset($_POST['add_universe'])){
    $name=trim($_POST['name']);
    $desc=trim($_POST['description']);
    $stmt=$conn->prepare("INSERT INTO universes (name, description) VALUES (?,?)");
    $stmt->bind_param("ss",$name,$desc);
    $stmt->execute();
    $msg="Universe added!";
}

// Delete Universe
if(isset($_GET['delete'])){
    $del_id=intval($_GET['delete']);
    $conn->query("DELETE FROM universes WHERE universe_id=$del_id");
    header("Location: manage_universes.php"); exit;
}

$universes=$conn->query("SELECT * FROM universes ORDER BY universe_id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin - Manage Universes</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'admin_nav.php';?>
    <div class="admin-content">
<h2>Manage Universes</h2>
<?php if($msg) echo "<p style='color:green;'>$msg</p>"; ?>

<h3>Add Universe</h3>
<form method="POST">
<input type="text" name="name" placeholder="Universe Name" required>
<textarea name="description" placeholder="Description"></textarea>
<input type="submit" name="add_universe" value="Add Universe">
</form>

<h3>Existing Universes</h3>
<table border="1" cellpadding="5" cellspacing="0">
<tr><th>ID</th><th>Name</th><th>Description</th><th>Actions</th></tr>
<?php while($u=$universes->fetch_assoc()): ?>
<tr>
<td><?php echo $u['universe_id']; ?></td>
<td><?php echo $u['name']; ?></td>
<td><?php echo $u['description']; ?></td>
<td><a href="manage_universes.php?delete=<?php echo $u['universe_id']; ?>" onclick="return confirm('Delete?')">Delete</a></td>
</tr>
<?php endwhile; ?>
</table>
</div>
</body>
</html>