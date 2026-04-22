@extends('docs.layout')
@section('role-name','Solo Lectura')
@section('role-color','#475569')
@section('role-accent','#64748B')
@section('role-light','#F1F5F9')

@section('content')
<section class="hero">
  <div class="hero-icon">👁️</div>
  <h1>Solo Lectura</h1>
  <p>Acceso de consulta y auditoría. Visualiza registros, tickets y políticas de tu empresa sin poder modificar nada.</p>
  <div class="hero-stats">
    <div class="hero-stat"><strong>Nivel 1</strong><span>Observador</span></div>
    <div class="hero-stat"><strong>Solo</strong><span>Consulta</span></div>
    <div class="hero-stat"><strong>3</strong><span>Módulos</span></div>
  </div>
</section>

<nav class="section-nav"><div class="section-nav-inner">
  <a href="#registros" class="active">Registros</a>
  <a href="#tickets">Tickets</a>
  <a href="#politicas">Políticas</a>
  <a href="#permisos">Permisos</a>
</nav></div>

<div class="content">
  <div class="module" id="registros">
    <div class="module-header"><div class="module-num">1</div><div class="module-label">Módulo</div></div>
    <h2>Consulta de Registros</h2>
    <p>Visualiza todos los registros de seguridad de tu empresa. Puedes filtrar y buscar, pero no crear ni editar.</p>
    <div class="cards cards-2">
      <div class="card"><div class="card-icon">📋</div><h3>Ver registros</h3><p>Accede a la lista completa de registros F01–F13 de tu empresa. Usa filtros por tipo, fecha o estado.</p><span class="card-path">Seguridad → Registros</span></div>
      <div class="card"><div class="card-icon">📄</div><h3>Exportar</h3><p>Puedes descargar registros en formato PDF o Excel para revisión externa o auditoría.</p><span class="card-path">Registro → Exportar</span></div>
    </div>
  </div>

  <div class="module" id="tickets">
    <div class="module-header"><div class="module-num">2</div><div class="module-label">Módulo</div></div>
    <h2>Consulta de Tickets</h2>
    <p>Visualiza los tickets de soporte de tu empresa. Puedes ver el historial pero no crear nuevos ni responder.</p>
    <div class="card"><div class="card-icon">🎫</div><h3>Ver tickets</h3><p>Consulta todos los tickets de tu empresa, su estado actual y el hilo de conversación completo. Ideal para auditoría de soporte.</p><span class="card-path">Soporte → Tickets</span></div>
  </div>

  <div class="module" id="politicas">
    <div class="module-header"><div class="module-num">3</div><div class="module-label">Módulo</div></div>
    <h2>Políticas y Capacitaciones</h2>
    <p>Consulta las políticas activas y las sesiones de capacitación de tu empresa.</p>
    <div class="cards cards-2">
      <div class="card"><div class="card-icon">📖</div><h3>Wiki de Políticas</h3><p>Lee las políticas de seguridad publicadas. Puedes ver el contenido completo y la versión vigente.</p><span class="card-path">Seguridad → Wiki de Políticas</span></div>
      <div class="card"><div class="card-icon">🎓</div><h3>Capacitaciones</h3><p>Consulta las sesiones programadas y la lista de asistentes confirmados.</p><span class="card-path">Capacitaciones</span></div>
    </div>
  </div>

  <div class="module" id="permisos">
    <div class="module-header"><div class="module-num">4</div><div class="module-label">Permisos</div></div>
    <h2>Qué puedes y no puedes hacer</h2>
    <div class="perms">
      <div class="perm-yes">
        <div class="perm-title yes">✅ Puedes</div>
        <div class="perm-item">✅ Ver registros de seguridad de tu empresa</div>
        <div class="perm-item">✅ Ver tickets y su historial</div>
        <div class="perm-item">✅ Consultar políticas activas</div>
        <div class="perm-item">✅ Exportar registros a PDF/Excel</div>
        <div class="perm-item">✅ Editar tu perfil</div>
      </div>
      <div class="perm-no">
        <div class="perm-title no">❌ No puedes</div>
        <div class="perm-item">❌ Crear, editar o eliminar registros</div>
        <div class="perm-item">❌ Crear o responder tickets</div>
        <div class="perm-item">❌ Gestionar usuarios</div>
        <div class="perm-item">❌ Crear o editar políticas</div>
        <div class="perm-item">❌ Confirmar asistencia a capacitaciones</div>
        <div class="perm-item">❌ Ver datos de otras empresas</div>
      </div>
    </div>
    <div class="alert">Este rol está diseñado para <strong>auditores externos</strong> o personal que necesita consultar información sin riesgo de modificaciones accidentales.</div>
  </div>
</div>
@endsection
