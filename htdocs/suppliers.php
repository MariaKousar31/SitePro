<?php include_once 'init.php'; ?>
<?php
$msg='';
if($_POST && isset($_POST['company'])){
    $c=mysqli_real_escape_string($conn,$_POST['company']);
    $cp=mysqli_real_escape_string($conn,$_POST['contact']??'');
    $e=mysqli_real_escape_string($conn,$_POST['email']??'');
    $p=mysqli_real_escape_string($conn,substr(preg_replace('/\D/','',$_POST['phone']??''),0,11));
    $co=mysqli_real_escape_string($conn,$_POST['country']??'');
    $g=(int)($_POST['green']??0);
    mysqli_query($conn,"INSERT INTO suppliers(CompanyName,ContactPerson,Email,Phone,Country,GreenCertification,user_id) VALUES('$c','$cp','$e','$p','$co',$g,$user_id)");
    $msg='success';
}
if($_POST && isset($_POST['sup_link'])){
    $mid=(int)$_POST['mat_id'];
    $sid=(int)$_POST['sup_id'];
    $price=(float)$_POST['unit_price'];
    $lead=(int)$_POST['lead'];
    mysqli_query($conn,"INSERT IGNORE INTO materialsuppliers(MaterialID,SupplierID,UnitPrice,LeadTimeDays) VALUES($mid,$sid,$price,$lead)");
    $msg='linked';
}
if(isset($_GET['delete'])){
    mysqli_query($conn,"DELETE FROM suppliers WHERE user_id=$user_id AND SupplierID=".(int)$_GET['delete']);
    header("Location: suppliers.php?deleted=1");exit;
}
$suppliers=mysqli_query($conn,"SELECT s.*,(SELECT COUNT(*) FROM materialsuppliers ms WHERE ms.SupplierID=s.SupplierID) AS mat_count FROM suppliers s WHERE s.user_id=$user_id ORDER BY s.SupplierID DESC");
$materials=mysqli_query($conn,"SELECT MaterialID,MaterialName FROM materials WHERE user_id=$user_id ORDER BY MaterialName");
$allsuppliers=mysqli_query($conn,"SELECT SupplierID,CompanyName FROM suppliers WHERE user_id=$user_id ORDER BY CompanyName");
$links=mysqli_query($conn,"SELECT ms.*,m.MaterialName as MName,s.CompanyName FROM materialsuppliers ms JOIN materials m ON ms.MaterialID=m.MaterialID JOIN suppliers s ON ms.SupplierID=s.SupplierID WHERE s.user_id=$user_id ORDER BY ms.MaterialID");
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Suppliers — SitePro CE</title>
<link rel="stylesheet" href="/css/fonts.css">
<style>
  :root{--bg:#f0f4f8;--surface:#ffffff;--surface2:#f7f9fc;--border:#dde3ec;--accent:#1a5c96;--accent-light:#e8f0f9;--accent-glow:rgba(26,92,150,0.12);--text:#1a2332;--muted:#6b7a90;--danger:#c0392b;--success:#1a7a4a;--header-bg:#1a2d4a;--gold:#f0a500;--green:#2e7d32;}
  *{margin:0;padding:0;box-sizing:border-box;}
  body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;min-height:100vh;}
  .topnav{background:var(--header-bg);padding:0 32px;display:flex;align-items:center;height:56px;box-shadow:0 2px 8px rgba(0,0,0,.18);}
  .topnav .brand{font-family:'Inter',sans-serif;font-weight:700;font-size:16px;color:#fff;display:flex;align-items:center;gap:10px;text-decoration:none;}
  .topnav .brand span{background:var(--gold);color:#1a2d4a;border-radius:4px;padding:2px 8px;font-size:12px;font-weight:700;}
  .topnav .back-btn{margin-left:24px;display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);border-radius:6px;padding:6px 14px;text-decoration:none;color:rgba(255,255,255,.85);font-size:12px;font-weight:500;transition:all .2s;}
  .topnav .back-btn:hover{background:rgba(255,255,255,.2);}
  .topnav .breadcrumb{margin-left:auto;font-size:11px;color:rgba(255,255,255,.5);letter-spacing:1px;}
  .layout{max-width:1280px;margin:0 auto;padding:32px 24px;}
  .page-header{margin-bottom:24px;}
  .page-header h1{font-family:'Inter',sans-serif;font-size:22px;font-weight:700;display:flex;align-items:center;gap:10px;}
  .page-header h1 .icon{background:#1565c0;color:#fff;width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:18px;}
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
  .field .hint{font-size:10px;color:var(--muted);margin-top:3px;}
  .toggle-row{display:flex;align-items:center;gap:8px;margin-bottom:13px;font-size:13px;color:var(--muted);}
  .btn-primary{width:100%;padding:10px;border-radius:7px;background:var(--accent);border:none;color:#fff;font-family:'Inter',sans-serif;font-size:13px;font-weight:600;cursor:pointer;transition:all .2s;}
  .btn-primary:hover{background:#154d80;}
  table{width:100%;border-collapse:collapse;}
  thead th{background:var(--surface2);padding:10px 14px;font-size:10px;letter-spacing:1px;text-transform:uppercase;color:var(--muted);text-align:left;font-weight:600;border-bottom:2px solid var(--border);}
  tbody tr:hover{background:#f4f7fb;}
  td{padding:11px 14px;font-size:13px;border-bottom:1px solid var(--border);}
  .row-num{background:var(--accent-light);color:var(--accent);border-radius:5px;padding:2px 7px;font-size:11px;font-weight:600;}
  .green-badge{background:#e8f5e9;border:1px solid #a5d6a7;border-radius:5px;padding:2px 9px;font-size:11px;color:var(--green);font-weight:500;}
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
  <div class="breadcrumb">DASHBOARD / SUPPLIERS</div>
</nav>
<div class="layout">
  <div class="page-header">
    <h1><span class="icon">&#128666;</span> Supplier Directory</h1>
    <p>Manage material suppliers, green certifications, pricing and material linkages.</p>
  </div>
  <?php if($msg==='success'||$msg==='linked'):?>
    <div class="alert alert-success">&#10004; <?=$msg==='linked'?'Material-Supplier link saved.':'Supplier added successfully.'?></div>
  <?php endif;?>
  <?php if(isset($_GET['deleted'])):?><div class="alert alert-danger">&#10006; Supplier removed.</div><?php endif;?>
  <div class="two-col">
    <div>
      <div class="card" style="animation:fadeUp .4s ease both">
        <div class="card-header"><span>&#43;</span><h3>Add Supplier</h3></div>
        <div class="card-body">
          <form method="POST">
            <div class="field"><label>Company Name</label><input name="company" placeholder="e.g. Bestway Cement Ltd." required></div>
            <div class="field"><label>Contact Person</label><input name="contact" placeholder="Name &amp; designation"></div>
            <div class="field"><label>Email</label><input name="email" type="email" placeholder="info@supplier.com"></div>
            <div class="field">
              <label>Phone</label>
              <input name="phone" placeholder="03001234567" maxlength="11" pattern="[0-9]{11}" title="11 digits" inputmode="numeric">
              <div class="hint">11-digit number only</div>
            </div>
            <div class="field"><label>Country</label><input name="country" placeholder="Pakistan"></div>
            <div class="toggle-row">
              <input type="checkbox" name="green" value="1" id="greenChk">
              <label for="greenChk">&#127807; Green / Eco-certified Supplier</label>
            </div>
            <button class="btn-primary" type="submit">+ Add Supplier</button>
          </form>
        </div>
      </div>
      <div class="card" style="animation:fadeUp .4s ease .1s both">
        <div class="card-header"><span>&#128279;</span><h3>Link Material &#8596; Supplier</h3></div>
        <div class="card-body">
          <form method="POST">
            <input type="hidden" name="sup_link" value="1">
            <div class="field"><label>Material</label>
              <select name="mat_id" required>
                <option value="">Select Material</option>
                <?php mysqli_data_seek($materials,0);while($r=mysqli_fetch_assoc($materials)):?>
                <option value="<?=$r['MaterialID']?>"><?=htmlspecialchars($r['MaterialName'])?></option>
                <?php endwhile;?>
              </select>
            </div>
            <div class="field"><label>Supplier</label>
              <select name="sup_id" required>
                <option value="">Select Supplier</option>
                <?php while($r=mysqli_fetch_assoc($allsuppliers)):?>
                <option value="<?=$r['SupplierID']?>"><?=htmlspecialchars($r['CompanyName'])?></option>
                <?php endwhile;?>
              </select>
            </div>
            <div class="field"><label>Unit Price (PKR)</label><input name="unit_price" type="number" step="0.01" placeholder="0.00"></div>
            <div class="field"><label>Lead Time (days)</label><input name="lead" type="number" placeholder="7"></div>
            <button class="btn-primary" type="submit">Link</button>
          </form>
        </div>
      </div>
    </div>
    <div class="card" style="animation:fadeUp .4s ease .15s both">
      <div class="card-header"><span>&#128203;</span><h3>My Suppliers</h3></div>
      <div class="table-wrap">
        <?php mysqli_data_seek($suppliers,0);if(mysqli_num_rows($suppliers)===0):?>
          <div class="empty-state"><p style="font-size:30px">&#128666;</p><p style="margin-top:8px">No suppliers yet.</p></div>
        <?php else:?>
        <table>
          <thead><tr><th>#</th><th>Company</th><th>Country</th><th>Phone</th><th>Materials</th><th>Green</th><th>Action</th></tr></thead>
          <tbody>
          <?php $seq=1;mysqli_data_seek($suppliers,0);while($r=mysqli_fetch_assoc($suppliers)):?>
          <tr>
            <td><span class="row-num"><?=$seq++?></span></td>
            <td style="font-weight:500"><?=htmlspecialchars($r['CompanyName'])?></td>
            <td style="font-size:12px;color:var(--muted)"><?=htmlspecialchars($r['Country']??'—')?></td>
            <td style="font-size:12px"><?=htmlspecialchars($r['Phone']??'—')?></td>
            <td><?=$r['mat_count']?></td>
            <td><?=$r['GreenCertification']?'<span class="green-badge">&#127807; Certified</span>':'<span style="font-size:11px;color:var(--muted)">No</span>'?></td>
            <td><a href="?delete=<?=$r['SupplierID']?>" class="del-btn" onclick="return confirm('Remove supplier?')">Remove</a></td>
          </tr>
          <?php endwhile;?>
          </tbody>
        </table>
        <?php endif;?>
      </div>
    </div>
  </div>
  <div class="card" style="animation:fadeUp .4s ease .25s both">
    <div class="card-header"><span>&#128279;</span><h3>Material–Supplier Links</h3></div>
    <div class="table-wrap">
      <?php if(mysqli_num_rows($links)===0):?>
        <div class="empty-state">No links established yet.</div>
      <?php else:?>
      <table>
        <thead><tr><th>Material</th><th>Supplier</th><th>Unit Price (PKR)</th><th>Lead Time</th></tr></thead>
        <tbody>
        <?php while($r=mysqli_fetch_assoc($links)):?>
        <tr>
          <td style="font-weight:500"><?=htmlspecialchars($r['MName'])?></td>
          <td><?=htmlspecialchars($r['CompanyName'])?></td>
          <td style="color:var(--accent);font-weight:600">PKR <?=number_format($r['UnitPrice'],2)?></td>
          <td style="color:var(--muted)"><?=$r['LeadTimeDays']?> days</td>
        </tr>
        <?php endwhile;?>
        </tbody>
      </table>
      <?php endif;?>
    </div>
  </div>
</div>
</body></html>
