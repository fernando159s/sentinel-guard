@extends('docs.layout')
@section('role-name','Super Administrador')
@section('role-color','#1E3A5F')
@section('role-accent','#3B82F6')
@section('role-light','#EFF6FF')

@section('content')
<section class="hero">
  <div class="hero-icon">🛡️</div>
  <h1>Super Administrador</h1>
  <p>Control total del sistema. Gestionas empresas, usuarios globales, tickets, configuración y tienes visibilidad completa de toda la plataforma.</p>
  <div class="hero-stats">
    <div class="hero-stat"><strong>Nivel 5</strong><span>Acceso máximo</span></div>
    <div class="hero-stat"><strong>13</strong><span>Formatos PSC</span></div>
    <div class="hero-stat"><strong>5</strong><span>Módulos</span></div>
  </div>
</section>

<nav class="section-nav"><div class="section-nav-inner">
  <a href="#dashboard" class="active">Dashboard</a>
  <a href="#empresas">Empresas</a>
  <a href="#usuarios">Personal</a>
  <a href="#registros">Registros</a>
  <a href="#agente">Panel Agente</a>
  <a href="#cumplimiento">Cumplimiento</a>
  <a href="#reportes">Reportes</a>
  <a href="#config">Configuración</a>
  <a href="#permisos">Permisos</a>
</nav></div>

