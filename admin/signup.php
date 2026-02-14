<?php
// admin/signup.php - Minimal admin-only signup (separate from user pages)
session_start();
require_once __DIR__ . '/../includes/db.php';

// If already admin, go to dashboard
if (!empty($_SESSION['user_id']) && !empty($_SESSION['is_admin'])) {
    header('Location: dashboard.php');
    exit;
}

// Change this and keep it secret. Consider moving to a config include.
$ADMIN_ACCESS_CODE = 'admin';

$error = '';
$username = '';
$email = '';
$full_name = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $passwordPlain = (string)($_POST['password'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $access_code = trim($_POST['access_code'] ?? '');

    if ($access_code !== $ADMIN_ACCESS_CODE) {
        $error = 'Invalid Admin Access Code.';
    } elseif ($username === '' || $email === '' || $passwordPlain === '' || $full_name === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($passwordPlain) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $password = password_hash($passwordPlain, PASSWORD_DEFAULT);
        $profile_pic = 'assets/images/default-avatar.svg';

        if ($stmt = $conn->prepare('SELECT 1 FROM users WHERE username=? OR email=?')) {
            $stmt->bind_param('ss', $username, $email);
            $stmt->execute();
            $exists = $stmt->get_result()->num_rows > 0;
            if ($exists) {
                $error = 'Username or Email already exists.';
            } else {
                if ($stmt = $conn->prepare('INSERT INTO users (username,email,password,full_name,profile_pic,is_admin) VALUES (?,?,?,?,?,1)')) {
                    $stmt->bind_param('sssss', $username, $email, $password, $full_name, $profile_pic);
                    if ($stmt->execute()) {
                        $_SESSION['user_id'] = (int)$stmt->insert_id;
                        $_SESSION['username'] = $username;
                        $_SESSION['is_admin'] = 1;
                        header('Location: dashboard.php');
                        exit;
                    } else {
                        $error = 'Failed to create admin account.';
                    }
                } else {
                    $error = 'Failed to prepare create statement.';
                }
            }
        } else {
            $error = 'Failed to prepare lookup statement.';
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Signup</title>
  <style>
    body{font-family:Arial, sans-serif;background:#0f1221;color:#e9edf5;margin:0;padding:24px}
    .wrap{max-width:480px;margin:40px auto;background:#161a2b;border:1px solid #252a42;border-radius:10px;padding:24px}
    h1{margin:0 0 16px;font-size:22px}
    .msg{margin:0 0 12px;color:#ff6b6b}
    label{display:block;margin:10px 0 6px}
    input[type=text],input[type=password],input[type=email]{width:100%;padding:10px;border-radius:6px;border:1px solid #2b3252;background:#0f1323;color:#e9edf5}
    button, input[type=submit]{margin-top:14px;width:100%;padding:10px 12px;border:0;border-radius:6px;background:#4dd2ff;color:#051223;font-weight:bold;cursor:pointer}
    .nav{margin-top:14px;font-size:14px}
    a{color:#9bdcff;text-decoration:none}
  </style>
</head>
<body>
  <div class="wrap">
    <h1>Admin Signup</h1>
    <?php if($error): ?><div class="msg"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="post">
      <label for="username">Username</label>
      <input id="username" name="username" type="text" value="<?php echo htmlspecialchars($username); ?>" required>

      <label for="full_name">Full Name</label>
      <input id="full_name" name="full_name" type="text" value="<?php echo htmlspecialchars($full_name); ?>" required>

      <label for="email">Email</label>
      <input id="email" name="email" type="email" value="<?php echo htmlspecialchars($email); ?>" required>

      <label for="password">Password</label>
      <input id="password" name="password" type="password" required>
      <label style="display:flex;align-items:center;gap:8px;margin-top:8px">
        <input id="show_password" type="checkbox" onclick="document.getElementById('password').type = this.checked ? 'text' : 'password'">
        Show Password
      </label>

      <label for="access_code">Admin Access Code</label>
      <input id="access_code" name="access_code" type="text" required>

      <input type="submit" value="Create Admin Account">
    </form>
    <div class="nav">
      <a href="login.php">Go to Admin Login</a> · <a href="../index.php">Back to site</a>
    </div>
  </div>
</body>
</html>
