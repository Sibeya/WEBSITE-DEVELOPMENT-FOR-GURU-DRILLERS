<?php
session_start();

// Simple admin credentials — CHANGE THESE IN PRODUCTION!
$admin_user = 'admin';
$admin_pass = 'GRD@2024!';  // Change this password

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';
    if ($user === $admin_user && $pass === $admin_pass) {
        $_SESSION['grd_admin'] = true;
        header('Location: index.html');
        exit;
    } else {
        $error = 'Invalid credentials. Please try again.';
    }
}
// Already logged in
// if (isset($_SESSION['grd_admin'])) { header('Location: index.html'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login – GRD GURUROCDRILLINGTOOL</title>
  <link rel="icon" type="image/png" href="../images/logo.png">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;600;700&family=Inter:wght@300;400;500&display=swap');
    :root { --blue-deep:#0A1628;--blue-dark:#0E1F3D;--blue-navy:#122454;--blue-accent:#2356C8;--blue-bright:#2E6BE6;--red:#C8291A;--silver:#C0CDD9;--silver-dark:#8FA3B8;--white:#F4F7FA; }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Inter', sans-serif; background: var(--blue-deep); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
    body::before { content:''; position:fixed; inset:0; background-image:linear-gradient(rgba(35,86,200,0.07) 1px, transparent 1px), linear-gradient(90deg, rgba(35,86,200,0.07) 1px, transparent 1px); background-size:50px 50px; pointer-events:none; }
    .login-box { background: var(--blue-dark); border: 1px solid rgba(35,86,200,0.25); padding: 48px 44px; width: 100%; max-width: 440px; position: relative; z-index: 1; }
    .login-box::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; background:linear-gradient(90deg,var(--red),var(--blue-accent)); }
    .login-logo { text-align: center; margin-bottom: 28px; }
    .login-logo img { height: 60px; margin: 0 auto 12px; }
    .login-logo h2 { font-family:'Rajdhani',sans-serif; font-size:13px; font-weight:700; letter-spacing:3px; text-transform:uppercase; color:var(--silver-dark); }
    .login-title { font-family:'Rajdhani',sans-serif; font-size:1.6rem; font-weight:700; text-transform:uppercase; letter-spacing:2px; color:var(--white); text-align:center; margin-bottom:28px; }
    .form-group { display:flex; flex-direction:column; gap:7px; margin-bottom:16px; }
    .form-group label { font-family:'Rajdhani',sans-serif; font-size:11px; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:var(--silver-dark); }
    .form-group input { background:rgba(10,22,40,0.7); border:1px solid rgba(35,86,200,0.2); color:var(--white); font-family:'Inter',sans-serif; font-size:0.9rem; padding:12px 14px; width:100%; outline:none; transition:border-color 0.3s; }
    .form-group input:focus { border-color:var(--blue-accent); box-shadow:0 0 0 3px rgba(35,86,200,0.12); }
    .error { background:rgba(200,41,26,0.1); border:1px solid rgba(200,41,26,0.3); color:#ef4444; padding:10px 14px; font-size:0.85rem; margin-bottom:16px; }
    .login-btn { width:100%; background:var(--red); color:var(--white); font-family:'Rajdhani',sans-serif; font-size:15px; font-weight:700; letter-spacing:2px; text-transform:uppercase; padding:14px; border:none; cursor:pointer; transition:background 0.3s; margin-top:8px; }
    .login-btn:hover { background:#e03422; }
    .back-link { display:block; text-align:center; margin-top:18px; font-size:0.8rem; color:var(--silver-dark); }
    .back-link a { color:var(--blue-bright); }
  </style>
</head>
<body>
  <div class="login-box">
    <div class="login-logo">
      <img src="../images/logo.png" alt="GRD Logo">
      <h2>GURUROCDRILLINGTOOL</h2>
    </div>
    <div class="login-title">Admin Login</div>
    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" placeholder="Enter username" required autocomplete="username">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Enter password" required autocomplete="current-password">
      </div>
      <button type="submit" class="login-btn">🔐 Login to Admin</button>
    </form>
    <div class="back-link"><a href="../index.html">← Back to Website</a></div>
  </div>
</body>
</html>
