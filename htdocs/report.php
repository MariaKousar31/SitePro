<?php include 'init.php'; ?>
<?php
// Fetch all project CO2 data
$projectData = [];
$res = mysqli_query($conn, "
  SELECT p.ProjectID, p.ProjectName as PName, p.Location,
         SUM(e.Volume * m.EmissionFactor) as TotalCO2,
         COUNT(e.ElementID) as Elements
  FROM projects p
  LEFT JOIN elements e ON p.ProjectID=e.ProjectID
  LEFT JOIN materials m ON e.MaterialID=m.MaterialID
  WHERE p.user_id=$user_id
  GROUP BY p.ProjectID
  ORDER BY TotalCO2 DESC
");
while ($r = mysqli_fetch_assoc($res)) $projectData[] = $r;

// Fetch material breakdown
$matBreakdown = [];
$mres = mysqli_query($conn, "
  SELECT m.MaterialName, SUM(e.Volume * m.EmissionFactor) as CO2, SUM(e.Volume) as TotalVol, m.Unit
  FROM elements e
  JOIN materials m ON e.MaterialID=m.MaterialID
  JOIN projects p ON e.ProjectID=p.ProjectID
  WHERE p.user_id=$user_id
  GROUP BY m.MaterialID
  ORDER BY CO2 DESC
");
while ($r = mysqli_fetch_assoc($mres)) $matBreakdown[] = $r;

// Fetch timeline data (by calcdate)
$timeline = [];
$tres = mysqli_query($conn, "
  SELECT DATE(ec.CalcDate) as d, SUM(ec.CO2_Emission) as dayco2
  FROM emissioncalculation ec
  JOIN elements e ON ec.ElementID=e.ElementID
  JOIN projects p ON e.ProjectID=p.ProjectID
  WHERE p.user_id=$user_id
  GROUP BY DATE(ec.CalcDate)
  ORDER BY d ASC
  LIMIT 30
");
while ($r = mysqli_fetch_assoc($tres)) $timeline[] = $r;

$totalCO2 = array_sum(array_column($projectData, 'TotalCO2'));
$totalProjects = count($projectData);
$avgCO2 = $totalProjects > 0 ? $totalCO2 / $totalProjects : 0;

// AI Suggestions based on materials
$suggestions = [];
$highEmitters = array_filter($matBreakdown, fn($m) => $m['CO2'] > ($totalCO2 * 0.2));
foreach ($highEmitters as $m) {
    $name = $m['MaterialName'];
    $co2 = round($m['CO2'], 1);
    $pct = $totalCO2 > 0 ? round($m['CO2'] / $totalCO2 * 100, 1) : 0;
    if (stripos($name,'concrete')!==false || stripos($name,'cement')!==false) {
        $suggestions[] = ["icon"=>"♻️","title"=>"Replace Portland Cement in $name","body"=>"$name contributes $co2 kg CO₂ ($pct% of total). Consider substituting 30–50% of Portland cement with supplementary cementitious materials (SCMs) like fly ash, GGBS, or silica fume. This can reduce embodied carbon by 20–40%.","impact"=>"high","saving"=>round($m['CO2']*0.35,1)];
    } elseif (stripos($name,'steel')!==false || stripos($name,'rebar')!==false) {
        $suggestions[] = ["icon"=>"🔄","title"=>"Specify Recycled-Content Steel","body"=>"$name accounts for $co2 kg CO₂ ($pct%). Switching to Electric Arc Furnace (EAF) steel with ≥90% recycled content can cut emissions by up to 75% vs virgin BOF steel.","impact"=>"high","saving"=>round($m['CO2']*0.65,1)];
    } elseif (stripos($name,'aluminum')!==false || stripos($name,'aluminium')!==false) {
        $suggestions[] = ["icon"=>"⚡","title"=>"Specify Low-Carbon Aluminum","body"=>"Aluminum at $co2 kg CO₂ ($pct%). Request primary aluminum produced with hydropower, or use secondary (recycled) aluminum which cuts emissions by up to 95%.","impact"=>"high","saving"=>round($m['CO2']*0.75,1)];
    } else {
        $suggestions[] = ["icon"=>"📦","title"=>"Optimize $name Quantities","body"=>"$name contributes $co2 kg CO₂ ($pct%). Run a material efficiency audit: optimizing design geometry and reducing waste can cut material use by 10–20%.","impact"=>"medium","saving"=>round($m['CO2']*0.15,1)];
    }
}
// General suggestions always shown
$suggestions[] = ["icon"=>"🌳","title"=>"Carbon Offset Program","body"=>"Consider enrolling in a verified carbon offset program (Gold Standard or VCS) to neutralize unavoidable emissions while decarbonization measures are implemented.","impact"=>"medium","saving"=>0];
$suggestions[] = ["icon"=>"📐","title"=>"Design for Disassembly","body"=>"Adopt design-for-disassembly principles to enable material reuse at end-of-life. This reduces lifecycle CO₂ by 15–30% and aligns with circular economy standards.","impact"=>"low","saving"=>round($totalCO2*0.2,1)];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CO₂ Report — SitePro CE</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=DM+Sans:wght@400;500;600" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<style>
  :root{--bg:#f0f4f8;--surface:#ffffff;--surface2:#f7f9fc;--border:#dde3ec;--accent:#1a5c96;--accent-light:#e8f0f9;--accent-glow:rgba(26,92,150,0.12);--text:#1a2332;--muted:#6b7a90;--danger:#c0392b;--success:#1a7a4a;--header-bg:#1a2d4a;--gold:#f0a500;}
  *{margin:0;padding:0;box-sizing:border-box;}
  body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;min-height:100vh;}
  body::before{display:none}
  .layout{position:relative;z-index:1;max-width:1280px;margin:0 auto;padding:40px 24px;}
  .topbar{display:flex;align-items:center;gap:16px;margin-bottom:48px;flex-wrap:wrap;}
  .back-btn{display:inline-flex;align-items:center;gap:8px;background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:10px 18px;text-decoration:none;color:var(--muted);font-size:12px;letter-spacing:1px;transition:all 0.3s ease;}
  .back-btn:hover{border-color:var(--accent);color:var(--accent);transform:translateX(-3px);}
  .page-title{font-family:'Syne',sans-serif;font-size:32px;font-weight:800;}
  .page-title span{color:var(--accent);}
  .breadcrumb{font-size:11px;color:var(--muted);letter-spacing:1px;}
  .btn-dl{display:inline-flex;align-items:center;gap:8px;background:var(--accent);border:none;border-radius:12px;padding:12px 24px;color:#000;font-family:'Syne',sans-serif;font-size:13px;font-weight:700;cursor:pointer;letter-spacing:1px;transition:all 0.3s ease;text-decoration:none;}
  .btn-dl:hover{transform:translateY(-2px);box-shadow:0 8px 24px var(--accent-glow);}
  @keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}

  /* KPI row */
  .kpi-row{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:32px;}
  .kpi{background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:24px;position:relative;overflow:hidden;animation:fadeUp 0.5s ease both;}
  .kpi::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:var(--kpi-color,var(--accent));}
  .kpi-label{font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-bottom:10px;}
  .kpi-val{font-family:'Syne',sans-serif;font-size:28px;font-weight:800;color:var(--kpi-color,var(--text));}
  .kpi-sub{font-size:11px;color:var(--muted);margin-top:6px;}
  .kpi-icon{position:absolute;top:20px;right:20px;font-size:24px;opacity:0.3;}

  /* Chart grid */
  .charts-row{display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:24px;}
  .charts-row-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-bottom:24px;}
  .card{background:var(--surface);border:1px solid var(--border);border-radius:20px;overflow:hidden;}
  .card-header{padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;}
  .card-header h3{font-family:'Syne',sans-serif;font-size:15px;font-weight:700;}
  .card-body{padding:24px;}
  .chart-container{position:relative;height:300px;}
  .chart-sm{height:220px;}

  /* Project table */
  table{width:100%;border-collapse:separate;border-spacing:0;}
  thead tr th{background:var(--surface2);padding:12px 16px;font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--muted);text-align:left;font-weight:400;border-bottom:1px solid var(--border);}
  tbody tr{transition:background 0.2s ease;}
  tbody tr:hover{background:rgba(199,125,255,0.04);}
  td{padding:14px 16px;font-size:12px;border-bottom:1px solid rgba(26,58,58,0.5);vertical-align:middle;}
  .id-badge{background:var(--surface2);border:1px solid var(--border);border-radius:6px;padding:3px 10px;font-size:11px;color:var(--muted);}
  .co2-bar-cell{display:flex;align-items:center;gap:10px;}
  .co2-bar{flex:1;height:6px;background:rgba(255,255,255,0.05);border-radius:3px;overflow:hidden;min-width:80px;}
  .co2-fill{height:100%;border-radius:3px;}

  /* Suggestions */
  .suggestions-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
  .suggestion-card{background:var(--surface2);border:1px solid var(--border);border-radius:16px;padding:22px;transition:all 0.3s ease;animation:fadeUp 0.5s ease both;}
  .suggestion-card:hover{border-color:var(--accent);transform:translateY(-3px);box-shadow:0 12px 40px var(--accent-glow);}
  .sug-top{display:flex;align-items:flex-start;gap:14px;margin-bottom:12px;}
  .sug-icon{font-size:28px;flex-shrink:0;}
  .sug-title{font-family:'Syne',sans-serif;font-size:14px;font-weight:700;line-height:1.3;}
  .sug-body{font-size:11px;color:var(--muted);line-height:1.7;}
  .sug-footer{display:flex;align-items:center;gap:10px;margin-top:14px;}
  .impact-badge{border-radius:6px;padding:4px 12px;font-size:10px;letter-spacing:1px;text-transform:uppercase;font-weight:600;}
  .impact-high{background:rgba(255,77,109,0.12);color:var(--danger);border:1px solid rgba(255,77,109,0.25);}
  .impact-medium{background:rgba(255,107,43,0.12);color:var(--orange);border:1px solid rgba(255,107,43,0.25);}
  .impact-low{background:rgba(0,229,176,0.12);color:var(--green);border:1px solid rgba(0,229,176,0.25);}
  .saving-badge{font-size:10px;color:var(--green);background:rgba(0,229,176,0.08);border:1px solid rgba(0,229,176,0.15);border-radius:6px;padding:4px 12px;}

  /* Section heading */
  .section-heading{display:flex;align-items:center;gap:16px;margin:36px 0 20px;}
  .section-heading h2{font-family:'Syne',sans-serif;font-size:22px;font-weight:800;}
  .section-heading h2 span{color:var(--accent);}
  .section-line{flex:1;height:1px;background:var(--border);}

  /* Score meter */
  .score-ring{position:relative;width:120px;height:120px;margin:0 auto 16px;}
  .score-ring svg{transform:rotate(-90deg);}
  .score-val{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;}
  .score-val .num{font-family:'Syne',sans-serif;font-size:28px;font-weight:800;color:var(--accent);}
  .score-val .lbl{font-size:9px;letter-spacing:1px;color:var(--muted);}

  /* Filter row */
  .filter-row{display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;}
  .filter-btn{background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:8px 16px;font-size:11px;color:var(--muted);cursor:pointer;transition:all 0.2s ease;font-family:'DM Sans',sans-serif;}
  .filter-btn.active,.filter-btn:hover{border-color:var(--accent);color:var(--accent);background:rgba(199,125,255,0.08);}

  .empty-state{text-align:center;padding:60px;color:var(--muted);}

  @media(max-width:900px){.kpi-row{grid-template-columns:repeat(2,1fr)}.charts-row{grid-template-columns:1fr}.charts-row-3{grid-template-columns:1fr}.suggestions-grid{grid-template-columns:1fr}}

  /* Print / PDF area */
  #pdf-area{display:none;}

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
    <div>
      <div class="page-title">Emission <span>Report</span></div>
      <div class="breadcrumb" style="margin-top:4px">HOME / REPORTS</div>
    </div>
    <div style="margin-left:auto;display:flex;gap:10px;flex-wrap:wrap">
      <button class="btn-dl" onclick="downloadPDF()" style="background:var(--green);color:#000">
        ⬇ Download PDF Report
      </button>
      <button class="btn-dl" onclick="window.print()" style="background:var(--surface);color:var(--text);border:1px solid var(--border)">
        🖨 Print
      </button>
    </div>
  </div>

  <!-- KPIs -->
  <div class="kpi-row">
    <div class="kpi" style="--kpi-color:var(--accent);animation-delay:0s">
      <div class="kpi-icon">📊</div>
      <div class="kpi-label">Total CO₂ Emissions</div>
      <div class="kpi-val"><?= number_format($totalCO2, 0) ?></div>
      <div class="kpi-sub">kg CO₂ equivalent across all projects</div>
    </div>
    <div class="kpi" style="--kpi-color:var(--blue);animation-delay:0.1s">
      <div class="kpi-icon">🏗️</div>
      <div class="kpi-label">Active Projects</div>
      <div class="kpi-val"><?= $totalProjects ?></div>
      <div class="kpi-sub">monitored construction projects</div>
    </div>
    <div class="kpi" style="--kpi-color:var(--orange);animation-delay:0.2s">
      <div class="kpi-icon">⚖️</div>
      <div class="kpi-label">Average CO₂ / Project</div>
      <div class="kpi-val"><?= number_format($avgCO2, 0) ?></div>
      <div class="kpi-sub">kg per project</div>
    </div>
    <div class="kpi" style="--kpi-color:var(--green);animation-delay:0.3s">
      <div class="kpi-icon">🧱</div>
      <div class="kpi-label">Materials Tracked</div>
      <div class="kpi-val"><?= count($matBreakdown) ?></div>
      <div class="kpi-sub">unique material types</div>
    </div>
  </div>

  <!-- Main Charts -->
  <div class="charts-row">
    <div class="card" style="animation:fadeUp 0.5s ease 0.2s both">
      <div class="card-header">
        <h3>📈 CO₂ by Project</h3>
        <span style="font-size:11px;color:var(--muted)">Bar Chart</span>
      </div>
      <div class="card-body">
        <div class="chart-container">
          <canvas id="projectChart"></canvas>
        </div>
      </div>
    </div>

    <div class="card" style="animation:fadeUp 0.5s ease 0.3s both">
      <div class="card-header">
        <h3>🥧 Material Split</h3>
      </div>
      <div class="card-body">
        <div class="chart-container">
          <canvas id="materialDonut"></canvas>
        </div>
      </div>
    </div>
  </div>

  <?php if (count($timeline) > 1): ?>
  <div class="card" style="margin-bottom:24px;animation:fadeUp 0.5s ease 0.35s both">
    <div class="card-header">
      <h3>📅 Emission Timeline</h3>
      <span style="font-size:11px;color:var(--muted)">Daily CO₂ Log</span>
    </div>
    <div class="card-body">
      <div class="chart-container">
        <canvas id="timelineChart"></canvas>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Project Table -->
  <div class="section-heading">
    <h2>Project <span>Breakdown</span></h2>
    <div class="section-line"></div>
  </div>

  <div class="card" style="margin-bottom:32px;animation:fadeUp 0.5s ease 0.4s both">
    <div style="overflow-x:auto">
      <?php if (empty($projectData)): ?>
        <div class="empty-state">No project data yet. Add elements to see the report.</div>
      <?php else: ?>
      <?php $maxProjCO2 = max(array_column($projectData, 'TotalCO2') ?: [1]); ?>
      <table>
        <thead>
          <tr><th>Project</th><th>Location</th><th>Elements</th><th>Total CO₂ (kg)</th><th>Share</th><th>Intensity</th></tr>
        </thead>
        <tbody>
          <?php foreach ($projectData as $i => $p):
            $share = $totalCO2 > 0 ? round($p['TotalCO2'] / $totalCO2 * 100, 1) : 0;
            $pct = $maxProjCO2 > 0 ? ($p['TotalCO2'] / $maxProjCO2) * 100 : 0;
            $color = $pct > 66 ? '#ff4d6d' : ($pct > 33 ? '#ff6b2b' : '#00e5b0');
          ?>
          <tr>
            <td>
              <div style="font-family:'Syne',sans-serif;font-weight:600;font-size:14px"><?= htmlspecialchars($p['PName']) ?></div>
            </td>
            <td style="color:var(--muted)"><?= htmlspecialchars($p['Location'] ?? '—') ?></td>
            <td><?= $p['Elements'] ?></td>
            <td>
              <span style="font-family:'Syne',sans-serif;font-size:15px;font-weight:700;color:<?= $color ?>"><?= number_format($p['TotalCO2'], 1) ?></span>
            </td>
            <td><?= $share ?>%</td>
            <td>
              <div class="co2-bar-cell">
                <div class="co2-bar">
                  <div class="co2-fill" style="width:<?= $pct ?>%;background:<?= $color ?>"></div>
                </div>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>

  <!-- Material breakdown table -->
  <?php if (!empty($matBreakdown)): ?>
  <div class="section-heading">
    <h2>Material <span>Analysis</span></h2>
    <div class="section-line"></div>
  </div>
  <div class="card" style="margin-bottom:32px;animation:fadeUp 0.5s ease 0.5s both">
    <div style="overflow-x:auto">
      <?php $maxMatCO2 = max(array_column($matBreakdown, 'CO2') ?: [1]); ?>
      <table>
        <thead>
          <tr><th>Material</th><th>Total Volume</th><th>Unit</th><th>CO₂ Emitted (kg)</th><th>% of Total</th><th>Bar</th></tr>
        </thead>
        <tbody>
          <?php foreach ($matBreakdown as $m):
            $share = $totalCO2 > 0 ? round($m['CO2'] / $totalCO2 * 100, 1) : 0;
            $pct = $maxMatCO2 > 0 ? ($m['CO2'] / $maxMatCO2) * 100 : 0;
            $color = $pct > 66 ? '#ff4d6d' : ($pct > 33 ? '#ff6b2b' : '#00e5b0');
          ?>
          <tr>
            <td style="font-weight:600"><?= htmlspecialchars($m['MaterialName']) ?></td>
            <td><?= number_format($m['TotalVol'], 2) ?></td>
            <td style="color:var(--muted)"><?= htmlspecialchars($m['Unit']) ?></td>
            <td><span style="font-family:'Syne',sans-serif;font-size:14px;font-weight:700;color:<?= $color ?>"><?= number_format($m['CO2'], 2) ?></span></td>
            <td><?= $share ?>%</td>
            <td>
              <div class="co2-bar" style="min-width:100px">
                <div class="co2-fill" style="width:<?= $pct ?>%;background:<?= $color ?>"></div>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <!-- AI Suggestions -->
  <div class="section-heading">
    <h2>Optimization <span>Recommendations</span></h2>
    <div class="section-line"></div>
  </div>
  <p style="font-family:'Crimson Pro',serif;font-style:italic;color:var(--muted);margin-bottom:24px;font-size:16px">
    AI-powered suggestions based on your project materials and emission patterns:
  </p>

  <div class="suggestions-grid">
    <?php foreach ($suggestions as $i => $s): ?>
    <div class="suggestion-card" style="animation-delay:<?= $i*0.08 ?>s">
      <div class="sug-top">
        <div class="sug-icon"><?= $s['icon'] ?></div>
        <div class="sug-title"><?= htmlspecialchars($s['title']) ?></div>
      </div>
      <div class="sug-body"><?= htmlspecialchars($s['body']) ?></div>
      <div class="sug-footer">
        <span class="impact-badge impact-<?= $s['impact'] ?>"><?= strtoupper($s['impact']) ?> IMPACT</span>
        <?php if ($s['saving'] > 0): ?>
        <span class="saving-badge">💚 Save ~<?= number_format($s['saving'], 0) ?> kg CO₂</span>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div style="height:60px"></div>
