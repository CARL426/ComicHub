<?php
include 'includes/db.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $passwordPlain = $_POST['password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $agree = isset($_POST['agree_terms']);

    if (!$agree) {
        $error = 'You must agree to the Terms of Use.';
    } else {
        $password = password_hash($passwordPlain, PASSWORD_DEFAULT);
        $profile_pic = 'assets/images/default-avatar.svg';

        $stmt = $conn->prepare("SELECT 1 FROM users WHERE username=? OR email=?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        if ($exists) {
            $error = "Username or Email already exists!";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (username,email,password,full_name,profile_pic) VALUES (?,?,?,?,?)");
            $stmt->bind_param("sssss", $username, $email, $password, $full_name, $profile_pic);
            if ($stmt->execute()) {
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['username'] = $username;
                header("Location: index.php");
                exit;
            } else {
                $error = "Signup failed!";
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
    <title>Sign Up - ComicHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bangers&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script>
    function validateSignup(e){
        var agree = document.getElementById('agree_terms');
        if(!agree.checked){
            e.preventDefault();
            alert('Please agree to the Terms of Use.');
        }
    }
    </script>
</head>
<body>
    <div class="auth-bg"></div>
    <div class="auth-overlay"></div>

    <a class="circle-btn circle-left" href="login.php" aria-label="Back to login">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </a>
    <a class="circle-btn circle-right" href="index.php" aria-label="Close to home">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </a>

    <div class="auth-page">
        <div class="auth-card">
            <a href="index.php" class="auth-logo">ComicHub</a>
            <div class="auth-title">Create an account to continue</div>
            <p class="auth-sub">With this account, you can log in to ComicHub and purchase comics.</p>

            <?php if($error): ?>
            <div class="error-text"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form class="auth-form" action="" method="POST" onsubmit="validateSignup(event)">
                <input type="text" name="username" placeholder="Username" value="<?php echo isset($username)?htmlspecialchars($username):''; ?>" required>
                <input type="text" name="full_name" placeholder="Full Name" value="<?php echo isset($full_name)?htmlspecialchars($full_name):''; ?>" required>
                <input type="email" name="email" placeholder="Email" value="<?php echo isset($email)?htmlspecialchars($email):''; ?>" required>
                <div class="pw-field">
                    <input type="password" id="signup_password" name="password" placeholder="Password" required>
                    <button type="button" id="signup_toggle" class="pw-toggle" aria-label="Show password">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 5c-7 0-10 7-10 7s3 7 10 7 10-7 10-7-3-7-10-7zm0 12a5 5 0 110-10 5 5 0 010 10zm0-2.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z"/></svg>
                    </button>
                </div>

                <label class="auth-terms">
                    <input type="checkbox" id="agree_terms" name="agree_terms" value="1" <?php echo !empty($agree)?'checked':''; ?> required>
                    <span>I have read and agree to the <a href="terms.php" target="_blank">Terms of Use</a>.</span>
                <input type="submit" value="Sign Up">
            </form>
        </div>
    </div>
<script>
(function(){
  var btn = document.getElementById('signup_toggle');
  var pw = document.getElementById('signup_password');
  if(btn && pw){
    var eye = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 5c-7 0-10 7-10 7s3 7 10 7 10-7 10-7-3-7-10-7zm0 12a5 5 0 110-10 5 5 0 010 10zm0-2.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z"/></svg>';
    var eyeOff = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M2.1 3.51L3.5 2.1l18.4 18.4-1.41 1.41-3.2-3.2A12.6 12.6 0 0112 19c-7 0-10-7-10-7a20.6 20.6 0 013.91-4.93L2.1 3.5zM7.6 9.01A12.6 12.6 0 004 12s3 7 10 7c1.21 0 2.34-.21 3.4-.59l-2.1-2.1A5 5 0 0112 17a5 5 0 01-4.4-7.99zm6.72 1.9l-1.48-1.48A2.5 2.5 0 0012 8.5a2.5 2.5 0 002.32 2.41z"/></svg>';
    btn.addEventListener('click', function(){
      var show = pw.type === 'password';
      pw.type = show ? 'text' : 'password';
      btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
      btn.innerHTML = show ? eyeOff : eye;
    });
  }
})();
</script>
</body>
</html>