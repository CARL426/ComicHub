<?php 
session_start();
include 'includes/db.php';
include 'includes/header.php';

// Universe list
$universes = $conn->query("SELECT * FROM universes ORDER BY name");

// GET params
$search = $_GET['search'] ?? '';
$mode = $_GET['mode'] ?? 'comic';
$universe_id = $_GET['universe'] ?? 'all';

// Base SQL
$sql = "SELECT comics.*, universes.name AS universe_name 
        FROM comics 
        LEFT JOIN universes ON comics.universe_id = universes.universe_id 
        WHERE 1";

$params = [];
$types = "";

// Universe filter
if($universe_id !== 'all'){
    $sql .= " AND comics.universe_id = ?";
    $params[] = $universe_id;
    $types .= "i";
}

// Search filter (normal submit fallback)
if($search){
    if($mode == "comic"){
        $sql .= " AND comics.title LIKE ?";
        $params[] = "%$search%";
        $types .= "s";
    } elseif($mode == "author"){
        $sql .= " AND comics.author LIKE ?";
        $params[] = "%$search%";
        $types .= "s";
    }
}

$sql .= " ORDER BY comics.created_at DESC";

$stmt = $conn->prepare($sql);
if($params){
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>Browse Comics</h2>

<!-- Search + Filter Form -->
<form method="GET" id="searchForm" class="filters-bar">
    <div class="filters-left">
        <input type="text" id="searchBox" name="search" placeholder="Search comics or authors..." 
               value="<?php echo htmlspecialchars($search); ?>" autocomplete="off" class="filter-input">
        <div class="filter-radios">
            <label class="radio-chip">
                <input type="radio" name="mode" value="comic" <?php if($mode=='comic') echo 'checked'; ?>>
                <span>Comic</span>
            </label>
            <label class="radio-chip">
                <input type="radio" name="mode" value="author" <?php if($mode=='author') echo 'checked'; ?>>
                <span>Author</span>
            </label>
        </div>
    </div>
    <div class="filters-right">
        <select name="universe" onchange="this.form.submit()" class="filter-select">
            <option value="all" <?php if($universe_id=='all') echo 'selected'; ?>>All Universes</option>
            <?php while($u=$universes->fetch_assoc()): ?>
                <option value="<?php echo $u['universe_id']; ?>" <?php if($universe_id==$u['universe_id']) echo 'selected'; ?>><?php echo $u['name']; ?></option>
            <?php endwhile; ?>
        </select>
        <input type="submit" value="Search" class="btn">
    </div>
    
</form>

<!-- Ajax Results -->
<div id="searchResults">
    <div class="catalog-grid">
    <?php if($result->num_rows > 0): ?>
        <?php while($c=$result->fetch_assoc()): ?>
            <?php 
            $coverPath = $c['cover_image'] ?? '';
            if($coverPath){ if(strpos($coverPath, '../') === 0){ $coverPath = substr($coverPath, 3); } }
            $coverSrc = $coverPath ? htmlspecialchars($coverPath) : 'assets/images/no-cover.png';
            ?>
            <a href="comic_detail.php?comic_id=<?php echo intval($c['comic_id']); ?>" class="catalog-card">
                <div class="catalog-cover">
                    <img src="<?php echo $coverSrc; ?>" alt="<?php echo htmlspecialchars($c['title']); ?>">
                    <span class="catalog-price">$<?php echo number_format($c['price'],2); ?></span>
                </div>
                <div class="catalog-info">
                    <h3 class="catalog-title"><?php echo htmlspecialchars($c['title']); ?></h3>
                    <div class="catalog-meta">
                        <span class="meta">By <?php echo htmlspecialchars($c['author']); ?></span>
                        <span class="meta">• <?php echo htmlspecialchars($c['universe_name'] ?? 'N/A'); ?></span>
                    </div>
                </div>
            </a>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No results found.</p>
    <?php endif; ?>
    </div>
</div>

<script>
// Live search with Ajax
document.getElementById("searchBox").addEventListener("keyup", function(){
    let query = this.value;
    let mode = document.querySelector('input[name="mode"]:checked').value;
    let universe = document.querySelector('select[name="universe"]').value;

    let xhr = new XMLHttpRequest();
    xhr.open("GET", "search_ajax.php?search="+encodeURIComponent(query)+"&mode="+mode+"&universe="+universe, true);
    xhr.onload = function(){
        if(this.status == 200){
            document.getElementById("searchResults").innerHTML = this.responseText;
        }
    };
    xhr.send();
});
</script>

<?php include 'includes/footer.php'; ?>