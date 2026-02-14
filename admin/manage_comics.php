<?php
session_start();
include '../includes/db.php';

// Admin check
if(!isset($_SESSION['user_id']) || $_SESSION['is_admin']!=1){
    echo "Access denied. Admins only.";
    exit;
}

$msg = '';
$edit_comic = null;

// Add Comic
if(isset($_POST['add_comic'])){
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $price = floatval($_POST['price']);
    $universe_id = intval($_POST['universe_id']);
    $description = trim($_POST['description']);

    $cover_image = '';
    if(isset($_FILES['cover_image']) && $_FILES['cover_image']['error']==0){
        $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg','jpeg','png','gif'];
        if(in_array(strtolower($ext), $allowed)){
            // Normalize stored path to be web-root relative
            $cover_image = 'assets/uploads/'.time().'_'.basename($_FILES['cover_image']['name']);
            // Move file relative to admin directory into project uploads dir
            move_uploaded_file($_FILES['cover_image']['tmp_name'], '../'.$cover_image);
        }
    }

    $stmt = $conn->prepare("INSERT INTO comics (title, author, price, universe_id, description, cover_image) VALUES (?,?,?,?,?,?)");
    // Correct types: title(s), author(s), price(d), universe_id(i), description(s), cover_image(s)
    $stmt->bind_param("ssdiss",$title,$author,$price,$universe_id,$description,$cover_image);
    if($stmt->execute()) $msg="Comic added successfully!";
    else $msg="Failed to add comic.";
}

// Load comic for editing
if(isset($_GET['edit'])){
    $edit_id = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM comics WHERE comic_id=$edit_id");
    $edit_comic = $res->fetch_assoc();
}

// Update Comic
if(isset($_POST['update_comic'])){
    $comic_id = intval($_POST['comic_id']);
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $price = floatval($_POST['price']);
    $universe_id = intval($_POST['universe_id']);
    $description = trim($_POST['description']);

    $cover_image = $_POST['old_cover']; // keep old cover if no new one
    if(isset($_FILES['cover_image']) && $_FILES['cover_image']['error']==0){
        $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg','jpeg','png','gif'];
        if(in_array(strtolower($ext), $allowed)){
            $cover_image = 'assets/uploads/'.time().'_'.basename($_FILES['cover_image']['name']);
            move_uploaded_file($_FILES['cover_image']['tmp_name'], '../'.$cover_image);
        }
    }

    $stmt = $conn->prepare("UPDATE comics SET title=?, author=?, price=?, universe_id=?, description=?, cover_image=? WHERE comic_id=?");
    // Correct types: s s d i s s i
    $stmt->bind_param("ssdissi", $title, $author, $price, $universe_id, $description, $cover_image, $comic_id);
    if($stmt->execute()) $msg="Comic updated successfully!";
    else $msg="Failed to update comic.";
}

// Delete Comic
if(isset($_GET['delete'])){
    $del_id=intval($_GET['delete']);
    $conn->query("DELETE FROM comics WHERE comic_id=$del_id");
    header("Location: manage_comics.php");
    exit;
}

// Fetch comics & universes
$comics = $conn->query("SELECT comics.*, universes.name AS universe_name FROM comics LEFT JOIN universes ON comics.universe_id = universes.universe_id ORDER BY comic_id DESC");
$universes = $conn->query("SELECT * FROM universes ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin - Manage Comics</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'admin_nav.php';?>
    <div class="admin-content">
<h2>Manage Comics</h2>
<?php if($msg) echo "<p style='color:green;'>$msg</p>"; ?>

<?php if($edit_comic): ?>
<h3>Edit Comic</h3>
<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="comic_id" value="<?php echo $edit_comic['comic_id']; ?>">
    <input type="hidden" name="old_cover" value="<?php echo $edit_comic['cover_image']; ?>">
    <input type="text" name="title" value="<?php echo htmlspecialchars($edit_comic['title']); ?>" required>
    <input type="text" name="author" value="<?php echo htmlspecialchars($edit_comic['author']); ?>" required>
    <input type="number" step="0.01" name="price" value="<?php echo $edit_comic['price']; ?>" required>
    <select name="universe_id" required>
        <option value="">Select Universe</option>
        <?php 
        $universes2 = $conn->query("SELECT * FROM universes ORDER BY name");
        while($u=$universes2->fetch_assoc()): ?>
        <option value="<?php echo $u['universe_id']; ?>" <?php if($u['universe_id']==$edit_comic['universe_id']) echo "selected"; ?>>
            <?php echo $u['name']; ?>
        </option>
        <?php endwhile; ?>
    </select>
    <textarea name="description" placeholder="Description" rows="3"><?php echo htmlspecialchars($edit_comic['description']); ?></textarea>
    <label>Cover Image:</label>
    <?php 
    if($edit_comic['cover_image']){
        $storedPath = $edit_comic['cover_image'];
        // If already starts with '../', use as-is; otherwise prefix for admin context
        $imgSrc = (strpos($storedPath, '../') === 0) ? $storedPath : '../'.$storedPath;
        echo "<img src='".$imgSrc."' style='width:50px;height:70px;'><br>";
    }
    ?>
    <input type="file" name="cover_image">
    <input type="submit" name="update_comic" value="Update Comic">
</form>
<hr>
<?php else: ?>
<h3>Add Comic</h3>
<form method="POST" enctype="multipart/form-data">
    <input type="text" name="title" placeholder="Title" required>
    <input type="text" name="author" placeholder="Author" required>
    <input type="number" step="0.01" name="price" placeholder="Price" required>
    <select name="universe_id" required>
        <option value="">Select Universe</option>
        <?php while($u=$universes->fetch_assoc()): ?>
        <option value="<?php echo $u['universe_id']; ?>"><?php echo $u['name']; ?></option>
        <?php endwhile; ?>
    </select>
    <textarea name="description" placeholder="Description" rows="3"></textarea>
    <label>Cover Image:</label>

    <input type="file" name="cover_image">
    <input type="submit" name="add_comic" value="Add Comic">
</form>
<?php endif; ?>

<h3>Existing Comics</h3>
<table border="1" cellpadding="5" cellspacing="0">
<tr>
<th>ID</th><th>Title</th><th>Author</th><th>Universe</th><th>Price</th><th>Cover</th><th>Actions</th>
</tr>
<?php while($c=$comics->fetch_assoc()): ?>
<tr>
<td><?php echo $c['comic_id']; ?></td>
<td><?php echo $c['title']; ?></td>
<td><?php echo $c['author']; ?></td>
<td><?php echo $c['universe_name'] ?? 'N/A'; ?></td>
<td>$<?php echo number_format($c['price'],2); ?></td>
<td><?php 
    if($c['cover_image']){
        $storedPath = $c['cover_image'];
        $imgSrc = (strpos($storedPath, '../') === 0) ? $storedPath : '../'.$storedPath;
        echo "<img src='".$imgSrc."' style='width:50px;height:70px;'>";
    }
?></td>
<td>
    <a href="manage_comics.php?edit=<?php echo $c['comic_id']; ?>">Edit</a> | 
    <a href="manage_comics.php?delete=<?php echo $c['comic_id']; ?>" onclick="return confirm('Delete?')">Delete</a>
</td>
</tr>
<?php endwhile; ?>
</table>
</div>
</body>
</html>
