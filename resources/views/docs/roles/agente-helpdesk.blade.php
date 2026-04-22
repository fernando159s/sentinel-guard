@extends('docs.layout')
@section('role-name','Agente Helpdesk')
@section('role-color','#7C3AED')
@section('role-accent','#8B5CF6')
@section('role-light','#F5F3FF')

@section('content')
<section class="hero">
  <div class="hero-icon">💬</div>
  <h1>Agente Helpdesk</h1>
  <p>Atiende tickets de soporte de todas las empresas. Gestiona estados, responde consultas y escala incidencias cuando sea necesario.</p>
  <div class="hero-stats">
    <div class="hero-stat"><strong>Nivel 3</strong><span>Soporte</span></div>
    <div class="hero-stat"><strong>Cross</strong><span>Multi-empresa</span></div>
    <div class="hero-stat"><strong>3</strong><span>Módulos</span></div>
  </div>
</section>

<nav class="section-nav"><div class="section-nav-inner">
  <a href="#tickets" class="active">Tickets</a>
  <a href="#registros">Registros</a>
  <a href="#perfil">Mi Perfil</a>
  <a href="#permisos">Permisos</a>
</nav></div>

<div class="content">
  <div class="module" id="tickets">
    <div class="module-header"><div class="module-num">1</div><div class="module-label">Módulo principal</div></div>
    <h2>Gestión de Tickets</h2>
    <p>Tu bandeja muestra tickets de todas las empresas. Filtra por estado, prioridad o empresa para organizar tu trabajo.</p>
    <h3 style="font-size:14px;font-weight:700;margin-bottom:12px">Flujo de estados</h3>
    <div class="flow">
      <div class="flow-step current">Nuevo</div><div class="flow-arrow">→</div>
      <div class="flow-step">En revisión</div><div class="flow-arrow">→</div>
      <div class="flow-step">Esperando usuario</div><div class="flow-arrow">→</div>
      <div class="flow-step">Resuelto</div><div class="flow-arrow">→</div>
      <div class="flow-step">Cerrado</div>
    </div>
    <div class="steps" style="margin-top:24px">
      <div class="step"><div class="step-num">1</div><div><h4>Revisar tickets nuevos</h4><p>Abre un ticket nuevo, lee la descripción y adjuntos del usuario. Cambia el estado a "En revisión" cuando empieces a trabajar.</p></div></div>
      <div class="step"><div class="step-num">2</div><div><h4>Responder al usuario</h4><p>Escribe una respuesta pública para el usuario. También puedes agregar notas internas visibles solo para otros agentes.</p></div></div>
      <div class="step"><div class="step-num">3</div><div><h4>Solicitar información</h4><p>Si necesitas más datos, cambia el estado a "Esperando usuario". El usuario recibirá notificación para responder.</p></div></div>
      <div class="step"><div class="step-num">4</div><div><h4>Resolver y cerrar</h4><p>Marca el ticket como "Resuelto" cuando termines. Se cerrará automáticamente después de 7 días si el usuario no lo reabre.</p></div></div>
    </div>
    <div class="tip"><span class="tip-icon">⚡</span><div><strong>Prioridad:</strong> Atiende primero los tickets marcados como Alta o Urgente. Los tickets sin respuesta por más de 48h se resaltan en la bandeja.</div></div>
  </div>

  <div class="module" id="registros">
    <div class="module-header"><div class="module-num">2</div><div class="module-label">Módulo</div></div>
    <h2>Registros de Incidencias</h2>
    <p>Como agente, puedes crear registros de incidencias (F09) relacionados con los tickets que atiendes.</p>
    <div class="card"><div class="card-icon">📋</div><h3>Crear registro desde ticket</h3><p>Si un ticket revela una incidencia de seguridad, crea un registro F09 directamente. Esto documenta el incidente formalmente.</p><span class="card-path">Seguridad → Registros → Nuevo (F09)</span></div>
  </div>

  <div class="module" id="perfil">
    <div class="module-header"><div class="module-num">3</div><div class="module-label">Módulo</div></div>
    <h2>Mi Perfil</h2>
    <p>Actualiza tu información personal y credenciales.</p>
    <div class="card"><div class="card-icon">👤</div><h3>Editar perfil</h3><p>Actualiza nombre, contraseña y foto de perfil. Los cambios se reflejan inmediatamente en tus respuestas de tickets.</p><span class="card-path">Admin → Mi Perfil</span></div>
  </div>

  <div class="module" id="permisos">
    <div class="module-header"><div class="module-num">4</div><div class="module-label">Permisos</div></div>
    <h2>Qué puedes y no puedes hacer</h2>
    <div class="perms">
      <div class="perm-yes">
        <div class="perm-title yes">✅ Puedes</div>
        <div class="perm-item">✅ Ver y gestionar tickets de todas las empresas</div>
        <div class="perm-item">✅ Responder con mensajes públicos y notas internas</div>
        <div class="perm-item">✅ Cambiar estado y prioridad de tickets</div>
        <div class="perm-item">✅ Crear registros de incidencias (F09)</div>
        <div class="perm-item">✅ Editar tu perfil</div>
      </div>
      <div class="perm-no">
        <div class="perm-title no">❌ No puedes</div>
        <div class="perm-item">❌ Gestionar usuarios o empresas</div>
        <div class="perm-item">❌ Crear registros que no sean F09</div>
        <div class="perm-item">❌ Ver registros de seguridad de empresas</div>
        <div class="perm-item">❌ Crear o editar políticas</div>
        <div class="perm-item">❌ Acceder al panel de cumplimiento</div>
      </div>
    </div>
  </div>
</div>
@endsection
