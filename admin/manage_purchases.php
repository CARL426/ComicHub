<?php
session_start();
include '../includes/db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['is_admin']!=1){
    echo "Access denied."; exit;
}

$sql = "SELECT purchases.*, users.username, comics.title 
        FROM purchases
        JOIN users ON purchases.user_id = users.user_id
        JOIN comics ON purchases.comic_id = comics.comic_id
        ORDER BY purchases.purchase_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin - Manage Purchases</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'admin_nav.php'; ?>
    <div class="admin-content">
        <h2>VIEW PURCHASES</h2>

        <table border="1" cellpadding="8" cellspacing="0">
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Comic</th>
                <th>Amount Paid</th>
                <th>Date</th>
            </tr>
            <?php while($row=$result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['purchase_id']; ?></td>
                <td><?php echo $row['username']; ?></td>
                <td><?php echo $row['title']; ?></td>
                <td>$<?php echo number_format($row['amount_paid'],2); ?></td>
                <td><?php echo $row['purchase_date']; ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>