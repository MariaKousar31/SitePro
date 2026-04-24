<?php include 'init.php'; ?>
<?php
$msg='';
if ($_POST && isset($_POST['inspector'])) {
    $pid = (int)$_POST['project'];
    $ins = $_POST['inspector']         ?? '';
    $dt  = $_POST['inspdate']          ?? '';
    $tp  = $_POST['type']              ?? 'Environmental';
    $rs  = $_POST['result']            ?? 'Pass';
    $no  = $_POST['notes']             ?? '';

    $stmt = mysqli_prepare($conn,
        "INSERT INTO inspections
             (ProjectID, InspectorName, InspectionDate, InspectionType, Result, Notes)
         VALUES (?, ?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param($stmt, 'isssss', $pid, $ins, $dt, $tp, $rs, $no);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $msg = 'success';
}
if(isset($_GET['delete'])){
    mysqli_query($conn,"DELETE FROM inspections WHERE InspectionID=".(int)$_GET['delete']);
    header("Location: inspections.php?deleted=1");exit;
}
$projects=mysqli_query($conn,"SELECT ProjectID,ProjectName FROM projects WHERE user_id=$user_id ORDER BY ProjectName");
$rows=mysqli_query($conn,"SELECT i.*,p.ProjectName as PName FROM inspections i JOIN projects p ON i.ProjectID=p.ProjectID WHERE p.user_id=$user_id ORDER BY i.InspectionDate DESC");
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Inspections — SitePro CE</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=DM+Sans:wght@400;500;600" rel="stylesheet">
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
  .alert-success{background:rgba(26,122,74,.1);border:1px solid rgba(26,122,74,.3);color:var(--green);}
  .alert-danger{background:rgba(255,77,109,.1);border:1px solid rgba(255,77,109,.3);color:var(--danger);}
  .two-col{display:grid;grid-template-columns:380px 1fr;gap:24px;align-items:start;}
  @media(max-width:900px){.two-col{grid-template-columns:1fr;}}
  .card{background:var(--surface);border:1px solid var(--border);border-radius:12px;overflow:hidden;}
  .card-header{padding:18px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;}
  .card-header h3{font-family:'Syne',sans-serif;font-size:15px;font-weight:700;}
  .card-body{padding:24px;}
  .field{margin-bottom:15px;}
  .field label{display:block;font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-bottom:6px;}
  .field input,.field select,.field textarea{width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:11px 14px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:13px;outline:none;transition:border-color .3s;}
  .field input:focus,.field select:focus,.field textarea:focus{border-color:var(--accent);}
  .field select option{background:#071414;}
  .field textarea{resize:vertical;min-height:60px;}
  .btn-primary{width:100%;padding:12px;border-radius:12px;background:var(--accent);border:none;color:#fff;font-family:'Syne',sans-serif;font-size:13px;font-weight:700;cursor:pointer;transition:all .3s;}
  .btn-primary:hover{transform:translateY(-2px);}
  table{width:100%;border-collapse:separate;border-spacing:0;}
  thead th{background:var(--surface2);padding:12px 16px;font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--muted);text-align:left;font-weight:400;border-bottom:1px solid var(--border);}
  tbody tr:hover{background:#f4f7fb;}
  td{padding:13px 16px;font-size:12px;border-bottom:1px solid rgba(26,58,58,.5);}
  .id-badge{background:var(--surface2);border:1px solid var(--border);border-radius:6px;padding:3px 10px;font-size:11px;color:var(--muted);}
  .result-pass{background:rgba(0,229,176,.12);border:1px solid rgba(26,122,74,.3);border-radius:6px;padding:3px 10px;font-size:10px;color:var(--green);}
  .result-fail{background:rgba(255,77,109,.12);border:1px solid rgba(255,77,109,.3);border-radius:6px;padding:3px 10px;font-size:10px;color:var(--danger);}
  .result-cond{background:rgba(244,162,97,.12);border:1px solid rgba(244,162,97,.3);border-radius:6px;padding:3px 10px;font-size:10px;color:var(--accent);}
  .del-btn{background:#fce9e8;border:1px solid #f0b0ab;border-radius:8px;padding:5px 12px;color:var(--danger);font-size:11px;cursor:pointer;text-decoration:none;display:inline-block;}
  .table-wrap{overflow-x:auto;}
  .empty-state{text-align:center;padding:50px;color:var(--muted);}
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
    <div class="page-title">Project <span>Inspections</span></div>
    <div class="bc">HOME / INSPECTIONS</div>
  </nav>
  <?php if($msg==='success'):?><div class="alert alert-success">✓ Inspection record saved</div><?php endif;?>
  <?php if(isset($_GET['deleted'])):?><div class="alert alert-danger">Record deleted.</div><?php endif;?>
  <div class="two-col">
    <div class="card" style="animation:fadeUp .5s ease both">
      <div class="card-header"><span>🔍</span><h3>Log Inspection</h3></div>
      <div class="card-body">
        <form method="POST">
          <div class="field"><label>Project</label>
            <select name="project" required>
              <option value="">— Select Project —</option>
              <?php while($r=mysqli_fetch_assoc($projects)):?>
              <option value="<?=$r['ProjectID']?>"><?=htmlspecialchars($r['ProjectName'])?></option>
              <?php endwhile;?>
            </select>
          </div>
          <div class="field"><label>Inspector Name</label><input name="inspector" placeholder="Eng. Name / Agency" required></div>
          <div class="field"><label>Inspection Date</label><input name="inspdate" type="date" required></div>
          <div class="field"><label>Type</label>
            <select name="type">
              <option>Environmental</option><option>Structural</option><option>Safety</option><option>Final</option>
            </select>
          </div>
          <div class="field"><label>Result</label>
            <select name="result">
              <option>Pass</option><option>Fail</option><option>Conditional</option>
            </select>
          </div>
          <div class="field"><label>Notes</label><textarea name="notes" placeholder="Findings or remarks..."></textarea></div>
          <button class="btn-primary" type="submit">+ LOG INSPECTION</button>
        </form>
      </div>
    </div>
    <div class="card" style="animation:fadeUp .5s ease .15s both">
      <div class="card-header"><span>📋</span><h3>Inspection Log</h3></div>
      <div class="table-wrap">
        <?php if(mysqli_num_rows($rows)===0):?>
          <div class="empty-state">No inspections logged yet.</div>
        <?php else:?>
        <table>
          <thead><tr><th>ID</th><th>Project</th><th>Inspector</th><th>Date</th><th>Type</th><th>Result</th><th>Action</th></tr></thead>
          <tbody>
          <?php while($r=mysqli_fetch_assoc($rows)):
            $cls=$r['Result']==='Pass'?'result-pass':($r['Result']==='Fail'?'result-fail':'result-cond');?>
          <tr>
            <td><span class="id-badge">#<?=$r['InspectionID']?></span></td>
            <td><?=htmlspecialchars($r['PName'])?></td>
            <td><?=htmlspecialchars($r['InspectorName'])?></td>
            <td><?=$r['InspectionDate']?></td>
            <td style="color:var(--accent)"><?=$r['InspectionType']?></td>
            <td><span class="<?=$cls?>"><?=$r['Result']?></span></td>
            <td><a href="?delete=<?=$r['InspectionID']?>" class="del-btn" onclick="return confirm('Delete?')">Delete</a></td>
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
