<?php
require 'config.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role']      = $user['role'];
        header('Location: index.php');
        exit;
    }
    $error = 'ভুল ইউজারনেম বা পাসওয়ার্ড';
}

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<title>লগইন — ISP Billing</title>
<style>
  body { font-family:'Segoe UI','Nirmala UI',sans-serif; background:#1e2a38;
         display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; }
  .box { background:#fff; padding:36px 32px; border-radius:14px; width:340px;
         box-shadow:0 10px 40px rgba(0,0,0,.3); }
  h2 { margin:0 0 6px; color:#1e2a38; font-size:22px; }
  p.sub { color:#7f8c8d; font-size:13px; margin:0 0 22px; }
  label { display:block; font-size:13px; color:#5a6b7c; margin-bottom:6px; font-weight:600; }
  input { width:100%; padding:11px 12px; border:1px solid #d5dde5; border-radius:8px;
          font-size:14px; margin-bottom:16px; box-sizing:border-box; outline:none; }
  input:focus { border-color:#4fc3f7; }
  button { width:100%; padding:12px; background:#4fc3f7; color:#fff; border:none;
           border-radius:8px; font-size:15px; font-weight:600; cursor:pointer; }
  button:hover { background:#29b6f6; }
  .err { background:#fdecea; color:#e74c3c; padding:10px; border-radius:8px;
         font-size:13px; margin-bottom:14px; }
</style>
</head>
<body>
<form class="box" method="post">
  <h2>⚡ ISP Billing</h2>
  <p class="sub">লগইন করুন</p>
  <?php if ($error): ?><div class="err"><?= $error ?></div><?php endif; ?>
  <label>ইউজারনেম</label>
  <input type="text" name="username" required autofocus>
  <label>পাসওয়ার্ড</label>
  <input type="password" name="password" required>
  <button type="submit">লগইন</button>
</form>
</body>
</html>