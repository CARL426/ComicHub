<?php
// admin/login.php - Minimal admin-only login (no dependency on user pages)
session_start();
require_once __DIR__ . '/../includes/db.php';

// If already admin, go to dashboard
if (!empty($_SESSION['user_id']) && !empty($_SESSION['is_admin'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } else {
        if ($stmt = $conn->prepare('SELECT user_id, username, password, is_admin FROM users WHERE username=? AND is_admin=1')) {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $res = $stmt->get_result();
            $user = $res->fetch_assoc();
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = (int)$user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = (int)$user['is_admin'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid admin credentials.';
            }
        } else {
            $error = 'Login failed. Please try again.';
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login</title>
  <style>
    body{font-family:Arial, sans-serif;background:#0f1221;color:#e9edf5;margin:0;padding:24px}
    .wrap{max-width:420px;margin:40px auto;background:#161a2b;border:1px solid #252a42;border-radius:10px;padding:24px}
    h1{margin:0 0 16px;font-size:22px}
    .msg{margin:0 0 12px;color:#ff6b6b}
    label{display:block;margin:10px 0 6px}
    input[type=text],input[type=password]{width:100%;padding:10px;border-radius:6px;border:1px solid #2b3252;background:#0f1323;color:#e9edf5}
    button, input[type=submit]{margin-top:14px;width:100%;padding:10px 12px;border:0;border-radius:6px;background:#4dd2ff;color:#051223;font-weight:bold;cursor:pointer}
    .nav{margin-top:14px;font-size:14px}
    a{color:#9bdcff;text-decoration:none}
  </style>
</head>
<body>
  <div class="wrap">
    <h1>Admin Login</h1>
    <?php if($error): ?><div class="msg"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="post">
      <label for="username">Username</label>
      <input id="username" name="username" type="text" value="<?php echo htmlspecialchars($username); ?>" required>
      <label for="password">Password</label>
      <input id="password" name="password" type="password" required>
      <label style="display:flex;align-items:center;gap:8px;margin-top:8px">
        <input id="show_password" type="checkbox" onclick="document.getElementById('password').type = this.checked ? 'text' : 'password'">
        Show Password
      </label>
      <input type="submit" value="Login">
    </form>
    <div class="nav">
      <a href="signup.php">Go to Admin Signup</a> · <a href="../index.php">Back to site</a>
    </div>
  </div>
</body>
</html>
