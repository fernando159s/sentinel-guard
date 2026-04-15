# Guia de Usuario — SentinelForms

> Guia completa para usuarios finales del sistema SentinelForms.
> Organizada por rol para facilitar la consulta rapida.

---

## Tabla de contenidos

1. [Acceso al sistema](#1-acceso-al-sistema)
2. [Roles del sistema](#2-roles-del-sistema)
3. [Guia por rol](#3-guia-por-rol)
   - [Usuario](#31-usuario)
   - [Admin de empresa](#32-admin-de-empresa)
   - [Agente helpdesk](#33-agente-helpdesk)
   - [Super administrador](#34-super-administrador)
   - [Solo lectura](#35-solo-lectura)
4. [Modulos del sistema](#4-modulos-del-sistema)
5. [Preguntas frecuentes (FAQ)](#5-preguntas-frecuentes-faq)
6. [Solucion de problemas](#6-solucion-de-problemas)

---

## 1. Acceso al sistema

### Iniciar sesion

1. Accede a la URL del portal proporcionada por tu administrador (ej: `https://tudominio.com/admin`)
2. Ingresa tu **email** y **contrasena**
3. Haz clic en **Iniciar sesion**

### Recuperar contrasena

1. En la pantalla de login, haz clic en **Olvidaste tu contrasena?**
2. Ingresa tu email registrado
3. Revisa tu bandeja de entrada (y spam) para el enlace de restablecimiento
4. El enlace es valido por **1 hora**

### Aceptacion de politicas

Al ingresar por primera vez (o cuando se publiquen nuevas politicas), el sistema te pedira aceptar las politicas obligatorias antes de continuar. Si alguna politica requiere firma digital, deberas firmar en el canvas que aparece en pantalla.

### Completar datos NDA

Si tu empresa tiene politicas NDA activas, deberas completar tus datos personales (DNI, direccion, telefono, puesto) antes de firmar el NDA.

---

## 2. Roles del sistema

| Rol | Que puede hacer |
|-----|----------------|
| **Super administrador** | Gestionar todas las empresas, usuarios, plantillas de email y configuracion global |
| **Admin de empresa** | Gestionar usuarios, registros, politicas, equipos y capacitaciones de SU empresa |
| **Usuario** | Crear y ver registros de seguridad, abrir tickets, ver sus capacitaciones |
| **Agente helpdesk** | Gestionar tickets de soporte de todas las empresas |
| **Solo lectura** | Ver registros y reportes sin poder crear ni editar |

---

## 3. Guia por rol

### 3.1 Usuario

#### Dashboard

Al iniciar sesion veras tu dashboard con:
- **Acciones rapidas**: crear registro, abrir ticket
- **Mis tickets recientes**: estado de tus tickets abiertos
- **Mis registros recientes**: ultimos registros que has creado
- **Mi equipo asignado**: informacion del equipo informatico que tienes asignado

#### Crear un registro de seguridad

1. Ve a **Registros** en el menu lateral
2. Haz clic en **Crear registro**
3. Se mostrara un **catalogo visual** con los 13 formatos disponibles
4. Selecciona el formato que necesitas (ej: F09 - Notificacion de incidencias)
5. Completa el formulario con los datos requeridos
6. Haz clic en **Guardar**
7. El sistema genera automaticamente el numero de registro (ej: `INC-2026-004`)

#### Formatos disponibles

| ID | Formato | Cuando usarlo |
|----|---------|--------------|
| F01 | Auditorias realizadas | Registrar una auditoria interna o externa |
| F02 | Banco de datos inscritos | Registrar un nuevo banco de datos |
| F03 | Prestadores con acceso | Registrar acceso de terceros a datos personales |
| F04 | Datos sensibles | Registrar datos clasificados como sensibles |
| F05 | Personal autorizado | Registrar autorizacion de acceso a banco de datos |
| F06 | Acceso no autorizado | Reportar un acceso no autorizado a soportes |
| F07 | Inventario de soportes | Consultar inventario (reporte automatico desde equipos) |
| F08 | Ingreso/salida soportes | Registrar movimiento de soportes fisicos |
| F09 | Notificacion de incidencias | Reportar un incidente de seguridad |
| F10 | Resolucion de incidencias | Documentar la resolucion de un incidente |
| F11 | Recuperacion de datos | Registrar un proceso de recuperacion |
| F12 | Copias de seguridad | Registrar o programar backups |
| F13 | Destruccion de activos | Registrar la destruccion segura de un activo |

#### Consultar registros

1. Ve a **Registros** en el menu lateral
2. Usa los **filtros** para buscar: por formato, fecha, o busqueda por numero
3. Haz clic en un registro para ver su detalle completo

#### Exportar un registro a PDF

1. Abre el registro que deseas exportar
2. Haz clic en el boton **Exportar PDF** en la barra de acciones
3. Se descargara un PDF con el detalle completo del registro

#### Abrir un ticket de soporte

1. Ve a **Tickets** en el menu lateral
2. Haz clic en **Crear ticket**
3. Completa: asunto, categoria, prioridad y descripcion
4. Opcionalmente, vincula equipos relacionados
5. Haz clic en **Guardar**

#### Seguimiento de ticket

1. Ve a **Tickets** y selecciona tu ticket
2. En la vista de detalle veras el **hilo de conversacion**
3. Puedes **responder** al agente adjuntando archivos si es necesario
4. Si un ticket fue resuelto pero el problema persiste, puedes **reabrirlo** dentro de los 7 dias posteriores a la resolucion

#### Mis capacitaciones

1. Ve a **Capacitaciones > Mis capacitaciones** en el menu lateral
2. Veras el historial de capacitaciones a las que fuiste convocado
3. Puedes ver el detalle de cada una y registrar tu asistencia si esta habilitada

#### Wiki de politicas

1. Ve a **Seguridad > Politicas** en el menu lateral
2. Lee las politicas vigentes de tu empresa
3. Puedes descargar los documentos adjuntos si estan disponibles

---

### 3.2 Admin de empresa

Ademas de todo lo que puede hacer un usuario, el admin de empresa tiene acceso a:

#### Gestionar usuarios

1. Ve a **Administracion > Personal** en el menu lateral
2. Puedes **crear**, **editar** y **desactivar** usuarios de tu empresa
3. Al crear un usuario, se le asigna un rol y se le envia un email de bienvenida con credenciales temporales

#### Gestionar equipos (activos)

1. Ve a **Equipos** en el menu lateral
2. Registra equipos informaticos con su ficha tecnica (marca, modelo, serie, categoria, clasificacion)
3. **Asignar** equipos a usuarios
4. **Transferir** equipos entre usuarios
5. **Dar de baja** equipos (genera automaticamente un registro F13)

#### Checklists de seguridad

1. Ve a **Checklists > Plantillas** para crear plantillas de verificacion
2. Ve a **Checklists > Ejecutar** para aplicar una plantilla sobre un equipo
3. Consulta el historial de ejecuciones por equipo

#### Capacitaciones

1. Ve a **Capacitaciones** en el menu lateral
2. Crea capacitaciones con tema, descripcion, fecha, instructor y duracion
3. El sistema genera automaticamente la lista de asistencia con todos los usuarios activos
4. Gestiona la asistencia desde la vista de detalle de cada capacitacion

#### Politicas

1. Ve a **Seguridad > Politicas** para crear o editar politicas
2. Marca una politica como **obligatoria** para que los usuarios deban aceptarla al iniciar sesion
3. Sube documentos adjuntos y gestiona versiones
4. Para politicas NDA: activa el toggle NDA, configura vigencia en meses y usa placeholders en el contenido

#### Panel de cumplimiento

1. Ve a **Seguridad > Cumplimiento**
2. Consulta el **% de cumplimiento general** y la cantidad de usuarios pendientes
3. Selecciona una politica para ver el detalle por usuario (aceptada / pendiente / vencida)
4. Envia **recordatorios por email** a usuarios pendientes (individual o masivo)

#### Reportes

- **Reporte de incidencias**: Ve a Reportes > Reporte Incidencias para generar PDF mensual
- **Reporte de capacitaciones**: Ve a Reportes > Reporte Capacitaciones para PDF con lista de asistentes
- **Inventario de soportes (F07)**: Ve a Reportes > Inventario Soportes
- **Movimientos de soportes (F08)**: Ve a Reportes > Movimientos Soportes
- **Destruccion de activos (F13)**: Ve a Reportes > Destruccion Activos
- **Exportar a Excel**: Desde la lista de registros, usa el boton de exportacion

#### Personalizacion del portal

Tu super administrador puede personalizar los colores y logo de tu portal desde la configuracion de empresa.

---

### 3.3 Agente helpdesk

#### Panel de agente

1. Ve a **Soporte > Panel de Agente** en el menu lateral
2. Veras tres columnas: lista de tickets, detalle del ticket seleccionado, y metricas
3. Usa los **filtros** para buscar por estado, prioridad, agente o empresa

#### Gestionar un ticket

1. Selecciona un ticket de la lista
2. **Asignate** el ticket o asignalo a otro agente
3. **Responde** al usuario con mensajes publicos
4. Agrega **notas internas** (solo visibles para agentes) activando el toggle
5. **Cambia el estado** segun avance: En revision, Esperando usuario, Resuelto, Cerrado
6. Adjunta archivos si es necesario (max 5MB, 3 archivos por mensaje)

#### Crear registro desde ticket resuelto

Desde el panel de agente, puedes crear un registro de formato directamente vinculado a un ticket resuelto para documentar el incidente.

#### Reabrir un ticket

Si un ticket resuelto necesita reabrir (dentro de 7 dias), haz clic en **Reabrir ticket** e indica el motivo. El agente asignado sera notificado por email.

---

### 3.4 Super administrador

Ademas de todas las funciones del admin de empresa, el super admin puede:

#### Gestionar empresas

1. Ve a **Administracion > Empresas**
2. Crea, edita o desactiva empresas
3. Haz clic en una empresa para ver su **detalle con metricas**: registros del mes, tickets abiertos, usuarios activos, actividad mensual
4. Cada empresa tiene su **lista de usuarios** y **registros recientes** en tabs

#### Registrar nueva empresa

1. Ve a la pagina de **Registrar empresa** (acceso desde menu o topbar)
2. Completa los datos de la empresa (RUC, razon social, etc.)
3. Crea el usuario administrador de la empresa
4. El sistema genera automaticamente la empresa con su admin

#### Cambiar entre empresas

Usa el **selector de empresa** en la barra superior para cambiar rapidamente entre empresas y ver sus datos.

#### Plantillas de email

1. Ve a **Administracion > Plantillas de Email**
2. Edita las plantillas del sistema (bienvenida, notificacion de ticket, recordatorio de politica, etc.)
3. Usa las **variables** disponibles en cada plantilla (ej: `{nombre}`, `{numero_ticket}`)

#### Usuarios de todas las empresas

Como super admin puedes ver y gestionar usuarios de cualquier empresa desde **Administracion > Personal**.

---

### 3.5 Solo lectura

Los usuarios con rol solo lectura pueden:
- Ver el dashboard
- Consultar registros existentes (sin crear ni editar)
- Ver reportes
- Ver wiki de politicas

No pueden crear registros, tickets, ni modificar datos.

---

## 4. Modulos del sistema

### Registros de seguridad
Sistema de 13 formatos basados en las politicas PSC del Estudio Palacios Abogados. Cada formato tiene campos especificos y genera un numero unico automatico.

### Helpdesk
Sistema de tickets para soporte tecnico. Los usuarios crean tickets y los agentes los gestionan con un hilo de conversacion, notas internas y adjuntos.

### Equipos (Activos)
Inventario de equipos informaticos con asignacion a usuarios, transferencias, bajas y vinculacion con tickets.

### Politicas y NDA
Gestion de politicas de seguridad con aceptacion obligatoria, firma digital, versionado y panel de cumplimiento.

### Capacitaciones
Modulo para registrar capacitaciones de ciberseguridad con control de asistencia y reportes PDF.

### Checklists
Plantillas de verificacion que se ejecutan sobre equipos para auditar su estado de seguridad.

### Reportes
Generacion de reportes PDF y exportacion Excel para incidencias, capacitaciones, inventario, movimientos y destruccion de activos.

---

## 5. Preguntas frecuentes (FAQ)

**P: Olvide mi contrasena, que hago?**
R: En la pantalla de login, haz clic en "Olvidaste tu contrasena?" e ingresa tu email. Recibiras un enlace para restablecerla.

**P: No puedo ver ciertos menus en el panel, por que?**
R: Los menus visibles dependen de tu rol. Contacta a tu administrador si necesitas acceso a funcionalidades adicionales.

**P: Como se que formato usar para registrar algo?**
R: Al crear un registro, el catalogo visual te muestra cada formato con su descripcion. Si tienes dudas, consulta a tu admin o abre un ticket de soporte.

**P: Puedo editar un registro ya guardado?**
R: Si, siempre que tengas permisos de edicion. Los registros eliminados van a la papelera (soft delete) y pueden ser restaurados por un admin.

**P: Puedo adjuntar archivos a un ticket?**
R: Si, al responder un ticket puedes adjuntar hasta 3 archivos de max 5MB cada uno (imagenes, PDF, Word, Excel).

**P: Me pide aceptar politicas cada vez que entro, es normal?**
R: Solo aparece cuando hay politicas nuevas o actualizadas que no has aceptado, o si tu aceptacion de NDA ha expirado.

**P: Mi ticket fue resuelto pero el problema sigue, que hago?**
R: Puedes reabrir el ticket dentro de los 7 dias posteriores a la resolucion usando el boton "Reabrir ticket".

**P: Como exporto registros a Excel?**
R: Desde la lista de registros, usa el boton de exportacion en la barra de herramientas. Se descargara un archivo Excel con los registros filtrados.

**P: Como veo el historial de capacitaciones?**
R: Ve a Capacitaciones > Mis capacitaciones para ver todas las capacitaciones a las que fuiste convocado y tu estado de asistencia.

---

## 6. Solucion de problemas

### La pagina no carga o muestra error 500
- Intenta recargar la pagina (F5 o Ctrl+R)
- Limpia la cache del navegador
- Si persiste, contacta a tu administrador

### No recibo emails del sistema
- Revisa tu carpeta de spam/correo no deseado
- Verifica que tu email este correctamente registrado en tu perfil
- Contacta a tu admin para verificar la configuracion de correo

### No puedo subir un archivo
- Verifica que el archivo sea de un tipo permitido (imagen, PDF, Word, Excel)
- Verifica que no exceda 5MB
- Intenta con otro navegador si el problema persiste

### El panel se ve diferente a lo esperado
- Tu empresa puede tener colores personalizados configurados por el admin
- Intenta limpiar cache del navegador (Ctrl+Shift+Delete)

### No puedo firmar una politica/NDA
- Asegurate de haber completado tus datos personales primero (DNI, direccion, telefono, puesto)
- Usa un navegador moderno (Chrome, Firefox, Edge) con JavaScript habilitado
- En dispositivos moviles, firma con el dedo directamente sobre el canvas

---

> **Necesitas mas ayuda?** Abre un ticket de soporte desde el panel o contacta a tu administrador de empresa.
