<?php include_once 'init.php'; ?>
<?php
$msg='';
if($_POST && isset($_POST['project'])){
    $pid=(int)$_POST['project'];
    $mid=(int)$_POST['material'];
    $vol=(float)$_POST['volume'];
    
    $notes=mysqli_real_escape_string($conn,$_POST['notes']??'');
    $phid_val=$phid>0?$phid:'NULL';
    mysqli_query($conn,"INSERT INTO elements(ProjectID,MaterialID,Volume,Notes) VALUES($pid,$mid,$vol,'$notes')");
    $eid=mysqli_insert_id($conn);
    $mrow=mysqli_fetch_assoc(mysqli_query($conn,"SELECT EmissionFactor FROM materials WHERE MaterialID=$mid"));
    $co2=$vol*$mrow['EmissionFactor'];
    mysqli_query($conn,"INSERT INTO emissioncalculation(ElementID,CO2_Emission) VALUES($eid,$co2)");
    // Audit log
    mysqli_query($conn,"INSERT INTO auditlogs(TableName,RecordID,Action,NewValue) VALUES('elements',$eid,'INSERT','ProjectID=$pid,MaterialID=$mid,Volume=$vol,CO2=$co2')");
    $msg='success';
    $last_co2=$co2;
}
if(isset($_GET['delete'])){
    $did=(int)$_GET['delete'];
    mysqli_query($conn,"DELETE FROM elements WHERE ElementID=$did AND ProjectID IN (SELECT ProjectID FROM projects WHERE user_id=$user_id)");
    mysqli_query($conn,"INSERT INTO auditlogs(TableName,RecordID,Action) VALUES('elements',$did,'DELETE')");
    header("Location: elements.php?deleted=1");exit;
}
$projects=mysqli_query($conn,"SELECT ProjectID,ProjectName FROM projects WHERE user_id=$user_id ORDER BY ProjectName");
$materials=mysqli_query($conn,"SELECT m.*,mc.CategoryName FROM materials m LEFT JOIN materialcategories mc ON m.CategoryID=mc.CategoryID ORDER BY m.MaterialName");
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Elements — SitePro CE</title>
<link rel="stylesheet" href="/css/fonts.css">
<style>
  :root{--bg:#f0f4f8;--surface:#ffffff;--surface2:#f7f9fc;--border:#dde3ec;--accent:#1a5c96;--accent-light:#e8f0f9;--accent-glow:rgba(26,92,150,0.12);--text:#1a2332;--muted:#6b7a90;--danger:#c0392b;--success:#1a7a4a;--header-bg:#1a2d4a;--gold:#f0a500;}
  *{margin:0;padding:0;box-sizing:border-box;}
  body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;min-height:100vh;}
  body::before{display:none}
  .layout{position:relative;z-index:1;max-width:1300px;margin:0 auto;padding:40px 24px;}
  .topbar{display:flex;align-items:center;gap:16px;margin-bottom:40px;}
  .back-btn{display:inline-flex;align-items:center;gap:8px;background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:10px 18px;text-decoration:none;color:var(--muted);font-size:12px;letter-spacing:1px;transition:all .3s;}
  .back-btn:hover{border-color:var(--accent);color:var(--accent);}
  .page-title{font-family:'Syne',sans-serif;font-size:28px;font-weight:800;}.page-title span{color:var(--accent);}
  .breadcrumb{font-size:11px;color:var(--muted);margin-left:auto;}
  .alert{border-radius:12px;padding:14px 20px;font-size:13px;margin-bottom:24px;}
  .alert-success{background:rgba(26,122,74,.1);border:1px solid rgba(26,122,74,.3);color:var(--green);}
  .alert-danger{background:rgba(255,77,109,.1);border:1px solid rgba(255,77,109,.3);color:var(--danger);}
  .content-grid{display:grid;grid-template-columns:400px 1fr;gap:28px;align-items:start;}
  @media(max-width:1000px){.content-grid{grid-template-columns:1fr;}}
  .card{background:var(--surface);border:1px solid var(--border);border-radius:12px;overflow:hidden;}
  .card-header{padding:20px 26px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px;}
  .card-header h3{font-family:'Syne',sans-serif;font-size:16px;font-weight:700;}
  .card-body{padding:26px;}
  .field{margin-bottom:14px;}
  .field label{display:block;font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-bottom:7px;}
  .field input,.field select,.field textarea{width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:7px;padding:9px 13px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:13px;outline:none;transition:border-color .3s,box-shadow .3s;}
  .field input:focus,.field select:focus{border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-glow);}
  .field select option{background:#071414;}
  .btn-primary{width:100%;padding:13px;border-radius:12px;background:var(--accent);border:none;color:#fff;font-family:'Syne',sans-serif;font-size:13px;font-weight:700;cursor:pointer;transition:all .3s;}
  .btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 24px var(--accent-glow);}
  .co2-preview{background:var(--surface2);border:1px solid var(--border);border-radius:14px;padding:18px;margin-top:18px;text-align:center;}
  .co2-preview-label{font-size:10px;letter-spacing:3px;text-transform:uppercase;color:var(--muted);margin-bottom:8px;}
  .co2-preview-val{font-family:'Syne',sans-serif;font-size:32px;font-weight:800;color:var(--accent);}
  .co2-preview-unit{font-size:11px;color:var(--muted);}
  table{width:100%;border-collapse:separate;border-spacing:0;}
  thead th{background:var(--surface2);padding:12px 14px;font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--muted);text-align:left;font-weight:400;border-bottom:1px solid var(--border);}
  tbody tr:hover{background:#f4f7fb;}
  td{padding:13px 14px;font-size:12px;border-bottom:1px solid rgba(26,58,58,.5);}
  .id-badge{background:var(--surface2);border:1px solid var(--border);border-radius:6px;padding:3px 10px;font-size:11px;color:var(--muted);}
  .co2-val{font-family:'Syne',sans-serif;font-weight:700;font-size:14px;}
  .co2-low{color:var(--green)}.co2-mid{color:#ff6b2b}.co2-high{color:var(--danger)}
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
    <div class="page-title">Emission <span>Elements</span></div>
    <div class="bc">HOME / ELEMENTS</div>
  </nav>
  <?php if($msg==='success'):?>
    <div class="alert alert-success">✓ Element added — CO₂: <strong><?=number_format($last_co2,2)?> kg</strong> logged</div>
  <?php endif;?>
  <?php if(isset($_GET['deleted'])):?><div class="alert alert-danger">Element deleted.</div><?php endif;?>
  <div class="content-grid">
    <div class="card" style="animation:fadeUp .5s ease both">
      <div class="card-header"><span>⚗️</span><h3>Log Element</h3></div>
      <div class="card-body">
        <form method="POST" id="elemForm">
          <div class="field"><label>Project</label>
            <select name="project" id="selProject" required onchange="loadPhases(this.value)">
              <option value="">— Select Project —</option>
              <?php mysqli_data_seek($projects,0);while($r=mysqli_fetch_assoc($projects)):?>
              <option value="<?=$r['ProjectID']?>"><?=htmlspecialchars($r['ProjectName'])?></option>
              <?php endwhile;?>
            </select>
          </div>

          <div class="field"><label>Material</label>
            <select name="material" id="selMaterial" required onchange="updatePreview()">
              <option value="">— Select Material —</option>
              <?php mysqli_data_seek($materials,0);while($r=mysqli_fetch_assoc($materials)):?>
              <option value="<?=$r['MaterialID']?>" data-factor="<?=$r['EmissionFactor']?>" data-unit="<?=htmlspecialchars($r['Unit'])?>">
                <?=htmlspecialchars($r['MaterialName'])?> <?=$r['CategoryName']?'['.$r['CategoryName'].'] ':''?>(<?=$r['EmissionFactor']?> kg/<?=$r['Unit']?>)
              </option>
              <?php endwhile;?>
            </select>
          </div>
          <div class="field"><label>Volume / Quantity</label>
            <input type="number" name="volume" id="inpVolume" step="0.01" placeholder="0.00" required oninput="updatePreview()">
          </div>
          <div class="field"><label>Notes</label>
            <textarea name="notes" placeholder="Optional notes..." style="resize:vertical;min-height:50px"></textarea>
          </div>
          <button class="btn-primary" type="submit">⚡ CALCULATE & SAVE</button>
        </form>
        <div class="co2-preview">
          <div class="co2-preview-label">Live CO₂ Estimate</div>
          <div class="co2-preview-val" id="previewVal">—</div>
          <div class="co2-preview-unit">kg CO₂ equivalent</div>
        </div>
      </div>
    </div>
    <div class="card" style="animation:fadeUp .5s ease .15s both">
      <div class="card-header"><span>📋</span><h3>Element Log</h3></div>
      <div class="table-wrap">
        <?php
        $elems=mysqli_query($conn,"
          SELECT e.ElementID,p.ProjectName as Pname,m.MaterialName as Mname,
                 e.Volume,m.EmissionFactor,m.Unit,mc.CategoryName,
                 (e.Volume*m.EmissionFactor) as CO2,e.LoggedAt
          FROM elements e
          JOIN projects p ON e.ProjectID=p.ProjectID
          JOIN materials m ON e.MaterialID=m.MaterialID

          LEFT JOIN materialcategories mc ON m.CategoryID=mc.CategoryID
          WHERE p.user_id=$user_id ORDER BY e.ElementID DESC
        ");
        $maxCO2=mysqli_fetch_assoc(mysqli_query($conn,"SELECT MAX(e.Volume*m.EmissionFactor) mx FROM elements e JOIN materials m ON e.MaterialID=m.MaterialID JOIN projects p ON e.ProjectID=p.ProjectID WHERE p.user_id=$user_id"))['mx']??1;
        if($maxCO2==0){$maxCO2=1;}
        if(mysqli_num_rows($elems)===0):?>
          <div class="empty-state"><div style="font-size:48px;opacity:.4;margin-bottom:16px">⚗️</div>No elements logged yet.</div>
        <?php else:?>
        <table>
          <thead><tr><th>ID</th><th>Project</th><th>Material</th><th>Category</th><th>Volume</th><th>CO₂ (kg)</th><th>Logged</th><th>Action</th></tr></thead>
          <tbody>
          <?php while($r=mysqli_fetch_assoc($elems)):
            $co2=$r['CO2'];
            $pct=$maxCO2>0?($co2/$maxCO2)*100:0;
            $cls=$pct>66?'co2-high':($pct>33?'co2-mid':'co2-low');?>
          <tr>
            <td><span class="id-badge">#<?=$r['ElementID']?></span></td>
            <td><?=htmlspecialchars($r['Pname'])?></td>
            <td><?=htmlspecialchars($r['Mname'])?></td>
            <td style="font-size:10px;color:#4d9eff"><?=$r['CategoryName']??'—'?></td>
            <td><?=$r['Volume']?> <?=$r['Unit']?></td>
            <td>
              <div style="display:flex;align-items:center;gap:6px">
                <span class="co2-val <?=$cls?>"><?=number_format($co2,2)?></span>
                <div style="width:40px;height:4px;background:rgba(255,255,255,.05);border-radius:2px;overflow:hidden"><div style="width:<?=$pct?>%;height:100%;background:<?=$pct>66?'#ff4d6d':($pct>33?'#ff6b2b':'#00e5b0')?>;border-radius:2px"></div></div>
              </div>
            </td>
            <td style="color:var(--muted);font-size:11px"><?=date('M d, Y',strtotime($r['LoggedAt']))?></td>
            <td><a href="?delete=<?=$r['ElementID']?>" class="del-btn" onclick="return confirm('Delete element?')">Delete</a></td>
          </tr>
          <?php endwhile;?>
          </tbody>
        </table>
        <?php endif;?>
      </div>
    </div>
  </div>
</div>
<script>
function updatePreview(){
  const sel=document.getElementById('selMaterial');
  const opt=sel.options[sel.selectedIndex];
  const factor=parseFloat(opt.getAttribute('data-factor'))||0;
  const vol=parseFloat(document.getElementById('inpVolume').value)||0;
  const co2=(factor*vol).toFixed(2);
  const el=document.getElementById('previewVal');
  el.textContent=co2>0?co2:'—';
  const pct=Math.min(1,co2/500);
  el.style.color=pct>.66?'#ff4d6d':pct>.33?'#ff6b2b':'#00e5b0';
}


</script>
</body></html>
