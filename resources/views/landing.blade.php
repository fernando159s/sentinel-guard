<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sentinel Guard — Seguridad documentada, cumplimiento garantizado</title>
  <meta name="description" content="Plataforma de gestión de seguridad de la información para organizaciones. Documenta, gestiona y audita tus procesos de seguridad.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/animejs@3.2.1/lib/anime.min.js"></script>
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{--navy:#1E3A5F;--navy-dark:#132845;--blue:#3B82F6;--blue-light:#60A5FA;--slate:#64748B;--surface:#F8FAFC;--border:#E2E8F0;--text:#0F172A;--success:#059669;--warning:#D97706;--danger:#DC2626}
    html{scroll-behavior:smooth}
    body{font-family:'Inter',sans-serif;background:var(--surface);color:var(--text);overflow-x:hidden}

    .bg-canvas{position:fixed;inset:0;pointer-events:none;z-index:0;overflow:hidden}
    .bg-shape{position:absolute;border-radius:50%;opacity:.05}
    .bg-shape-1{width:800px;height:800px;background:var(--blue);top:-300px;right:-300px}
    .bg-shape-2{width:500px;height:500px;background:var(--navy);bottom:-200px;left:-200px}
    .bg-shape-3{width:300px;height:300px;background:var(--blue);top:50%;left:15%}
    .bg-lines{position:absolute;inset:0;background-image:linear-gradient(rgba(30,58,95,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(30,58,95,.03) 1px,transparent 1px);background-size:48px 48px}

    .page{position:relative;z-index:1}

    /* NAV */
    nav{padding:20px 48px;display:flex;align-items:center;justify-content:space-between;opacity:0}
    .logo-wrap{display:flex;align-items:center;gap:14px;text-decoration:none}
    .wordmark-name{font-size:18px;font-weight:800;color:var(--navy);letter-spacing:-.5px}
    .wordmark-tag{font-size:10px;color:var(--slate);text-transform:uppercase;letter-spacing:.3px}
    .nav-links{display:flex;align-items:center;gap:24px}
    .nav-link{font-size:13px;font-weight:600;color:var(--slate);text-decoration:none;transition:color .2s}
    .nav-link:hover{color:var(--navy)}
    .btn-login{background:var(--navy);color:#fff;padding:9px 24px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;transition:opacity .2s}
    .btn-login:hover{opacity:.88}

    /* HERO */
    .hero{text-align:center;padding:80px 24px 60px;max-width:900px;margin:0 auto}
    .hero-badge{display:inline-flex;align-items:center;gap:8px;background:#EFF6FF;border:1px solid #BFDBFE;color:var(--blue);font-size:12px;font-weight:600;padding:6px 16px;border-radius:999px;text-transform:uppercase;letter-spacing:.5px;margin-bottom:32px;opacity:0}
    .hero-badge-dot{width:6px;height:6px;background:var(--blue);border-radius:50%;animation:pulse-dot 2s infinite}
    @keyframes pulse-dot{0%,100%{opacity:1}50%{opacity:.3}}
    .hero-shield{margin:0 auto 32px;opacity:0}
    .hero h1{font-size:clamp(36px,6vw,72px);font-weight:900;color:var(--navy);letter-spacing:-2px;line-height:1.05;margin-bottom:20px;opacity:0}
    .hero h1 span{color:var(--blue)}
    .hero p{font-size:18px;color:var(--slate);max-width:560px;margin:0 auto 48px;line-height:1.6;opacity:0}
    .hero-actions{display:flex;justify-content:center;gap:16px;opacity:0}
    .btn-primary{background:var(--navy);color:#fff;padding:14px 32px;border-radius:10px;font-size:14px;font-weight:700;text-decoration:none;transition:all .2s;border:2px solid var(--navy)}
    .btn-primary:hover{background:var(--navy-dark);transform:translateY(-2px);box-shadow:0 8px 24px rgba(30,58,95,.25)}
    .btn-secondary{background:#fff;color:var(--navy);padding:14px 32px;border-radius:10px;font-size:14px;font-weight:700;text-decoration:none;border:2px solid var(--border);transition:all .2s}
    .btn-secondary:hover{border-color:var(--navy);transform:translateY(-2px)}

    /* STATS */
    .stats{display:flex;justify-content:center;gap:64px;padding:40px 24px 80px;opacity:0}
    .stat{text-align:center}
    .stat-num{font-size:40px;font-weight:900;color:var(--navy);line-height:1}
    .stat-label{font-size:12px;color:var(--slate);margin-top:6px;text-transform:uppercase;letter-spacing:.5px}

    /* FEATURES */
    .section{max-width:1100px;margin:0 auto;padding:0 48px 100px}
    .section-label{text-align:center;font-size:13px;font-weight:700;color:var(--slate);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:16px}
    .section-title{text-align:center;font-size:clamp(24px,3vw,36px);font-weight:800;color:var(--navy);letter-spacing:-1px;margin-bottom:48px}
    .features-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
    .feature-card{background:#fff;border:1px solid var(--border);border-radius:14px;padding:28px 24px;transition:all .25s;opacity:0}
    .feature-card:hover{transform:translateY(-6px);box-shadow:0 16px 40px rgba(0,0,0,.08);border-color:transparent}
    .feature-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;font-size:24px}
    .feature-card h3{font-size:15px;font-weight:700;margin-bottom:8px}
    .feature-card p{font-size:13px;color:var(--slate);line-height:1.5}

    /* ROLES */
    .roles-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:16px}
    .role-card{background:#fff;border:1px solid var(--border);border-radius:14px;padding:24px 20px;text-align:center;position:relative;overflow:hidden;transition:all .25s;opacity:0}
    .role-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:var(--rc);border-radius:14px 14px 0 0}
    .role-card:hover{transform:translateY(-6px);box-shadow:0 16px 40px rgba(0,0,0,.08)}
    .role-icon{width:52px;height:52px;border-radius:14px;margin:0 auto 14px;display:flex;align-items:center;justify-content:center;font-size:24px}
    .role-card h3{font-size:14px;font-weight:700;margin-bottom:6px}
    .role-card p{font-size:11px;color:var(--slate);line-height:1.4}

    /* CTA */
    .cta{background:linear-gradient(135deg,var(--navy) 0%,#2563EB 100%);border-radius:20px;padding:64px 48px;text-align:center;margin:0 48px 80px;max-width:1100px;margin-left:auto;margin-right:auto;opacity:0}
    .cta h2{font-size:clamp(24px,3vw,36px);font-weight:800;color:#fff;letter-spacing:-1px;margin-bottom:12px}
    .cta p{font-size:16px;color:rgba(255,255,255,.7);margin-bottom:32px}
    .cta .btn-cta{background:#fff;color:var(--navy);padding:14px 36px;border-radius:10px;font-size:14px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:8px;transition:all .2s}
    .cta .btn-cta:hover{transform:translateY(-2px);box-shadow:0 12px 32px rgba(0,0,0,.2)}

    /* FOOTER */
    footer{border-top:1px solid var(--border);padding:32px 48px;display:flex;align-items:center;justify-content:space-between;max-width:1200px;margin:0 auto}
    .footer-name{font-size:14px;font-weight:700;color:var(--navy)}
    .footer-tag{font-size:12px;color:var(--slate)}
    .footer-note{font-size:11px;color:var(--slate)}

    @media(max-width:900px){
      nav{padding:16px 24px}
      .hero{padding:48px 16px 32px}
      .features-grid,.roles-grid{grid-template-columns:1fr 1fr}
      .section{padding:0 24px 64px}
      .stats{gap:32px}
      .cta{margin:0 16px 48px;padding:40px 24px}
      footer{flex-direction:column;gap:12px;text-align:center}
    }
    @media(max-width:600px){
      .features-grid,.roles-grid{grid-template-columns:1fr}
      .hero-actions{flex-direction:column;align-items:center}
      .nav-links .nav-link{display:none}
    }
  </style>
</head>
<body>
<div class="bg-canvas">
  <div class="bg-lines"></div>
  <div class="bg-shape bg-shape-1"></div>
  <div class="bg-shape bg-shape-2"></div>
  <div class="bg-shape bg-shape-3"></div>
</div>

<div class="page">
  <!-- NAV -->
  <nav id="nav">
    <a href="/" class="logo-wrap">
      <svg width="32" height="38" viewBox="0 0 48 56" fill="none"><defs><linearGradient id="gl" x1="4" y1="2" x2="44" y2="54" gradientUnits="userSpaceOnUse"><stop offset="0%" stop-color="#3B82F6"/><stop offset="100%" stop-color="#1E3A5F"/></linearGradient></defs><path d="M24 2L44 10V28C44 40.4 35.2 51.6 24 54C12.8 51.6 4 40.4 4 28V10L24 2Z" fill="url(#gl)"/><path d="M24 8L38 14.4V28C38 37.6 32.4 46 24 48.4C15.6 46 10 37.6 10 28V14.4L24 8Z" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="1.5"/><text x="24" y="37" text-anchor="middle" font-family="Inter" font-size="20" font-weight="800" fill="white">S</text></svg>
      <div>
        <div class="wordmark-name">Sentinel Guard</div>
        <div class="wordmark-tag">Seguridad documentada, cumplimiento garantizado</div>
      </div>
    </a>
    <div class="nav-links">
      <a href="/docs" class="nav-link">Documentación</a>
      <a href="/admin/login" class="btn-login">Iniciar Sesión</a>
    </div>
  </nav>

  <!-- HERO -->
  <section class="hero">
    <div class="hero-badge" id="heroBadge"><span class="hero-badge-dot"></span>Plataforma de seguridad empresarial</div>
    <div class="hero-shield" id="heroShield">
      <svg width="90" height="105" viewBox="0 0 48 56" fill="none"><defs><linearGradient id="gh" x1="4" y1="2" x2="44" y2="54" gradientUnits="userSpaceOnUse"><stop offset="0%" stop-color="#60A5FA"/><stop offset="100%" stop-color="#1E3A5F"/></linearGradient><filter id="hs" x="-30%" y="-10%" width="160%" height="140%"><feDropShadow dx="0" dy="8" stdDeviation="8" flood-color="#1E3A5F" flood-opacity=".25"/></filter></defs><path d="M24 2L44 10V28C44 40.4 35.2 51.6 24 54C12.8 51.6 4 40.4 4 28V10L24 2Z" fill="url(#gh)" filter="url(#hs)"/><path d="M24 8L38 14.4V28C38 37.6 32.4 46 24 48.4C15.6 46 10 37.6 10 28V14.4L24 8Z" fill="none" stroke="rgba(255,255,255,.25)" stroke-width="1.5"/><text x="24" y="37" text-anchor="middle" font-family="Inter" font-size="20" font-weight="800" fill="white">S</text></svg>
    </div>
    <h1 id="heroTitle">Gestiona tu <span>seguridad</span><br>de la información</h1>
    <p id="heroSub">Documenta, audita y garantiza el cumplimiento de las políticas de seguridad de tu organización desde una sola plataforma centralizada.</p>
    <div class="hero-actions" id="heroActions">
      <a href="/admin/login" class="btn-primary">Acceder al Panel →</a>
      <a href="/docs" class="btn-secondary">Ver Documentación</a>
    </div>
  </section>

  <!-- STATS -->
  <div class="stats" id="stats">
    <div class="stat"><div class="stat-num" id="s1">0</div><div class="stat-label">Formatos PSC</div></div>
    <div class="stat"><div class="stat-num" id="s2">0</div><div class="stat-label">Roles del sistema</div></div>
    <div class="stat"><div class="stat-num" id="s3">0</div><div class="stat-label">Módulos integrados</div></div>
    <div class="stat"><div class="stat-num" id="s4">0</div><div class="stat-label">Reportes PDF</div></div>
  </div>

  <!-- FEATURES -->
  <div class="section">
    <div class="section-label">Funcionalidades</div>
    <div class="section-title">Todo lo que necesitas para el cumplimiento</div>
    <div class="features-grid">
      <div class="feature-card"><div class="feature-icon" style="background:#EFF6FF">🛡️</div><h3>13 Formatos de Seguridad</h3><p>Auditorías, inventarios, incidencias, copias de seguridad y más. Todos los formatos PSC digitalizados.</p></div>
      <div class="feature-card"><div class="feature-icon" style="background:#ECFDF5">🏢</div><h3>Multi-Empresa</h3><p>Aislamiento total de datos por empresa. Cada organización trabaja en su propio espacio seguro.</p></div>
      <div class="feature-card"><div class="feature-icon" style="background:#F5F3FF">🎫</div><h3>Helpdesk Integrado</h3><p>Sistema de tickets con hilo de conversación, notas internas, adjuntos y asignación de agentes.</p></div>
      <div class="feature-card"><div class="feature-icon" style="background:#FFFBEB">📊</div><h3>Reportes y PDF</h3><p>Exporta registros en PDF y Excel. Reportes de incidencias, capacitaciones e inventario.</p></div>
      <div class="feature-card"><div class="feature-icon" style="background:#FEF2F2">📋</div><h3>Políticas y NDA</h3><p>Gestión de políticas con firma digital, versionado, cumplimiento y recordatorios automáticos.</p></div>
      <div class="feature-card"><div class="feature-icon" style="background:#F0FDF4">🔐</div><h3>Control de Acceso</h3><p>5 roles con permisos granulares. Cada usuario ve solo lo que le corresponde.</p></div>
    </div>
  </div>

  <!-- ROLES -->
  <div class="section">
    <div class="section-label">Roles del Sistema</div>
    <div class="section-title">Un espacio para cada tipo de usuario</div>
    <div class="roles-grid">
      <div class="role-card" style="--rc:#1E3A5F"><div class="role-icon" style="background:#EEF2FF">🛡️</div><h3>Super Admin</h3><p>Control total del sistema y todas las empresas</p></div>
      <div class="role-card" style="--rc:#2563EB"><div class="role-icon" style="background:#EFF6FF">🏢</div><h3>Admin Empresa</h3><p>Gestiona usuarios y registros de su organización</p></div>
      <div class="role-card" style="--rc:#059669"><div class="role-icon" style="background:#ECFDF5">👤</div><h3>Usuario</h3><p>Crea registros y abre tickets de soporte</p></div>
      <div class="role-card" style="--rc:#7C3AED"><div class="role-icon" style="background:#F5F3FF">💬</div><h3>Agente Helpdesk</h3><p>Atiende tickets de todas las empresas</p></div>
      <div class="role-card" style="--rc:#475569"><div class="role-icon" style="background:#F1F5F9">👁️</div><h3>Solo Lectura</h3><p>Consulta y auditoría sin modificaciones</p></div>
    </div>
  </div>

  <!-- CTA -->
  <div class="cta" id="cta">
    <h2>¿Listo para documentar tu seguridad?</h2>
    <p>Accede al panel o consulta la documentación interactiva por rol.</p>
    <a href="/docs" class="btn-cta">Explorar Documentación <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
  </div>

  <!-- FOOTER -->
  <footer>
    <div>
      <div class="footer-name">Sentinel Guard</div>
      <div class="footer-tag">Seguridad documentada, cumplimiento garantizado</div>
    </div>
    <div class="footer-note">Estudio Palacios Abogados S.A.C. · 2026</div>
  </footer>
</div>

<script>
const tl=anime.timeline({easing:'easeOutCubic'});
tl.add({targets:'#nav',opacity:[0,1],translateY:[-20,0],duration:500})
  .add({targets:'#heroBadge',opacity:[0,1],translateY:[10,0],duration:400},'-=200')
  .add({targets:'#heroShield',opacity:[0,1],scale:[.7,1],duration:600,easing:'easeOutBack'},'-=100')
  .add({targets:'#heroTitle',opacity:[0,1],translateY:[30,0],duration:500},'-=300')
  .add({targets:'#heroSub',opacity:[0,1],translateY:[20,0],duration:400},'-=300')
  .add({targets:'#heroActions',opacity:[0,1],translateY:[20,0],duration:400},'-=200')
  .add({targets:'#stats',opacity:[0,1],translateY:[20,0],duration:400},'-=200')
  .add({targets:'.feature-card',opacity:[0,1],translateY:[30,0],delay:anime.stagger(60),duration:400},'-=100')
  .add({targets:'.role-card',opacity:[0,1],translateY:[30,0],delay:anime.stagger(60),duration:400},'-=200')
  .add({targets:'#cta',opacity:[0,1],translateY:[30,0],duration:500},'-=200');

anime({targets:'#s1',innerHTML:[0,13],round:1,duration:1200,delay:800,easing:'easeOutCubic'});
anime({targets:'#s2',innerHTML:[0,5],round:1,duration:1200,delay:900,easing:'easeOutCubic'});
anime({targets:'#s3',innerHTML:[0,8],round:1,duration:1200,delay:1000,easing:'easeOutCubic'});
anime({targets:'#s4',innerHTML:[0,5],round:1,duration:1200,delay:1100,easing:'easeOutCubic'});

anime({targets:'.bg-shape',translateY:anime.stagger(['-30px','30px'],{from:'center'}),direction:'alternate',loop:true,duration:4000,easing:'easeInOutSine'});
anime({targets:'#heroShield svg',scale:[1,1.04,1],direction:'alternate',loop:true,duration:2500,easing:'easeInOutSine'});
</script>
</body>
</html>
