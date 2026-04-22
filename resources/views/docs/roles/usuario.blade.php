@extends('docs.layout')
@section('role-name','Usuario')
@section('role-color','#047857')
@section('role-accent','#10B981')
@section('role-light','#ECFDF5')

@section('content')
<section class="hero">
  <div class="hero-icon">👤</div>
  <h1>Usuario</h1>
  <p>Documenta incidencias y formatos de seguridad, abre tickets de soporte y consulta las políticas de tu empresa. Simple y directo.</p>
  <div class="hero-stats">
    <div class="hero-stat"><strong>Nivel 3</strong><span>Colaborador</span></div>
    <div class="hero-stat"><strong>13</strong><span>Formatos disponibles</span></div>
    <div class="hero-stat"><strong>5</strong><span>Módulos</span></div>
  </div>
</section>

<nav class="section-nav"><div class="section-nav-inner">
  <a href="#firma-politicas" class="active">Firma de Políticas</a>
  <a href="#registros">Registros</a>
  <a href="#tickets">Tickets</a>
  <a href="#politicas">Políticas</a>
  <a href="#perfil">Mi Perfil</a>
  <a href="#permisos">Permisos</a>
</nav></div>

<div class="content">
  <div class="module" id="firma-politicas">
    <div class="module-header"><div class="module-num">1</div><div class="module-label">Módulo</div></div>
    <h2>Firma de Políticas y NDA</h2>
    <p>Antes de acceder al sistema debes leer y firmar digitalmente los documentos obligatorios de tu empresa. Esto garantiza que conoces las reglas de seguridad y que existe constancia legal de tu aceptación.</p>

    <h3 style="margin-top:24px;font-size:14px;font-weight:700;color:#047857">¿Por qué debo firmar?</h3>
    <div class="cards cards-2" style="margin-top:12px">
      <div class="card"><div class="card-icon">🛡️</div><h3>Cumplimiento legal</h3><p>El estudio está sujeto a la Ley N° 29733 de Protección de Datos Personales. Tu firma acredita que conoces cómo se trata la información confidencial.</p></div>
      <div class="card"><div class="card-icon">🔒</div><h3>Seguridad de la información</h3><p>Las políticas definen qué puedes y no puedes hacer con la información del estudio y sus clientes. Firmarlas confirma tu compromiso con esas reglas.</p></div>
    </div>

    <h3 style="margin-top:28px;font-size:14px;font-weight:700">Flujo completo al iniciar sesión</h3>
    <div class="steps" style="margin-top:12px">
      <div class="step"><div class="step-num">1</div><div><h4>El sistema detecta documentos pendientes</h4><p>Al entrar, si tienes políticas obligatorias sin firmar, el sistema te redirige automáticamente al flujo de firma antes de mostrarte el panel principal.</p></div></div>
      <div class="step"><div class="step-num">2</div><div><h4>Completas tus datos personales (solo si hay NDA)</h4><p>Si uno de los documentos pendientes es el Acuerdo de Confidencialidad, primero deberás ingresar tu DNI, dirección, teléfono y puesto. Estos datos aparecerán dentro del documento del NDA.</p></div></div>
      <div class="step"><div class="step-num">3</div><div><h4>Lees el documento completo</h4><p>El texto del documento aparece en la pantalla izquierda. Léelo completo antes de firmar. Para el NDA, verás tu nombre y datos ya incorporados en el texto.</p></div></div>
      <div class="step"><div class="step-num">4</div><div><h4>Firmas digitalmente</h4><p>En el panel derecho escribe tu nombre, tu cargo (opcional) y dibuja tu firma con el ratón o el dedo en la pantalla táctil. También puedes subir una imagen de tu firma.</p></div></div>
      <div class="step"><div class="step-num">5</div><div><h4>Marcas la casilla de aceptación y confirmas</h4><p>Activa la casilla "He leído y acepto esta política" y presiona <strong>Firmar y aceptar</strong>. El sistema guarda tu firma, la fecha, la hora y tu dirección IP como evidencia.</p></div></div>
      <div class="step"><div class="step-num">6</div><div><h4>Repites por cada documento pendiente</h4><p>Si hay más de un documento obligatorio, el flujo continúa automáticamente con el siguiente. Al terminar todos, accedes al panel principal.</p></div></div>
    </div>

    <div class="tip" style="margin-top:24px;background:#fef9c3;border-left-color:#ca8a04"><span class="tip-icon">📋</span><div><strong>Acuerdo de Confidencialidad (NDA):</strong> Es el documento más importante. Establece que mantendrás en secreto toda la información del estudio y sus clientes, incluso después de dejar de trabajar allí. Tiene una vigencia de <strong>2 años</strong> — al vencer te pedirá firmarlo de nuevo.</div></div>
    <div class="tip" style="margin-top:12px"><span class="tip-icon">✍️</span><div><strong>Tu firma se guarda:</strong> La primera vez que firmas, el sistema recuerda tu firma para los siguientes documentos. No tendrás que dibujarla otra vez en esa sesión.</div></div>
    <div class="tip" style="margin-top:12px;background:#fef2f2;border-left-color:#dc2626"><span class="tip-icon">⚠️</span><div><strong>Sin firma, sin acceso:</strong> Mientras haya documentos pendientes de firma, no podrás acceder al panel. Es obligatorio completar el proceso.</div></div>
  </div>

  <div class="module" id="registros">
    <div class="module-header"><div class="module-num">2</div><div class="module-label">Módulo</div></div>
    <h2>Registros de Seguridad</h2>
    <p>Documenta situaciones de seguridad de la información usando los formatos PSC establecidos por tu empresa.</p>
    <div class="steps">
      <div class="step"><div class="step-num">1</div><div><h4>Ve a Seguridad → Registros</h4><p>Verás la lista de todos los registros de tu empresa. Usa filtros para buscar por tipo de formato o fecha.</p></div></div>
      <div class="step"><div class="step-num">2</div><div><h4>Haz clic en "Nuevo registro"</h4><p>Selecciona el tipo de formato (ej. F09 para notificar una incidencia). El formulario cambia según el formato elegido.</p></div></div>
      <div class="step"><div class="step-num">3</div><div><h4>Completa el formulario</h4><p>Cada formato tiene campos específicos según la política PSC correspondiente. Completa todos los campos requeridos.</p></div></div>
      <div class="step"><div class="step-num">4</div><div><h4>Guarda — el número se asigna automáticamente</h4><p>El sistema genera el código del registro (ej: INC-2026-007) y queda guardado en el historial de tu empresa.</p></div></div>
    </div>
    <div class="tip"><span class="tip-icon">📝</span><div><strong>Importante:</strong> Puedes editar un registro después de crearlo, pero NO puedes eliminarlo. Si necesitas borrar uno, contacta a tu admin de empresa.</div></div>
  </div>

  <div class="module" id="tickets">
    <div class="module-header"><div class="module-num">3</div><div class="module-label">Módulo</div></div>
    <h2>Tickets de Soporte</h2>
    <p>Cuando tengas un problema técnico o una consulta, crea un ticket para el equipo de soporte.</p>
    <div class="cards cards-2">
      <div class="card"><div class="card-icon">📤</div><h3>Crear ticket</h3><p>Soporte → Tickets → Nuevo. Describe el problema, agrega adjuntos si ayudan y elige la prioridad.</p><span class="card-path">Soporte → Tickets → Nuevo</span></div>
      <div class="card"><div class="card-icon">💬</div><h3>Seguimiento</h3><p>Haz clic en tu ticket para ver respuestas del agente. Puedes agregar mensajes adicionales con más información.</p><span class="card-path">Soporte → Mis Tickets</span></div>
    </div>
    <h3 style="margin-top:24px;font-size:14px;font-weight:700">Ciclo de vida del ticket</h3>
    <div class="flow">
      <div class="flow-step current">Nuevo</div><div class="flow-arrow">→</div>
      <div class="flow-step">En revisión</div><div class="flow-arrow">→</div>
      <div class="flow-step">Esperando info</div><div class="flow-arrow">→</div>
      <div class="flow-step">Resuelto</div><div class="flow-arrow">→</div>
      <div class="flow-step">Cerrado</div>
    </div>
    <div class="tip"><span class="tip-icon">🔄</span><div><strong>¿Ticket resuelto pero sigue el problema?</strong> Puedes reabrir un ticket resuelto dentro de los 7 días siguientes desde el botón "Reabrir" en el detalle del ticket.</div></div>
  </div>

  <div class="module" id="politicas">
    <div class="module-header"><div class="module-num">4</div><div class="module-label">Módulo</div></div>
    <h2>Capacitaciones y Políticas</h2>
    <p>Consulta sesiones de formación y accede a las políticas de seguridad de tu empresa.</p>
    <div class="cards cards-2">
      <div class="card"><div class="card-icon">🎓</div><h3>Mis Capacitaciones</h3><p>Ve sesiones de formación donde estás inscrito. Consulta fecha, modalidad e instructor. Confirma tu asistencia.</p><span class="card-path">Capacitaciones → Mis Capacitaciones</span></div>
      <div class="card"><div class="card-icon">📖</div><h3>Wiki de Políticas</h3><p>Consulta las políticas de seguridad activas de tu empresa en cualquier momento. Son documentos de referencia.</p><span class="card-path">Seguridad → Wiki de Políticas</span></div>
      <div class="card"><div class="card-icon">✍️</div><h3>Aceptar políticas</h3><p>Cuando se publiquen nuevas políticas obligatorias, recibirás una notificación para aceptarlas y mantener tu acceso.</p><span class="card-path">Notificación → Aceptar</span></div>
      <div class="card"><div class="card-icon">✅</div><h3>Confirmar asistencia</h3><p>Si asististe a una sesión, confírmalo. Queda registrado en el historial de cumplimiento de tu empresa.</p><span class="card-path">Mis Capacitaciones → Confirmar</span></div>
    </div>
  </div>

  <div class="module" id="perfil">
    <div class="module-header"><div class="module-num">5</div><div class="module-label">Módulo</div></div>
    <h2>Mi Perfil</h2>
    <p>Personaliza tu cuenta y mantén tu información actualizada.</p>
    <div class="card"><div class="card-icon">👤</div><h3>Editar perfil</h3><p>Puedes actualizar tu nombre, contraseña y foto de perfil en cualquier momento. Los cambios son inmediatos.</p><span class="card-path">Admin → Mi Perfil</span></div>
  </div>

  <div class="module" id="permisos">
    <div class="module-header"><div class="module-num">6</div><div class="module-label">Permisos</div></div>
    <h2>Qué puedes y no puedes hacer</h2>
    <div class="perms">
      <div class="perm-yes">
        <div class="perm-title yes">✅ Puedes</div>
        <div class="perm-item">✅ Crear registros de seguridad (F01–F13)</div>
        <div class="perm-item">✅ Ver todos los registros de tu empresa</div>
        <div class="perm-item">✅ Crear y seguir tickets de soporte</div>
        <div class="perm-item">✅ Consultar la Wiki de Políticas</div>
        <div class="perm-item">✅ Ver y confirmar capacitaciones</div>
        <div class="perm-item">✅ Editar tu perfil y contraseña</div>
      </div>
      <div class="perm-no">
        <div class="perm-title no">❌ No puedes</div>
        <div class="perm-item">❌ Eliminar registros</div>
        <div class="perm-item">❌ Gestionar usuarios</div>
        <div class="perm-item">❌ Ver datos de otras empresas</div>
        <div class="perm-item">❌ Crear o editar políticas</div>
        <div class="perm-item">❌ Acceder al Panel de Agente</div>
      </div>
    </div>
    <div class="tip"><span class="tip-icon">💬</span><div>Si necesitas permisos adicionales, <strong>abre un ticket</strong> explicando lo que necesitas. El equipo de soporte te ayudará.</div></div>
  </div>
</div>
@endsection
