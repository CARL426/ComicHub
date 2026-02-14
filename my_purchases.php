<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';

if(!isset($_SESSION['user_id'])){
    echo "<p style='text-align:center;color:red;'>Please <a href='login.php'>login</a> to view your purchases.</p>";
    include 'includes/footer.php';
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT purchases.*, comics.title, comics.cover_image, comics.author
        FROM purchases
        JOIN comics ON purchases.comic_id = comics.comic_id
        WHERE purchases.user_id = ?
        ORDER BY purchases.purchase_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$purchases = $stmt->get_result();
?>

<h2>My Purchases</h2>

<div class="comic-list">
<?php if($purchases->num_rows > 0): ?>
    <?php while($p = $purchases->fetch_assoc()): ?>
        <div class="comic-item">
            <a href="comic_detail.php?comic_id=<?php echo intval($p['comic_id']); ?>">
                <?php $coverPath = $p['cover_image'] ?? ''; if($coverPath && strpos($coverPath,'../')===0){ $coverPath = substr($coverPath,3); } ?>
                <img src="<?php echo htmlspecialchars($coverPath ?: 'assets/images/no-cover.png'); ?>" alt="<?php echo htmlspecialchars($p['title']); ?>" class="img-cover-150x200">
                <h3><?php echo htmlspecialchars($p['title']); ?></h3>
            </a>
            <p>Author: <?php echo htmlspecialchars($p['author']); ?></p>
            <p>Purchased on: <?php echo $p['purchase_date']; ?></p>
            <p>Price: $<?php echo number_format($p['amount_paid'],2); ?></p>
            <a href="purchase_invoice.php?id=<?php echo intval($p['purchase_id']); ?>" 
               class="btn btn-dark mt-5">
               View Invoice
            </a>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>You haven't purchased any comics yet.</p>
<?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>