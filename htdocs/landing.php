<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>SitePro CE — Emission Intelligence Platform</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=DM+Sans:wght@400;500;600" rel="stylesheet">
<style>
:root {
  --bg: #eef2f7;
  --surface: #ffffff;
  --surface2: #f7f9fc;
  --border: #dde3ec;
  --accent: #1a5c96;
  --accent2: #f0a500;
  --accent3: #4a90e2;
  --text: #1a2332;
  --muted: #6b7a90;
}
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
html { scroll-behavior: smooth; }

body {

  background: var(--bg);

  color: var(--text);
  font-family: 'DM Sans', sans-serif;
  min-height: 100vh;
  overflow-x: hidden;
}

body::before {
  content: '';
  position: fixed; inset: 0;
  background-image:
    linear-gradient(rgba(0,180,229,.025) 1px, transparent 1px),
    linear-gradient(90deg, rgba(0,180,229,.025) 1px, transparent 1px);
  background-size: 60px 60px;
  animation: gridPan 25s linear infinite;
  z-index: 0;
}
@keyframes gridPan { to { background-position: 60px 60px; } }

.orb {
  position: fixed; border-radius: 50%;
  filter: blur(130px); opacity: .11; z-index: 0;
  animation: orbDrift 10s ease-in-out infinite alternate;
}
.orb1 { width: 700px; height: 700px; background: #00b4e5; top: -250px; left: -200px; }
.orb2 { width: 500px; height: 500px; background: #ff6b2b; bottom: -150px; right: -150px; animation-delay: -4s; }
.orb3 { width: 350px; height: 350px; background: #7ec8ff; top: 40%; left: 55%; animation-delay: -7s; }
@keyframes orbDrift { to { transform: translate(40px, -40px) scale(1.08); } }

body::after {
  content: '';
  position: fixed; inset: 0;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
  opacity: .025; z-index: 0; pointer-events: none;
}

.z { position: relative; z-index: 1; }
.wrap { max-width: 1100px; margin: 0 auto; padding: 0 28px; }

nav {
  position: fixed; top: 0; left: 0; right: 0;
  z-index: 100;
  padding: 18px 40px;
  display: flex; align-items: center; justify-content: space-between;

  background: #1a2d4a;
  border-bottom: none;

  border-bottom: 1px solid rgba(26,47,74,.5);
}

/* ── NAV LOGO — matches login badge style ── */
.nav-logo {
  font-family: 'Inter', sans-serif;
  font-weight: 800;
  font-size: 16px;
  color: #fff;
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
}
.nav-logo .badge {
  background: var(--accent);
  color: #000;
  border-radius: 5px;
  padding: 2px 9px;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: .5px;
}

.nav-pill {
  display: flex; align-items: center;
  background: rgba(0,180,229,.06);
  border: 1px solid rgba(0,180,229,.15);
  border-radius: 8px; overflow: hidden;
}
.nav-pill a {
  padding: 9px 22px;
  font-family: 'DM Sans', sans-serif;
  font-size: 12px; font-weight: 700;
  letter-spacing: .5px;
  text-decoration: none;
  transition: all .25s;
}
.nav-pill a:first-child {
  color: var(--muted);
  border-right: 1px solid rgba(26,47,74,.8);
}
.nav-pill a:first-child:hover { color: var(--text); background: rgba(255,255,255,.04); }
.nav-pill a:last-child { background: var(--accent); color: #000; }
.nav-pill a:last-child:hover { background: var(--accent3); color: #000; }

.hero {
  min-height: 100vh;
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  text-align: center;
  padding: 120px 28px 80px;
}
.hero-eyebrow {
  display: inline-flex; align-items: center; gap: 10px;
  background: rgba(0,180,229,.07);
  border: 1px solid rgba(0,180,229,.2);
  border-radius: 100px;
  padding: 7px 20px;
  font-size: 10px; letter-spacing: 3px; text-transform: uppercase;
  color: var(--accent); margin-bottom: 32px;
  animation: fadeUp .8s ease both;
}
.hero-eyebrow::before {
  content: '';
  width: 7px; height: 7px;
  background: var(--accent); border-radius: 50%;
  animation: pulse 2s ease-in-out infinite;
}
@keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.3;transform:scale(.7)} }

.hero-title {
  font-family: 'DM Sans', sans-serif;
  font-size: clamp(52px, 9vw, 110px);
  font-weight: 800;
  line-height: .92;
  letter-spacing: -3px;
  animation: fadeUp .8s ease .1s both;
  margin-bottom: 8px;
}
.hero-title .solid { color: var(--text); display: block; }
.hero-title .outline { color: transparent; -webkit-text-stroke: 1.5px var(--accent); display: block; }
.hero-title .sub-word {
  font-family: 'Georgia', serif;
  font-style: italic; font-weight: 400;
  font-size: clamp(40px, 7vw, 88px);
  color: var(--accent2); -webkit-text-stroke: 0; letter-spacing: -1px;
}

.hero-desc {
  max-width: 520px; margin: 28px auto 44px;
  font-size: 14px; line-height: 1.8; color: var(--muted);
  animation: fadeUp .8s ease .2s both;
}

.hero-cta {
  display: flex; gap: 14px; justify-content: center; flex-wrap: wrap;
  animation: fadeUp .8s ease .3s both;
}
.btn-primary {
  display: inline-flex; align-items: center; gap: 8px;
  background: var(--accent); color: #000;
  font-family: 'DM Sans', sans-serif;
  font-size: 13px; font-weight: 800; letter-spacing: .5px;
  padding: 15px 34px; border-radius: 12px; text-decoration: none;
  transition: all .3s cubic-bezier(.23,1,.32,1);
}
.btn-primary:hover { transform: translateY(-3px); box-shadow: 0 16px 50px rgba(0,180,229,.3); background: var(--accent3); color: #000; }
.btn-secondary {
  display: inline-flex; align-items: center; gap: 8px;
  background: transparent; color: var(--text);
  font-family: 'DM Sans', sans-serif;
  font-size: 13px; font-weight: 700;
  padding: 15px 34px; border-radius: 12px;
  border: 1px solid var(--border); text-decoration: none; transition: all .3s;
}
.btn-secondary:hover { border-color: var(--accent); color: var(--accent); transform: translateY(-3px); }

.scroll-hint {
  margin-top: 70px;
  display: flex; flex-direction: column; align-items: center; gap: 8px;
  font-size: 9px; letter-spacing: 3px; text-transform: uppercase; color: var(--muted);
  animation: fadeUp .8s ease .5s both;
}
.scroll-line {
  width: 1px; height: 50px;
  background: linear-gradient(to bottom, var(--accent), transparent);
  animation: scrollPulse 2s ease-in-out infinite;
}
@keyframes scrollPulse { 0%,100%{opacity:.3} 50%{opacity:1} }

.stats {
  display: grid; grid-template-columns: repeat(4, 1fr);
  gap: 1px; background: var(--border);
  border: 1px solid var(--border); border-radius: 16px; overflow: hidden;
  margin: 0 0 100px; animation: fadeUp .8s ease .1s both;
}
.stat { background: var(--surface); padding: 28px 20px; text-align: center; }
.stat-num {
  font-family: 'DM Sans', sans-serif;
  font-size: 32px; font-weight: 800; color: var(--accent); line-height: 1; margin-bottom: 6px;
}
.stat-lbl { font-size: 9px; letter-spacing: 2px; text-transform: uppercase; color: var(--muted); }

.section-label { font-size: 9px; letter-spacing: 4px; text-transform: uppercase; color: var(--accent); margin-bottom: 16px; display: block; }
.section-title { font-family: 'DM Sans', sans-serif; font-size: clamp(28px, 4vw, 44px); font-weight: 800; letter-spacing: -1px; color: var(--text); margin-bottom: 14px; }
.section-sub { font-size: 13px; color: var(--muted); line-height: 1.8; max-width: 480px; margin-bottom: 52px; }

.features { margin-bottom: 100px; }
.bento { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
.bento-card {
  background: var(--surface); border: 1px solid var(--border);
  border-radius: 20px; padding: 30px;
  position: relative; overflow: hidden;
  transition: all .4s cubic-bezier(.23,1,.32,1);
}
.bento-card::before {
  content: ''; position: absolute; inset: 0;
  background: var(--card-glow, var(--accent));
  opacity: 0; transition: opacity .4s; border-radius: inherit;
}
.bento-card:hover { transform: translateY(-4px); border-color: var(--card-glow, var(--accent)); }
.bento-card:hover::before { opacity: .05; }
.bento-card.wide { grid-column: span 2; }

.card-icon-big { font-size: 36px; margin-bottom: 18px; display: block; }
.card-name { font-family: 'DM Sans', sans-serif; font-size: 18px; font-weight: 700; color: var(--text); margin-bottom: 8px; }
.card-body { font-size: 12px; color: var(--muted); line-height: 1.7; }
.card-tag-pill {
  display: inline-block; margin-top: 16px;
  background: rgba(255,255,255,.04); border: 1px solid var(--border);
  border-radius: 6px; padding: 3px 10px;
  font-size: 9px; letter-spacing: 2px; text-transform: uppercase;
  color: var(--card-glow, var(--accent));
}

.how { margin-bottom: 100px; }
.steps {
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 2px;
  background: var(--border); border: 1px solid var(--border); border-radius: 20px; overflow: hidden;
}
.step { background: var(--surface); padding: 40px 32px; position: relative; }
.step-num {
  font-family: 'DM Sans', sans-serif; font-size: 64px; font-weight: 800;
  color: rgba(0,180,229,.08); line-height: 1; position: absolute; top: 20px; right: 24px;
}
.step-icon { font-size: 28px; margin-bottom: 16px; }
.step-title { font-family: 'DM Sans', sans-serif; font-size: 16px; font-weight: 700; color: var(--text); margin-bottom: 8px; }
.step-desc { font-size: 12px; color: var(--muted); line-height: 1.7; }

.cta-section {
  margin-bottom: 80px; background: var(--surface);
  border: 1px solid var(--border); border-radius: 28px;
  padding: 70px 60px; text-align: center; position: relative; overflow: hidden;
}
.cta-section::before {
  content: ''; position: absolute; inset: 0;
  background: radial-gradient(ellipse at 50% 0%, rgba(0,180,229,.08) 0%, transparent 70%);
}
.cta-title { font-family: 'DM Sans', sans-serif; font-size: clamp(30px, 4vw, 52px); font-weight: 800; letter-spacing: -1.5px; color: var(--text); margin-bottom: 14px; position: relative; }
.cta-title span { color: var(--accent); }
.cta-sub { font-size: 13px; color: var(--muted); margin-bottom: 36px; position: relative; }
.cta-btns { display: flex; gap: 14px; justify-content: center; position: relative; }

footer { border-top: 1px solid var(--border); padding: 28px 0; text-align: center; font-size: 11px; color: var(--muted); letter-spacing: 1px; }

@keyframes fadeUp { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }
.reveal { opacity: 0; transform: translateY(30px); transition: opacity .7s ease, transform .7s ease; }
.reveal.visible { opacity: 1; transform: none; }

@media (max-width: 768px) {
  .bento { grid-template-columns: 1fr; }
  .bento-card.wide { grid-column: span 1; }
  .steps { grid-template-columns: 1fr; }
  .stats { grid-template-columns: repeat(2,1fr); }
  nav { padding: 14px 20px; }
  .cta-section { padding: 50px 28px; }
  .cta-btns { flex-direction: column; align-items: center; }
}
</style>
</head>
<body>

<div class="orb orb1"></div>
<div class="orb orb2"></div>
<div class="orb orb3"></div>

<nav class="z">
<a href="landing.php" class="nav-logo">
  <span style="background:#f0a500;color:#1a2d4a;border-radius:4px;padding:2px 9px;font-size:12px;font-weight:700;">CE</span>
  SitePro
</a>
  <div class="nav-pill">
    <a href="login.php">Sign In</a>
    <a href="register.php">Get Started →</a>
  </div>
</nav>

<section class="hero z">
  <div class="hero-eyebrow">Carbon Intelligence Platform</div>
  <h1 class="hero-title">
    <span class="solid">MEASURE.</span>
    <span class="outline">MONITOR.</span>
    <span class="sub-word">Reduce.</span>
  </h1>
  <p class="hero-desc">
    SitePro CE gives construction teams a complete platform to track,
    calculate and cut their carbon footprint — from materials to machinery,
    site to certification.
  </p>
  <div class="hero-cta">
    <a href="register.php" class="btn-primary">Start for Free →</a>
    <a href="login.php" class="btn-secondary">Sign In</a>
  </div>
  <div class="scroll-hint">
    <div class="scroll-line"></div>
    scroll
  </div>
</section>

<section class="z wrap">
  <div class="stats reveal">
    <div class="stat"><div class="stat-num">12</div><div class="stat-lbl">Modules</div></div>
    <div class="stat"><div class="stat-num">CO₂</div><div class="stat-lbl">Auto Calculated</div></div>
    <div class="stat"><div class="stat-num">5</div><div class="stat-lbl">Green Standards</div></div>
    <div class="stat"><div class="stat-num">∞</div><div class="stat-lbl">Projects</div></div>
  </div>
</section>

<section class="features z wrap">
  <span class="section-label reveal">What's inside</span>
  <h2 class="section-title reveal">Everything you need.<br>Nothing you don't.</h2>
  <p class="section-sub reveal">From project kickoff to green certification — every piece of your carbon story in one place.</p>
  <div class="bento">
    <div class="bento-card wide reveal" style="--card-glow:#00b4e5">
      <span class="card-icon-big">⚗️</span>
      <div class="card-name">Auto CO₂ Calculation</div>
      <div class="card-body">Log construction elements by volume. CarbonTrack multiplies against material emission factors instantly — no spreadsheets, no guesswork. Every phase, every material, tracked.</div>
      <span class="card-tag-pill">Core Engine</span>
    </div>
    <div class="bento-card reveal" style="--card-glow:#4d9eff; grid-row: span 2;">
      <span class="card-icon-big">🏗️</span>
      <div class="card-name">Project Management</div>
      <div class="card-body">Full project lifecycle — clients, contractors, budgets, timelines, phases and team assignments all in one unified view.</div>
      <span class="card-tag-pill">Projects</span>
    </div>
    <div class="bento-card reveal" style="--card-glow:#c77dff">
      <span class="card-icon-big">📊</span>
      <div class="card-name">Visual Reports</div>
      <div class="card-body">Interactive dashboards and emission timelines. See where your carbon is coming from at a glance.</div>
      <span class="card-tag-pill">Analytics</span>
    </div>
    <div class="bento-card reveal" style="--card-glow:#ff6b2b">
      <span class="card-icon-big">🏅</span>
      <div class="card-name">Certification Tracking</div>
      <div class="card-body">Apply, track and manage LEED, BREEAM, ISO 14001 and EDGE certifications per project with live status.</div>
      <span class="card-tag-pill">Standards</span>
    </div>
    <div class="bento-card reveal" style="--card-glow:#e9c46a">
      <span class="card-icon-big">🎯</span>
      <div class="card-name">Carbon Targets</div>
      <div class="card-body">Set reduction goals, define baselines and monitor progress against your CO₂ targets in real time.</div>
      <span class="card-tag-pill">Goals</span>
    </div>
    <div class="bento-card reveal" style="--card-glow:#ef476f">
      <span class="card-icon-big">🏗</span>
      <div class="card-name">Equipment & Machinery</div>
      <div class="card-body">Track excavators, cranes and generators by fuel type and CO₂ per hour. Assign to projects and see true operational emissions.</div>
      <span class="card-tag-pill">Assets</span>
    </div>
    <div class="bento-card reveal" style="--card-glow:#3bbfff">
      <span class="card-icon-big">🚚</span>
      <div class="card-name">Supplier Directory</div>
      <div class="card-body">Maintain a green-certified supplier database linked directly to your materials for transparent sourcing.</div>
      <span class="card-tag-pill">Procurement</span>
    </div>
    <div class="bento-card reveal" style="--card-glow:#f4a261">
      <span class="card-icon-big">🔍</span>
      <div class="card-name">Inspections</div>
      <div class="card-body">Log structural, environmental, safety and final inspections with pass/fail results and inspector records.</div>
      <span class="card-tag-pill">Compliance</span>
    </div>
  </div>
</section>

<section class="how z wrap">
  <span class="section-label reveal">How it works</span>
  <h2 class="section-title reveal">Up and running in minutes.</h2>
  <p class="section-sub reveal">No setup complexity. Your data, your projects, isolated and secure.</p>
  <div class="steps reveal">
    <div class="step">
      <div class="step-num">01</div>
      <div class="step-icon">👤</div>
      <div class="step-title">Create your account</div>
      <div class="step-desc">Sign up in seconds. Your workspace is completely isolated — other users never see your data.</div>
    </div>
    <div class="step">
      <div class="step-num">02</div>
      <div class="step-icon">🏗️</div>
      <div class="step-title">Add your projects</div>
      <div class="step-desc">Create projects, assign clients and contractors, define phases and build your team structure.</div>
    </div>
    <div class="step">
      <div class="step-num">03</div>
      <div class="step-icon">📊</div>
      <div class="step-title">Track & analyse</div>
      <div class="step-desc">Log materials and equipment usage. Watch your emission reports populate in real time.</div>
    </div>
  </div>
</section>

<section class="z wrap">
  <div class="cta-section reveal">
    <h2 class="cta-title">Ready to track your <span>carbon footprint</span>?</h2>
    <p class="cta-sub">Join SitePro CE — free, instant, no credit card required.</p>
    <div class="cta-btns">
      <a href="register.php" class="btn-primary">Create Free Account →</a>
      <a href="login.php" class="btn-secondary">Already have an account</a>
    </div>
  </div>
</section>

<footer class="z">
  © 2025 SitePro CE &nbsp;·&nbsp; DBMS Emission Intelligence System &nbsp;·&nbsp; SE-24004 &nbsp;SE-24012
</footer>

<script>
const observer = new IntersectionObserver(entries => {
  entries.forEach((e, i) => {
    if (e.isIntersecting) {
      setTimeout(() => e.target.classList.add('visible'), i * 80);
      observer.unobserve(e.target);
    }
  });
}, { threshold: 0.1 });
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

const canvas = document.createElement('canvas');
canvas.style.cssText = 'position:fixed;inset:0;z-index:0;pointer-events:none;opacity:.4';
document.body.prepend(canvas);
const ctx = canvas.getContext('2d');
let W, H, pts = [];
function resize() { W = canvas.width = innerWidth; H = canvas.height = innerHeight; }
resize(); window.addEventListener('resize', resize);
for (let i = 0; i < 60; i++) pts.push({ x: Math.random()*2000, y: Math.random()*1200, vx: (Math.random()-.5)*.3, vy: (Math.random()-.5)*.3, r: Math.random()*1.5+.3 });
function draw() {
  ctx.clearRect(0,0,W,H);
  pts.forEach(p => {
    p.x+=p.vx; p.y+=p.vy;
    if(p.x<0)p.x=W; if(p.x>W)p.x=0; if(p.y<0)p.y=H; if(p.y>H)p.y=0;
    ctx.beginPath(); ctx.arc(p.x,p.y,p.r,0,Math.PI*2);
    ctx.fillStyle='rgba(0,180,229,.6)'; ctx.fill();
  });
  requestAnimationFrame(draw);
}
draw();
</script>
</body>
</html>