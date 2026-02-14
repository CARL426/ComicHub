<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';

if(!isset($_GET['comic_id'])){
    echo "<p>Invalid comic ID.</p>";
    include 'includes/footer.php';
    exit;
}

$comic_id = intval($_GET['comic_id']);
$user_id = $_SESSION['user_id'] ?? 0;

// Handle delete my review (now that $user_id/$comic_id are defined)
if ($user_id && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review'])) {
    if ($stmt = $conn->prepare("DELETE FROM reviews WHERE user_id=? AND comic_id=?")) {
        $stmt->bind_param("ii", $user_id, $comic_id);
        $stmt->execute();
    }
}

// Handle add/remove favorite
if($user_id && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])){
    if($_POST['action'] === 'add'){
        $stmt = $conn->prepare("INSERT IGNORE INTO favorites (user_id, comic_id, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ii", $user_id, $comic_id);
        $stmt->execute();
    } elseif($_POST['action'] === 'remove'){
        $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id=? AND comic_id=?");
        $stmt->bind_param("ii", $user_id, $comic_id);
        $stmt->execute();
    }
}

// Handle review submit/update (keeps UI intact)
if ($user_id && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_submit'])) {
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $comment = trim($_POST['comment'] ?? '');
    if ($rating < 1) $rating = 1;
    if ($rating > 5) $rating = 5;

    if ($stmt = $conn->prepare("SELECT review_id FROM reviews WHERE user_id=? AND comic_id=? LIMIT 1")) {
        $stmt->bind_param("ii", $user_id, $comic_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $rid = intval($row['review_id']);
            if ($stmt2 = $conn->prepare("UPDATE reviews SET rating=?, comment=?, updated_at=NOW() WHERE review_id=?")) {
                $stmt2->bind_param("isi", $rating, $comment, $rid);
                $stmt2->execute();
            }
        } else {
            if ($stmt2 = $conn->prepare("INSERT INTO reviews (user_id, comic_id, rating, comment, created_at) VALUES (?,?,?,?, NOW())")) {
                $stmt2->bind_param("iiis", $user_id, $comic_id, $rating, $comment);
                $stmt2->execute();
            }
        }
    }
}

// Fetch comic details
$sql = "SELECT comics.*, universes.name AS universe_name 
        FROM comics 
        LEFT JOIN universes ON comics.universe_id = universes.universe_id 
        WHERE comic_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $comic_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0){
    echo "<p style='color:red;'>Comic not found.</p>";
    include 'includes/footer.php';
    exit;
}

$comic = $result->fetch_assoc();

// Check favorite status
$is_favorite = false;
if($user_id){
    $stmt = $conn->prepare("SELECT 1 FROM favorites WHERE user_id=? AND comic_id=?");
    $stmt->bind_param("ii", $user_id, $comic_id);
    $stmt->execute();
    $is_favorite = $stmt->get_result()->num_rows > 0;
}

