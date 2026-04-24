<?php include_once 'init.php'; ?>
<?php
$msg='';
if($_POST && isset($_POST['target_co2'])){
    $pid=(int)$_POST['project'];
    $tg=(float)$_POST['target_co2'];
    $bl=(float)($_POST['baseline']??0);
    $yr=(int)($_POST['year']??date('Y'));
    $no=mysqli_real_escape_string($conn,$_POST['notes']??'');
    mysqli_query($conn,"INSERT INTO carbontargets(ProjectID,TargetCO2_kg,BaselineCO2,TargetYear,Notes) VALUES($pid,$tg,$bl,$yr,'$no') ON DUPLICATE KEY UPDATE TargetCO2_kg=$tg,BaselineCO2=$bl,TargetYear=$yr,Notes='$no'");
    $msg='success';
}
if(isset($_GET['delete'])){
    mysqli_query($conn,"DELETE FROM carbontargets WHERE ProjectID=".(int)$_GET['delete']);
    header("Location: targets.php?deleted=1");exit;
}
$projects=mysqli_query($conn,"SELECT ProjectID,ProjectName FROM projects WHERE user_id=$user_id ORDER BY ProjectName");
// Get targets with actual CO2
$rows=mysqli_query($conn,"
  SELECT ct.*,p.ProjectName as PName,
         COALESCE(SUM(e.Volume*m.EmissionFactor),0) as ActualCO2
  FROM carbontargets ct
  JOIN projects p ON ct.ProjectID=p.ProjectID
  LEFT JOIN elements e ON e.ProjectID=ct.ProjectID
  LEFT JOIN materials m ON e.MaterialID=m.MaterialID
  WHERE p.user_id=$user_id
  GROUP BY ct.TargetID
  ORDER BY ct.TargetID DESC
");
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>CO₂ Targets — SitePro CE</title>
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
  .two-col{display:grid;grid-template-columns:360px 1fr;gap:24px;align-items:start;}
  @media(max-width:900px){.two-col{grid-template-columns:1fr;}}
  .card{background:var(--surface);border:1px solid var(--border);border-radius:12px;overflow:hidden;}
  .card-header{padding:18px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;}
  .card-header h3{font-family:'Syne',sans-serif;font-size:15px;font-weight:700;}
  .card-body{padding:24px;}
  .field{margin-bottom:15px;}
  .field label{display:block;font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-bottom:6px;}
  .field input,.field select,.field textarea{width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:11px 14px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:13px;outline:none;transition:border-color .3s;}
  .field input:focus,.field select:focus{border-color:var(--accent);}
  .field select option{background:#071414;}
  .field textarea{resize:vertical;min-height:60px;}
  .btn-primary{width:100%;padding:12px;border-radius:12px;background:var(--accent);border:none;color:#fff;font-family:'Syne',sans-serif;font-size:13px;font-weight:700;cursor:pointer;transition:all .3s;}
  .btn-primary:hover{transform:translateY(-2px);}
  /* Target cards */
  .target-grid{display:grid;grid-template-columns:1fr;gap:16px;padding:24px;}
  .target-card{background:var(--surface2);border:1px solid var(--border);border-radius:14px;padding:20px;}
  .target-card-title{font-family:'Syne',sans-serif;font-size:15px;font-weight:700;margin-bottom:4px;}
  .target-meta{font-size:11px;color:var(--muted);margin-bottom:14px;}
  .progress-wrap{margin-bottom:10px;}
  .progress-label{display:flex;justify-content:space-between;font-size:11px;color:var(--muted);margin-bottom:6px;}
  .progress-bar{height:8px;background:rgba(255,255,255,.05);border-radius:4px;overflow:hidden;}
  .progress-fill{height:100%;border-radius:4px;transition:width .6s;}
  .on-track{background:var(--green);}
  .over-target{background:var(--danger);}
  .near-target{background:#ff6b2b;}
  .target-nums{display:flex;gap:20px;font-size:11px;}
  .tnum{color:var(--muted)}
  .tnum strong{font-family:'Syne',sans-serif;font-size:14px;font-weight:700;display:block;}
  .del-btn{background:#fce9e8;border:1px solid #f0b0ab;border-radius:8px;padding:5px 12px;color:var(--danger);font-size:11px;cursor:pointer;text-decoration:none;display:inline-block;margin-top:12px;}
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
    <div class="page-title">Carbon <span>Targets</span></div>
    <div class="bc">HOME / TARGETS</div>
  </nav>
  <?php if($msg==='success'):?><div class="alert alert-success">✓ Target saved</div><?php endif;?>
  <?php if(isset($_GET['deleted'])):?><div class="alert alert-danger">Target removed.</div><?php endif;?>
  <div class="two-col">
    <div class="card" style="animation:fadeUp .5s ease both">
      <div class="card-header"><span>🎯</span><h3>Set Carbon Target</h3></div>
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
          <div class="field"><label>Target CO₂ (kg)</label><input name="target_co2" type="number" step="0.01" placeholder="50000" required></div>
          <div class="field"><label>Baseline CO₂ (kg)</label><input name="baseline" type="number" step="0.01" placeholder="80000"></div>
          <div class="field"><label>Target Year</label><input name="year" type="number" placeholder="<?=date('Y')?>"></div>
          <div class="field"><label>Notes</label><textarea name="notes" placeholder="Reduction strategy..."></textarea></div>
          <button class="btn-primary" type="submit">SET TARGET</button>
        </form>
      </div>
    </div>
    <div class="card" style="animation:fadeUp .5s ease .15s both">
      <div class="card-header"><span>📊</span><h3>Target Progress</h3></div>
      <div class="target-grid">
        <?php if(mysqli_num_rows($rows)===0):?>
          <div class="empty-state">No targets set yet.</div>
        <?php else:?>
        <?php while($r=mysqli_fetch_assoc($rows)):
          $actual=$r['ActualCO2'];
          $target=$r['TargetCO2_kg'];
          $pct=$target>0?min(100,($actual/$target)*100):0;
          $cls=$pct>100?'over-target':($pct>80?'near-target':'on-track');
          $reduction=$target>0?round((1-$target/max($r['BaselineCO2'],1))*100,1):0;
        ?>
        <div class="target-card">
          <div class="target-card-title">🎯 <?=htmlspecialchars($r['PName'])?></div>
          <div class="target-meta">Target Year: <?=$r['TargetYear']?> · Reduction Goal: <?=$reduction?>%</div>
          <div class="progress-wrap">
            <div class="progress-label"><span>Actual vs Target</span><span><?=round($pct,1)?>%</span></div>
            <div class="progress-bar"><div class="progress-fill <?=$cls?>" style="width:<?=$pct?>%"></div></div>
          </div>
<div class="target-nums">
  <div class="tnum">
    <strong style="color:<?= $pct > 100 ? '#ff4d6d' : '#00e5b0' ?>">
      <?= number_format($actual,0) ?> kg
    </strong>
    Actual CO₂
  </div>

  <div class="tnum">
    <strong style="color:var(--accent)">
      <?= number_format($target,0) ?> kg
    </strong>
    Target
  </div>

  <div class="tnum">
    <strong style="color:var(--muted)">
      <?= number_format($r['BaselineCO2'],0) ?> kg
    </strong>
    Baseline
  </div>
</div>
          <?php if($r['Notes']):?><div style="font-size:11px;color:var(--muted);margin-top:10px"><?=htmlspecialchars($r['Notes'])?></div><?php endif;?>
          <a href="?delete=<?=$r['ProjectID']?>" class="del-btn" onclick="return confirm('Remove target?')">Remove</a>
        </div>
        <?php endwhile;?>
        <?php endif;?>
      </div>
    </div>
  </div>
</div>
</body></html>
