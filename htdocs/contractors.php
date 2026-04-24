<?php include_once 'init.php'; ?>
<?php
$msg='';
if($_POST && isset($_POST['name'])){
    $n=mysqli_real_escape_string($conn,$_POST['name']);
    $e=mysqli_real_escape_string($conn,$_POST['email']??'');
    $p=mysqli_real_escape_string($conn,substr(preg_replace('/\D/','',$_POST['phone']??''),0,11));
    $l=mysqli_real_escape_string($conn,$_POST['license']??'');
    $s=mysqli_real_escape_string($conn,$_POST['specialty']??'');
    $r=(float)($_POST['rating']??0);
    mysqli_query($conn,"INSERT INTO contractors(ContractorName,Email,Phone,LicenseNumber,Specialty,Rating,user_id) VALUES('$n','$e','$p','$l','$s',$r,$user_id)");
    $msg='success';
}
if(isset($_GET['delete'])){
    mysqli_query($conn,"DELETE FROM contractors WHERE user_id=$user_id AND ContractorID=".(int)$_GET['delete']);
    header("Location: contractors.php?deleted=1");exit;
}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Contractors — SitePro CE</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  :root{--bg:#f0f4f8;--surface:#ffffff;--surface2:#f7f9fc;--border:#dde3ec;--accent:#1a5c96;--accent-light:#e8f0f9;--accent-glow:rgba(26,92,150,0.12);--text:#1a2332;--muted:#6b7a90;--danger:#c0392b;--success:#1a7a4a;--header-bg:#1a2d4a;--gold:#f0a500;}
  *{margin:0;padding:0;box-sizing:border-box;}
  body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;min-height:100vh;}
  .topnav{background:var(--header-bg);padding:0 32px;display:flex;align-items:center;gap:0;height:56px;box-shadow:0 2px 8px rgba(0,0,0,.18);}
  .topnav .brand{font-family:'Inter',sans-serif;font-weight:700;font-size:16px;color:#fff;letter-spacing:.5px;display:flex;align-items:center;gap:10px;text-decoration:none;}
  .topnav .brand span{background:var(--gold);color:#1a2d4a;border-radius:4px;padding:2px 8px;font-size:12px;font-weight:700;}
  .topnav .back-btn{margin-left:24px;display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);border-radius:6px;padding:6px 14px;text-decoration:none;color:rgba(255,255,255,.85);font-size:12px;font-weight:500;transition:all .2s;}
  .topnav .back-btn:hover{background:rgba(255,255,255,.2);}
  .topnav .breadcrumb{margin-left:auto;font-size:11px;color:rgba(255,255,255,.5);letter-spacing:1px;}
  .layout{max-width:1240px;margin:0 auto;padding:32px 24px;}
  .page-header{margin-bottom:28px;}
  .page-header h1{font-family:'Inter',sans-serif;font-size:22px;font-weight:700;color:var(--text);display:flex;align-items:center;gap:10px;}
  .page-header h1 .icon{background:var(--accent);color:#fff;width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:18px;}
  .page-header p{font-size:13px;color:var(--muted);margin-top:4px;}
  .alert{border-radius:8px;padding:12px 18px;font-size:13px;margin-bottom:20px;}
  .alert-success{background:#e6f4ec;border:1px solid #a3d4b5;color:var(--success);}
  .alert-danger{background:#fce9e8;border:1px solid #f0b0ab;color:var(--danger);}
  .content-grid{display:grid;grid-template-columns:360px 1fr;gap:24px;align-items:start;}
  @media(max-width:900px){.content-grid{grid-template-columns:1fr;}}
  .card{background:var(--surface);border:1px solid var(--border);border-radius:12px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06);}
  .card-header{padding:16px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;background:var(--surface2);}
  .card-header h3{font-family:'Inter',sans-serif;font-size:14px;font-weight:600;color:var(--text);}
  .card-body{padding:22px;}
  .field{margin-bottom:14px;}
  .field label{display:block;font-size:11px;font-weight:600;letter-spacing:.5px;text-transform:uppercase;color:var(--muted);margin-bottom:6px;}
  .field input,.field select{width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:7px;padding:9px 13px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:13px;outline:none;transition:border-color .2s,box-shadow .2s;}
  .field input:focus,.field select:focus{border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-glow);}
  .field .hint{font-size:10px;color:var(--muted);margin-top:4px;}
  .btn-primary{width:100%;padding:11px;border-radius:8px;background:var(--accent);border:none;color:#fff;font-family:'Inter',sans-serif;font-size:13px;font-weight:600;cursor:pointer;letter-spacing:.3px;transition:all .2s;}
  .btn-primary:hover{background:#154d80;box-shadow:0 4px 14px var(--accent-glow);}
  table{width:100%;border-collapse:collapse;}
  thead th{background:var(--surface2);padding:11px 14px;font-size:10px;letter-spacing:1px;text-transform:uppercase;color:var(--muted);text-align:left;font-weight:600;border-bottom:2px solid var(--border);}
  tbody tr:hover{background:#f4f7fb;}
  td{padding:12px 14px;font-size:13px;border-bottom:1px solid var(--border);}
  .row-num{background:var(--accent-light);color:var(--accent);border-radius:5px;padding:2px 8px;font-size:11px;font-weight:600;}
  .rating-stars{color:var(--gold);letter-spacing:-1px;}
  .badge{border-radius:5px;padding:3px 9px;font-size:11px;font-weight:500;}
  .badge-specialty{background:#e8f0f9;color:var(--accent);}
  .del-btn{background:#fce9e8;border:1px solid #f0b0ab;border-radius:6px;padding:4px 11px;color:var(--danger);font-size:11px;font-weight:500;cursor:pointer;text-decoration:none;display:inline-block;transition:all .2s;}
  .del-btn:hover{background:#f5c2be;}
  .empty-state{text-align:center;padding:50px;color:var(--muted);}
  .table-wrap{overflow-x:auto;}
  @keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
</style>
</head>
<body>
<nav class="topnav">
  <a href="index.php" class="brand"><span>CE</span> SitePro</a>
  <a href="index.php" class="back-btn">&#8592; Dashboard</a>
  <div class="breadcrumb">DASHBOARD / CONTRACTORS</div>
</nav>
<div class="layout">
  <div class="page-header">
    <h1><span class="icon">&#128119;</span> Contractor Registry</h1>
    <p>Register and manage construction contractors, PEC licenses and specialties.</p>
  </div>
  <?php if($msg==='success'):?><div class="alert alert-success">&#10004; Contractor registered successfully.</div><?php endif;?>
  <?php if(isset($_GET['deleted'])):?><div class="alert alert-danger">&#10006; Contractor removed.</div><?php endif;?>
  <div class="content-grid">
    <div class="card" style="animation:fadeUp .4s ease both">
      <div class="card-header"><span>&#128221;</span><h3>Register New Contractor</h3></div>
      <div class="card-body">
        <form method="POST">
          <div class="field"><label>Company / Name</label><input name="name" placeholder="e.g. Habib Construction Co." required></div>
          <div class="field"><label>Email</label><input name="email" type="email" placeholder="info@contractor.com"></div>
          <div class="field">
            <label>Phone</label>
            <input name="phone" placeholder="03001234567" maxlength="11" pattern="[0-9]{11}" title="Enter exactly 11 digits" inputmode="numeric">
            <div class="hint">11-digit number only, e.g. 03001234567</div>
          </div>
          <div class="field"><label>PEC License No.</label><input name="license" placeholder="PEC-XXXXX"></div>
          <div class="field"><label>Specialty</label>
            <select name="specialty">
              <option value="">Select Specialty</option>
              <option>Civil Works</option><option>Structural Engineering</option><option>MEP</option>
              <option>Fit-out &amp; Finishing</option><option>Roads &amp; Infrastructure</option>
              <option>Green Building</option><option>Geotechnical</option><option>Water &amp; Sanitation</option>
            </select>
          </div>
          <div class="field"><label>Rating (0-5)</label><input name="rating" type="number" step="0.1" min="0" max="5" placeholder="4.5"></div>
          <button class="btn-primary" type="submit">+ Register Contractor</button>
        </form>
      </div>
    </div>
    <div class="card" style="animation:fadeUp .4s ease .1s both">
      <div class="card-header"><span>&#128203;</span><h3>My Contractors</h3></div>
      <div class="table-wrap">
        <?php
        $rows=mysqli_query($conn,"SELECT c.*,(SELECT COUNT(*) FROM projects p WHERE p.ContractorID=c.ContractorID AND p.user_id=$user_id) AS proj FROM contractors c WHERE c.user_id=$user_id ORDER BY c.ContractorID DESC");
        if(mysqli_num_rows($rows)===0):?>
          <div class="empty-state"><p style="font-size:32px">&#127959;</p><p style="margin-top:10px">No contractors registered yet.</p></div>
        <?php else:?>
        <table>
          <thead><tr><th>#</th><th>Name</th><th>Specialty</th><th>License</th><th>Phone</th><th>Rating</th><th>Projects</th><th>Action</th></tr></thead>
          <tbody>
          <?php $seq=1; while($r=mysqli_fetch_assoc($rows)):
            $stars=str_repeat('&#9733;',round($r['Rating']??0)).str_repeat('&#9734;',5-round($r['Rating']??0));?>
          <tr>
            <td><span class="row-num"><?=$seq++?></span></td>
            <td style="font-weight:500"><?=htmlspecialchars($r['ContractorName'])?></td>
            <td><span class="badge badge-specialty"><?=htmlspecialchars($r['Specialty']??'—')?></span></td>
            <td style="font-size:12px;color:var(--muted)"><?=htmlspecialchars($r['LicenseNumber']??$r['LicenseNo']??'—')?></td>
            <td style="font-size:12px"><?=htmlspecialchars($r['Phone']??'—')?></td>
            <td><span class="rating-stars"><?=$stars?></span></td>
            <td><?=$r['proj']?></td>
            <td><a href="?delete=<?=$r['ContractorID']?>" class="del-btn" onclick="return confirm('Remove this contractor?')">Remove</a></td>
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
