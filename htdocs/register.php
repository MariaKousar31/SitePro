<?php
session_start();
include 'db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$msg = '';
$msgType = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u  = mysqli_real_escape_string($conn, trim($_POST['username'] ?? ''));
    $p  = $_POST['password'] ?? '';
    $p2 = $_POST['password2'] ?? '';

    if (strlen($u) < 5) {
        $msg = "Please enter a valid email address."; $msgType = 'danger';
    } elseif ($p !== $p2) {
        $msg = "Passwords do not match."; $msgType = 'danger';
    } elseif (strlen($p) < 6) {
        $msg = "Password must be at least 6 characters."; $msgType = 'danger';
    } else {
        $check = mysqli_query($conn, "SELECT UserID FROM users WHERE Email='$u' LIMIT 1");
        if (mysqli_num_rows($check) > 0) {
            $msg = "This email is already registered."; $msgType = 'danger';
        } else {
            $hash = password_hash($p, PASSWORD_DEFAULT);
            $res  = mysqli_query($conn, "INSERT INTO users (Email, Password, Role) VALUES ('$u','$hash','user')");
            if ($res) {
                $msg = "Account created! You can now sign in."; $msgType = 'success';
            } else {
                $msg = "Registration error: " . mysqli_error($conn); $msgType = 'danger';
            }
        }
    }
}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Register — SitePro CE</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  :root{--bg:#eef2f7;--surface:#fff;--border:#dde3ec;--accent:#1a5c96;--text:#1a2332;--muted:#6b7a90;--danger:#c0392b;--success:#1a7a4a;--gold:#f0a500;--header-bg:#1a2d4a;}
  *{margin:0;padding:0;box-sizing:border-box;}
  body{background:var(--bg);min-height:100vh;display:flex;flex-direction:column;font-family:'DM Sans',sans-serif;color:var(--text);}
  .topbar{background:var(--header-bg);height:56px;display:flex;align-items:center;padding:0 32px;box-shadow:0 2px 8px rgba(0,0,0,.2);}
  .brand{font-family:'Inter',sans-serif;font-weight:800;font-size:16px;color:#fff;display:flex;align-items:center;gap:10px;text-decoration:none;}
  .brand span{background:var(--gold);color:#1a2d4a;border-radius:4px;padding:2px 9px;font-size:12px;font-weight:700;}
  .main{flex:1;display:flex;align-items:center;justify-content:center;padding:40px 20px;}
  .box{width:100%;max-width:420px;animation:fadeUp .4s ease both;}
  @keyframes fadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
  .logo{text-align:center;margin-bottom:24px;}
  .logo h1{font-family:'Inter',sans-serif;font-size:24px;font-weight:800;color:var(--header-bg);}
  .logo p{font-size:12px;color:var(--muted);margin-top:6px;}
  .card{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:32px;box-shadow:0 2px 16px rgba(0,0,0,.07);}
  .card h2{font-family:'Inter',sans-serif;font-size:17px;font-weight:700;margin-bottom:4px;}
  .card .sub{font-size:12px;color:var(--muted);margin-bottom:22px;}
  .alert{border-radius:8px;padding:11px 16px;font-size:13px;margin-bottom:18px;}
  .alert-danger{background:#fce9e8;border:1px solid #f0b0ab;color:var(--danger);}
  .alert-success{background:#e6f4ec;border:1px solid #a3d4b5;color:var(--success);}
  .field{margin-bottom:15px;}
  .field label{display:block;font-size:11px;font-weight:600;letter-spacing:.5px;text-transform:uppercase;color:var(--muted);margin-bottom:6px;}
  .field input{width:100%;background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:10px 14px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:14px;outline:none;transition:border-color .2s,box-shadow .2s;}
  .field input:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(26,92,150,.1);}
  .btn{width:100%;padding:12px;border-radius:9px;background:var(--accent);border:none;color:#fff;font-family:'Inter',sans-serif;font-size:14px;font-weight:600;cursor:pointer;transition:background .2s;}
  .btn:hover{background:#154d80;}
  .footer-link{text-align:center;margin-top:18px;font-size:13px;color:var(--muted);}
  .footer-link a{color:var(--accent);text-decoration:none;font-weight:500;}
  .footer-link a:hover{text-decoration:underline;}
</style>
</head>
<body>
<div class="topbar">
  <a href="landing.php" class="brand"><span>CE</span> SitePro</a>
</div>
<div class="main">
  <div class="box">
    <div class="logo">
      <h1>&#127959; Create Account</h1>
      <p>Register for SitePro CE — Civil Engineering Management</p>
    </div>
    <div class="card">
      <h2>New Account</h2>
      <div class="sub">Enter your details to get started</div>
      <?php if($msg):?><div class="alert alert-<?=$msgType?>"><?=htmlspecialchars($msg)?></div><?php endif;?>
      <?php if($msgType!=='success'):?>
      <form method="POST">
        <div class="field"><label>Email Address</label><input name="username" type="email" placeholder="you@organisation.com" required autocomplete="email"></div>
        <div class="field"><label>Password</label><input name="password" type="password" placeholder="Min. 6 characters" required autocomplete="new-password"></div>
        <div class="field"><label>Confirm Password</label><input name="password2" type="password" placeholder="Repeat password" required autocomplete="new-password"></div>
        <button class="btn" type="submit">Create Account &#8594;</button>
      </form>
      <?php endif;?>
      <div class="footer-link">Already have an account? <a href="login.php">Sign in</a></div>
    </div>
  </div>
</div>
</body></html>