<div class="content">

  {{-- 1. Dashboard --}}
  <div class="module" id="dashboard">
    <div class="module-header"><div class="module-num">1</div><div class="module-label">Módulo</div></div>
    <h2>Dashboard Global</h2>
    <p>Vista panorámica de toda la plataforma. Métricas en tiempo real de todas las empresas, registros y tickets.</p>
    <div class="cards cards-3">
      <div class="card"><div class="card-icon">📊</div><h3>Registros totales</h3><p>Total de registros creados en todas las empresas, con tendencia mensual.</p></div>
      <div class="card"><div class="card-icon">🎫</div><h3>Tickets activos</h3><p>Tickets abiertos y en proceso de todas las empresas con prioridad.</p></div>
      <div class="card"><div class="card-icon">🏢</div><h3>Empresas activas</h3><p>Cantidad de empresas registradas y usuarios por cada una.</p></div>
    </div>
    <div class="tip"><span class="tip-icon">💡</span><div>El dashboard se actualiza automáticamente. Puedes filtrar por empresa o rango de fechas desde los controles superiores.</div></div>
  </div>

  {{-- 2. Empresas --}}
  <div class="module" id="empresas">
    <div class="module-header"><div class="module-num">2</div><div class="module-label">Módulo</div></div>
    <h2>Gestión de Empresas</h2>
    <p>Crea, edita y administra todas las organizaciones que usan la plataforma. Cada empresa opera en su propio espacio aislado.</p>
    <div class="steps">
      <div class="step"><div class="step-num">1</div><div><h4>Crear nueva empresa</h4><p>Ve a Empresas → Nueva empresa. Completa RUC, razón social y datos de contacto. El RUC se usa como identificador único.</p></div></div>
      <div class="step"><div class="step-num">2</div><div><h4>Configurar logo y branding</h4><p>Sube el logotipo de la empresa. Este se usará en PDFs exportados y en el panel de la organización.</p></div></div>
      <div class="step"><div class="step-num">3</div><div><h4>Asignar administrador</h4><p>Crea un usuario con rol Admin Empresa para que gestione su propia organización de forma independiente.</p></div></div>
    </div>
    <div class="card" style="margin-top:20px"><div class="card-icon">🔒</div><h3>Aislamiento de datos (Multi-Tenancy)</h3><p>Cada empresa solo puede ver sus propios datos. El sistema filtra automáticamente por empresa usando un EmpresaScope global. Un usuario de Empresa A nunca puede ver datos de Empresa B.</p></div>
  </div>

  {{-- 3. Personal --}}
  <div class="module" id="usuarios">
    <div class="module-header"><div class="module-num">3</div><div class="module-label">Módulo</div></div>
    <h2>Gestión de Personal</h2>
    <p>Administra los usuarios de cualquier empresa. Asigna roles, resetea contraseñas y controla el acceso.</p>
    <div class="cards cards-2">
      <div class="card"><div class="card-icon">👤</div><h3>Crear usuario</h3><p>Personal → Nuevo. Asigna nombre, email, empresa y rol. El usuario recibe sus credenciales por email.</p><span class="card-path">Personal → Usuarios → Nuevo</span></div>
      <div class="card"><div class="card-icon">🔑</div><h3>Asignar roles</h3><p>5 roles disponibles: Super Admin, Admin Empresa, Usuario, Agente Helpdesk, Solo Lectura. Cada rol tiene permisos específicos.</p><span class="card-path">Personal → Usuarios → Editar</span></div>
      <div class="card"><div class="card-icon">🏢</div><h3>Cambiar de empresa</h3><p>Como Super Admin puedes cambiar entre empresas desde el selector de empresa en la barra superior.</p><span class="card-path">Selector de empresa (top bar)</span></div>
      <div class="card"><div class="card-icon">🚫</div><h3>Desactivar usuario</h3><p>Cambia el estado a "inactivo". El usuario no podrá iniciar sesión pero sus registros se mantienen.</p><span class="card-path">Personal → Usuarios → Estado</span></div>
    </div>
  </div>

  {{-- 4. Registros --}}
  <div class="module" id="registros">
    <div class="module-header"><div class="module-num">4</div><div class="module-label">Módulo</div></div>
    <h2>Registros F01–F13</h2>
    <p>Los 13 formatos de seguridad de la información según políticas PSC. Puedes crear, editar, exportar y eliminar registros de cualquier empresa.</p>
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
    <div class="steps" style="margin-top:24px">
      <div class="step"><div class="step-num">1</div><div><h4>Seleccionar formato</h4><p>Seguridad → Registros → Nuevo registro. Elige el tipo de formato (F01–F13). El formulario se adapta dinámicamente.</p></div></div>
      <div class="step"><div class="step-num">2</div><div><h4>Completar formulario</h4><p>Cada formato tiene campos específicos según la política PSC. Completa toda la información requerida.</p></div></div>
      <div class="step"><div class="step-num">3</div><div><h4>Guardar y exportar</h4><p>El número de registro se asigna automáticamente (ej: INC-2026-007). Puedes exportar a PDF o Excel en cualquier momento.</p></div></div>
    </div>
  </div>

  {{-- 5. Panel Agente --}}
  <div class="module" id="agente">
    <div class="module-header"><div class="module-num">5</div><div class="module-label">Módulo</div></div>
    <h2>Panel de Agente</h2>
    <p>Gestiona tickets de soporte de todas las empresas. Asigna prioridades, responde y cierra tickets.</p>
    <div class="flow">
      <div class="flow-step current">Nuevo</div><div class="flow-arrow">→</div>
      <div class="flow-step">En revisión</div><div class="flow-arrow">→</div>
      <div class="flow-step">Esperando usuario</div><div class="flow-arrow">→</div>
      <div class="flow-step">Resuelto</div><div class="flow-arrow">→</div>
      <div class="flow-step">Cerrado</div>
    </div>
    <div class="cards cards-2" style="margin-top:20px">
      <div class="card"><div class="card-icon">📋</div><h3>Bandeja de tickets</h3><p>Todos los tickets de todas las empresas ordenados por prioridad y fecha. Filtra por estado o empresa.</p><span class="card-path">Soporte → Tickets</span></div>
      <div class="card"><div class="card-icon">💬</div><h3>Responder tickets</h3><p>Abre un ticket para ver el hilo completo. Responde con mensajes públicos o notas internas para el equipo.</p><span class="card-path">Ticket → Responder</span></div>
    </div>
  </div>

  {{-- 6. Cumplimiento --}}
  <div class="module" id="cumplimiento">
    <div class="module-header"><div class="module-num">6</div><div class="module-label">Módulo</div></div>
    <h2>Panel de Cumplimiento</h2>
    <p>Monitorea el cumplimiento de políticas y NDA por empresa. Ve qué usuarios han aceptado las políticas y quiénes están pendientes.</p>
    <div class="cards cards-2">
      <div class="card"><div class="card-icon">📑</div><h3>Wiki de Políticas</h3><p>Crea y publica políticas de seguridad. Cada política tiene versión, fecha de vigencia y puede ser obligatoria o informativa.</p><span class="card-path">Seguridad → Wiki de Políticas</span></div>
      <div class="card"><div class="card-icon">✅</div><h3>Panel de cumplimiento</h3><p>Vista de qué porcentaje de usuarios han aceptado cada política obligatoria. Envía recordatorios masivos a los pendientes.</p><span class="card-path">Seguridad → Cumplimiento</span></div>
    </div>
  </div>

  {{-- 7. Reportes --}}
  <div class="module" id="reportes">
    <div class="module-header"><div class="module-num">7</div><div class="module-label">Módulo</div></div>
    <h2>Reportes y Exportación</h2>
    <p>Exporta registros individuales o en lote. Genera PDFs con el logo de la empresa y exporta datos a Excel.</p>
    <div class="cards cards-3">
      <div class="card"><div class="card-icon">📄</div><h3>PDF individual</h3><p>Exporta cualquier registro como PDF con formato oficial y logo de la empresa.</p></div>
      <div class="card"><div class="card-icon">📊</div><h3>Excel masivo</h3><p>Selecciona múltiples registros y exporta todos a un archivo Excel con una hoja por registro.</p></div>
      <div class="card"><div class="card-icon">📋</div><h3>Reporte de firmantes</h3><p>Descarga la lista de usuarios que han firmado cada política en formato PDF.</p></div>
    </div>
  </div>

  {{-- 8. Configuración --}}
  <div class="module" id="config">
    <div class="module-header"><div class="module-num">8</div><div class="module-label">Módulo</div></div>
    <h2>Configuración</h2>
    <p>Ajustes globales del sistema disponibles solo para Super Admin.</p>
    <div class="cards cards-2">
      <div class="card"><div class="card-icon">📧</div><h3>Templates de email</h3><p>Personaliza las plantillas de notificaciones del sistema: bienvenida, recordatorios, tickets.</p><span class="card-path">Configuración → Email Templates</span></div>
      <div class="card"><div class="card-icon">⚙️</div><h3>Capacitaciones</h3><p>Programa sesiones de capacitación para las empresas. Define tema, fecha, modalidad e instructor.</p><span class="card-path">Capacitaciones → Gestionar</span></div>
    </div>
  </div>

  {{-- 9. Permisos --}}
  <div class="module" id="permisos">
    <div class="module-header"><div class="module-num">9</div><div class="module-label">Permisos</div></div>
    <h2>Qué puedes y no puedes hacer</h2>
    <div class="perms">
      <div class="perm-yes">
        <div class="perm-title yes">✅ Acceso completo</div>
        <div class="perm-item">✅ Crear, editar y eliminar empresas</div>
        <div class="perm-item">✅ Gestionar usuarios de cualquier empresa</div>
        <div class="perm-item">✅ Crear y eliminar registros F01–F13</div>
        <div class="perm-item">✅ Gestionar tickets de todas las empresas</div>
        <div class="perm-item">✅ Crear y publicar políticas y NDA</div>
        <div class="perm-item">✅ Exportar PDF y Excel</div>
        <div class="perm-item">✅ Configurar templates de email</div>
        <div class="perm-item">✅ Ver panel de cumplimiento global</div>
      </div>
      <div class="perm-no">
        <div class="perm-title no">⚠️ Consideraciones</div>
        <div class="perm-item">⚠️ Las eliminaciones son permanentes</div>
        <div class="perm-item">⚠️ Al cambiar empresa se cambia el contexto</div>
        <div class="perm-item">⚠️ Los datos entre empresas están aislados</div>
        <div class="perm-item">⚠️ Las acciones quedan registradas en auditoría</div>
      </div>
    </div>
  </div>

</div>
@endsection
