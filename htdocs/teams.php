<?php include 'init.php'; ?>
<?php
$msg='';
// Add team
if($_POST && isset($_POST['teamname'])){
    $pid=(int)$_POST['project'];
    $tn=mysqli_real_escape_string($conn,$_POST['teamname']);
    $role=mysqli_real_escape_string($conn,$_POST['role']??'');
    mysqli_query($conn,"INSERT INTO teams(ProjectID,TeamName,Role) VALUES($pid,'$tn','$role')");
    $msg='team';
}
// Add member
if ($_POST && isset($_POST['add_member'])) {
    $tid = (int)$_POST['team'];
    $fn  = $_POST['fullname']    ?? '';
    $des = $_POST['designation'] ?? '';
    $em  = $_POST['email']       ?? '';
    $ph  = $_POST['phone']       ?? '';
    $jd  = $_POST['joindate']    ?? '';
    $hr  = (float)($_POST['hourlyrate'] ?? 0);

    $stmt = mysqli_prepare($conn,
        "INSERT INTO teammembers
             (TeamID, FullName, Designation, Email, Phone, JoinDate, HourlyRate)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param($stmt, 'isssssd', $tid, $fn, $des, $em, $ph, $jd, $hr);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $msg = 'member';
}
if(isset($_GET['delteam'])){
    mysqli_query($conn,"DELETE FROM teams WHERE TeamID=".(int)$_GET['delteam']);
    header("Location: teams.php?deleted=1");exit;
}
if(isset($_GET['delmember'])){
    mysqli_query($conn,"DELETE FROM teammembers WHERE MemberID=".(int)$_GET['delmember']);
    header("Location: teams.php?deleted=1");exit;
}
$projects=mysqli_query($conn,"SELECT ProjectID,ProjectName FROM projects WHERE user_id=$user_id ORDER BY ProjectName");
$teams=mysqli_query($conn,"SELECT t.*,p.ProjectName as PName FROM teams t JOIN projects p ON t.ProjectID=p.ProjectID WHERE p.user_id=$user_id ORDER BY t.TeamID DESC");
$members=mysqli_query($conn,"SELECT tm.*,t.TeamName,p.ProjectName as PName FROM teammembers tm JOIN teams t ON tm.TeamID=t.TeamID JOIN projects p ON t.ProjectID=p.ProjectID WHERE p.user_id=$user_id ORDER BY tm.MemberID DESC");
$teamsForSelect=mysqli_query($conn,"SELECT t.TeamID,CONCAT(p.ProjectName,' — ',t.TeamName) as label FROM teams t JOIN projects p ON t.ProjectID=p.ProjectID WHERE p.user_id=$user_id ORDER BY label");
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Teams — SitePro CE</title>
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
  .page-title{font-family:'Syne',sans-serif;font-size:28px;font-weight:800;}.page-title span{color:var(--accent);}
  .breadcrumb{font-size:11px;color:var(--muted);margin-left:auto;}
  .alert{border-radius:12px;padding:14px 20px;font-size:13px;margin-bottom:24px;}
  .alert-success{background:rgba(26,122,74,.1);border:1px solid rgba(26,122,74,.3);color:var(--success);}
  .alert-danger{background:rgba(255,77,109,.1);border:1px solid rgba(255,77,109,.3);color:var(--danger);}
  .two-col{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:28px;}
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
    <div class="page-title">Teams &amp; <span>Members</span></div>
    <div class="bc">HOME / TEAMS</div>
  </nav>
  <?php if($msg==='team'):?><div class="alert alert-success">✓ Team created</div><?php endif;?>
  <?php if($msg==='member'):?><div class="alert alert-success">✓ Member added</div><?php endif;?>
  <?php if(isset($_GET['deleted'])):?><div class="alert alert-danger">Record deleted.</div><?php endif;?>

  <div class="two-col">
    <!-- Add Team -->
    <div class="card" style="animation:fadeUp .5s ease both">
      <div class="card-header"><span>👥</span><h3>Create Team</h3></div>
      <div class="card-body">
        <form method="POST">
          <input type="hidden" name="teamname" value="placeholder"><!-- disambiguate -->
          <div class="field"><label>Project</label>
            <select name="project" required>
              <option value="">— Select Project —</option>
              <?php mysqli_data_seek($projects,0);while($r=mysqli_fetch_assoc($projects)):?>
              <option value="<?=$r['ProjectID']?>"><?=htmlspecialchars($r['ProjectName'])?></option>
              <?php endwhile;?>
            </select>
          </div>
          <div class="field"><label>Team Name</label><input name="teamname" placeholder="e.g. Structural Team" required></div>
          <div class="field"><label>Role / Discipline</label>
            <select name="role">
              <option value="">— Select —</option>
              <option>Civil</option><option>Structural</option><option>MEP</option>
              <option>Architecture</option><option>Sustainability</option><option>QA/QC</option>
            </select>
          </div>
          <button class="btn-primary" type="submit">+ CREATE TEAM</button>
        </form>
      </div>
    </div>
    <!-- Add Member -->
    <div class="card" style="animation:fadeUp .5s ease .1s both">
      <div class="card-header"><span>👤</span><h3>Add Member</h3></div>
      <div class="card-body">
        <form method="POST">
          <div class="field"><label>Team</label>
            <select name="team" required>
              <option value="">— Select Team —</option>
              <?php while($r=mysqli_fetch_assoc($teamsForSelect)):?>
              <option value="<?=$r['TeamID']?>"><?=htmlspecialchars($r['label'])?></option>
              <?php endwhile;?>
            </select>
          </div>
          <div class="field"><label>Full Name</label><input name="fullname" placeholder="e.g. Eng. Ali Hassan" required></div>
          <div class="field"><label>Designation</label><input name="designation" placeholder="e.g. Structural Engineer"></div>
          <div class="field"><label>Email</label><input name="email" type="email" placeholder="ali@firm.com"></div>
          <div class="field"><label>Join Date</label><input name="joindate" type="date"></div>
          <div class="field"><label>Hourly Rate (PKR)</label><input name="hourlyrate" type="number" step="0.01" placeholder="2500.00"></div>
          <button class="btn-primary" type="submit">+ ADD MEMBER</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Teams Table -->
  <div class="card" style="margin-bottom:24px;animation:fadeUp .5s ease .2s both">
    <div class="card-header"><span>👥</span><h3>All Teams</h3></div>
    <div class="table-wrap">
      <?php mysqli_data_seek($teams,0);if(mysqli_num_rows($teams)===0):?>
        <div class="empty-state">No teams yet.</div>
      <?php else:?>
      <table>
        <thead><tr><th>ID</th><th>Team Name</th><th>Project</th><th>Role</th><th>Action</th></tr></thead>
        <tbody>
        <?php mysqli_data_seek($teams,0);while($r=mysqli_fetch_assoc($teams)):?>
        <tr>
          <td><span class="id-badge">#<?=$r['TeamID']?></span></td>
          <td><?=htmlspecialchars($r['TeamName'])?></td>
          <td><?=htmlspecialchars($r['PName'])?></td>
          <td><?=htmlspecialchars($r['Role']??'—')?></td>
          <td><a href="?delteam=<?=$r['TeamID']?>" class="del-btn" onclick="return confirm('Delete team and all members?')">Delete</a></td>
        </tr>
        <?php endwhile;?>
        </tbody>
      </table>
      <?php endif;?>
    </div>
  </div>

  <!-- Members Table -->
  <div class="card" style="animation:fadeUp .5s ease .3s both">
    <div class="card-header"><span>👤</span><h3>All Members</h3></div>
    <div class="table-wrap">
      <?php if(mysqli_num_rows($members)===0):?>
        <div class="empty-state">No members yet.</div>
      <?php else:?>
      <table>
        <thead><tr><th>ID</th><th>Name</th><th>Designation</th><th>Team</th><th>Project</th><th>Hourly Rate</th><th>Action</th></tr></thead>
        <tbody>
        <?php while($r=mysqli_fetch_assoc($members)):?>
        <tr>
          <td><span class="id-badge">#<?=$r['MemberID']?></span></td>
          <td><?=htmlspecialchars($r['FullName'])?></td>
          <td><?=htmlspecialchars($r['Designation']??'—')?></td>
          <td><?=htmlspecialchars($r['TeamName'])?></td>
          <td><?=htmlspecialchars($r['PName'])?></td>
          <td style="color:var(--accent)">PKR <?=number_format($r['HourlyRate'],2)?></td>
          <td><a href="?delmember=<?=$r['MemberID']?>" class="del-btn" onclick="return confirm('Delete member?')">Delete</a></td>
        </tr>
        <?php endwhile;?>
        </tbody>
      </table>
      <?php endif;?>
    </div>
  </div>
</div>
</body></html>
