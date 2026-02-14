<?php
// header.php

// Session မရှိသေးရင်သာ စတင်
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Attempt to load current user info for avatar/username if available
$currentUsername = $_SESSION['username'] ?? null;
$currentUserId = $_SESSION['user_id'] ?? null;
$currentUserAvatar = 'assets/images/default-avatar.svg';
if ($currentUserId && isset($conn)) {
    if ($stmt = $conn->prepare("SELECT username, profile_pic FROM users WHERE user_id=?")) {
        $stmt->bind_param("i", $currentUserId);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $currentUsername = $row['username'] ?: $currentUsername;
            $pic = $row['profile_pic'] ?: '';
            if ($pic) {
                // Normalize any '../' stored path to public path
                if (strpos($pic, '../') === 0) { $pic = substr($pic, 3); }
                // Validate file exists; if not, keep default SVG
                $abs = __DIR__ . '/../' . ltrim($pic, '/');
                if (is_file($abs)) {
                    $currentUserAvatar = $pic;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <?php $cssVersion = @filemtime(__DIR__ . '/../assets/css/style.css') ?: time(); ?>
    <title>ComicHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bangers&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo $cssVersion; ?>">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
</head>
<body>
<header class="site-header">
    <div class="header-left">
        <?php if(isset($_SESSION['user_id'])): ?>
            <div class="user-menu">
                <button type="button" class="user-chip" id="userMenuButton" aria-haspopup="true" aria-expanded="false">
                    <img src="<?php echo htmlspecialchars($currentUserAvatar); ?>" alt="Avatar" class="avatar-circle" onerror="this.onerror=null;this.src='assets/images/default-avatar.svg';">
                    <span class="user-name"><?php echo htmlspecialchars($currentUsername ?? 'User'); ?></span>
                </button>
                <ul class="user-dropdown" id="userDropdown" role="menu">
                    <li><a href="account_setting.php">ACCOUNT SETTING</a></li>
                    <li><a href="logout.php">LOGOUT</a></li>
                </ul>
            </div>
        <?php else: ?>
            <div class="auth-actions">
                <a href="login.php" class="auth-link">Login</a>
                <a href="signup.php" class="auth-link">Sign Up</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="logo">
        <a href="index.php">ComicHub</a>
    </div>

    <nav class="header-right">
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="comics.php">Comics</a></li>
            <?php if(isset($_SESSION['user_id'])): ?>
                <li><a href="favorites.php">Favorites</a></li>
                <li><a href="my_purchases.php">My Purchases</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<script>
(function(){
    var btn = document.getElementById('userMenuButton');
    var menu = document.getElementById('userDropdown');
    if(btn && menu){
        btn.addEventListener('click', function(e){
            var isOpen = menu.classList.contains('open');
            menu.classList.toggle('open', !isOpen);
            btn.setAttribute('aria-expanded', String(!isOpen));
        });
        document.addEventListener('click', function(e){
            if(!menu.contains(e.target) && !btn.contains(e.target)){
                menu.classList.remove('open');
                btn.setAttribute('aria-expanded','false');
            }
        });
    }
    // removed signup popover per user request
})();
</script>
<main>