</div>

<script>
// ——— Chart.js Global Defaults ———
Chart.defaults.color = '#4a8a82';
Chart.defaults.borderColor = '#1a3a3a';
Chart.defaults.font.family = "'DM Mono', monospace";

// ——— Project Bar Chart ———
const projLabels = <?= json_encode(array_column($projectData, 'PName')) ?>;
const projCO2 = <?= json_encode(array_map(fn($p) => round($p['TotalCO2'],2), $projectData)) ?>;

if (projLabels.length > 0) {
  const pctx = document.getElementById('projectChart').getContext('2d');
  const gradient = pctx.createLinearGradient(0, 0, 0, 300);
  gradient.addColorStop(0, 'rgba(199,125,255,0.8)');
  gradient.addColorStop(1, 'rgba(199,125,255,0.1)');

  new Chart(pctx, {
    type: 'bar',
    data: {
      labels: projLabels,
      datasets: [{
        label: 'CO₂ Emissions (kg)',
        data: projCO2,
        backgroundColor: gradient,
        borderColor: '#c77dff',
        borderWidth: 1,
        borderRadius: 8,
        borderSkipped: false,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false }, tooltip: { backgroundColor: '#0c1f1f', borderColor: '#1a3a3a', borderWidth: 1, padding: 12 } },
      scales: {
        y: { grid: { color: 'rgba(26,58,58,0.5)' }, ticks: { callback: v => v.toLocaleString() + ' kg' } },
        x: { grid: { display: false } }
      }
    }
  });
}

