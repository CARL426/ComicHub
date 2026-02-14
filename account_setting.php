<?php
session_start();
include 'includes/db.php';

if(!isset($_SESSION['user_id'])){
    header('Location: login.php');
    exit;
}

$user_id = intval($_SESSION['user_id']);
$stmt = $conn->prepare("SELECT username, full_name, email, profile_pic FROM users WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$avatar = $user['profile_pic'] ?: 'assets/images/default-avatar.svg';
if($avatar && strpos($avatar,'../')===0){ $avatar = substr($avatar,3); }
// Validate avatar path exists; if not, fallback to default SVG
$absAvatar = __DIR__ . '/' . ltrim($avatar, '/');
if (!is_file($absAvatar)) {
    $avatar = 'assets/images/default-avatar.svg';
}
?>

<?php include 'includes/header.php'; ?>
    <div class="account-wrap">
        <video class="account-bg" autoplay muted loop playsinline>
            <source src="assets/images/ProfileBackground.MP4" type="video/mp4">
        </video>
        <div class="account-overlay"></div>

        <div class="account-card">
            <div class="account-brand">ComicHub</div>
            <h1 class="account-title">ACCOUNT INFO</h1>

            <div class="account-avatar-row">
                <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Avatar" class="avatar-lg account-avatar" id="accountAvatar" onerror="this.onerror=null;this.src='assets/images/default-avatar.svg';">
            </div>

            <div class="account-info">
                <div class="info-row">
                    <span class="info-label">Username</span>
                    <span class="info-value" id="infoUsername"><?php echo htmlspecialchars($user['username']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Full Name</span>
                    <span class="info-value" id="infoFullName"><?php echo htmlspecialchars($user['full_name']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email</span>
                    <span class="info-value" id="infoEmail"><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
            </div>

            <div class="account-actions">
                <a href="edit_account.php" class="btn btn-primary">Update Account</a>
                <a href="index.php" class="btn btn-dark">Back to Home</a>
            </div>
        </div>
    </div>
<?php include 'includes/footer.php'; ?>