// Rating summary + recent reviews
$avg_rating = 0.0; $rating_count = 0; $reviews = [];
if ($stmt = $conn->prepare("SELECT AVG(rating) AS avg_r, COUNT(*) AS cnt FROM reviews WHERE comic_id=?")) {
    $stmt->bind_param("i", $comic_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $avg_rating = round(floatval($row['avg_r'] ?? 0), 1);
    $rating_count = intval($row['cnt'] ?? 0);
}
if ($stmt = $conn->prepare("SELECT r.rating, r.comment, r.created_at, u.username FROM reviews r JOIN users u ON r.user_id=u.user_id WHERE r.comic_id=? ORDER BY r.created_at DESC LIMIT 20")) {
    $stmt->bind_param("i", $comic_id);
    $stmt->execute();
    $rs = $stmt->get_result();
    while ($r = $rs->fetch_assoc()) { $reviews[] = $r; }
}

// Current user's review (used for showing Delete button)
$my_review = null;
if ($user_id) {
    if ($stmt = $conn->prepare("SELECT rating, comment, created_at FROM reviews WHERE user_id=? AND comic_id=? LIMIT 1")) {
        $stmt->bind_param("ii", $user_id, $comic_id);
        $stmt->execute();
        $my_review = $stmt->get_result()->fetch_assoc();
    }
}
?>

<div class="comic-detail">
    <div class="detail-left">
        <div class="detail-cover-wrap">
            <?php $coverPath = $comic['cover_image'] ?? ''; if($coverPath && strpos($coverPath,'../')===0){ $coverPath = substr($coverPath,3); } ?>
            <img src="<?php echo $coverPath ?: 'assets/images/no-cover.png'; ?>" 
                 alt="<?php echo htmlspecialchars($comic['title']); ?>" 
                 class="detail-cover">
            <div class="cover-badge">$<?php echo number_format($comic['price'],2); ?></div>
        </div>
    </div>

    <div class="detail-right">
        <h1 class="detail-title"><?php echo htmlspecialchars($comic['title']); ?></h1>
        <div class="detail-meta">
            <span class="meta-chip">Author: <strong><?php echo htmlspecialchars($comic['author']); ?></strong></span>
            <span class="meta-chip">Universe: <strong><?php echo htmlspecialchars($comic['universe_name'] ?? 'N/A'); ?></strong></span>
        </div>

        <div class="detail-divider"></div>

        <div class="detail-description">
            <?php echo nl2br(htmlspecialchars($comic['description'])); ?>
        </div>

        <div class="detail-cta-row">
            <!-- Favorites -->
            <?php if($user_id): ?>
                <form method="POST">
                    <?php if($is_favorite): ?>
                        <button type="submit" name="action" value="remove" class="btn btn-danger">❌ Remove Favorite</button>
                    <?php else: ?>
                        <button type="submit" name="action" value="add" class="btn btn-success">⭐ Add to Favorites</button>
                    <?php endif; ?>
                </form>
            <?php endif; ?>

            <!-- Buy Now Form: preview invoice mode -->
            <form method="POST" action="purchase_invoice.php">
                <input type="hidden" name="comic_id" value="<?php echo $comic['comic_id']; ?>">
                <input type="hidden" name="amount" value="<?php echo $comic['price']; ?>">
                <button type="submit" class="btn btn-primary">💳 Buy Now</button>
            </form>
        </div>

        <div class="detail-divider"></div>

        <section class="reviews-section">
            <h2 class="reviews-title">Reviews & Ratings</h2>
            <div class="reviews-summary">
                <span class="rating-stars" aria-label="Average rating: <?php echo $avg_rating; ?> out of 5">
                    <?php
                        $full = floor($avg_rating);
                        $half = ($avg_rating - $full) >= 0.5 ? 1 : 0;
                        for($i=0;$i<$full;$i++) echo '★';
                        if($half) echo '☆';
                        for($i=$full+$half;$i<5;$i++) echo '☆';
                    ?>
                </span>
                <span class="reviews-meta"><?php echo $avg_rating; ?>/5 (<?php echo $rating_count; ?>)</span>
            </div>

            <?php if($user_id): ?>
            <form method="POST" class="review-form">
                <label for="rating">Your Rating</label>
                <select id="rating" name="rating" required>
                    <option value="5">★★★★★ (5)</option>
                    <option value="4">★★★★☆ (4)</option>
                    <option value="3">★★★☆☆ (3)</option>
                    <option value="2">★★☆☆☆ (2)</option>
                    <option value="1">★☆☆☆☆ (1)</option>
                </select>
                <label for="comment">Your Review (optional)</label>
                <textarea id="comment" name="comment" rows="3" placeholder="Write your thoughts..."></textarea>
                <button type="submit" name="review_submit" class="btn btn-dark">Submit Review</button>
                <?php if($my_review): ?>
                    <button type="submit" name="delete_review" class="btn btn-danger">Delete My Review</button>
                <?php endif; ?>
            </form>
            <?php else: ?>
            <div class="muted">Please log in to write a review.</div>
            <?php endif; ?>

            <div class="reviews-list">
                <?php if(empty($reviews)): ?>
                    <div class="muted">No reviews yet. Be the first to review!</div>
                <?php else: ?>
                    <?php foreach($reviews as $rv): ?>
                        <div class="review-item">
                            <div class="review-head">
                                <strong><?php echo htmlspecialchars($rv['username']); ?></strong>
                                <span class="rating-stars">
                                    <?php for($i=0;$i<intval($rv['rating']);$i++) echo '★'; for($i=intval($rv['rating']);$i<5;$i++) echo '☆'; ?>
                                </span>
                                <span class="review-date"><?php echo htmlspecialchars(date('Y-m-d', strtotime($rv['created_at']))); ?></span>
                            </div>
                            <?php if(!empty($rv['comment'])): ?>
                                <div class="review-body"><?php echo nl2br(htmlspecialchars($rv['comment'])); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<?php include 'includes/footer.php'; ?>