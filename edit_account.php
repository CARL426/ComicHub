<?php
session_start();
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = intval($_SESSION['user_id']);

// Load current user
$stmt = $conn->prepare("SELECT username, full_name, email, profile_pic FROM users WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$avatar = $user['profile_pic'] ?: 'assets/images/default-avatar.svg';
if ($avatar && strpos($avatar, '../') === 0) { $avatar = substr($avatar, 3); }
// Validate avatar path exists; fallback if missing
$absAvatar = __DIR__ . '/' . ltrim($avatar, '/');
if (!is_file($absAvatar)) { $avatar = 'assets/images/default-avatar.svg'; }

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($username === '' || $full_name === '' || $email === '') {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } else {
        // Ensure username/email are unique among other users
        if ($stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM users WHERE (username=? OR email=?) AND user_id<>?")) {
            $stmt->bind_param("ssi", $username, $email, $user_id);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                if (intval($row['cnt']) > 0) {
                    $error = 'Username or email already in use.';
                }
            }
        }

        $profile_pic_path = $user['profile_pic'];
        // Optional file upload
        if (!$error && isset($_FILES['profile_pic']) && is_array($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['profile_pic'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $error = 'File upload error.';
            } else {
                $allowed_types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                if (!isset($allowed_types[$mime])) {
                    $error = 'Unsupported image type. Please upload JPG, PNG, GIF, or WEBP.';
                } else {
                    $ext = $allowed_types[$mime];
                    $upload_dir = __DIR__ . '/assets/uploads';
                    if (!is_dir($upload_dir)) { @mkdir($upload_dir, 0777, true); }

                    $new_name = 'avatar_' . $user_id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $dest_path = $upload_dir . '/' . $new_name;

                    if (!move_uploaded_file($file['tmp_name'], $dest_path)) {
                        $error = 'Failed to save uploaded file.';
                    } else {
                        // Delete old uploaded avatar if it was in assets/uploads
                        if (!empty($user['profile_pic']) && strpos($user['profile_pic'], 'assets/uploads/') === 0) {
                            $old_abs = __DIR__ . '/' . $user['profile_pic'];
                            if (strpos($user['profile_pic'], '../') === 0) {
                                $old_abs = __DIR__ . '/' . substr($user['profile_pic'], 3);
                            }
                            if (is_file($old_abs)) { @unlink($old_abs); }
                        }
                        $profile_pic_path = 'assets/uploads/' . $new_name;
                    }
                }
            }
        }

        if (!$error) {
            if ($profile_pic_path) {
                $stmt = $conn->prepare("UPDATE users SET username=?, full_name=?, email=?, profile_pic=? WHERE user_id=?");
                $stmt->bind_param("ssssi", $username, $full_name, $email, $profile_pic_path, $user_id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET username=?, full_name=?, email=? WHERE user_id=?");
                $stmt->bind_param("sssi", $username, $full_name, $email, $user_id);
            }

            if ($stmt && $stmt->execute()) {
                $_SESSION['username'] = $username;
                $success = 'Account updated successfully.';
                // Refresh current values for re-render
                $user['username'] = $username;
                $user['full_name'] = $full_name;
                $user['email'] = $email;
                $user['profile_pic'] = $profile_pic_path ?: $user['profile_pic'];
                $avatar = $user['profile_pic'] ?: 'assets/images/default-avatar.svg';
                if ($avatar && strpos($avatar, '../') === 0) { $avatar = substr($avatar, 3); }
            } else {
                $error = 'Failed to update account.';
            }
        }
    }
}
?>

<?php include 'includes/header.php'; ?>
<div class="account-wrap">
    <video class="account-bg" autoplay muted loop playsinline>
        <source src="assets/images/ProfileBackground.MP4" type="video/mp4">
    </video>
    <div class="account-overlay"></div>

    <div class="account-card" style="max-width:640px; margin:60px auto;">
        <div class="account-brand">ComicHub</div>
        <h1 class="account-title">Update Account</h1>

        <?php if($error): ?>
            <div class="form-msg notice-error" role="alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if($success): ?>
            <div class="form-msg notice-success" role="status"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="account-avatar-row">
            <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Avatar" class="avatar-lg account-avatar" onerror="this.onerror=null;this.src='assets/images/default-avatar.svg';">
        </div>

        <form class="account-form" action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="profile_pic">Profile Picture</label>
                <input type="file" name="profile_pic" id="profile_pic" accept="image/*">
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" name="full_name" id="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="account-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="account_setting.php" class="btn btn-dark">Back</a>
            </div>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
