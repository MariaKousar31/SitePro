<?php include 'init.php'; ?>
<?php
$msg='';

// ── Update status (prepared statement, whitelisted values) ──────
if (isset($_POST['update_status'])) {
    $pid = (int)($_POST['pid'] ?? 0);
    $cid = (int)($_POST['cid'] ?? 0);
    $st  = trim($_POST['status'] ?? '');
    $allowed = ['Applied','In Review','Awarded','Expired'];
    if (!in_array($st, $allowed, true)) {
        header("Location: certifications.php?error=invalid_status"); exit;
    }
    $stmt = mysqli_prepare($conn,
        "UPDATE projectcertifications SET Status=? WHERE ProjectID=? AND CertID=? AND user_id=?"
    );
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "siii", $st, $pid, $cid, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header("Location: certifications.php?updated=1"); exit;
}

// ── Award / insert certification (prepared statement) ───────────
if ($_POST && isset($_POST['cert_award'])) {
    $pid = (int)($_POST['project'] ?? 0);
    $cid = (int)($_POST['cert']    ?? 0);
    $ad  = $_POST['awarded'] ?? '';
    $ex  = $_POST['expiry']  ?? '';
    $sc  = (float)($_POST['score'] ?? 0);
    $st  = trim($_POST['status'] ?? 'Applied');
    $allowed = ['Applied','In Review','Awarded','Expired'];
    if (!in_array($st, $allowed, true)) $st = 'Applied';

    $stmt = mysqli_prepare($conn,
        "INSERT INTO projectcertifications (ProjectID,CertID,AwardedDate,ExpiryDate,Score,Status,user_id)
         VALUES (?,?,?,?,?,?,?)
         ON DUPLICATE KEY UPDATE Status=VALUES(Status), Score=VALUES(Score)"
    );
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iissdsi", $pid, $cid, $ad, $ex, $sc, $st, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    $msg = 'success';
}

// ── Delete (prepared statement) ─────────────────────────────────
if (isset($_GET['delete'])) {
    $p = (int)($_GET['p'] ?? 0);
    $c = (int)($_GET['c'] ?? 0);
    $stmt = mysqli_prepare($conn,
        "DELETE FROM projectcertifications WHERE ProjectID=? AND CertID=? AND user_id=?"
    );
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iii", $p, $c, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header("Location: certifications.php?deleted=1"); exit;
}

// ── Queries (user-scoped) ───────────────────────────────────────
$projects = mysqli_query($conn,
    "SELECT ProjectID,ProjectName FROM projects WHERE user_id=$user_id ORDER BY ProjectName");
$certs = mysqli_query($conn,
    "SELECT * FROM certifications ORDER BY CertificationType");
