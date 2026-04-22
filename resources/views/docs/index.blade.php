<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sentinel Guard — Centro de Documentación</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/animejs@3.2.1/lib/anime.min.js"></script>
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{--navy:#1E3A5F;--blue:#3B82F6;--blue-light:#60A5FA;--slate:#64748B;--surface:#F8FAFC;--border:#E2E8F0;--text:#0F172A}
    html{scroll-behavior:smooth}
    body{font-family:'Inter',sans-serif;background:var(--surface);color:var(--text);min-height:100vh;overflow-x:hidden}

    .bg-canvas{position:fixed;inset:0;pointer-events:none;z-index:0;overflow:hidden}
    .bg-shape{position:absolute;border-radius:50%;opacity:.06}
    .bg-shape-1{width:600px;height:600px;background:var(--blue);top:-200px;right:-200px}
    .bg-shape-2{width:400px;height:400px;background:var(--navy);bottom:-100px;left:-100px}
    .bg-lines{position:absolute;inset:0;background-image:linear-gradient(rgba(30,58,95,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(30,58,95,.03) 1px,transparent 1px);background-size:48px 48px}

    .page{position:relative;z-index:1}

    header{padding:20px 48px;display:flex;align-items:center;justify-content:space-between;opacity:0}
    .logo-wrap{display:flex;align-items:center;gap:14px;text-decoration:none}
    .wordmark-name{font-size:18px;font-weight:800;color:var(--navy);letter-spacing:-.5px}
    .wordmark-tag{font-size:10px;color:var(--slate);text-transform:uppercase;letter-spacing:.3px}
    .nav-links{display:flex;gap:16px;align-items:center}
    .nav-link{font-size:13px;font-weight:600;color:var(--slate);text-decoration:none;transition:color .2s}
    .nav-link:hover{color:var(--navy)}
    .badge-docs{background:var(--blue);color:#fff;font-size:11px;font-weight:600;padding:4px 12px;border-radius:999px;letter-spacing:.5px}

    .hero{text-align:center;padding:64px 24px 48px}
    .hero-eyebrow{display:inline-flex;align-items:center;gap:8px;background:#EFF6FF;border:1px solid #BFDBFE;color:var(--blue);font-size:12px;font-weight:600;padding:6px 16px;border-radius:999px;letter-spacing:.5px;text-transform:uppercase;margin-bottom:28px;opacity:0}
    .hero-eyebrow-dot{width:6px;height:6px;background:var(--blue);border-radius:50%}
    .hero-logo-wrap{display:flex;justify-content:center;margin-bottom:32px;opacity:0}
    .hero-title{font-size:clamp(36px,5vw,64px);font-weight:900;color:var(--navy);letter-spacing:-2px;line-height:1.05;margin-bottom:16px;opacity:0}
    .hero-title span{color:var(--blue)}
    .hero-sub{font-size:18px;color:var(--slate);max-width:560px;margin:0 auto 48px;line-height:1.6;opacity:0}
    .hero-stat-row{display:flex;justify-content:center;gap:48px;margin-bottom:64px;opacity:0}
    .hero-stat{text-align:center}
    .hero-stat-num{font-size:32px;font-weight:800;color:var(--navy);line-height:1}
    .hero-stat-label{font-size:12px;color:var(--slate);margin-top:4px;text-transform:uppercase;letter-spacing:.5px}

    .section{padding:0 48px 80px;max-width:1200px;margin:0 auto}
    .section-title{font-size:13px;font-weight:700;color:var(--slate);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:32px;display:flex;align-items:center;gap:12px}
    .section-title::before,.section-title::after{content:'';flex:1;height:1px;background:var(--border)}

    .roles-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px}
    .role-card{background:#fff;border:1px solid var(--border);border-radius:16px;padding:28px 24px;text-decoration:none;color:inherit;display:flex;flex-direction:column;position:relative;overflow:hidden;transition:transform .2s,box-shadow .2s,border-color .2s;opacity:0;cursor:pointer}
    .role-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:var(--role-color,var(--navy));border-radius:16px 16px 0 0}
    .role-card:hover{transform:translateY(-6px);box-shadow:0 20px 40px rgba(0,0,0,.1);border-color:transparent}
    .role-card-icon{width:52px;height:52px;border-radius:14px;background:var(--role-bg,#EFF6FF);display:flex;align-items:center;justify-content:center;margin-bottom:20px;font-size:26px}
    .role-card-name{font-size:16px;font-weight:700;margin-bottom:8px}
    .role-card-desc{font-size:13px;color:var(--slate);line-height:1.5;flex:1;margin-bottom:20px}
    .role-card-link{display:flex;align-items:center;gap:6px;font-size:13px;font-weight:600;color:var(--role-color,var(--blue))}
    .role-card-link svg{width:14px;height:14px;transition:transform .2s}
    .role-card:hover .role-card-link svg{transform:translateX(4px)}
    .role-card-badge{position:absolute;top:16px;right:16px;font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;padding:3px 8px;border-radius:999px;background:var(--role-bg,#EFF6FF);color:var(--role-color,var(--navy))}

    footer{border-top:1px solid var(--border);padding:32px 48px;display:flex;align-items:center;justify-content:space-between;max-width:1200px;margin:0 auto}
    .footer-brand{display:flex;align-items:center;gap:10px}
    .footer-brand-name{font-size:14px;font-weight:700;color:var(--navy)}
    .footer-tagline{font-size:12px;color:var(--slate)}
    .footer-note{font-size:11px;color:var(--slate)}

    @media(max-width:768px){
      header{padding:16px 24px}
      .hero{padding:40px 16px 32px}
      .section{padding:0 16px 48px}
      .hero-stat-row{gap:24px}
      .roles-grid{grid-template-columns:1fr}
      footer{flex-direction:column;gap:16px;text-align:center}
    }
  </style>
</head>
<body>
<div class="bg-canvas">
  <div class="bg-lines"></div>
  <div class="bg-shape bg-shape-1"></div>
  <div class="bg-shape bg-shape-2"></div>
</div>

<div class="page">
  <header id="header">
    <a href="/" class="logo-wrap">
      <svg width="32" height="38" viewBox="0 0 48 56" fill="none"><defs><linearGradient id="gi" x1="4" y1="2" x2="44" y2="54" gradientUnits="userSpaceOnUse"><stop offset="0%" stop-color="#3B82F6"/><stop offset="100%" stop-color="#1E3A5F"/></linearGradient></defs><path d="M24 2L44 10V28C44 40.4 35.2 51.6 24 54C12.8 51.6 4 40.4 4 28V10L24 2Z" fill="url(#gi)"/><text x="24" y="37" text-anchor="middle" font-family="Inter" font-size="20" font-weight="800" fill="white">S</text></svg>
      <div>
        <div class="wordmark-name">Sentinel Guard</div>
        <div class="wordmark-tag">Seguridad documentada, cumplimiento garantizado</div>
      </div>
    </a>
    <div class="nav-links">
      <a href="/" class="nav-link">← Inicio</a>
      <div class="badge-docs">Documentación</div>
    </div>
  </header>

  <section class="hero">
    <div class="hero-eyebrow" id="eyebrow"><span class="hero-eyebrow-dot"></span>Guía de usuario por rol</div>
    <div class="hero-logo-wrap" id="heroLogo">
      <svg width="90" height="105" viewBox="0 0 48 56" fill="none"><defs><linearGradient id="gh" x1="4" y1="2" x2="44" y2="54" gradientUnits="userSpaceOnUse"><stop offset="0%" stop-color="#60A5FA"/><stop offset="100%" stop-color="#1E3A5F"/></linearGradient><filter id="hs2" x="-30%" y="-10%" width="160%" height="140%"><feDropShadow dx="0" dy="8" stdDeviation="8" flood-color="#1E3A5F" flood-opacity=".25"/></filter></defs><path d="M24 2L44 10V28C44 40.4 35.2 51.6 24 54C12.8 51.6 4 40.4 4 28V10L24 2Z" fill="url(#gh)" filter="url(#hs2)"/><path d="M24 8L38 14.4V28C38 37.6 32.4 46 24 48.4C15.6 46 10 37.6 10 28V14.4L24 8Z" fill="none" stroke="rgba(255,255,255,.25)" stroke-width="1.5"/><text x="24" y="37" text-anchor="middle" font-family="Inter" font-size="20" font-weight="800" fill="white">S</text></svg>
    </div>
    <h1 class="hero-title" id="heroTitle">Conoce tu <span>espacio</span><br>en Sentinel Guard</h1>
    <p class="hero-sub" id="heroSub">Selecciona tu rol para acceder a la guía interactiva. Aprende qué puedes hacer, cómo hacerlo y por qué importa.</p>
    <div class="hero-stat-row" id="heroStats">
      <div class="hero-stat"><div class="hero-stat-num" id="stat-roles">0</div><div class="hero-stat-label">Roles del sistema</div></div>
      <div class="hero-stat"><div class="hero-stat-num" id="stat-formats">0</div><div class="hero-stat-label">Formatos de seguridad</div></div>
      <div class="hero-stat"><div class="hero-stat-num" id="stat-slides">0</div><div class="hero-stat-label">Diapositivas de guía</div></div>
    </div>
  </section>

  <div class="section">
    <div class="section-title">Elige tu rol</div>
    <div class="roles-grid">
      <a class="role-card" href="/docs/super-admin" style="--role-color:#1E3A5F;--role-bg:#EEF2FF">
        <span class="role-card-badge">Nivel 5</span>
        <div class="role-card-icon" style="background:#EEF2FF">🛡️</div>
        <div class="role-card-name">Super Administrador</div>
        <div class="role-card-desc">Control total del sistema. Gestiona empresas, usuarios, tickets y configuración global.</div>
        <div class="role-card-link">Ver guía <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg></div>
      </a>
      <a class="role-card" href="/docs/admin-empresa" style="--role-color:#2563EB;--role-bg:#EFF6FF">
        <span class="role-card-badge">Nivel 4</span>
        <div class="role-card-icon" style="background:#EFF6FF">🏢</div>
        <div class="role-card-name">Admin de Empresa</div>
        <div class="role-card-desc">Administra tu organización: usuarios, registros, cumplimiento y capacitaciones.</div>
        <div class="role-card-link">Ver guía <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg></div>
      </a>
      <a class="role-card" href="/docs/usuario" style="--role-color:#059669;--role-bg:#ECFDF5">
        <span class="role-card-badge">Nivel 3</span>
        <div class="role-card-icon" style="background:#ECFDF5">👤</div>
        <div class="role-card-name">Usuario</div>
        <div class="role-card-desc">Crea registros de seguridad, gestiona tickets y consulta políticas de tu empresa.</div>
        <div class="role-card-link">Ver guía <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg></div>
      </a>
      <a class="role-card" href="/docs/agente-helpdesk" style="--role-color:#7C3AED;--role-bg:#F5F3FF">
        <span class="role-card-badge">Nivel 3</span>
        <div class="role-card-icon" style="background:#F5F3FF">💬</div>
        <div class="role-card-name">Agente Helpdesk</div>
        <div class="role-card-desc">Atiende tickets de todas las empresas, gestiona estados y crea registros de incidencias.</div>
        <div class="role-card-link">Ver guía <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg></div>
      </a>
      <a class="role-card" href="/docs/solo-lectura" style="--role-color:#475569;--role-bg:#F1F5F9">
        <span class="role-card-badge">Nivel 1</span>
        <div class="role-card-icon" style="background:#F1F5F9">👁️</div>
        <div class="role-card-name">Solo Lectura</div>
        <div class="role-card-desc">Acceso de auditoría y consulta. Visualiza registros, tickets y políticas sin modificar nada.</div>
        <div class="role-card-link">Ver guía <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg></div>
      </a>
    </div>
  </div>

  <footer>
    <div class="footer-brand">
      <div class="footer-brand-name">Sentinel Guard</div>
      <div class="footer-tagline">Seguridad documentada, cumplimiento garantizado</div>
    </div>
    <div class="footer-note">Documentación interna · v1.0 · 2026</div>
  </footer>
</div>

<script>
const tl=anime.timeline({easing:'easeOutCubic'});
tl.add({targets:'#header',opacity:[0,1],translateY:[-20,0],duration:500})
  .add({targets:'#eyebrow',opacity:[0,1],translateY:[10,0],duration:400},'-=200')
  .add({targets:'#heroLogo',opacity:[0,1],scale:[.7,1],duration:600,easing:'easeOutBack'},'-=100')
  .add({targets:'#heroTitle',opacity:[0,1],translateY:[30,0],duration:500},'-=300')
  .add({targets:'#heroSub',opacity:[0,1],translateY:[20,0],duration:400},'-=300')
  .add({targets:'#heroStats',opacity:[0,1],translateY:[20,0],duration:400},'-=200')
  .add({targets:'.role-card',opacity:[0,1],translateY:[40,0],delay:anime.stagger(80),duration:400},'-=100');

anime({targets:'#stat-roles',innerHTML:[0,5],round:1,duration:1200,delay:900,easing:'easeOutCubic'});
anime({targets:'#stat-formats',innerHTML:[0,13],round:1,duration:1200,delay:1000,easing:'easeOutCubic'});
anime({targets:'#stat-slides',innerHTML:[0,31],round:1,duration:1200,delay:1100,easing:'easeOutCubic'});
anime({targets:'.bg-shape',translateY:anime.stagger(['-30px','30px'],{from:'center'}),direction:'alternate',loop:true,duration:4000,easing:'easeInOutSine'});
anime({targets:'#heroLogo svg',scale:[1,1.04,1],direction:'alternate',loop:true,duration:2500,easing:'easeInOutSine'});
</script>
</body>
</html>
