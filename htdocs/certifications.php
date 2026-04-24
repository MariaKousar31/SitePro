
<?php include 'init.php'; ?>

<?php
// Single source of truth — update here, everywhere updates
define('CERT_STATUSES', ['Applied', 'In Review', 'Awarded', 'Expired']);

$msg = '';
if (isset($_POST['update_status'])) {
    $pid = (int)($_POST['pid'] ?? 0);
    $cid = (int)($_POST['cid'] ?? 0);
    $st  = trim($_POST['status'] ?? '');

    // Whitelist check against the single source of truth
if (!in_array($st, CERT_STATUSES, true)) {
    header("Location: certifications.php?error=invalid_status");
    exit;
}

    $stmt = mysqli_prepare($conn,
        "UPDATE projectcertifications
         SET Status = ?
         WHERE ProjectID = ? AND CertID = ? AND user_id = ?"
    );

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "siii", $st, $pid, $cid, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header("Location: certifications.php?updated=1");
    exit;
}
// Award certification to project
if ($_POST && isset($_POST['cert_award'])) {
    $pid = (int)$_POST['project'];
    $cid = (int)$_POST['cert'];
    $ad  = $_POST['awarded'] ?? '';
    $ex  = $_POST['expiry']  ?? '';
    $sc  = (float)($_POST['score'] ?? 0);
    $st  = $_POST['status']  ?? 'Applied';

    $allowed_status = ['Applied', 'In Progress', 'Certified', 'Expired'];
    if (!in_array($st, $allowed_status)) {$st = 'Applied';}

    $stmt = mysqli_prepare($conn,
        "INSERT INTO projectcertifications
             (ProjectID, CertID, AwardedDate, ExpiryDate, Score, Status, user_id)
         VALUES (?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE Status = VALUES(Status), Score = VALUES(Score)"
    );

    mysqli_stmt_bind_param($stmt, 'iissdsi', $pid, $cid, $ad, $ex, $sc, $st, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $msg = 'success';
}
if (isset($_GET['delete'])) {
    $p = (int)($_GET['p'] ?? 0);
    $c = (int)($_GET['c'] ?? 0);

    $stmt = mysqli_prepare($conn,
        "DELETE FROM projectcertifications
         WHERE ProjectID = ? AND CertID = ? AND user_id = ?"
    );

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iii", $p, $c, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header("Location: certifications.php?deleted=1");
    exit;
}
$projects=mysqli_query($conn,"SELECT ProjectID,ProjectName FROM projects WHERE user_id=$user_id ORDER BY ProjectName");
$certs=mysqli_query($conn,"SELECT * FROM certifications ORDER BY CertificationType");
$links=mysqli_query($conn,"SELECT pc.*,p.ProjectName as PName,c.CertificationType,c.IssuingBody FROM projectcertifications pc JOIN projects p ON pc.ProjectID=p.ProjectID JOIN certifications c ON pc.CertID=c.CertificationID WHERE pc.user_id=$user_id ORDER BY pc.AwardedDate DESC");
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Certifications — SitePro CE</title>
<link rel="stylesheet" href="/css/fonts.css">
<style>
  :root{--bg:#f0f4f8;--surface:#ffffff;--surface2:#f7f9fc;--border:#dde3ec;--accent:#1a5c96;--accent-light:#e8f0f9;--accent-glow:rgba(26,92,150,0.12);--text:#1a2332;--muted:#6b7a90;--danger:#c0392b;--success:#1a7a4a;--header-bg:#1a2d4a;--gold:#f0a500;}
  *{margin:0;padding:0;box-sizing:border-box;}
  body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;min-height:100vh;}
  body::before{display:none}
  .layout{position:relative;z-index:1;max-width:1200px;margin:0 auto;padding:40px 24px;}
  .topbar{display:flex;align-items:center;gap:16px;margin-bottom:40px;}
  .back-btn{display:inline-flex;align-items:center;gap:8px;background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:10px 18px;text-decoration:none;color:var(--muted);font-size:12px;letter-spacing:1px;transition:all .3s;}
  .back-btn:hover{border-color:var(--accent);color:var(--accent);}
  .page-title{font-family:'Syne',sans-serif;font-size:28px;font-weight:800;}.page-title span{color:var(--accent);}
  .breadcrumb{font-size:11px;color:var(--muted);margin-left:auto;}
  .alert{border-radius:12px;padding:14px 20px;font-size:13px;margin-bottom:24px;}
  .alert-success{background:rgba(46,196,182,.1);border:1px solid rgba(46,196,182,.3);color:var(--accent);}
  .alert-danger{background:rgba(255,77,109,.1);border:1px solid rgba(255,77,109,.3);color:var(--danger);}
  .two-col{display:grid;grid-template-columns:380px 1fr;gap:24px;align-items:start;margin-bottom:28px;}
  @media(max-width:900px){.two-col{grid-template-columns:1fr;}}
  .card{background:var(--surface);border:1px solid var(--border);border-radius:12px;overflow:hidden;}
  .card-header{padding:18px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;}
  .card-header h3{font-family:'Syne',sans-serif;font-size:15px;font-weight:700;}
  .card-body{padding:24px;}
  .field{margin-bottom:15px;}
  .field label{display:block;font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-bottom:6px;}
  .field input,.field select{width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:11px 14px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:13px;outline:none;transition:border-color .3s;}
  .field input:focus,.field select:focus{border-color:var(--accent);}
  .field select option{background:#071414;}
  .btn-primary{width:100%;padding:12px;border-radius:12px;background:var(--accent);border:none;color:#fff;font-family:'Syne',sans-serif;font-size:13px;font-weight:700;cursor:pointer;transition:all .3s;}
  .btn-primary:hover{transform:translateY(-2px);}
  table{width:100%;border-collapse:separate;border-spacing:0;}
  thead th{background:var(--surface2);padding:12px 16px;font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--muted);text-align:left;font-weight:400;border-bottom:1px solid var(--border);}
  tbody tr:hover{background:#f4f7fb;}
  td{padding:13px 16px;font-size:12px;border-bottom:1px solid rgba(26,58,58,.5);}
  .id-badge{background:var(--surface2);border:1px solid var(--border);border-radius:6px;padding:3px 10px;font-size:11px;color:var(--muted);}
  .status-awarded{background:rgba(0,229,176,.12);border:1px solid rgba(26,122,74,.3);border-radius:6px;padding:3px 10px;font-size:10px;color:var(--success);}
  .status-applied{background:rgba(77,158,255,.12);border:1px solid rgba(77,158,255,.3);border-radius:6px;padding:3px 10px;font-size:10px;color:#4d9eff;}
  .status-review{background:rgba(255,209,102,.12);border:1px solid rgba(255,209,102,.3);border-radius:6px;padding:3px 10px;font-size:10px;color:#ffd166;}
  .status-expired{background:rgba(255,77,109,.12);border:1px solid rgba(255,77,109,.3);border-radius:6px;padding:3px 10px;font-size:10px;color:var(--danger);}
  .del-btn{background:#fce9e8;border:1px solid #f0b0ab;border-radius:8px;padding:5px 12px;color:var(--danger);font-size:11px;cursor:pointer;text-decoration:none;display:inline-block;}
  .table-wrap{overflow-x:auto;}
  .empty-state{text-align:center;padding:50px;color:var(--muted);}
  .cert-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;margin-bottom:20px;}
  .cert-card{background:var(--surface2);border:1px solid var(--border);border-radius:12px;padding:14px;text-align:center;}
  .cert-card .cert-name{font-family:'Syne',sans-serif;font-size:13px;font-weight:700;color:var(--text);margin-bottom:4px;}
  .cert-card .cert-body{font-size:10px;color:var(--muted);}
  @keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}

  /* ---- Civil Eng Nav ---- */
  .topnav{background:var(--header-bg);padding:0 28px;display:flex;align-items:center;height:54px;box-shadow:0 2px 8px rgba(0,0,0,.18);margin-bottom:0;}
  .topnav .brand{font-family:'Inter',sans-serif;font-weight:700;font-size:15px;color:#fff;display:flex;align-items:center;gap:9px;text-decoration:none;}
  .topnav .brand .badge{background:var(--gold);color:#1a2d4a;border-radius:4px;padding:2px 8px;font-size:12px;font-weight:700;}
  .topnav .back-btn{margin-left:18px;display:inline-flex;align-items:center;gap:5px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);border-radius:6px;padding:5px 13px;text-decoration:none;color:rgba(255,255,255,.85);font-size:12px;font-weight:500;transition:background .2s;}
  .topnav .back-btn:hover{background:rgba(255,255,255,.2);}
  .topnav .bc{margin-left:auto;font-size:11px;color:rgba(255,255,255,.45);letter-spacing:1px;}

</style>
</head>
<body>
<div class="layout">
  <nav class="topnav">
    <a href="index.php" class="brand"><span class="badge">CE</span> SitePro</a>
  <a href="index.php" class="back-btn">&#8592; Dashboard</a>
    <div class="page-title">Project <span>Certifications</span></div>
    <div class="bc">HOME / CERTIFICATIONS</div>
  </nav>
  <?php if($msg==='success'):?><div class="alert alert-success">✓ Certification record saved</div><?php endif;?>
  <?php if(isset($_GET['deleted'])):?><div class="alert alert-danger">Record removed.</div><?php endif;?>

  <!-- Available Certs -->
  <div style="margin-bottom:28px">
    <div style="font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-bottom:14px">Available Green Building Standards</div>
    <div class="cert-grid">
      <?php mysqli_data_seek($certs,0);while($r=mysqli_fetch_assoc($certs)):?>
      <div class="cert-card">
        <div class="cert-name">🏅 <?=htmlspecialchars($r['CertificationType'])?></div>
        <div class="cert-body"><?=htmlspecialchars($r['IssuingBody'])?><br></div>
      </div>
      <?php endwhile;?>
    </div>
  </div>

  <div class="two-col">
    <div class="card" style="animation:fadeUp .5s ease both">
      <div class="card-header"><span>🏅</span><h3>Apply / Award Certification</h3></div>
      <div class="card-body">
        <form method="POST">
          <input type="hidden" name="cert_award" value="1">

          <div class="field">
            <label for="cert-project">Project</label>
            <select id="cert-project" name="project" required>
              <option value="">— Select Project —</option>
              <?php mysqli_data_seek($projects,0);while($r=mysqli_fetch_assoc($projects)):?>
              <option value="<?=$r['ProjectID']?>"><?=htmlspecialchars($r['ProjectName'])?></option>
              <?php endwhile;?>
            </select>
          </div>

          <div class="field">
            <label for="cert-type">Certification</label>
            <select id="cert-type" name="cert" required>
              <option value="">— Select —</option>
              <?php mysqli_data_seek($certs,0);while($r=mysqli_fetch_assoc($certs)):?>
              <option value="<?=$r['CertificationID']?>">
                <?=htmlspecialchars($r['CertificationType'])?> (<?=htmlspecialchars($r['IssuingBody'])?>)
              </option>
              <?php endwhile;?>
            </select>
          </div>

          <div class="field">
            <label for="cert-awarded">Awarded Date</label>
            <input id="cert-awarded" name="awarded" type="date">
          </div>

          <div class="field">
            <label for="cert-expiry">Expiry Date</label>
            <input id="cert-expiry" name="expiry" type="date">
          </div>

          <div class="field">
            <label for="cert-score">Score / Points</label>
            <input id="cert-score" name="score" type="number" step="0.01" placeholder="85.0">
          </div>

          <div class="field">
            <label for="cert-status">Status</label>
            <select id="cert-status" name="status">
              <option value="Applied">Applied</option>
              <option value="In Review">In Review</option>
              <option value="Awarded">Awarded</option>
              <option value="Expired">Expired</option>
            </select>
          </div>

          <button class="btn-primary" type="submit">SAVE CERTIFICATION</button>
        </form>
      </div>
    </div>

    <div class="card" style="animation:fadeUp .5s ease .15s both">
      <div class="card-header"><span>📋</span><h3>Certification Records</h3></div>
      <div class="table-wrap">
        <?php if(mysqli_num_rows($links)===0):?>
          <div class="empty-state">No certifications recorded yet.</div>
        <?php else:?>
        <table>
          <thead>
            <tr>
              <th>Project</th><th>Certification</th><th>Issuing Body</th>
              <th>Score</th><th>Status</th><th>Awarded</th><th>Expiry</th><th>Action</th>
            </tr>
          </thead>
          <tbody>
          <?php while($r=mysqli_fetch_assoc($links)):
$statusClasses = [
    CERT_STATUSES[0] => 'status-applied',
    CERT_STATUSES[1] => 'status-review',
    CERT_STATUSES[2] => 'status-awarded',
    CERT_STATUSES[3] => 'status-expired'
];

$cls = $statusClasses[$st] ?? 'status-applied';
          <tr>
            <td><?=htmlspecialchars($r['PName'])?></td>
            <td style="font-weight:600"><?=htmlspecialchars($r['CertificationType'])?></td>
            <td style="color:var(--muted);font-size:11px"><?=htmlspecialchars($r['IssuingBody'])?></td>
            <td style="color:var(--accent)"><?= isset($r['Score']) ? htmlspecialchars($r['Score']) : '—' ?></td>
            <td>
              <form method="POST" style="display:flex;gap:6px;align-items:center;">
                <input type="hidden" name="pid" value="<?=(int)$r['ProjectID']?>">
                <input type="hidden" name="cid" value="<?=(int)$r['CertID']?>">
                <label for="status-<?=(int)$r['ProjectID']?>-<?=(int)$r['CertID']?>" class="sr-only">Status</label>
                <select
                  id="status-<?=(int)$r['ProjectID']?>-<?=(int)$r['CertID']?>"
                  name="status"
                  style="background:var(--surface2);border:1px solid var(--border);color:var(--text);padding:5px;border-radius:6px;font-size:11px;">
                  <option value="Applied"   <?= $st==='Applied'   ? 'selected' : '' ?>>Applied</option>
                  <option value="In Review" <?= $st==='In Review' ? 'selected' : '' ?>>In Review</option>
                  <option value="Awarded"   <?= $st==='Awarded'   ? 'selected' : '' ?>>Awarded</option>
                  <option value="Expired"   <?= $st==='Expired'   ? 'selected' : '' ?>>Expired</option>
                </select>
                <button type="submit" name="update_status"
                  style="background:var(--accent);border:none;color:#fff;padding:5px 8px;border-radius:6px;font-size:10px;cursor:pointer;">
                  Save
                </button>
              </form>
            </td>
            <td style="color:var(--muted)"><?=htmlspecialchars($r['AwardedDate'] ?? '—')?></td>
            <td style="color:var(--muted)"><?=htmlspecialchars($r['ExpiryDate']  ?? '—')?></td>
            <td>
              <a href="?delete=1&p=<?=(int)$r['ProjectID']?>&c=<?=(int)$r['CertID']?>"
                 class="del-btn"
                 onclick="return confirm('Remove?')">Remove</a>
            </td>
          </tr>
          <?php endwhile;?>
          </tbody>
        </table>
        <?php endif;?>
      </div>
    </div>
  </div>
</div>
</body>
</html>