$links = mysqli_query($conn,
    "SELECT pc.*,p.ProjectName AS PName,c.CertificationType,c.IssuingBody
     FROM projectcertifications pc
     JOIN projects p ON pc.ProjectID=p.ProjectID
     JOIN certifications c ON pc.CertID=c.CertificationID
     WHERE pc.user_id=$user_id
     ORDER BY pc.AwardedDate DESC");
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Certifications — SitePro CE</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  :root{
    --bg:#f0f4f8;--surface:#ffffff;--surface2:#f7f9fc;--border:#dde3ec;
    --accent:#1a5c96;--accent-light:#e8f0f9;--accent-glow:rgba(26,92,150,0.12);
    --text:#1a2332;--muted:#6b7a90;--danger:#c0392b;--success:#1a7a4a;
    --header-bg:#1a2d4a;--gold:#f0a500;--green:#2e7d32;
  }
  *{margin:0;padding:0;box-sizing:border-box;}
  body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;min-height:100vh;}

  /* ── Nav ── */
  .topnav{background:var(--header-bg);padding:0 32px;display:flex;align-items:center;height:56px;box-shadow:0 2px 8px rgba(0,0,0,.18);}
  .topnav .brand{font-family:'Inter',sans-serif;font-weight:700;font-size:16px;color:#fff;display:flex;align-items:center;gap:10px;text-decoration:none;}
  .topnav .brand .badge{background:var(--gold);color:#1a2d4a;border-radius:4px;padding:2px 9px;font-size:12px;font-weight:700;}
  .topnav .back-btn{margin-left:20px;display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);border-radius:6px;padding:6px 14px;text-decoration:none;color:rgba(255,255,255,.85);font-size:12px;font-weight:500;transition:background .2s;}
  .topnav .back-btn:hover{background:rgba(255,255,255,.2);}
  .topnav .bc{margin-left:auto;font-size:11px;color:rgba(255,255,255,.45);letter-spacing:1px;}

  /* ── Layout ── */
  .layout{max-width:1240px;margin:0 auto;padding:30px 24px;}
  .page-header{margin-bottom:24px;}
  .page-header h1{font-family:'Inter',sans-serif;font-size:22px;font-weight:700;display:flex;align-items:center;gap:10px;}
  .page-header h1 .icon{background:#00695c;color:#fff;width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:18px;}
  .page-header p{font-size:13px;color:var(--muted);margin-top:4px;}

  /* ── Alerts ── */
  .alert{border-radius:8px;padding:12px 18px;font-size:13px;margin-bottom:20px;}
  .alert-success{background:#e6f4ec;border:1px solid #a3d4b5;color:var(--success);}
  .alert-danger{background:#fce9e8;border:1px solid #f0b0ab;color:var(--danger);}
  .alert-warn{background:#fff8e1;border:1px solid #ffe082;color:#e65100;}

  /* ── Standards grid ── */
  .standards-label{font-size:11px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--muted);margin-bottom:12px;}
  .cert-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px;margin-bottom:26px;}
  .cert-tile{background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:14px 12px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.04);}
  .cert-tile .ct-icon{font-size:22px;margin-bottom:6px;}
  .cert-tile .ct-name{font-family:'Inter',sans-serif;font-size:12px;font-weight:700;color:var(--text);margin-bottom:3px;}
  .cert-tile .ct-body{font-size:10px;color:var(--muted);}

  /* ── Two column ── */
  .two-col{display:grid;grid-template-columns:370px 1fr;gap:22px;align-items:start;}
  @media(max-width:900px){.two-col{grid-template-columns:1fr;}}

  /* ── Card ── */
  .card{background:var(--surface);border:1px solid var(--border);border-radius:12px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06);}
  .card-header{padding:15px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;background:var(--surface2);}
  .card-header h3{font-family:'Inter',sans-serif;font-size:14px;font-weight:600;color:var(--text);}
  .card-body{padding:22px;}

  /* ── Form fields ── */
  .field{margin-bottom:14px;}
  .field label{display:block;font-size:11px;font-weight:600;letter-spacing:.5px;text-transform:uppercase;color:var(--muted);margin-bottom:5px;}
  .field input,.field select{width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:7px;padding:9px 13px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:13px;outline:none;transition:border-color .2s,box-shadow .2s;}
  .field input:focus,.field select:focus{border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-glow);}
  .field .hint{font-size:10px;color:var(--muted);margin-top:3px;}
  .two-fields{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
  .btn-primary{width:100%;padding:11px;border-radius:8px;background:var(--accent);border:none;color:#fff;font-family:'Inter',sans-serif;font-size:13px;font-weight:600;cursor:pointer;transition:all .2s;}
  .btn-primary:hover{background:#154d80;box-shadow:0 4px 14px var(--accent-glow);}

  /* ── Table ── */
  .table-wrap{overflow-x:auto;}
  table{width:100%;border-collapse:collapse;}
  thead th{background:var(--surface2);padding:10px 14px;font-size:10px;letter-spacing:1px;text-transform:uppercase;color:var(--muted);text-align:left;font-weight:600;border-bottom:2px solid var(--border);}
  tbody tr:hover{background:#f4f7fb;}
  td{padding:11px 14px;font-size:13px;border-bottom:1px solid var(--border);vertical-align:middle;}
  .row-num{background:var(--accent-light);color:var(--accent);border-radius:5px;padding:2px 8px;font-size:11px;font-weight:600;}

  /* ── Status badges ── */
  .s-awarded{background:#e8f5e9;border:1px solid #a5d6a7;border-radius:5px;padding:3px 9px;font-size:11px;font-weight:600;color:var(--green);}
  .s-applied{background:#e3f2fd;border:1px solid #90caf9;border-radius:5px;padding:3px 9px;font-size:11px;font-weight:600;color:#1565c0;}
  .s-review{background:#fff8e1;border:1px solid #ffe082;border-radius:5px;padding:3px 9px;font-size:11px;font-weight:600;color:#f57f17;}
  .s-expired{background:#fce9e8;border:1px solid #f0b0ab;border-radius:5px;padding:3px 9px;font-size:11px;font-weight:600;color:var(--danger);}

  /* ── Inline status form ── */
  .status-form{display:flex;gap:6px;align-items:center;}
  .status-form select{background:var(--surface2);border:1px solid var(--border);color:var(--text);padding:4px 8px;border-radius:6px;font-size:11px;font-family:'DM Sans',sans-serif;outline:none;}
  .status-form select:focus{border-color:var(--accent);}
  .save-btn{background:var(--accent);border:none;padding:5px 10px;border-radius:6px;font-size:10px;font-weight:600;color:#fff;cursor:pointer;font-family:'Inter',sans-serif;white-space:nowrap;}
  .save-btn:hover{background:#154d80;}

  /* ── Delete button ── */
  .del-btn{background:#fce9e8;border:1px solid #f0b0ab;border-radius:6px;padding:4px 11px;color:var(--danger);font-size:11px;font-weight:500;cursor:pointer;text-decoration:none;display:inline-block;transition:all .2s;white-space:nowrap;}
  .del-btn:hover{background:#f5c2be;}

  .empty-state{text-align:center;padding:50px;color:var(--muted);}
  .empty-state .ei{font-size:36px;margin-bottom:10px;}

  @keyframes fadeUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
</style>
</head>
<body>

<nav class="topnav">
  <a href="index.php" class="brand"><span class="badge">CE</span> SitePro</a>
  <a href="index.php" class="back-btn">&#8592; Dashboard</a>
  <div class="bc">DASHBOARD / CERTIFICATIONS</div>
</nav>

<div class="layout">

  <div class="page-header">
    <h1><span class="icon">&#127941;</span> Project Certifications</h1>
    <p>Track LEED, BREEAM, EDGE, WELL and other green building certifications per project.</p>
  </div>

  <?php if($msg==='success'):?>
    <div class="alert alert-success">&#10004; Certification record saved successfully.</div>
  <?php endif;?>
  <?php if(isset($_GET['updated'])):?>
    <div class="alert alert-success">&#10004; Status updated.</div>
  <?php endif;?>
  <?php if(isset($_GET['deleted'])):?>
    <div class="alert alert-danger">&#10006; Certification record removed.</div>
  <?php endif;?>
  <?php if(isset($_GET['error'])):?>
    <div class="alert alert-warn">&#9888; Invalid status value submitted.</div>
  <?php endif;?>

  <!-- Available Standards -->
  <div class="standards-label">Recognised Green Building Standards</div>
  <div class="cert-grid">
    <?php
    $cert_icons = ['LEED'=>'🌿','BREEAM'=>'🏅','EDGE'=>'⚡','WELL'=>'💧','Green Star'=>'⭐','ISO 14001'=>'🔰'];
    mysqli_data_seek($certs,0);
    while($r=mysqli_fetch_assoc($certs)):
      $icon = $cert_icons[$r['CertificationType']] ?? '🏛️';
    ?>
    <div class="cert-tile">
      <div class="ct-icon"><?=$icon?></div>
      <div class="ct-name"><?=htmlspecialchars($r['CertificationType'])?></div>
      <div class="ct-body"><?=htmlspecialchars($r['IssuingBody']??'')?></div>
    </div>
    <?php endwhile;?>
  </div>

  <div class="two-col">

    <!-- ── Form ── -->
    <div class="card" style="animation:fadeUp .4s ease both">
      <div class="card-header"><span>&#127941;</span><h3>Apply / Award Certification</h3></div>
      <div class="card-body">
        <form method="POST">
          <input type="hidden" name="cert_award" value="1">

          <div class="field">
            <label>Project</label>
            <select name="project" required>
              <option value="">— Select Project —</option>
              <?php mysqli_data_seek($projects,0);while($r=mysqli_fetch_assoc($projects)):?>
              <option value="<?=$r['ProjectID']?>"><?=htmlspecialchars($r['ProjectName'])?></option>
              <?php endwhile;?>
            </select>
          </div>

          <div class="field">
            <label>Certification Standard</label>
            <select name="cert" required>
              <option value="">— Select Standard —</option>
              <?php mysqli_data_seek($certs,0);while($r=mysqli_fetch_assoc($certs)):?>
              <option value="<?=$r['CertificationID']?>">
                <?=htmlspecialchars($r['CertificationType'])?> — <?=htmlspecialchars($r['IssuingBody']?:'')?>
              </option>
              <?php endwhile;?>
            </select>
          </div>

          <div class="two-fields">
            <div class="field">
              <label>Awarded Date</label>
              <input name="awarded" type="date"
                     min="2000-01-01"
                     max="<?=date('Y-m-d')?>">
              <div class="hint">Cannot be a future date</div>
            </div>
            <div class="field">
              <label>Expiry Date</label>
              <input name="expiry" type="date"
                     min="<?=date('Y-m-d')?>"
                     max="2099-12-31">
              <div class="hint">Must be today or later</div>
            </div>
          </div>

          <div class="field">
            <label>Score / Points</label>
            <input name="score" type="number" step="0.01" min="0" max="1000" placeholder="e.g. 85.0">
          </div>

          <div class="field">
            <label>Initial Status</label>
            <select name="status">
              <option value="Applied">Applied</option>
              <option value="In Review">In Review</option>
              <option value="Awarded">Awarded</option>
              <option value="Expired">Expired</option>
            </select>
          </div>

          <button class="btn-primary" type="submit">+ Save Certification</button>
        </form>
      </div>
    </div>

    <!-- ── Table ── -->
    <div class="card" style="animation:fadeUp .4s ease .1s both">
      <div class="card-header"><span>&#128203;</span><h3>My Certification Records</h3></div>
      <div class="table-wrap">
        <?php if(mysqli_num_rows($links)===0):?>
          <div class="empty-state">
            <div class="ei">&#127941;</div>
            <p>No certifications recorded yet.</p>
          </div>
        <?php else:?>
        <table>
          <thead>
            <tr>
              <th>#</th><th>Project</th><th>Standard</th><th>Body</th>
              <th>Score</th><th>Status</th><th>Awarded</th><th>Expiry</th><th>Remove</th>
            </tr>
          </thead>
          <tbody>
          <?php $seq=1; while($r=mysqli_fetch_assoc($links)):
            $st  = $r['Status'];
            $cls = match($st){
              'Awarded'   => 's-awarded',
              'Expired'   => 's-expired',
              'In Review' => 's-review',
              default     => 's-applied',
            };
          ?>
          <tr>
            <td><span class="row-num"><?=$seq++?></span></td>
            <td style="font-weight:500"><?=htmlspecialchars($r['PName'])?></td>
            <td style="font-weight:600;color:var(--accent)"><?=htmlspecialchars($r['CertificationType'])?></td>
            <td style="color:var(--muted);font-size:11px"><?=htmlspecialchars($r['IssuingBody']??'')?></td>
            <td style="color:var(--accent);font-weight:600"><?=isset($r['Score'])&&$r['Score']>0 ? $r['Score'] : '—'?></td>
            <td>
              <form method="POST" class="status-form">
                <input type="hidden" name="pid" value="<?=(int)$r['ProjectID']?>">
                <input type="hidden" name="cid" value="<?=(int)$r['CertID']?>">
                <select name="status">
                  <?php foreach(['Applied','In Review','Awarded','Expired'] as $opt):?>
                  <option value="<?=$opt?>" <?=$st===$opt?'selected':''?>><?=$opt?></option>
                  <?php endforeach;?>
                </select>
                <button type="submit" name="update_status" class="save-btn">Save</button>
              </form>
            </td>
            <td style="color:var(--muted);font-size:12px"><?=$r['AwardedDate']??'—'?></td>
            <td style="color:var(--muted);font-size:12px"><?=$r['ExpiryDate']??'—'?></td>
            <td>
              <a href="?delete=1&p=<?=(int)$r['ProjectID']?>&c=<?=(int)$r['CertID']?>"
                 class="del-btn"
                 onclick="return confirm('Remove this certification record?')">
                Remove
              </a>
            </td>
          </tr>
          <?php endwhile;?>
          </tbody>
        </table>
        <?php endif;?>
      </div>
    </div>

  </div><!-- .two-col -->
</div><!-- .layout -->
</body>
</html>