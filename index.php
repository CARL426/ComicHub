<?php 
session_start();
include 'includes/db.php';
include 'includes/header.php';

// Get featured comics (latest 6)
$featured_comics = $conn->query("SELECT comics.*, universes.name AS universe_name 
                                FROM comics 
                                LEFT JOIN universes ON comics.universe_id = universes.universe_id 
                                ORDER BY comics.created_at DESC 
                                LIMIT 6");

// Get universes
$universes = $conn->query("SELECT * FROM universes ORDER BY name");

// Get stats
$total_comics = $conn->query("SELECT COUNT(*) as count FROM comics")->fetch_assoc()['count'];
$total_universes = $conn->query("SELECT COUNT(*) as count FROM universes")->fetch_assoc()['count'];
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
?>

<div class="hero-section">
    <div class="hero-content">
        <h1 class="comic-accent">Welcome to <span class="hero-title">ComicHub</span></h1>
        <p class="hero-subtitle">Discover amazing comics from your favorite universes!</p>
        <div class="hero-stats">
            <div class="stat-item">
                <span class="stat-number"><?php echo $total_comics; ?></span>
                <span class="stat-label">Comics</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo $total_universes; ?></span>
                <span class="stat-label">Universes</span>
            </div>
        </div>
        <a href="comics.php" class="btn btn-primary">Explore Comics</a>
    </div>
</div>

<div class="featured-section">
    <h2>🔥 Featured Comics</h2>
    <div class="comic-grid">
        <?php if($featured_comics->num_rows > 0): ?>
            <?php while($comic = $featured_comics->fetch_assoc()): ?>
                <div class="featured-comic">
                    <a href="comic_detail.php?comic_id=<?php echo intval($comic['comic_id']); ?>" class="comic-link">
                        <div class="comic-cover">
                            <?php 
                            $coverPath = $comic['cover_image'] ?? '';
                            if($coverPath){
                                // Normalize: if stored with '../', strip it for public pages
                                if(strpos($coverPath, '../') === 0){ $coverPath = substr($coverPath, 3); }
                            }
                            if($coverPath): ?>
                                <img src="<?php echo htmlspecialchars($coverPath); ?>" 
                                     alt="<?php echo htmlspecialchars($comic['title']); ?>" 
                                     class="img-cover-150x200">
                            <?php else: ?>
                                <img src="assets/images/no-cover.png" 
                                     alt="No cover" 
                                     class="img-cover-150x200">
                            <?php endif; ?>
                            <div class="comic-overlay">
                                <span class="comic-price">$<?php echo number_format($comic['price'], 2); ?></span>
                            </div>
                        </div>
                        <div class="comic-info">
                            <h3><?php echo htmlspecialchars($comic['title']); ?></h3>
                            <p class="comic-author"><?php echo htmlspecialchars($comic['author']); ?></p>
                            <p class="comic-universe"><?php echo htmlspecialchars($comic['universe_name'] ?? 'N/A'); ?></p>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No comics available yet. <a href="admin/manage_comics.php">Add some comics</a> to get started!</p>
        <?php endif; ?>
    </div>
</div>

<div class="universes-section">
    <h2>🌌 Explore Universes</h2>
    <div class="universe-grid">
        <?php if($universes->num_rows > 0): ?>
            <?php while($universe = $universes->fetch_assoc()): ?>
                <div class="universe-card">
                    <h3><?php echo htmlspecialchars($universe['name']); ?></h3>
                    <p><?php echo htmlspecialchars($universe['description'] ?? 'Explore this amazing universe!'); ?></p>
                    <a href="comics.php?universe=<?php echo intval($universe['universe_id']); ?>" class="btn btn-secondary">Browse</a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No universes available yet. <a href="admin/manage_universes.php">Create some universes</a> to get started!</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>