@extends('docs.layout')
@section('role-name','Admin de Empresa')
@section('role-color','#2563EB')
@section('role-accent','#3B82F6')
@section('role-light','#EFF6FF')

@section('content')
<section class="hero">
  <div class="hero-icon">🏢</div>
  <h1>Admin de Empresa</h1>
  <p>Administras tu organización dentro de Sentinel Guard. Gestionas usuarios, registros de seguridad, capacitaciones y cumplimiento de políticas.</p>
  <div class="hero-stats">
    <div class="hero-stat"><strong>Nivel 4</strong><span>Administrador</span></div>
    <div class="hero-stat"><strong>13</strong><span>Formatos PSC</span></div>
    <div class="hero-stat"><strong>6</strong><span>Módulos</span></div>
  </div>
</section>

<nav class="section-nav"><div class="section-nav-inner">
  <a href="#dashboard" class="active">Dashboard</a>
  <a href="#usuarios">Personal</a>
  <a href="#registros">Registros</a>
  <a href="#politicas">Políticas</a>
  <a href="#capacitaciones">Capacitaciones</a>
  <a href="#reportes">Reportes</a>
  <a href="#permisos">Permisos</a>
</nav></div>

<div class="content">
  <div class="module" id="dashboard">
    <div class="module-header"><div class="module-num">1</div><div class="module-label">Módulo</div></div>
    <h2>Dashboard de tu Empresa</h2>
    <p>Vista general de los indicadores de tu organización: registros recientes, tickets abiertos y estado de cumplimiento.</p>
    <div class="cards cards-3">
      <div class="card"><div class="card-icon">📊</div><h3>Registros del mes</h3><p>Total de registros de seguridad creados por tu equipo este mes.</p></div>
      <div class="card"><div class="card-icon">🎫</div><h3>Tickets abiertos</h3><p>Tickets de tu empresa que están pendientes de resolución.</p></div>
      <div class="card"><div class="card-icon">✅</div><h3>Cumplimiento</h3><p>Porcentaje de usuarios que han aceptado las políticas obligatorias.</p></div>
    </div>
  </div>

  <div class="module" id="usuarios">
    <div class="module-header"><div class="module-num">2</div><div class="module-label">Módulo</div></div>
    <h2>Gestión de Personal</h2>
    <p>Administra los usuarios de tu empresa. Crea cuentas, asigna roles internos y controla el acceso.</p>
    <div class="steps">
      <div class="step"><div class="step-num">1</div><div><h4>Crear usuario</h4><p>Personal → Nuevo. Ingresa nombre, email y selecciona el rol (Usuario o Solo Lectura). El sistema envía credenciales por email.</p></div></div>
      <div class="step"><div class="step-num">2</div><div><h4>Editar o desactivar</h4><p>Cambia datos del usuario o ponlo en estado "inactivo" para revocar su acceso sin borrar su historial.</p></div></div>
      <div class="step"><div class="step-num">3</div><div><h4>Roles disponibles para asignar</h4><p>Puedes asignar: Usuario (crea registros y tickets) o Solo Lectura (solo consulta). No puedes crear otros Admin ni Super Admin.</p></div></div>
    </div>
    <div class="tip"><span class="tip-icon">💡</span><div>Solo ves los usuarios de <strong>tu empresa</strong>. No puedes ver ni modificar usuarios de otras organizaciones.</div></div>
  </div>

  <div class="module" id="registros">
    <div class="module-header"><div class="module-num">3</div><div class="module-label">Módulo</div></div>
    <h2>Registros F01–F13</h2>
    <p>Crea y gestiona registros de seguridad. Puedes crear, editar, exportar y eliminar registros de tu empresa.</p>
    <div class="formats">
      <div class="format"><code>F01</code><span>Control de acceso físico</span></div>
      <div class="format"><code>F02</code><span>Inventario de activos</span></div>
      <div class="format"><code>F03</code><span>Copia de seguridad</span></div>
      <div class="format"><code>F04</code><span>Mantenimiento de equipos</span></div>
      <div class="format"><code>F05</code><span>Gestión de cambios</span></div>
      <div class="format"><code>F06</code><span>Monitoreo de red</span></div>
      <div class="format"><code>F07</code><span>Control de acceso lógico</span></div>
      <div class="format"><code>F08</code><span>Capacitación de seguridad</span></div>
      <div class="format"><code>F09</code><span>Reporte de incidencias</span></div>
      <div class="format"><code>F10</code><span>Auditoría interna</span></div>
      <div class="format"><code>F11</code><span>Gestión de proveedores</span></div>
      <div class="format"><code>F12</code><span>Plan de continuidad</span></div>
      <div class="format"><code>F13</code><span>Revisión por la dirección</span></div>
    </div>
  </div>

  <div class="module" id="politicas">
    <div class="module-header"><div class="module-num">4</div><div class="module-label">Módulo</div></div>
    <h2>Políticas y Cumplimiento</h2>
    <p>Crea políticas de seguridad, NDA y monitorea el cumplimiento de tu equipo.</p>
    <div class="cards cards-2">
      <div class="card"><div class="card-icon">📑</div><h3>Crear política</h3><p>Define título, contenido, versión y si es obligatoria. Puedes marcarla como NDA para requerir firma digital.</p><span class="card-path">Seguridad → Wiki de Políticas → Nueva</span></div>
      <div class="card"><div class="card-icon">📊</div><h3>Panel de cumplimiento</h3><p>Ve qué porcentaje de tu equipo ha aceptado cada política. Envía recordatorios individuales o masivos.</p><span class="card-path">Seguridad → Cumplimiento</span></div>
    </div>
  </div>

  <div class="module" id="capacitaciones">
    <div class="module-header"><div class="module-num">5</div><div class="module-label">Módulo</div></div>
    <h2>Capacitaciones</h2>
    <p>Consulta las sesiones de capacitación programadas y la asistencia de tu equipo.</p>
    <div class="card"><div class="card-icon">🎓</div><h3>Sesiones programadas</h3><p>Ve las capacitaciones asignadas a tu empresa con fecha, modalidad (presencial/virtual), instructor y lista de asistentes confirmados.</p><span class="card-path">Capacitaciones → Sesiones</span></div>
  </div>

  <div class="module" id="reportes">
    <div class="module-header"><div class="module-num">6</div><div class="module-label">Módulo</div></div>
    <h2>Reportes</h2>
    <p>Exporta registros y reportes de cumplimiento de tu empresa.</p>
    <div class="cards cards-3">
      <div class="card"><div class="card-icon">📄</div><h3>PDF individual</h3><p>Exporta un registro con logo de tu empresa.</p></div>
      <div class="card"><div class="card-icon">📊</div><h3>Excel masivo</h3><p>Exporta múltiples registros a Excel.</p></div>
      <div class="card"><div class="card-icon">📋</div><h3>Firmantes</h3><p>PDF con la lista de firmantes por política.</p></div>
    </div>
  </div>

  <div class="module" id="permisos">
    <div class="module-header"><div class="module-num">7</div><div class="module-label">Permisos</div></div>
    <h2>Qué puedes y no puedes hacer</h2>
    <div class="perms">
      <div class="perm-yes">
        <div class="perm-title yes">✅ Puedes</div>
        <div class="perm-item">✅ Crear y gestionar usuarios de tu empresa</div>
        <div class="perm-item">✅ Crear, editar y eliminar registros F01–F13</div>
        <div class="perm-item">✅ Crear y publicar políticas y NDA</div>
        <div class="perm-item">✅ Ver panel de cumplimiento de tu empresa</div>
        <div class="perm-item">✅ Exportar PDF y Excel</div>
        <div class="perm-item">✅ Abrir y seguir tickets</div>
      </div>
      <div class="perm-no">
        <div class="perm-title no">❌ No puedes</div>
        <div class="perm-item">❌ Ver datos de otras empresas</div>
        <div class="perm-item">❌ Crear otros Admin de Empresa</div>
        <div class="perm-item">❌ Acceder al panel de agente</div>
        <div class="perm-item">❌ Modificar configuración global</div>
        <div class="perm-item">❌ Gestionar templates de email</div>
      </div>
    </div>
  </div>
</div>
@endsection
