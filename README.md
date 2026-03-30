# SecuriForm

Sistema de gestión de formatos de seguridad multiempresa con helpdesk centralizado.

---

## Requisitos del servidor

| Requisito | Mínimo | Recomendado |
|-----------|--------|-------------|
| PHP | 8.1 | 8.2+ |
| MySQL | 5.7 | 8.0 / MariaDB 10.6 |
| Extensiones PHP | pdo, pdo_mysql, mbstring, json, fileinfo, gd | + openssl, curl |
| Espacio en disco | 50 MB | 500 MB (para uploads) |
| Memoria PHP | 128 MB | 256 MB |

---

## Instalación

### Paso 1 — Subir archivos

```bash
# Opción A: FTP/SFTP al hosting
# Subir el contenido de la carpeta /public/ al public_html del hosting
# Subir el resto de carpetas FUERA del public_html

# Estructura recomendada en el servidor:
/home/tuusuario/
├── public_html/        ← aquí va el contenido de /public/
│   ├── index.php
│   ├── .htaccess
│   ├── css/
│   └── js/
├── securiform/         ← aquí va el resto (app, config, vendor, etc.)
│   ├── app/
│   ├── config/
│   ├── vendor/
│   └── uploads/
```

### Paso 2 — Configurar la base de datos

1. Crear una base de datos MySQL en el panel de hosting (cPanel → MySQL Databases)
2. Crear un usuario MySQL y asignarlo a la base de datos con todos los permisos
3. Importar el schema:

```bash
# Desde terminal SSH:
mysql -u USUARIO_BD -p NOMBRE_BD < install.sql
mysql -u USUARIO_BD -p NOMBRE_BD < seed.sql

# O desde cPanel → phpMyAdmin → Importar
```

### Paso 3 — Configurar variables de entorno

Copiar `.env.example` a `.env` y editar:

```bash
cp .env.example .env
nano .env   # o editar con cualquier editor
```

Completar todos los campos marcados con `← CAMBIAR`.

### Paso 4 — Ajustar la ruta de la aplicación

En `public/index.php`, verificar que la ruta al directorio raíz sea correcta:

```php
// Si securiform/ está en /home/tuusuario/securiform/
define('APP_ROOT', '/home/tuusuario/securiform');
```

### Paso 5 — Verificar permisos

```bash
# El directorio de uploads debe ser escribible por el servidor web
chmod 755 uploads/
chmod 755 uploads/logos/
chmod 755 uploads/tickets/
```

### Paso 6 — Primer acceso

Abrir el navegador en la URL del hosting. Las credenciales iniciales son:

```
Email:      admin@securiform.local
Contraseña: Admin2024!
Empresa:    (cualquiera, es super admin)
```

**Cambiar la contraseña inmediatamente después del primer acceso.**

---

## Instalación de dependencias PHP

### Con Composer (recomendado)

```bash
composer install --no-dev --optimize-autoloader
```

### Sin Composer (hosting sin acceso SSH)

Descargar manualmente e incluir:

1. **PHPMailer** → https://github.com/PHPMailer/PHPMailer/releases
   - Copiar `src/` a `vendor/phpmailer/phpmailer/src/`

2. **mPDF** → https://github.com/mpdf/mpdf/releases
   - Copiar a `vendor/mpdf/mpdf/`

3. **PhpSpreadsheet** → https://github.com/PHPOffice/PhpSpreadsheet
   - Copiar a `vendor/phpoffice/phpspreadsheet/`

---

## Configuración de email

En `.env`, configurar el servidor SMTP:

```ini
# Con Gmail (requiere contraseña de aplicación, no la contraseña normal)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tuemail@gmail.com
MAIL_PASSWORD=xxxx-xxxx-xxxx-xxxx  # Contraseña de aplicación de Google
MAIL_ENCRYPTION=tls

# Con servidor del hosting (ej: cPanel)
MAIL_HOST=mail.tudominio.com
MAIL_PORT=587
MAIL_USERNAME=noreply@tudominio.com
MAIL_PASSWORD=tu_contraseña_email
MAIL_ENCRYPTION=tls
```

---

## Estructura del proyecto

