<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include_once 'init.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SitePro CE — Civil Engineering Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  :root{--bg:#eef2f7;--surface:#ffffff;--surface2:#f4f7fb;--border:#dde3ec;--accent:#1a5c96;--accent-light:#e8f0f9;--text:#1a2332;--muted:#6b7a90;--header-bg:#1a2d4a;--gold:#f0a500;--danger:#c0392b;--success:#1a7a4a;}
  *{margin:0;padding:0;box-sizing:border-box;}
  body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;min-height:100vh;}

  /* Top navigation */
  .topnav{background:var(--header-bg);padding:0 36px;display:flex;align-items:center;height:60px;box-shadow:0 2px 12px rgba(0,0,0,.2);}
  .brand{font-family:'Inter',sans-serif;font-weight:800;font-size:18px;color:#fff;display:flex;align-items:center;gap:12px;}
  .brand .badge{background:var(--gold);color:#1a2d4a;border-radius:5px;padding:3px 10px;font-size:12px;font-weight:700;letter-spacing:.5px;}
  .brand .sub{font-size:12px;font-weight:400;color:rgba(255,255,255,.5);margin-left:4px;}
  .topnav-right{margin-left:auto;display:flex;align-items:center;gap:14px;}
  .user-chip{display:flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);border-radius:8px;padding:6px 14px;font-size:12px;color:rgba(255,255,255,.8);}
  .user-chip .dot{width:7px;height:7px;background:#4caf50;border-radius:50%;}
  .logout-btn{display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:7px;padding:6px 14px;text-decoration:none;color:rgba(255,255,255,.7);font-size:12px;transition:all .2s;}
  .logout-btn:hover{background:rgba(192,57,43,.3);border-color:rgba(192,57,43,.5);color:#fff;}

  /* Sub-header / breadcrumb bar */
  .subbar{background:var(--surface);border-bottom:1px solid var(--border);padding:0 36px;height:44px;display:flex;align-items:center;gap:6px;font-size:12px;color:var(--muted);}
  .subbar .crumb{color:var(--text);font-weight:500;}

  /* Main wrapper */
  .wrapper{max-width:1400px;margin:0 auto;padding:32px 28px;}

  /* Welcome bar */
  .welcome{background:linear-gradient(135deg,#1a2d4a 0%,#1a5c96 100%);border-radius:14px;padding:28px 32px;margin-bottom:28px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 4px 18px rgba(26,44,74,.2);}
  .welcome h2{font-family:'Inter',sans-serif;font-size:20px;font-weight:700;color:#fff;margin-bottom:4px;}
  .welcome p{font-size:13px;color:rgba(255,255,255,.6);}
  .welcome-badge{background:rgba(240,165,0,.2);border:1px solid rgba(240,165,0,.4);border-radius:8px;padding:8px 18px;font-size:12px;font-weight:600;color:var(--gold);letter-spacing:.5px;}

  /* Stats strip */
  .stats-strip{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px;}
  @media(max-width:700px){.stats-strip{grid-template-columns:repeat(2,1fr);}}
  .stat-card{background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:20px 22px;display:flex;align-items:center;gap:14px;box-shadow:0 1px 4px rgba(0,0,0,.05);transition:transform .2s,box-shadow .2s;}
  .stat-card:hover{transform:translateY(-2px);box-shadow:0 4px 16px rgba(0,0,0,.09);}
  .stat-icon{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;}
  .stat-val{font-family:'Inter',sans-serif;font-size:24px;font-weight:700;color:var(--text);}
  .stat-lbl{font-size:12px;color:var(--muted);margin-top:1px;}

  /* Section title */
  .section-title{font-family:'Inter',sans-serif;font-size:15px;font-weight:700;color:var(--text);margin-bottom:16px;display:flex;align-items:center;gap:8px;}
  .section-title::after{content:'';flex:1;height:1px;background:var(--border);}

  /* Nav grid */
  .nav-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:32px;}
  @media(max-width:1100px){.nav-grid{grid-template-columns:repeat(3,1fr);}}
  @media(max-width:700px){.nav-grid{grid-template-columns:repeat(2,1fr);}}
  .nav-card{position:relative;display:block;text-decoration:none;background:var(--surface);border:1px solid var(--border);border-left:4px solid var(--card-color,var(--accent));border-radius:10px;padding:20px;overflow:hidden;transition:all .25s cubic-bezier(.23,1,.32,1);box-shadow:0 1px 4px rgba(0,0,0,.05);}
  .nav-card:hover{transform:translateY(-4px);box-shadow:0 8px 28px rgba(0,0,0,.1);border-color:var(--card-color,var(--accent));}
  .card-icon{font-size:26px;margin-bottom:10px;display:block;}
  .card-title{font-family:'Inter',sans-serif;font-size:14px;font-weight:700;color:var(--text);margin-bottom:4px;}
  .card-desc{font-size:11px;color:var(--muted);line-height:1.5;}
  .card-arrow{position:absolute;top:14px;right:14px;font-size:16px;color:var(--border);transition:all .2s;}
  .nav-card:hover .card-arrow{color:var(--card-color,var(--accent));transform:translate(2px,-2px);}
  .card-tag{display:inline-block;background:rgba(0,0,0,.04);border-radius:4px;padding:2px 7px;font-size:9px;letter-spacing:1px;text-transform:uppercase;color:var(--card-color,var(--muted));margin-top:8px;font-weight:600;}

  /* Footer */
  .footer{text-align:center;font-size:11px;color:var(--muted);padding:20px 0 10px;border-top:1px solid var(--border);}
  @keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
</style>
</head>
<body>
<nav class="topnav">
  <div class="brand"><span class="badge">CE</span> SitePro <span class="sub">Civil Engineering Management</span></div>
  <div class="topnav-right">
    <div class="user-chip"><span class="dot"></span>Logged In</div>
    <a href="logout.php" class="logout-btn">&#x2192; Sign Out</a>
  </div>
</nav>
<div class="subbar"><span>&#127968;</span>&nbsp;<span class="crumb">Dashboard</span>&nbsp;/&nbsp;Overview</div>

<div class="wrapper">
  <?php
  $stat = function($q) use ($conn){ return mysqli_fetch_assoc(mysqli_query($conn,$q))['c']??0; };
  $projects   = $stat("SELECT COUNT(*) c FROM projects WHERE user_id=$user_id");
  $materials  = $stat("SELECT COUNT(*) c FROM materials WHERE user_id=$user_id");
  $elements   = $stat("SELECT COUNT(*) c FROM elements e JOIN projects p ON e.ProjectID=p.ProjectID WHERE p.user_id=$user_id");
  $clients    = $stat("SELECT COUNT(*) c FROM clients WHERE user_id=$user_id");
  $contractors= $stat("SELECT COUNT(*) c FROM contractors WHERE user_id=$user_id");
  $suppliers  = $stat("SELECT COUNT(*) c FROM suppliers WHERE user_id=$user_id");
  $totalco2   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COALESCE(SUM(e.Volume*m.EmissionFactor),0) t FROM elements e JOIN materials m ON e.MaterialID=m.MaterialID JOIN projects p ON e.ProjectID=p.ProjectID WHERE p.user_id=$user_id"))['t']??0;
  ?>

  <div class="welcome" style="animation:fadeUp .4s ease both">
    <div>
      <h2>&#128119; Welcome to SitePro CE</h2>
      <p>Your civil engineering project management &amp; carbon tracking workspace.</p>
    </div>
    <div class="welcome-badge">&#9745; Active Session</div>
  </div>

  <div class="stats-strip" style="animation:fadeUp .4s ease .05s both">
    <div class="stat-card">
      <div class="stat-icon" style="background:#e8f0f9">&#127959;</div>
      <div><div class="stat-val"><?=$projects?></div><div class="stat-lbl">Projects</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:#e8f5e9">&#127970;</div>
      <div><div class="stat-val"><?=$clients?></div><div class="stat-lbl">Clients</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:#fff8e1">&#128119;</div>
      <div><div class="stat-val"><?=$contractors?></div><div class="stat-lbl">Contractors</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:#fce4ec">&#128200;</div>
      <div><div class="stat-val"><?=number_format($totalco2,0)?></div><div class="stat-lbl">Total CO&#8322; (kg)</div></div>
    </div>
  </div>

  <div class="section-title" style="animation:fadeUp .4s ease .1s both">&#128196; Project Management</div>
  <div class="nav-grid" style="animation:fadeUp .4s ease .12s both">
    <a href="projects.php" class="nav-card" style="--card-color:#1a5c96"><span class="card-arrow">&#8599;</span><span class="card-icon">&#127959;</span><div class="card-title">Projects</div><div class="card-desc">Manage construction projects, budgets, status and timelines.</div><span class="card-tag">Core</span></a>
    <a href="clients.php" class="nav-card" style="--card-color:#2e7d32"><span class="card-arrow">&#8599;</span><span class="card-icon">&#127970;</span><div class="card-title">Clients</div><div class="card-desc">Track client organisations and their project associations.</div><span class="card-tag">CRM</span></a>
    <a href="contractors.php" class="nav-card" style="--card-color:#f0a500"><span class="card-arrow">&#8599;</span><span class="card-icon">&#128119;</span><div class="card-title">Contractors</div><div class="card-desc">Register contractors, PEC licenses and specialties.</div><span class="card-tag">Workforce</span></a>
    <a href="teams.php" class="nav-card" style="--card-color:#00838f"><span class="card-arrow">&#8599;</span><span class="card-icon">&#128101;</span><div class="card-title">Teams</div><div class="card-desc">Project teams, members, designations and hourly rates.</div><span class="card-tag">HR</span></a>
  </div>

  <div class="section-title" style="animation:fadeUp .4s ease .2s both">&#9874; Site Resources</div>
  <div class="nav-grid" style="animation:fadeUp .4s ease .22s both">
    <a href="materials.php" class="nav-card" style="--card-color:#5c4033"><span class="card-arrow">&#8599;</span><span class="card-icon">&#129521;</span><div class="card-title">Materials</div><div class="card-desc">CO&#8322; factors, categories, density and recycled content.</div><span class="card-tag">Configure</span></a>
    <a href="suppliers.php" class="nav-card" style="--card-color:#1565c0"><span class="card-arrow">&#8599;</span><span class="card-icon">&#128666;</span><div class="card-title">Suppliers</div><div class="card-desc">Supplier directory, green certifications and pricing.</div><span class="card-tag">Procurement</span></a>
    <a href="equipment.php" class="nav-card" style="--card-color:#e67e22"><span class="card-arrow">&#8599;</span><span class="card-icon">&#9874;</span><div class="card-title">Equipment</div><div class="card-desc">Track machinery CO&#8322; per hour, fuel type and daily rates.</div><span class="card-tag">Assets</span></a>
    <a href="elements.php" class="nav-card" style="--card-color:#6a1b9a"><span class="card-arrow">&#8599;</span><span class="card-icon">&#128202;</span><div class="card-title">Elements</div><div class="card-desc">Log construction elements, phases and calculate footprint.</div><span class="card-tag">Calculate</span></a>
  </div>

  <div class="section-title" style="animation:fadeUp .4s ease .3s both">&#9989; Compliance &amp; Reporting</div>
  <div class="nav-grid" style="animation:fadeUp .4s ease .32s both">
    <a href="inspections.php" class="nav-card" style="--card-color:#c62828"><span class="card-arrow">&#8599;</span><span class="card-icon">&#128269;</span><div class="card-title">Inspections</div><div class="card-desc">Structural, environmental and safety inspection records.</div><span class="card-tag">Compliance</span></a>
    <a href="certifications.php" class="nav-card" style="--card-color:#00695c"><span class="card-arrow">&#8599;</span><span class="card-icon">&#127941;</span><div class="card-title">Certifications</div><div class="card-desc">LEED, BREEAM, ISO 14001 project certification tracking.</div><span class="card-tag">Standards</span></a>
    <a href="targets.php" class="nav-card" style="--card-color:#f57f17"><span class="card-arrow">&#8599;</span><span class="card-icon">&#127919;</span><div class="card-title">CO&#8322; Targets</div><div class="card-desc">Set and monitor carbon reduction targets per project.</div><span class="card-tag">Goals</span></a>
    <a href="report.php" class="nav-card" style="--card-color:#4527a0"><span class="card-arrow">&#8599;</span><span class="card-icon">&#128196;</span><div class="card-title">Reports</div><div class="card-desc">Visual dashboards, charts and downloadable emission reports.</div><span class="card-tag">Analyze</span></a>
  </div>

  <div class="footer">&copy; <?=date('Y')?> SitePro CE &mdash; Civil Engineering Management System &mdash; SE-24004 &middot; SE-24012</div>
</div>
</body>
</html>
