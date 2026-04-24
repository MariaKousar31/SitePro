<?php
session_start();
include_once 'db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = mysqli_real_escape_string($conn, trim($_POST['username'] ?? ''));
    $p = $_POST['password'] ?? '';
    $res = mysqli_query($conn, "SELECT * FROM users WHERE Email='$u' LIMIT 1");
    $user = mysqli_fetch_assoc($res);
    if ($user && password_verify($p, $user['Password'])) {
        $_SESSION['user_id'] = $user['UserID'];
        $_SESSION['role']    = $user['Role'];
        header("Location: index.php");
        exit();
    } else {
        $msg = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Sign In — SitePro CE</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  :root{--bg:#eef2f7;--surface:#fff;--border:#dde3ec;--accent:#1a5c96;--accent-light:#e8f0f9;--text:#1a2332;--muted:#6b7a90;--danger:#c0392b;--gold:#f0a500;--header-bg:#1a2d4a;}
  *{margin:0;padding:0;box-sizing:border-box;}
  body{background:var(--bg);min-height:100vh;display:flex;flex-direction:column;font-family:'DM Sans',sans-serif;color:var(--text);}
  .topbar{background:var(--header-bg);height:56px;display:flex;align-items:center;padding:0 32px;box-shadow:0 2px 8px rgba(0,0,0,.2);}
  .brand{font-family:'Inter',sans-serif;font-weight:800;font-size:16px;color:#fff;display:flex;align-items:center;gap:10px;text-decoration:none;}
  .brand span{background:var(--gold);color:#1a2d4a;border-radius:4px;padding:2px 9px;font-size:12px;font-weight:700;}
  .main{flex:1;display:flex;align-items:center;justify-content:center;padding:40px 20px;}
  .box{width:100%;max-width:420px;animation:fadeUp .4s ease both;}
  @keyframes fadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
  .logo{text-align:center;margin-bottom:28px;}
  .logo h1{font-family:'Inter',sans-serif;font-size:26px;font-weight:800;color:var(--header-bg);}
  .logo p{font-size:12px;color:var(--muted);margin-top:6px;}
  .logo .blueprint-icon{font-size:48px;margin-bottom:10px;}
  .card{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:32px;box-shadow:0 2px 16px rgba(0,0,0,.07);}
  .card h2{font-family:'Inter',sans-serif;font-size:17px;font-weight:700;margin-bottom:4px;}
  .card .sub{font-size:12px;color:var(--muted);margin-bottom:24px;}
  .alert-danger{background:#fce9e8;border:1px solid #f0b0ab;border-radius:8px;padding:11px 16px;font-size:13px;color:var(--danger);margin-bottom:18px;}
  .field{margin-bottom:16px;}
  .field label{display:block;font-size:11px;font-weight:600;letter-spacing:.5px;text-transform:uppercase;color:var(--muted);margin-bottom:6px;}
  .field input{width:100%;background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:10px 14px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:14px;outline:none;transition:border-color .2s,box-shadow .2s;}
  .field input:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(26,92,150,.1);}
  .btn{width:100%;padding:12px;border-radius:9px;background:var(--accent);border:none;color:#fff;font-family:'Inter',sans-serif;font-size:14px;font-weight:600;cursor:pointer;transition:background .2s;}
  .btn:hover{background:#154d80;}
  .footer-link{text-align:center;margin-top:20px;font-size:13px;color:var(--muted);}
  .footer-link a{color:var(--accent);text-decoration:none;font-weight:500;}
  .footer-link a:hover{text-decoration:underline;}
  .divider{display:flex;align-items:center;gap:10px;margin:20px 0;color:var(--muted);font-size:11px;}
  .divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--border);}
</style>
</head>
<body>
<div class="topbar">
  <a href="landing.php" class="brand"><span>CE</span> SitePro</a>
</div>
<div class="main">
  <div class="box">
    <div class="logo">
      <div class="blueprint-icon">&#127959;</div>
      <h1>SitePro CE</h1>
      <p>Civil Engineering Project &amp; Carbon Management</p>
    </div>
    <div class="card">
      <h2>Sign In</h2>
      <div class="sub">Access your project workspace</div>
      <?php if($msg):?><div class="alert-danger">&#9888; <?=htmlspecialchars($msg)?></div><?php endif;?>
      <form method="POST">
        <div class="field"><label>Email Address</label><input name="username" type="email" placeholder="you@organisation.com" required autocomplete="email"></div>
        <div class="field"><label>Password</label><input name="password" type="password" placeholder="&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;" required autocomplete="current-password"></div>
        <button class="btn" type="submit">Sign In &#8594;</button>
      </form>
      <div class="divider">or</div>
      <div class="footer-link">Don't have an account? <a href="register.php">Register here</a></div>
    </div>
  </div>
</div>
</body></html>
