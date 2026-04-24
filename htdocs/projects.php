<?php include 'init.php'; ?>
<?php
$msg='';
if ($_POST && isset($_POST['name'])) {
    $name   = $_POST['name']                ?? '';
    $loc    = $_POST['location']            ?? '';
    $start  = $_POST['start']               ?? '';
    $end    = $_POST['end']                 ?? '';
    $cid    = (int)($_POST['client']        ?? 0);
    $conid  = (int)($_POST['contractor']    ?? 0);
    $budget = (float)($_POST['budget']      ?? 0);
    $status = $_POST['status']              ?? 'Planning';
    $desc   = $_POST['desc']               ?? '';

    // Convert 0 to null so foreign keys store NULL rather than 0
    $cid_val   = $cid   > 0 ? $cid   : null;
    $conid_val = $conid > 0 ? $conid : null;

    $stmt = mysqli_prepare($conn,
        "INSERT INTO projects
             (ProjectName, Location, StartDate, EndDate,
              ClientID, ContractorID, Budget, Status, Description, user_id)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param($stmt, 'ssssiiдssi',
        $name, $loc, $start, $end,
        $cid_val, $conid_val, $budget, $status, $desc, $user_id
    );
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $msg = 'success';
}
if(isset($_GET['delete'])){
    mysqli_query($conn,"DELETE FROM projects WHERE user_id=$user_id AND ProjectID=".(int)$_GET['delete']);
    header("Location: projects.php?deleted=1");exit;
}
$clients=mysqli_query($conn,"SELECT ClientID,CompanyName FROM clients WHERE user_id=$user_id ORDER BY CompanyName");
$contractors=mysqli_query($conn,"SELECT ContractorID,ContractorName FROM contractors WHERE user_id=$user_id ORDER BY ContractorName");
$projects=mysqli_query($conn,"SELECT p.*,c.CompanyName as CName,con.ContractorName as ConName FROM projects p LEFT JOIN clients c ON p.ClientID=c.ClientID LEFT JOIN contractors con ON p.ContractorID=con.ContractorID WHERE p.user_id=$user_id ORDER BY p.ProjectID DESC");
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>projects — SitePro CE</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=DM+Sans:wght@400;500;600" rel="stylesheet">
<style>
  :root{--bg:#f0f4f8;--surface:#ffffff;--surface2:#f7f9fc;--border:#dde3ec;--accent:#1a5c96;--accent-light:#e8f0f9;--accent-glow:rgba(26,92,150,0.12);--text:#1a2332;--muted:#6b7a90;--danger:#c0392b;--success:#1a7a4a;--header-bg:#1a2d4a;--gold:#f0a500;}
  *{margin:0;padding:0;box-sizing:border-box;}
  body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;min-height:100vh;}
  body::before{display:none}
  .layout{position:relative;z-index:1;max-width:1300px;margin:0 auto;padding:40px 24px;}
  .topbar{display:flex;align-items:center;gap:16px;margin-bottom:40px;}
  .back-btn{display:inline-flex;align-items:center;gap:8px;background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:10px 18px;text-decoration:none;color:var(--muted);font-size:12px;letter-spacing:1px;transition:all .3s;}
  .back-btn:hover{border-color:var(--accent);color:var(--accent);}
  .page-title{font-family:'Syne',sans-serif;font-size:28px;font-weight:800;color:var(--accent)}.page-title span{color:var(--accent);}
  .breadcrumb{font-size:11px;color:var(--muted);margin-left:auto;}
  .alert{border-radius:12px;padding:14px 20px;font-size:13px;margin-bottom:24px;}
  .alert-success{background:rgba(26,122,74,.1);border:1px solid rgba(26,122,74,.3);color:var(--success);}
  .alert-danger{background:rgba(255,77,109,.1);border:1px solid rgba(255,77,109,.3);color:var(--danger);}
  .content-grid{display:grid;grid-template-columns:400px 1fr;gap:28px;align-items:start;}
  @media(max-width:1000px){.content-grid{grid-template-columns:1fr;}}
  .card{background:var(--surface);border:1px solid var(--border);border-radius:12px;overflow:hidden;}
  .card-header{padding:20px 26px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px;}
  .card-header h3{font-family:'Syne',sans-serif;font-size:16px;font-weight:700;}
  .card-body{padding:26px;}
  .two-fields{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
  .field{margin-bottom:14px;}
  .field label{display:block;font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-bottom:7px;}
  .field input,.field select,.field textarea{width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:7px;padding:9px 13px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:13px;outline:none;transition:border-color .3s,box-shadow .3s;}
  .field input:focus,.field select:focus{border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-glow);}
  .field select option{background:#071414;}
  .btn-primary{width:100%;padding:13px;border-radius:12px;background:var(--accent);border:none;color:#fff;font-family:'Syne',sans-serif;font-size:13px;font-weight:700;cursor:pointer;transition:all .3s;}
  .btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 24px var(--accent-glow);}
  table{width:100%;border-collapse:separate;border-spacing:0;}
  thead th{background:var(--surface2);padding:12px 14px;font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--muted);text-align:left;font-weight:400;border-bottom:1px solid var(--border);}
  tbody tr:hover{background:#f4f7fb;}
  td{padding:13px 14px;font-size:12px;border-bottom:1px solid rgba(26,58,58,.5);}
  .id-badge{background:var(--surface2);border:1px solid var(--border);border-radius:6px;padding:3px 10px;font-size:11px;color:var(--muted);}
  .status-badge{border-radius:6px;padding:3px 10px;font-size:10px;letter-spacing:1px;}
  .s-Planning{background:rgba(77,158,255,.12);border:1px solid rgba(77,158,255,.3);color:#4d9eff;}
  .s-Active{background:rgba(0,229,176,.12);border:1px solid rgba(26,122,74,.3);color:var(--success);}
  .s-Completed{background:rgba(199,125,255,.12);border:1px solid rgba(199,125,255,.3);color:#c77dff;}
  .s-On_Hold{background:rgba(255,209,102,.12);border:1px solid rgba(255,209,102,.3);color:#ffd166;}
  .s-Cancelled{background:rgba(255,77,109,.12);border:1px solid rgba(255,77,109,.3);color:#ff4d6d;}
  .status-dot{display:inline-block;width:7px;height:7px;border-radius:50%;background:var(--accent);margin-right:6px;animation:pulse 2s ease-in-out infinite;}
  @keyframes pulse{0%,100%{opacity:1}50%{opacity:.3}}
  .del-btn{background:#fce9e8;border:1px solid #f0b0ab;border-radius:6px;padding:4px 11px;color:var(--danger);font-size:11px;font-weight:500;cursor:pointer;text-decoration:none;display:inline-block;transition:all .2s;}
  .del-btn:hover{background:rgba(255,77,109,.25);}
  .table-wrap{overflow-x:auto;}
  .empty-state{text-align:center;padding:60px;color:var(--muted);}
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
      <div class="page-title"><span></span>Construction <span>projects</span></div>
    <div class="bc">HOME / PROJECTS</div>
  </nav>
  <?php if($msg==='success'):?><div class="alert alert-success">✓ Project added successfully</div><?php endif;?>
  <?php if(isset($_GET['deleted'])):?><div class="alert alert-danger">Project deleted.</div><?php endif;?>
  <div class="content-grid">
    <div class="card" style="animation:fadeUp .5s ease both">
      <div class="card-header"><span>🏗️</span><h3>Add New Project</h3></div>
      <div class="card-body">
        <form method="POST">
          <div class="field"><label>Project Name</label><input name="name" placeholder="e.g. Tower Block A" required></div>
          <div class="field"><label>Location</label><input name="location" placeholder="e.g. Karachi, PK"></div>
          <div class="field"><label>Client</label>
            <select name="client">
              <option value="0">— No Client —</option>
              <?php while($r=mysqli_fetch_assoc($clients)):?>
              <option value="<?=$r['ClientID']?>"><?=htmlspecialchars($r['CompanyName'])?></option>
              <?php endwhile;?>
            </select>
          </div>
          <div class="field"><label>Contractor</label>
            <select name="contractor">
              <option value="0">— No Contractor —</option>
              <?php while($r=mysqli_fetch_assoc($contractors)):?>
              <option value="<?=$r['ContractorID']?>"><?=htmlspecialchars($r['ContractorName'])?></option>
              <?php endwhile;?>
            </select>
          </div>
          <div class="two-fields">
            <div class="field"><label>Start Date</label><input type="date" name="start"></div>
            <div class="field"><label>End Date</label><input type="date" name="end"></div>
          </div>
          <div class="two-fields">
            <div class="field"><label>Budget (PKR)</label><input name="budget" type="number" step="0.01" placeholder="0.00"></div>
            <div class="field"><label>Status</label>
              <select name="status">
                <option>Planning</option><option>Active</option><option>On Hold</option><option>Completed</option><option>Cancelled</option>
              </select>
            </div>
          </div>
          <button class="btn-primary" type="submit">+ ADD PROJECT</button>
        </form>
      </div>
    </div>
    <div class="card" style="animation:fadeUp .5s ease .15s both">
      <div class="card-header"><span>📋</span><h3>All projects</h3></div>
      <div class="table-wrap">
        <?php if(mysqli_num_rows($projects)===0):?>
          <div class="empty-state"><div style="font-size:48px;margin-bottom:16px;opacity:.4">🏗️</div>No projects yet.</div>
        <?php else:?>
        <table>
          <thead><tr><th>ID</th><th>Name</th><th>Client</th><th>Contractor</th><th>Location</th><th>Budget</th><th>Status</th><th>Dates</th><th>Action</th></tr></thead>
          <tbody>
          <?php $seq=1; while($r=mysqli_fetch_assoc($projects)):
            $scls='s-'.str_replace(' ','_',$r['Status']);?>
          <tr>
            <td><span class="id-badge">#<?=$r['ProjectID']?></span></td>
            <td><span class="status-dot"></span><?=htmlspecialchars($r['ProjectName'])?></td>
            <td style="font-size:11px"><?=htmlspecialchars($r['CName']??'—')?></td>
            <td style="font-size:11px"><?=htmlspecialchars($r['ConName']??'—')?></td>
            <td style="color:var(--muted)"><?=htmlspecialchars($r['Location']??'—')?></td>
            <td style="color:var(--success);font-size:11px"><?=$r['Budget']>0?'PKR '.number_format($r['Budget'],0):'—'?></td>
            <td><span class="status-badge <?=$scls?>"><?=$r['Status']?></span></td>
            <td style="color:var(--muted);font-size:10px"><?=$r['StartDate']??'—'?> → <?=$r['EndDate']??'—'?></td>
            <td><a href="?delete=<?=$r['ProjectID']?>" class="del-btn" onclick="return confirm('Delete project?')">Delete</a></td>
          </tr>
          <?php endwhile;?>
          </tbody>
        </table>
        <?php endif;?>
      </div>
    </div>
  </div>
</div>
</body></html>
