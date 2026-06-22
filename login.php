<?php
// admin/login.php
require_once __DIR__ . '/../config.php';

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    redirect('/obs/admin/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id, username, password FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            // Successful login
            session_regenerate_id(true);
            $_SESSION['admin_id']       = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['last_activity']  = time();
            redirect('/obs/admin/index.php');
        } else {
            $error = 'Invalid credentials. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login — BookHaven</title>
  <link rel="stylesheet" href="/obs/css/style.css">
  <style>
    body { display:flex; align-items:center; justify-content:center; min-height:100vh; background:var(--ink); }
    .login-box { width: 100%; max-width: 400px; }
    .login-box h1 { font-family:var(--font-head); color:#fff; font-size:2rem; margin-bottom:.3rem; text-align:center; }
    .login-box p  { color:var(--muted); text-align:center; margin-bottom:2rem; font-size:.9rem; }
  </style>
</head>
<body>
  <div class="login-box">
    <h1>📚 BookHaven</h1>
    <p>Admin Panel — Sign In</p>

    <?php if (!empty($error)): ?>
      <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['timeout'])): ?>
      <div class="alert alert-info">Your session expired. Please log in again.</div>
    <?php endif; ?>

    <div class="form-card" style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);">
      <form action="/obs/admin/login.php" method="POST">
        <div class="form-group">
          <label style="color:rgba(255,255,255,.8);" for="username">Username</label>
          <input type="text" id="username" name="username" class="form-control"
                 placeholder="admin" autocomplete="username">
        </div>
        <div class="form-group">
          <label style="color:rgba(255,255,255,.8);" for="password">Password</label>
          <input type="password" id="password" name="password" class="form-control"
                 placeholder="••••••••" autocomplete="current-password">
        </div>
        <button type="submit" class="btn btn-gold" style="width:100%;padding:.8rem;">
          Sign In
        </button>
      </form>
    </div>
    <p style="text-align:center;margin-top:1rem;">
      <a href="/obs/index.php" style="color:var(--muted);font-size:.85rem;">← Back to Store</a>
    </p>
  </div>
</body>
</html>
