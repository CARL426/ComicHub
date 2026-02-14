<?php
include 'includes/db.php';

$search = $_GET['search'] ?? '';
$mode = $_GET['mode'] ?? 'comic';
$universe_id = $_GET['universe'] ?? 'all';

$sql = "SELECT comics.*, universes.name AS universe_name 
        FROM comics 
        LEFT JOIN universes ON comics.universe_id = universes.universe_id 
        WHERE 1";

$params = [];
$types = "";

if($universe_id !== 'all'){
    $sql .= " AND comics.universe_id = ?";
    $params[] = $universe_id;
    $types .= "i";
}

if($search){
    if($mode == "comic"){
        $sql .= " AND comics.title LIKE ?";
        $params[] = "%$search%";
        $types .= "s";
    } else {
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

if($result->num_rows > 0){
    echo "<div class='comic-list'>";
    while($c=$result->fetch_assoc()){
        $comicId = intval($c['comic_id']);
        $title = htmlspecialchars($c['title']);
        $author = htmlspecialchars($c['author']);
        $universe = htmlspecialchars($c['universe_name'] ?? '');
        $cover = $c['cover_image'] ? $c['cover_image'] : '';
        if($cover && strpos($cover, '../') === 0){ $cover = substr($cover, 3); }
        $cover = $cover ? htmlspecialchars($cover) : 'assets/images/no-cover.png';
        echo "<a href='comic_detail.php?comic_id={$comicId}' class='comic-item-link'>";
        echo "<div class='comic-item'>";
        echo "<img src='{$cover}' class='img-cover-120x160'><br>";
        echo "<h4>{$title}</h4>";
        echo "<p><strong>Author:</strong> {$author}</p>";
        echo "<p><strong>Universe:</strong> {$universe}</p>";
        echo "<p><strong>Price:</strong> $".number_format($c['price'],2)."</p>";
        echo "</div>";
        echo "</a>";
    }
    echo "</div>";
} else {
    echo "<p>No results found.</p>";
}
?>