```
securiform/
├── CLAUDE.md          ← contexto para Claude Code (no subir a producción)
├── README.md          ← este archivo
├── .env               ← variables de entorno (NO commitear a git)
├── .env.example       ← plantilla de variables (sí commitear)
├── .gitignore
├── composer.json
├── install.sql        ← script de creación de tablas
├── seed.sql           ← datos iniciales (super admin)
│
├── config/
│   └── config.php     ← carga .env y define constantes
│
├── app/
│   ├── controllers/   ← lógica de negocio
│   ├── models/        ← acceso a datos
│   ├── views/         ← templates PHP + layouts
│   └── helpers/       ← Database, Auth, Router, Mailer, etc.
│
├── public/            ← WEBROOT (apuntar el hosting aquí)
│   ├── index.php
│   ├── .htaccess
│   ├── css/
│   └── js/
│
├── uploads/           ← archivos subidos (NO en webroot)
│   ├── logos/
│   └── tickets/
│
├── vendor/            ← dependencias composer
│
└── scripts/           ← herramientas de desarrollo
    ├── linear_get_tasks.sh
    ├── linear_update_status.sh
    └── import_backlog.py
```

---

## Roles de usuario

| Rol | Descripción |
|-----|-------------|
| `super_admin` | Administrador global: gestiona empresas, usuarios de cualquier empresa, ve logs globales |
| `admin_empresa` | Admin de una empresa: gestiona usuarios de su empresa, ve todos los registros de su empresa |
| `usuario` | Usuario estándar: crea y consulta registros de su empresa |
| `agente_helpdesk` | Equipo de soporte: atiende tickets de todas las empresas |
| `solo_lectura` | Auditor: solo puede ver registros, no crear ni editar |

---

## Los 13 formatos de seguridad

| Código | Formato | Política |
|--------|---------|---------|
| F01 | Registro de auditorías realizadas | PSC000001 |
| F02 | Registro de banco de datos inscritos | PSC000001 |
| F03 | Prestadores con acceso a datos personales | PSC000001 / PSC000002 |
| F04 | Registro para datos sensibles | PSC000001 / PSC000-46 |
| F05 | Personal autorizado al banco de datos | PSC000001 |
| F06 | Acceso de soporte no autorizado | PSC000001 |
| F07 | Inventario de soportes | PSC000003 / PSC000004 |
| F08 | Ingreso y salida de soportes | PSC000003 |
| F09 | Notificación de incidencias | PSC000001 / PSC000-25 |
| F10 | Resolución de incidencias | PSC000-25 |
| F11 | Recuperación de datos | PSC000001 / PSC000-25 |
| F12 | Copias de seguridad | PSC000003 / PSC000-15 |
| F13 | Destrucción de activos | PSC000003 / PSC000004 |

---

## Checklist de seguridad para producción

Antes de publicar, verificar:

- [ ] `.env` tiene credenciales reales y no está accesible desde el navegador
- [ ] `APP_ENV=production` en el `.env`
- [ ] `APP_DEBUG=false` en el `.env`
- [ ] El directorio `uploads/` no es accesible directamente desde el navegador
- [ ] El directorio `app/` no es accesible desde el navegador
- [ ] HTTPS activado en el hosting (certificado SSL)
- [ ] La contraseña del super_admin inicial fue cambiada
- [ ] Se probó que un usuario de empresa A no puede ver datos de empresa B
- [ ] Los logs de PHP no muestran errores en producción

---

## Actualización de versiones

Para actualizar a una nueva versión:

1. Hacer backup de la BD: `mysqldump -u usuario -p nombre_bd > backup_FECHA.sql`
2. Hacer backup de `uploads/`
3. Subir los nuevos archivos (no sobreescribir `.env`)
4. Si hay migraciones, ejecutar: `mysql -u usuario -p nombre_bd < migrations/vX.X.sql`
5. Verificar que todo funciona antes de confirmar la actualización

---

## Soporte y desarrollo

- Documentación técnica: `docs/`
- Backlog de tareas: `docs/02_BACKLOG_SecuriForm.md`
- Tareas en Linear: ver `scripts/linear_get_tasks.sh`
- Contexto para Claude Code: `CLAUDE.md`
