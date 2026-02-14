<?php
include '../includes/db.php';
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['is_admin']!=1){ echo "Access denied"; exit; }

$total_comics = $conn->query("SELECT COUNT(*) as c FROM comics")->fetch_assoc()['c'];
$total_users = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$total_purchases = $conn->query("SELECT COUNT(*) as c FROM purchases")->fetch_assoc()['c'];
$total_reviews = $conn->query("SELECT COUNT(*) as c FROM reviews")->fetch_assoc()['c'];

// Purchases per day (last 7 days)
$labels = [];
$counts = [];
for($i=6; $i>=0; $i--){
    $day = date('Y-m-d', strtotime("-$i days"));
    $labels[] = $day;
    if($stmt = $conn->prepare("SELECT COUNT(*) as c FROM purchases WHERE DATE(purchase_date)=?")){
        $stmt->bind_param('s', $day);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $counts[] = intval($res['c'] ?? 0);
    } else {
        $counts[] = 0;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="../assets/css/style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
<?php include 'admin_nav.php'; ?>

<div class="admin-content admin-dashboard">
    <h1 class="admin-title">Dashboard</h1>

    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-label">Total Comics</div>
            <div class="stat-value"><?php echo (int)$total_comics; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Users</div>
            <div class="stat-value"><?php echo (int)$total_users; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Purchases</div>
            <div class="stat-value"><?php echo (int)$total_purchases; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Reviews</div>
            <div class="stat-value"><?php echo (int)$total_reviews; ?></div>
        </div>
    </div>

    <div class="cards-grid">
        <div class="chart-card">
            <div class="card-header">Purchases (Last 7 Days)</div>
            <canvas id="purchasesChart" height="120"></canvas>
        </div>
        <div class="quick-actions">
            <div class="card-header">Quick Actions</div>
            <div class="actions-grid">
                <a href="manage_comics.php" class="btn">Manage Comics</a>
                <a href="manage_users.php" class="btn btn-success">Manage Users</a>
                <a href="manage_purchases.php" class="btn btn-primary">View Purchases</a>
                <a href="manage_reviews.php" class="btn btn-dark">MANAGE REVIEWS</a>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
  const ctx = document.getElementById('purchasesChart');
  if(!ctx) return;
  const labels = <?php echo json_encode($labels); ?>;

  const data = <?php echo json_encode($counts); ?>;

  new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Purchases',
        data: data,
        borderColor: '#4dd2ff',
        backgroundColor: 'rgba(77,210,255,0.15)',
        tension: 0.35,
        fill: true,
        pointRadius: 4,
        pointBackgroundColor: '#4dd2ff'
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { color: 'rgba(255,255,255,0.06)' }, ticks: { color: '#aab2c5' } },
        y: { grid: { color: 'rgba(255,255,255,0.06)' }, ticks: { color: '#aab2c5', precision:0 } }
      }
    }
  });
})();
</script>
</body>
</html>