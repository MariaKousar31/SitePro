<?php include 'init.php'; ?>
<?php
$msg='';
if($_POST && isset($_POST['name'])){
    $name=mysqli_real_escape_string($conn,$_POST['name']);
    $factor=(float)$_POST['factor'];
    $unit=mysqli_real_escape_string($conn,$_POST['unit']??'m3');
    $cat=(int)($_POST['category']??0);
    $density=(float)($_POST['density']??0);
    $recycled=(float)($_POST['recycled']??0);
    $cat_val=$cat>0?$cat:'NULL';
    mysqli_query($conn,"INSERT INTO materials(MaterialName,EmissionFactor,Unit,CategoryID,Density,RecycledContent,user_id) VALUES('$name',$factor,'$unit',$cat_val,$density,$recycled,$user_id)");
    $msg='success';
}
if(isset($_GET['delete'])){
    mysqli_query($conn,"DELETE FROM materials WHERE user_id=$user_id AND MaterialID=".(int)$_GET['delete']);
    header("Location: materials.php?deleted=1");exit;
}
$categories=mysqli_query($conn,"SELECT * FROM materialcategories ORDER BY CategoryName");
$mats=mysqli_query($conn,"SELECT m.*,mc.CategoryName FROM materials m LEFT JOIN materialcategories mc ON m.CategoryID=mc.CategoryID WHERE m.user_id=$user_id ORDER BY m.MaterialID DESC");
$maxFactor=mysqli_fetch_assoc(mysqli_query($conn,"SELECT MAX(EmissionFactor) mx FROM materials WHERE user_id=$user_id"))['mx']??1;
if($maxFactor==0)$maxFactor=1;
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Materials — SitePro CE</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  :root{--bg:#f0f4f8;--surface:#ffffff;--surface2:#f7f9fc;--border:#dde3ec;--accent:#1a5c96;--accent-light:#e8f0f9;--accent-glow:rgba(26,92,150,0.12);--text:#1a2332;--muted:#6b7a90;--danger:#c0392b;--success:#1a7a4a;--header-bg:#1a2d4a;--gold:#f0a500;}
  *{margin:0;padding:0;box-sizing:border-box;}
  body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;min-height:100vh;}
  .topnav{background:var(--header-bg);padding:0 32px;display:flex;align-items:center;height:56px;box-shadow:0 2px 8px rgba(0,0,0,.18);}
  .topnav .brand{font-family:'Inter',sans-serif;font-weight:700;font-size:16px;color:#fff;display:flex;align-items:center;gap:10px;text-decoration:none;}
  .topnav .brand span{background:var(--gold);color:#1a2d4a;border-radius:4px;padding:2px 8px;font-size:12px;font-weight:700;}
  .topnav .back-btn{margin-left:24px;display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);border-radius:6px;padding:6px 14px;text-decoration:none;color:rgba(255,255,255,.85);font-size:12px;font-weight:500;transition:all .2s;}
  .topnav .back-btn:hover{background:rgba(255,255,255,.2);}
  .topnav .breadcrumb{margin-left:auto;font-size:11px;color:rgba(255,255,255,.5);letter-spacing:1px;}
  .layout{max-width:1240px;margin:0 auto;padding:32px 24px;}
  .page-header{margin-bottom:24px;}
  .page-header h1{font-family:'Inter',sans-serif;font-size:22px;font-weight:700;display:flex;align-items:center;gap:10px;}
  .page-header h1 .icon{background:#5c4033;color:#fff;width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:18px;}
  .page-header p{font-size:13px;color:var(--muted);margin-top:4px;}
  .alert{border-radius:8px;padding:12px 18px;font-size:13px;margin-bottom:20px;}
  .alert-success{background:#e6f4ec;border:1px solid #a3d4b5;color:var(--success);}
  .alert-danger{background:#fce9e8;border:1px solid #f0b0ab;color:var(--danger);}
  .content-grid{display:grid;grid-template-columns:360px 1fr;gap:24px;align-items:start;}
  @media(max-width:900px){.content-grid{grid-template-columns:1fr;}}
  .card{background:var(--surface);border:1px solid var(--border);border-radius:12px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06);}
  .card-header{padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;background:var(--surface2);}
  .card-header h3{font-family:'Inter',sans-serif;font-size:14px;font-weight:600;}
  .card-body{padding:20px;}
  .two-fields{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
  .field{margin-bottom:13px;}
  .field label{display:block;font-size:11px;font-weight:600;letter-spacing:.5px;text-transform:uppercase;color:var(--muted);margin-bottom:5px;}
  .field input,.field select{width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:7px;padding:9px 12px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:13px;outline:none;transition:border-color .2s,box-shadow .2s;}
  .field input:focus,.field select:focus{border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-glow);}
  .btn-primary{width:100%;padding:10px;border-radius:7px;background:var(--accent);border:none;color:#fff;font-family:'Inter',sans-serif;font-size:13px;font-weight:600;cursor:pointer;transition:all .2s;}
  .btn-primary:hover{background:#154d80;}
  .presets{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:14px;}
  .preset-chip{background:var(--surface2);border:1px solid var(--border);border-radius:6px;padding:4px 10px;font-size:11px;font-weight:500;color:var(--muted);cursor:pointer;transition:all .15s;}
  .preset-chip:hover{border-color:var(--accent);color:var(--accent);background:var(--accent-light);}
  .factor-bar-wrap{margin-top:6px;}
  .factor-bar{height:4px;background:var(--border);border-radius:2px;overflow:hidden;}
  .factor-bar-fill{height:100%;background:var(--accent);border-radius:2px;transition:width .5s ease,background .3s;}
  table{width:100%;border-collapse:collapse;}
  thead th{background:var(--surface2);padding:10px 14px;font-size:10px;letter-spacing:1px;text-transform:uppercase;color:var(--muted);text-align:left;font-weight:600;border-bottom:2px solid var(--border);}
  tbody tr:hover{background:#f4f7fb;}
  td{padding:11px 14px;font-size:13px;border-bottom:1px solid var(--border);}
  .row-num{background:var(--accent-light);color:var(--accent);border-radius:5px;padding:2px 7px;font-size:11px;font-weight:600;}
  .factor-badge{background:#fff8e1;border:1px solid #ffe082;border-radius:5px;padding:3px 9px;font-size:12px;color:#e65100;font-weight:600;}
  .cat-badge{background:var(--accent-light);border-radius:5px;padding:2px 8px;font-size:11px;color:var(--accent);}
  .del-btn{background:#fce9e8;border:1px solid #f0b0ab;border-radius:6px;padding:4px 10px;color:var(--danger);font-size:11px;font-weight:500;cursor:pointer;text-decoration:none;display:inline-block;}
  .table-wrap{overflow-x:auto;}
  .empty-state{text-align:center;padding:50px;color:var(--muted);}
  @keyframes fadeUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
</style>
</head>
<body>
<nav class="topnav">
  <a href="index.php" class="brand"><span>CE</span> SitePro</a>
  <a href="index.php" class="back-btn">&#8592; Dashboard</a>
  <div class="breadcrumb">DASHBOARD / MATERIALS</div>
</nav>
<div class="layout">
  <div class="page-header">
    <h1><span class="icon">&#129521;</span> Materials Library</h1>
    <p>Manage construction materials, CO&#8322; emission factors, density and recycled content.</p>
  </div>
  <?php if($msg==='success'):?><div class="alert alert-success">&#10004; Material added successfully.</div><?php endif;?>
  <?php if(isset($_GET['deleted'])):?><div class="alert alert-danger">&#10006; Material removed.</div><?php endif;?>
  <div class="content-grid">
    <div class="card" style="animation:fadeUp .4s ease both">
      <div class="card-header"><span>&#43;</span><h3>Add Material</h3></div>
      <div class="card-body">
        <div style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px">Quick Presets</div>
        <div class="presets">
          <div class="preset-chip" onclick="setPreset('Concrete',0.159,'m3',1,2400,0)">Concrete</div>
          <div class="preset-chip" onclick="setPreset('Steel Rebar',2.0,'kg',2,7850,25)">Steel Rebar</div>
          <div class="preset-chip" onclick="setPreset('Timber',0.46,'m3',4,600,0)">Timber</div>
          <div class="preset-chip" onclick="setPreset('Float Glass',0.86,'m2',5,2500,0)">Glass</div>
          <div class="preset-chip" onclick="setPreset('Fired Brick',0.24,'m3',3,1800,0)">Brick</div>
          <div class="preset-chip" onclick="setPreset('Aluminum',11.5,'kg',2,2700,30)">Aluminum</div>
          <div class="preset-chip" onclick="setPreset('EPS Insulation',3.29,'m3',6,25,0)">EPS Insul.</div>
        </div>
        <form method="POST" id="matForm">
          <div class="field"><label>Material Name</label><input name="name" id="matName" placeholder="e.g. Reinforced Concrete M25" required></div>
          <div class="field"><label>Category</label>
            <select name="category" id="matCat">
              <option value="0">No Category</option>
              <?php while($r=mysqli_fetch_assoc($categories)):?>
              <option value="<?=$r['CategoryID']?>"><?=htmlspecialchars($r['CategoryName'])?></option>
              <?php endwhile;?>
            </select>
          </div>
          <div class="field"><label>CO&#8322; Emission Factor (kg per unit)</label>
            <input name="factor" id="matFactor" type="number" step="0.001" placeholder="kg CO&#8322; per unit" required>
            <div class="factor-bar-wrap"><div class="factor-bar"><div class="factor-bar-fill" id="factorBar" style="width:0%"></div></div></div>
          </div>
          <div class="two-fields">
            <div class="field"><label>Unit</label>
              <select name="unit" id="matUnit">
                <option value="m3">m&#179;</option><option value="m2">m&#178;</option><option value="kg">kg</option>
                <option value="tonne">tonne</option><option value="m">m</option>
              </select>
            </div>
            <div class="field"><label>Density (kg/m&#179;)</label><input name="density" id="matDensity" type="number" step="1" placeholder="2400"></div>
          </div>
          <div class="field"><label>Recycled Content (%)</label><input name="recycled" id="matRecycled" type="number" step="0.1" min="0" max="100" placeholder="0"></div>
          <button class="btn-primary" type="submit">+ Add Material</button>
        </form>
      </div>
    </div>
    <div class="card" style="animation:fadeUp .4s ease .1s both">
      <div class="card-header"><span>&#128203;</span><h3>My Materials</h3></div>
      <div class="table-wrap">
        <?php mysqli_data_seek($mats,0);if(mysqli_num_rows($mats)===0):?>
          <div class="empty-state"><p style="font-size:30px">&#129521;</p><p style="margin-top:8px">No materials added yet.</p></div>
        <?php else:?>
        <table>
          <thead><tr><th>#</th><th>Name</th><th>Category</th><th>CO&#8322; Factor</th><th>Unit</th><th>Density</th><th>Recycled%</th><th>Intensity</th><th>Action</th></tr></thead>
          <tbody>
          <?php $seq=1;mysqli_data_seek($mats,0);while($r=mysqli_fetch_assoc($mats)):
            $pct=min(100,($r['EmissionFactor']/$maxFactor)*100);
            $barclr=$pct>66?'#c0392b':($pct>33?'#e67e22':'#2e7d32');?>
          <tr>
            <td><span class="row-num"><?=$seq++?></span></td>
            <td style="font-weight:500"><?=htmlspecialchars($r['MaterialName'])?></td>
            <td><?=$r['CategoryName']?'<span class="cat-badge">'.htmlspecialchars($r['CategoryName']).'</span>':'<span style="color:var(--muted);font-size:11px">—</span>'?></td>
            <td><span class="factor-badge"><?=$r['EmissionFactor']?></span></td>
            <td style="color:var(--muted)"><?=htmlspecialchars($r['Unit']??'m3')?></td>
            <td style="color:var(--muted);font-size:12px"><?=$r['Density']>0?$r['Density'].' kg/m&#179;':'—'?></td>
            <td style="color:<?=$r['RecycledContent']>0?'#2e7d32':'var(--muted)'?>;font-size:12px"><?=$r['RecycledContent']>0?$r['RecycledContent'].'%':'0%'?></td>
            <td>
              <div style="display:flex;align-items:center;gap:8px;min-width:90px">
                <div style="flex:1;height:5px;background:var(--border);border-radius:3px;overflow:hidden"><div style="width:<?=$pct?>%;height:100%;background:<?=$barclr?>;border-radius:3px"></div></div>
                <span style="font-size:10px;color:var(--muted)"><?=round($pct)?>%</span>
              </div>
            </td>
            <td><a href="?delete=<?=$r['MaterialID']?>" class="del-btn" onclick="return confirm('Remove material?')">Remove</a></td>
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
function setPreset(name,factor,unit,cat,density,recycled){
  document.getElementById('matName').value=name;
  document.getElementById('matFactor').value=factor;
  document.getElementById('matUnit').value=unit;
  if(document.getElementById('matCat'))document.getElementById('matCat').value=cat;
  document.getElementById('matDensity').value=density;
  document.getElementById('matRecycled').value=recycled;
  updateBar();
}
function updateBar(){
  const val=parseFloat(document.getElementById('matFactor').value)||0;
  const pct=Math.min(100,(val/15)*100);
  const bar=document.getElementById('factorBar');
  bar.style.width=pct+'%';
  bar.style.background=pct>66?'#c0392b':pct>33?'#e67e22':'#2e7d32';
}
document.getElementById('matFactor').addEventListener('input',updateBar);
</script>
</body></html>
