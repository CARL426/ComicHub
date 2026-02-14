<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';

// Check login
if(!isset($_SESSION['user_id'])){
    echo "<p style='color:red;'>You must be logged in to view your favorites.</p>";
    include 'includes/footer.php';
    exit;
}

$user_id = intval($_SESSION['user_id']);

// Handle remove action (POST)
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comic_id'])){
    $comic_id = intval($_POST['comic_id']);
    $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id=? AND comic_id=?");
    $stmt->bind_param("ii", $user_id, $comic_id);
    $stmt->execute();
}

// Get favorites
$sql = "SELECT comics.*, universes.name AS universe_name 
        FROM favorites 
        JOIN comics ON favorites.comic_id = comics.comic_id 
        LEFT JOIN universes ON comics.universe_id = universes.universe_id 
        WHERE favorites.user_id=? 
        ORDER BY favorites.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>My Favorites</h2>

<div class="comic-list">
<?php if($result->num_rows > 0): ?>
    <?php while($c=$result->fetch_assoc()): ?>
        <div class="comic-item">
            <a href="comic_detail.php?comic_id=<?php echo intval($c['comic_id']); ?>" class="comic-item-link">
                <?php 
                $coverPath = $c['cover_image'] ?? '';
                if($coverPath){ if(strpos($coverPath, '../') === 0){ $coverPath = substr($coverPath, 3); } }
                if($coverPath): ?>
                    <img src="<?php echo htmlspecialchars($coverPath); ?>" 
                         alt="<?php echo htmlspecialchars($c['title']); ?>" 
                         class="img-cover-150x200">
                <?php else: ?>
                    <img src="assets/images/no-cover.png" 
                         alt="No cover" 
                         class="img-cover-150x200">
                <?php endif; ?>

                <h3><?php echo htmlspecialchars($c['title']); ?></h3>
                <p><strong>Author:</strong> <?php echo htmlspecialchars($c['author']); ?></p>
                <p><strong>Universe:</strong> <?php echo htmlspecialchars($c['universe_name'] ?? 'N/A'); ?></p>
                <p><strong>Price:</strong> $<?php echo number_format($c['price'],2); ?></p>
            </a>

            <!-- Remove button -->
            <form method="POST" class="mt-10">
                <input type="hidden" name="comic_id" value="<?php echo $c['comic_id']; ?>">
                <button type="submit" class="btn btn-danger">
                    ❌ Remove
                </button>
            </form>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>You don’t have any favorites yet.</p>
<?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>