// ——— Material Donut ———
const matLabels = <?= json_encode(array_column($matBreakdown, 'MaterialName')) ?>;
const matCO2 = <?= json_encode(array_map(fn($m) => round($m['CO2'],2), $matBreakdown)) ?>;

if (matLabels.length > 0) {
  const dctx = document.getElementById('materialDonut').getContext('2d');
  const colors = ['#c77dff','#00e5b0','#ff6b2b','#4d9eff','#ff4d6d','#7efff5','#ffd60a'];
  new Chart(dctx, {
    type: 'doughnut',
    data: {
      labels: matLabels,
      datasets: [{
        data: matCO2,
        backgroundColor: colors.slice(0, matLabels.length),
        borderColor: '#030a0a',
        borderWidth: 3,
        hoverOffset: 8
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      cutout: '68%',
      plugins: {
        legend: { position: 'bottom', labels: { padding: 14, boxWidth: 12, font: { size: 11 } } },
        tooltip: { backgroundColor: '#0c1f1f', borderColor: '#1a3a3a', borderWidth: 1, padding: 12 }
      }
    }
  });
}

// ——— Timeline Chart ———
<?php if (count($timeline) > 1): ?>
const tlLabels = <?= json_encode(array_column($timeline, 'd')) ?>;
const tlData = <?= json_encode(array_map(fn($t) => round($t['dayco2'],2), $timeline)) ?>;
const tctx = document.getElementById('timelineChart').getContext('2d');
const tlGrad = tctx.createLinearGradient(0, 0, 0, 300);
tlGrad.addColorStop(0, 'rgba(0,229,176,0.3)');
tlGrad.addColorStop(1, 'rgba(0,229,176,0)');
new Chart(tctx, {
  type: 'line',
  data: {
    labels: tlLabels,
    datasets: [{
      label: 'Daily CO₂ (kg)',
      data: tlData,
      borderColor: '#00e5b0',
      backgroundColor: tlGrad,
      borderWidth: 2,
      pointBackgroundColor: '#00e5b0',
      pointRadius: 4,
      fill: true,
      tension: 0.4
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false }, tooltip: { backgroundColor: '#0c1f1f', borderColor: '#1a3a3a', borderWidth: 1, padding: 12 } },
    scales: {
      y: { grid: { color: 'rgba(26,58,58,0.5)' }, ticks: { callback: v => v + ' kg' } },
      x: { grid: { display: false } }
    }
  }
});
<?php endif; ?>

// ——— PDF Download ———
async function downloadPDF() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
  const W = doc.internal.pageSize.getWidth();
  const H = doc.internal.pageSize.getHeight();
  let y = 0;

  // Background
  doc.setFillColor(3, 10, 10);
  doc.rect(0, 0, W, H, 'F');

  // Header band
  doc.setFillColor(7, 20, 20);
  doc.rect(0, 0, W, 40, 'F');
  doc.setDrawColor(199, 125, 255);
  doc.setLineWidth(0.5);
  doc.line(0, 40, W, 40);

  // Logo / Title
  doc.setFont('helvetica', 'bold');
  doc.setFontSize(22);
  doc.setTextColor(199, 125, 255);
  doc.text('SitePro CE', 14, 18);
  doc.setFontSize(10);
  doc.setTextColor(74, 138, 130);
  doc.text('Carbon Emission Report', 14, 26);
  doc.text('Generated: ' + new Date().toLocaleDateString(), 14, 33);

  // Right side — total
  doc.setFont('helvetica', 'bold');
  doc.setFontSize(18);
  doc.setTextColor(0, 229, 176);
  doc.text('<?= number_format($totalCO2, 0) ?> kg CO₂', W - 14, 20, { align: 'right' });
  doc.setFontSize(9);
  doc.setTextColor(74, 138, 130);
  doc.text('TOTAL EMISSIONS', W - 14, 27, { align: 'right' });
  doc.text('<?= $totalProjects ?> Projects · <?= count($matBreakdown) ?> Materials', W - 14, 33, { align: 'right' });

  y = 52;

  // KPI Row
  const kpis = [
    { label: 'Total CO₂', val: '<?= number_format($totalCO2, 0) ?> kg' },
    { label: 'Projects', val: '<?= $totalProjects ?>' },
    { label: 'Avg / Project', val: '<?= number_format($avgCO2, 0) ?> kg' },
    { label: 'Materials', val: '<?= count($matBreakdown) ?>' },
  ];
  const kw = (W - 28 - 12) / 4;
  kpis.forEach((k, i) => {
    const x = 14 + i * (kw + 4);
    doc.setFillColor(12, 31, 31);
    doc.roundedRect(x, y, kw, 18, 2, 2, 'F');
    doc.setDrawColor(26, 58, 58);
    doc.setLineWidth(0.3);
    doc.roundedRect(x, y, kw, 18, 2, 2, 'S');
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(11);
    doc.setTextColor(212, 240, 236);
    doc.text(k.val, x + kw/2, y + 10, { align: 'center' });
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(7);
    doc.setTextColor(74, 138, 130);
    doc.text(k.label.toUpperCase(), x + kw/2, y + 15, { align: 'center' });
  });
  y += 28;

  // Section: Projects
  doc.setFont('helvetica', 'bold');
  doc.setFontSize(12);
  doc.setTextColor(199, 125, 255);
  doc.text('PROJECT BREAKDOWN', 14, y);
  doc.setDrawColor(199, 125, 255);
  doc.setLineWidth(0.3);
  doc.line(14, y + 2, W - 14, y + 2);
  y += 10;

  // Project rows
  const headers = ['Project', 'Location', 'Elements', 'CO₂ (kg)', 'Share'];
  const colW = [55, 40, 22, 30, 22];
  let x = 14;
  doc.setFillColor(12, 31, 31);
  doc.rect(14, y - 4, W - 28, 8, 'F');
  doc.setFont('helvetica', 'bold');
  doc.setFontSize(8);
  doc.setTextColor(74, 138, 130);
  headers.forEach((h, i) => { doc.text(h, x + 2, y); x += colW[i]; });
  y += 5;

  doc.setFont('helvetica', 'normal');
  doc.setFontSize(9);
  <?php foreach ($projectData as $p):
    $share = $totalCO2 > 0 ? round($p['TotalCO2']/$totalCO2*100,1) : 0;
  ?>
  {
    const row = [
      '<?= addslashes(substr($p['PName'],0,28)) ?>',
      '<?= addslashes(substr($p['Location']??'—',0,20)) ?>',
      '<?= $p['Elements'] ?>',
      '<?= number_format($p['TotalCO2'],1) ?>',
      '<?= $share ?>%'
    ];
    let rx = 14;
    doc.setTextColor(212, 240, 236);
    row.forEach((v, i) => { doc.text(v, rx + 2, y); rx += [55,40,22,30,22][i]; });
    doc.setDrawColor(26, 58, 58);
    doc.setLineWidth(0.2);
    doc.line(14, y + 2, W - 14, y + 2);
    y += 8;
    if (y > H - 30) { doc.addPage(); doc.setFillColor(3,10,10); doc.rect(0,0,W,H,'F'); y = 20; }
  }
  <?php endforeach; ?>

  y += 6;

  // Section: Materials
  if (y > H - 50) { doc.addPage(); doc.setFillColor(3,10,10); doc.rect(0,0,W,H,'F'); y = 20; }
  doc.setFont('helvetica', 'bold');
  doc.setFontSize(12);
  doc.setTextColor(0, 229, 176);
  doc.text('MATERIAL ANALYSIS', 14, y);
  doc.setDrawColor(0, 229, 176);
  doc.line(14, y + 2, W - 14, y + 2);
  y += 10;

  const mHeaders = ['Material', 'Volume', 'Unit', 'CO₂ (kg)', '% Total'];
  const mColW = [55, 30, 20, 35, 20];
  x = 14;
  doc.setFillColor(12, 31, 31);
  doc.rect(14, y - 4, W - 28, 8, 'F');
  doc.setFont('helvetica', 'bold');
  doc.setFontSize(8);
  doc.setTextColor(74, 138, 130);
  mHeaders.forEach((h, i) => { doc.text(h, x + 2, y); x += mColW[i]; });
  y += 5;

  doc.setFont('helvetica', 'normal');
  doc.setFontSize(9);
  <?php foreach ($matBreakdown as $m):
    $share = $totalCO2 > 0 ? round($m['CO2']/$totalCO2*100,1) : 0;
  ?>
  {
    const row = ['<?= addslashes(substr($m['MaterialName'],0,28)) ?>','<?= number_format($m['TotalVol'],2) ?>','<?= htmlspecialchars($m['Unit']) ?>','<?= number_format($m['CO2'],2) ?>','<?= $share ?>%'];
    let rx = 14;
    doc.setTextColor(212, 240, 236);
    row.forEach((v, i) => { doc.text(v, rx + 2, y); rx += [55,30,20,35,20][i]; });
    doc.setDrawColor(26, 58, 58);
    doc.setLineWidth(0.2);
    doc.line(14, y + 2, W - 14, y + 2);
    y += 8;
    if (y > H - 30) { doc.addPage(); doc.setFillColor(3,10,10); doc.rect(0,0,W,H,'F'); y = 20; }
  }
  <?php endforeach; ?>

  y += 8;

  // Section: Suggestions
  if (y > H - 50) { doc.addPage(); doc.setFillColor(3,10,10); doc.rect(0,0,W,H,'F'); y = 20; }
  doc.setFont('helvetica', 'bold');
  doc.setFontSize(12);
  doc.setTextColor(255, 107, 43);
  doc.text('OPTIMIZATION RECOMMENDATIONS', 14, y);
  doc.setDrawColor(255, 107, 43);
  doc.line(14, y + 2, W - 14, y + 2);
  y += 10;

  const sugs = <?= json_encode($suggestions) ?>;
  sugs.forEach(s => {
    if (y > H - 35) { doc.addPage(); doc.setFillColor(3,10,10); doc.rect(0,0,W,H,'F'); y = 20; }
    doc.setFillColor(12, 31, 31);
    const lines = doc.splitTextToSize(s.body, W - 36);
    const bh = 12 + lines.length * 5;
    doc.roundedRect(14, y - 5, W - 28, bh, 2, 2, 'F');
    doc.setDrawColor(26, 58, 58);
    doc.roundedRect(14, y - 5, W - 28, bh, 2, 2, 'S');
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(9);
    doc.setTextColor(212, 240, 236);
    doc.text(s.icon + ' ' + s.title, 18, y + 1);
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(8);
    doc.setTextColor(74, 138, 130);
    lines.forEach((line, li) => { doc.text(line, 18, y + 7 + li * 5); });
    if (s.saving > 0) {
      doc.setTextColor(0, 229, 176);
      doc.text('Potential saving: ~' + s.saving.toLocaleString() + ' kg CO₂', 18, y + 7 + lines.length * 5);
      y += bh + 8;
    } else {
      y += bh + 6;
    }
  });

  // Footer
  doc.setFillColor(7, 20, 20);
  doc.rect(0, H - 12, W, 12, 'F');
  doc.setFont('helvetica', 'normal');
  doc.setFontSize(7);
  doc.setTextColor(74, 138, 130);
  doc.text('SitePro CE — Carbon Emission Intelligence System', 14, H - 5);
  doc.text('Generated on ' + new Date().toLocaleString(), W - 14, H - 5, { align: 'right' });

  doc.save('CarbonTrack_Report_' + new Date().toISOString().split('T')[0] + '.pdf');
}
</script>

<style>
@media print {
  body::before { display: none; }
  .btn-dl, .back-btn { display: none !important; }
  .card { break-inside: avoid; }
}
</style>
</body>
</html>
