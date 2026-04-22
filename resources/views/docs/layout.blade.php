<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sentinel Guard — @yield('role-name')</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{--rc:@yield('role-color','#1E3A5F');--ra:@yield('role-accent','#3B82F6');--rl:@yield('role-light','#EFF6FF');--navy:#1E3A5F;--slate:#64748B;--border:#E2E8F0;--surface:#F8FAFC;--text:#0F172A}
    html{scroll-behavior:smooth}
    body{font-family:'Inter',sans-serif;background:var(--surface);color:var(--text)}

    /* === NAV === */
    .topnav{position:sticky;top:0;z-index:50;background:rgba(255,255,255,.92);backdrop-filter:blur(12px);border-bottom:1px solid var(--border);padding:0 48px;display:flex;align-items:center;justify-content:space-between;height:56px}
    .topnav-left{display:flex;align-items:center;gap:14px}
    .topnav a.logo{display:flex;align-items:center;gap:10px;text-decoration:none}
    .logo-name{font-size:15px;font-weight:800;color:var(--navy)}
    .topnav-role{font-size:11px;font-weight:700;color:var(--rc);background:var(--rl);padding:4px 12px;border-radius:999px;text-transform:uppercase;letter-spacing:.5px}
    .topnav-links{display:flex;gap:16px;align-items:center}
    .topnav-links a{font-size:13px;font-weight:600;color:var(--slate);text-decoration:none;transition:color .2s}
    .topnav-links a:hover{color:var(--rc)}

    /* === HERO === */
    .hero{text-align:center;padding:64px 24px 48px;background:linear-gradient(180deg,var(--rl) 0%,var(--surface) 100%)}
    .hero-icon{width:80px;height:80px;border-radius:22px;background:#fff;border:2px solid var(--border);display:flex;align-items:center;justify-content:center;margin:0 auto 24px;font-size:36px;box-shadow:0 8px 24px rgba(0,0,0,.06)}
    .hero h1{font-size:clamp(28px,4vw,48px);font-weight:900;color:var(--rc);letter-spacing:-1.5px;line-height:1.1;margin-bottom:12px}
    .hero p{font-size:16px;color:var(--slate);max-width:600px;margin:0 auto 32px;line-height:1.6}
    .hero-stats{display:flex;justify-content:center;gap:40px;flex-wrap:wrap}
    .hero-stat{text-align:center}
    .hero-stat strong{display:block;font-size:28px;font-weight:800;color:var(--rc)}
    .hero-stat span{font-size:11px;color:var(--slate);text-transform:uppercase;letter-spacing:.5px}

    /* === SECTION NAV === */
    .section-nav{position:sticky;top:56px;z-index:40;background:rgba(255,255,255,.95);backdrop-filter:blur(8px);border-bottom:1px solid var(--border);overflow-x:auto;-webkit-overflow-scrolling:touch}
    .section-nav-inner{display:flex;gap:0;max-width:1100px;margin:0 auto;padding:0 48px}
    .section-nav a{display:block;padding:14px 20px;font-size:13px;font-weight:600;color:var(--slate);text-decoration:none;white-space:nowrap;border-bottom:2px solid transparent;transition:all .2s}
    .section-nav a:hover{color:var(--rc);background:var(--rl)}
    .section-nav a.active{color:var(--rc);border-bottom-color:var(--rc)}

    /* === CONTENT === */
    .content{max-width:1100px;margin:0 auto;padding:0 48px 80px}
    .module{padding:64px 0 0;scroll-margin-top:130px}
    .module-header{display:flex;align-items:center;gap:14px;margin-bottom:8px}
    .module-num{width:36px;height:36px;border-radius:10px;background:var(--rc);color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;flex-shrink:0}
    .module-label{font-size:11px;font-weight:700;color:var(--rc);text-transform:uppercase;letter-spacing:1px}
    .module h2{font-size:clamp(22px,3vw,32px);font-weight:800;color:var(--text);letter-spacing:-.5px;margin-bottom:8px}
    .module>p{font-size:15px;color:var(--slate);line-height:1.6;margin-bottom:32px;max-width:700px}

    /* === CARDS === */
    .cards{display:grid;gap:16px}
    .cards-2{grid-template-columns:repeat(2,1fr)}
    .cards-3{grid-template-columns:repeat(3,1fr)}
    .card{background:#fff;border:1px solid var(--border);border-radius:14px;padding:24px;transition:transform .2s,box-shadow .2s}
    .card:hover{transform:translateY(-3px);box-shadow:0 12px 32px rgba(0,0,0,.07)}
    .card-icon{font-size:24px;margin-bottom:12px}
    .card h3{font-size:14px;font-weight:700;margin-bottom:6px}
    .card p{font-size:13px;color:var(--slate);line-height:1.5}
    .card-path{display:inline-block;margin-top:10px;font-size:10px;font-weight:700;color:var(--rc);background:var(--rl);padding:3px 10px;border-radius:6px;font-family:monospace}

    /* === STEPS === */
    .steps{counter-reset:step}
    .step{display:flex;gap:16px;padding:20px 0;border-bottom:1px solid var(--border);counter-increment:step}
    .step:last-child{border-bottom:none}
    .step-num{width:36px;height:36px;border-radius:10px;background:var(--rc);color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;flex-shrink:0}
    .step h4{font-size:14px;font-weight:700;margin-bottom:4px}
    .step p{font-size:13px;color:var(--slate);line-height:1.5}

    /* === FORMAT PILLS === */
    .formats{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:10px}
    .format{display:flex;align-items:center;gap:10px;background:#fff;border:1px solid var(--border);border-radius:10px;padding:12px 14px}
    .format code{font-size:11px;font-weight:700;color:var(--rc);background:var(--rl);padding:3px 8px;border-radius:5px;white-space:nowrap}
    .format span{font-size:12px;color:var(--slate);line-height:1.3}

    /* === PERMISSIONS === */
    .perms{display:grid;grid-template-columns:1fr 1fr;gap:16px}
    .perm-yes{background:#ECFDF5;border:1px solid #A7F3D0;border-radius:14px;padding:24px}
    .perm-no{background:#FFF1F2;border:1px solid #FECDD3;border-radius:14px;padding:24px}
    .perm-title{font-size:14px;font-weight:700;margin-bottom:14px}
    .perm-title.yes{color:#047857}
    .perm-title.no{color:#BE123C}
    .perm-item{display:flex;align-items:center;gap:8px;font-size:13px;margin-bottom:8px}
    .perm-item:last-child{margin-bottom:0}

    /* === TIP / ALERT === */
    .tip{background:#FFFBEB;border:1px solid #FDE68A;border-radius:12px;padding:16px 20px;display:flex;gap:12px;margin-top:24px;font-size:13px;color:#78350F;line-height:1.5}
    .tip strong{font-weight:700}
    .tip-icon{font-size:18px;flex-shrink:0}
    .alert{background:#FFF7ED;border:1px solid #FDBA74;border-left:4px solid #EA580C;border-radius:0 10px 10px 0;padding:14px 18px;font-size:13px;color:#9A3412;line-height:1.5;margin-top:20px}

    /* === FLOW === */
    .flow{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin:16px 0}
    .flow-step{background:#fff;border:1px solid var(--border);border-radius:8px;padding:8px 16px;font-size:12px;font-weight:600}
    .flow-step.current{background:var(--rl);border-color:var(--rc);color:var(--rc)}
    .flow-arrow{color:var(--slate);font-size:16px}

    /* === FOOTER === */
    .doc-footer{border-top:1px solid var(--border);padding:32px 48px;text-align:center}
    .doc-footer p{font-size:12px;color:var(--slate)}

    /* === RESPONSIVE === */
    @media(max-width:768px){
      .topnav,.section-nav-inner,.content{padding-left:20px;padding-right:20px}
      .cards-2,.cards-3,.perms{grid-template-columns:1fr}
      .hero{padding:40px 20px 32px}
      .hero-stats{gap:24px}
    }

    /* === ANIMATIONS === */
    .module{animation:fadeUp .5s ease both}
    @keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
  </style>
</head>
<body>

<nav class="topnav">
  <div class="topnav-left">
    <a href="/" class="logo">
      <svg width="28" height="33" viewBox="0 0 48 56" fill="none"><defs><linearGradient id="gl" x1="4" y1="2" x2="44" y2="54" gradientUnits="userSpaceOnUse"><stop offset="0%" stop-color="#3B82F6"/><stop offset="100%" stop-color="#1E3A5F"/></linearGradient></defs><path d="M24 2L44 10V28C44 40.4 35.2 51.6 24 54C12.8 51.6 4 40.4 4 28V10L24 2Z" fill="url(#gl)"/><text x="24" y="37" text-anchor="middle" font-family="Inter" font-size="20" font-weight="800" fill="white">S</text></svg>
      <span class="logo-name">Sentinel Guard</span>
    </a>
    <span class="topnav-role">@yield('role-name')</span>
  </div>
  <div class="topnav-links">
    <a href="/docs">← Todos los roles</a>
    <a href="/admin/login">Iniciar sesión</a>
  </div>
</nav>

@yield('content')

<footer class="doc-footer">
  <p>Sentinel Guard · Seguridad documentada, cumplimiento garantizado · Estudio Palacios Abogados S.A.C. · 2026</p>
</footer>

<script>
// Highlight active section in nav on scroll
const sections = document.querySelectorAll('.module');
const navLinks = document.querySelectorAll('.section-nav a');
window.addEventListener('scroll', () => {
  let current = '';
  sections.forEach(s => { if (window.scrollY >= s.offsetTop - 160) current = s.id; });
  navLinks.forEach(a => a.classList.toggle('active', a.getAttribute('href') === '#' + current));
});
</script>
</body>
</html>
