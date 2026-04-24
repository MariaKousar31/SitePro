<?php include_once 'init.php'; ?>
<?php
$msg='';
if($_POST && isset($_POST['ename'])){
    $n=mysqli_real_escape_string($conn,$_POST['ename']);
    $t=mysqli_real_escape_string($conn,$_POST['type']??'');
    $f=mysqli_real_escape_string($conn,$_POST['fuel']??'');
    $c=(float)$_POST['co2h'];
    $d=(float)$_POST['daily'];
    mysqli_query($conn,"INSERT INTO equipment(EquipmentName,Type,FuelType,CO2PerHour,DailyRate) VALUES('$n','$t','$f',$c,$d)");
    $msg='equipment';
}
if ($_POST && isset($_POST['assign'])) {
    $pid = (int)$_POST['proj'];
    $eid = (int)$_POST['equip'];
    $h   = (float)$_POST['hours'];
    $sd  = $_POST['sd'] ?? '';
    $ed  = $_POST['ed'] ?? '';

    $stmt = mysqli_prepare($conn,
        "INSERT INTO projectequipment
             (ProjectID, EquipmentID, HoursUsed, StartDate, EndDate)
         VALUES (?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE HoursUsed = HoursUsed + VALUES(HoursUsed)"
    );

    mysqli_stmt_bind_param($stmt, 'iidss', $pid, $eid, $h, $sd, $ed);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $msg = 'assigned';
}
if(isset($_GET['delete'])){
    mysqli_query($conn,"DELETE FROM equipment WHERE EquipmentID=".(int)$_GET['delete']);
    header("Location: equipment.php?deleted=1");exit;
}
$equip=mysqli_query($conn,"SELECT * FROM equipment ORDER BY EquipmentID DESC");
$projects=mysqli_query($conn,"SELECT ProjectID,ProjectName FROM projects WHERE user_id=$user_id ORDER BY ProjectName");
$equipForSelect=mysqli_query($conn,"SELECT EquipmentID,EquipmentName FROM equipment ORDER BY EquipmentName");
$assignments=mysqli_query($conn,"SELECT pe.*,e.EquipmentName as EName,e.CO2PerHour,p.ProjectName as PName,(pe.HoursUsed*e.CO2PerHour) as TotalCO2 FROM projectequipment pe JOIN equipment e ON pe.EquipmentID=e.EquipmentID JOIN projects p ON pe.ProjectID=p.ProjectID WHERE p.user_id=$user_id ORDER BY pe.ProjectID");
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Equipment — SitePro CE</title>
<link rel="stylesheet" href="/css/fonts.css">
<style>
  :root{--bg:#f0f4f8;--surface:#ffffff;--surface2:#f7f9fc;--border:#dde3ec;--accent:#1a5c96;--accent-light:#e8f0f9;--accent-glow:rgba(26,92,150,0.12);--text:#1a2332;--muted:#6b7a90;--danger:#c0392b;--success:#1a7a4a;--header-bg:#1a2d4a;--gold:#f0a500;--orange:#e67e22;}
  *{margin:0;padding:0;box-sizing:border-box;}
  body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;min-height:100vh;}
  .topnav{background:var(--header-bg);padding:0 32px;display:flex;align-items:center;height:56px;box-shadow:0 2px 8px rgba(0,0,0,.18);}
  .topnav .brand{font-family:'Inter',sans-serif;font-weight:700;font-size:16px;color:#fff;display:flex;align-items:center;gap:10px;text-decoration:none;}
  .topnav .brand span{background:var(--gold);color:#1a2d4a;border-radius:4px;padding:2px 8px;font-size:12px;font-weight:700;}
  .topnav .back-btn{margin-left:24px;display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);border-radius:6px;padding:6px 14px;text-decoration:none;color:rgba(255,255,255,.85);font-size:12px;font-weight:500;transition:all .2s;}
  .topnav .back-btn:hover{background:rgba(255,255,255,.2);}
  .topnav .breadcrumb{margin-left:auto;font-size:11px;color:rgba(255,255,255,.5);letter-spacing:1px;}
  .layout{max-width:1300px;margin:0 auto;padding:32px 24px;}
  .page-header{margin-bottom:24px;}
  .page-header h1{font-family:'Inter',sans-serif;font-size:22px;font-weight:700;display:flex;align-items:center;gap:10px;}
  .page-header h1 .icon{background:var(--orange);color:#fff;width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:18px;}
  .page-header p{font-size:13px;color:var(--muted);margin-top:4px;}
  .alert{border-radius:8px;padding:12px 18px;font-size:13px;margin-bottom:20px;}
  .alert-success{background:#e6f4ec;border:1px solid #a3d4b5;color:var(--success);}
  .alert-danger{background:#fce9e8;border:1px solid #f0b0ab;color:var(--danger);}
  .two-col{display:grid;grid-template-columns:340px 1fr;gap:22px;align-items:start;margin-bottom:22px;}
  @media(max-width:900px){.two-col{grid-template-columns:1fr;}}
  .card{background:var(--surface);border:1px solid var(--border);border-radius:12px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:20px;}
  .card-header{padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;background:var(--surface2);}
  .card-header h3{font-family:'Inter',sans-serif;font-size:14px;font-weight:600;}
  .card-body{padding:20px;}
  .field{margin-bottom:13px;}
  .field label{display:block;font-size:11px;font-weight:600;letter-spacing:.5px;text-transform:uppercase;color:var(--muted);margin-bottom:5px;}
  .field input,.field select{width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:7px;padding:9px 12px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:13px;outline:none;transition:border-color .2s,box-shadow .2s;}
  .field input:focus,.field select:focus{border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-glow);}
  .btn-primary{width:100%;padding:10px;border-radius:7px;background:var(--accent);border:none;color:#fff;font-family:'Inter',sans-serif;font-size:13px;font-weight:600;cursor:pointer;transition:all .2s;}
  .btn-primary:hover{background:#154d80;}
  table{width:100%;border-collapse:collapse;}
  thead th{background:var(--surface2);padding:10px 14px;font-size:10px;letter-spacing:1px;text-transform:uppercase;color:var(--muted);text-align:left;font-weight:600;border-bottom:2px solid var(--border);}
  tbody tr:hover{background:#f4f7fb;}
  td{padding:11px 14px;font-size:13px;border-bottom:1px solid var(--border);}
  .row-num{background:var(--accent-light);color:var(--accent);border-radius:5px;padding:2px 7px;font-size:11px;font-weight:600;}
  .fuel-badge{border-radius:5px;padding:2px 8px;font-size:11px;font-weight:500;}
  .fuel-Diesel{background:#fff3e0;color:#e65100;}
  .fuel-Electric{background:#e3f2fd;color:#1565c0;}
  .fuel-Hybrid{background:#e8f5e9;color:#2e7d32;}
  .fuel-Petrol{background:#fce4ec;color:#c62828;}
  .co2-val{font-weight:600;color:var(--orange);}
  .del-btn{background:#fce9e8;border:1px solid #f0b0ab;border-radius:6px;padding:4px 10px;color:var(--danger);font-size:11px;font-weight:500;cursor:pointer;text-decoration:none;display:inline-block;}
  .table-wrap{overflow-x:auto;}
  .empty-state{text-align:center;padding:40px;color:var(--muted);}
  @keyframes fadeUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
</style>
</head>
<body>
<nav class="topnav">
  <a href="index.php" class="brand"><span>CE</span> SitePro</a>
  <a href="index.php" class="back-btn">&#8592; Dashboard</a>
  <div class="breadcrumb">DASHBOARD / EQUIPMENT</div>
</nav>
<div class="layout">
  <div class="page-header">
    <h1><span class="icon">&#9874;</span> Equipment Registry</h1>
    <p>Register site machinery, track CO&#8322; per hour, fuel type, daily rates and project assignments.</p>
  </div>
  <?php if($msg):?><div class="alert alert-success">&#10004; <?=$msg==='assigned'?'Equipment assigned to project.':'Equipment added successfully.'?></div><?php endif;?>
  <?php if(isset($_GET['deleted'])):?><div class="alert alert-danger">&#10006; Equipment removed.</div><?php endif;?>
  <div class="two-col">
    <div>
      <div class="card" style="animation:fadeUp .4s ease both">
        <div class="card-header"><span>&#43;</span><h3>Add Equipment</h3></div>
        <div class="card-body">
          <form method="POST">
            <div class="field"><label>Equipment Name</label><input name="ename" placeholder="e.g. Tower Crane 50T" required></div>
            <div class="field"><label>Type</label>
              <select name="type">
                <option value="">Select Type</option>
                <option>Crane</option><option>Excavator</option><option>Bulldozer</option>
                <option>Concrete Pump</option><option>Generator</option><option>Compactor</option>
                <option>Loader</option><option>Grader</option><option>Piling Rig</option>
              </select>
            </div>
            <div class="field"><label>Fuel Type</label>
              <select name="fuel">
                <option>Diesel</option><option>Electric</option><option>Hybrid</option><option>Petrol</option>
              </select>
            </div>
            <div class="field"><label>CO&#8322; per Hour (kg)</label><input name="co2h" type="number" step="0.01" placeholder="18.6" required></div>
            <div class="field"><label>Daily Rate (PKR)</label><input name="daily" type="number" step="0.01" placeholder="35000.00"></div>
            <button class="btn-primary" type="submit">+ Add Equipment</button>
          </form>
        </div>
      </div>
      <div class="card" style="animation:fadeUp .4s ease .1s both">
        <div class="card-header"><span>&#128204;</span><h3>Assign to Project</h3></div>
        <div class="card-body">
          <form method="POST">
            <input type="hidden" name="assign" value="1">
            <div class="field"><label>Project</label>
              <select name="proj" required>
                <option value="">Select Project</option>
                <?php mysqli_data_seek($projects,0);while($r=mysqli_fetch_assoc($projects)):?>
                <option value="<?=$r['ProjectID']?>"><?=htmlspecialchars($r['ProjectName'])?></option>
                <?php endwhile;?>
              </select>
            </div>
            <div class="field"><label>Equipment</label>
              <select name="equip" required>
                <option value="">Select Equipment</option>
                <?php while($r=mysqli_fetch_assoc($equipForSelect)):?>
                <option value="<?=$r['EquipmentID']?>"><?=htmlspecialchars($r['EquipmentName'])?></option>
                <?php endwhile;?>
              </select>
            </div>
            <div class="field"><label>Hours Used</label><input name="hours" type="number" step="0.5" placeholder="120" required></div>
            <div class="field"><label>Start Date</label><input name="sd" type="date" max="<?=date('Y-m-d')?>"></div>
            <div class="field"><label>End Date</label><input name="ed" type="date" max="2099-12-31"></div>
            <button class="btn-primary" type="submit">Assign</button>
          </form>
        </div>
      </div>
    </div>
    <div class="card" style="animation:fadeUp .4s ease .15s both">
      <div class="card-header"><span>&#128203;</span><h3>Equipment Inventory</h3></div>
      <div class="table-wrap">
        <?php mysqli_data_seek($equip,0);if(mysqli_num_rows($equip)===0):?>
          <div class="empty-state"><p style="font-size:30px">&#9874;</p><p style="margin-top:8px">No equipment added yet.</p></div>
        <?php else:?>
        <table>
          <thead><tr><th>#</th><th>Name</th><th>Type</th><th>Fuel</th><th>CO&#8322;/hr (kg)</th><th>Daily Rate (PKR)</th><th>Action</th></tr></thead>
          <tbody>
          <?php $seq=1;mysqli_data_seek($equip,0);while($r=mysqli_fetch_assoc($equip)):
            $fuelClass='fuel-'.($r['FuelType']??'Diesel');?>
          <tr>
            <td><span class="row-num"><?=$seq++?></span></td>
            <td style="font-weight:500"><?=htmlspecialchars($r['EquipmentName'])?></td>
            <td style="font-size:12px;color:var(--muted)"><?=htmlspecialchars($r['Type']??'—')?></td>
            <td><span class="fuel-badge <?=$fuelClass?>"><?=htmlspecialchars($r['FuelType']??'—')?></span></td>
            <td><span class="co2-val"><?=$r['CO2PerHour']?></span></td>
            <td style="color:var(--muted)">PKR <?=number_format($r['DailyRatePKR']??$r['DailyRate']??0,0)?></td>
            <td><a href="?delete=<?=$r['EquipmentID']?>" class="del-btn" onclick="return confirm('Remove equipment?')">Remove</a></td>
          </tr>
          <?php endwhile;?>
          </tbody>
        </table>
        <?php endif;?>
      </div>
    </div>
  </div>
  <div class="card" style="animation:fadeUp .4s ease .3s both">
    <div class="card-header"><span>&#128204;</span><h3>Project Equipment Assignments</h3></div>
    <div class="table-wrap">
      <?php if(mysqli_num_rows($assignments)===0):?>
        <div class="empty-state">No assignments yet.</div>
      <?php else:?>
      <table>
        <thead><tr><th>Project</th><th>Equipment</th><th>Hours Used</th><th>CO&#8322; Contribution (kg)</th><th>Start Date</th><th>End Date</th></tr></thead>
        <tbody>
        <?php while($r=mysqli_fetch_assoc($assignments)):?>
        <tr>
          <td style="font-weight:500"><?=htmlspecialchars($r['PName'])?></td>
          <td><?=htmlspecialchars($r['EName'])?></td>
          <td><?=number_format($r['HoursUsed'],1)?> hrs</td>
          <td><span style="font-weight:700;color:var(--orange)"><?=number_format($r['TotalCO2'],2)?></span> kg</td>
          <td style="color:var(--muted);font-size:12px"><?=$r['StartDate']??'—'?></td>
          <td style="color:var(--muted);font-size:12px"><?=$r['EndDate']??'—'?></td>
        </tr>
        <?php endwhile;?>
        </tbody>
      </table>
      <?php endif;?>
    </div>
  </div>
</div>
</body></html>
