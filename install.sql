-- ============================================================
-- SecuriForm — Script de instalación de base de datos
-- Versión: 1.0 | Fecha: 2026-03-29
-- ============================================================
-- Ejecutar: mysql -u usuario -p nombre_bd < install.sql
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- ------------------------------------------------------------
-- TABLA: empresas
-- Tenants del sistema. Cada empresa tiene sus datos aislados.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `empresas` (
  `id`               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `ruc`              VARCHAR(11)     NOT NULL COMMENT 'RUC peruano: 11 dígitos',
  `razon_social`     VARCHAR(200)    NOT NULL,
  `logo_path`        VARCHAR(500)    DEFAULT NULL COMMENT 'Ruta relativa al logo en uploads/logos/',
  `direccion`        VARCHAR(300)    DEFAULT NULL,
  `email`            VARCHAR(150)    DEFAULT NULL,
  `telefono`         VARCHAR(30)     DEFAULT NULL,
  `estado`           ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  `fecha_creacion`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ruc` (`ruc`),
  KEY `idx_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------
-- TABLA: usuarios
-- Todos los usuarios de todas las empresas + agentes + super admin.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id`                  INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `empresa_id`          INT UNSIGNED  DEFAULT NULL COMMENT 'NULL solo para super_admin y agentes',
  `nombre`              VARCHAR(150)  NOT NULL,
  `email`               VARCHAR(150)  NOT NULL,
  `password_hash`       VARCHAR(255)  NOT NULL COMMENT 'bcrypt hash',
  `rol`                 ENUM('super_admin','admin_empresa','usuario','agente_helpdesk','solo_lectura') NOT NULL DEFAULT 'usuario',
  `estado`              ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  `intentos_fallidos`   TINYINT       NOT NULL DEFAULT 0,
  `bloqueado_hasta`     DATETIME      DEFAULT NULL,
  `notif_tickets`       TINYINT(1)    NOT NULL DEFAULT 1 COMMENT 'Recibe emails de tickets',
  `notif_incidencias`   TINYINT(1)    NOT NULL DEFAULT 1 COMMENT 'Recibe emails de incidencias',
  `ultimo_acceso`       DATETIME      DEFAULT NULL,
  `fecha_creacion`      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_email` (`email`),
  KEY `idx_empresa_id` (`empresa_id`),
  KEY `idx_rol` (`rol`),
  KEY `idx_estado` (`estado`),
  CONSTRAINT `fk_usuarios_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------
-- TABLA: sesiones
-- Control de sesiones activas (complementa sesiones PHP nativas).
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sesiones` (
  `id`           BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `usuario_id`   INT UNSIGNED     NOT NULL,
  `ip`           VARCHAR(45)      NOT NULL COMMENT 'Soporta IPv6',
  `user_agent`   VARCHAR(300)     DEFAULT NULL,
  `creado_en`    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expira_en`    DATETIME         NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_expira_en` (`expira_en`),
  CONSTRAINT `fk_sesiones_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------
-- TABLA: tokens_recuperacion
-- Tokens de un solo uso para recuperación de contraseña.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tokens_recuperacion` (
  `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `usuario_id`   INT UNSIGNED  NOT NULL,
  `token`        VARCHAR(100)  NOT NULL COMMENT 'Token SHA-256 aleatorio',
  `usado`        TINYINT(1)    NOT NULL DEFAULT 0,
  `expira_en`    DATETIME      NOT NULL COMMENT 'TTL: 1 hora',
  `creado_en`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_token` (`token`),
  KEY `idx_usuario_id` (`usuario_id`),
  CONSTRAINT `fk_tokens_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------
-- TABLA: registros
-- Los 13 formatos de seguridad en una sola tabla.
-- El campo `datos` (JSON) almacena los campos específicos de cada formato.
-- Esto permite agregar nuevos formatos sin migraciones de BD.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `registros` (
  `id`                        BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `empresa_id`                INT UNSIGNED     NOT NULL,
  `tipo_formato`              ENUM('F01','F02','F03','F04','F05','F06','F07','F08','F09','F10','F11','F12','F13') NOT NULL,
  `numero_registro`           VARCHAR(20)      NOT NULL COMMENT 'Ej: INC-2026-004',
  `datos`                     JSON             NOT NULL COMMENT 'Todos los campos del formulario como JSON',
  `estado`                    ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  `creado_por`                INT UNSIGNED     NOT NULL,
  `fecha_creacion`            DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modificado_por`            INT UNSIGNED     DEFAULT NULL,
  `fecha_modificacion`        DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_numero_registro` (`empresa_id`, `numero_registro`),
  KEY `idx_empresa_tipo` (`empresa_id`, `tipo_formato`),
  KEY `idx_empresa_fecha` (`empresa_id`, `fecha_creacion`),
  KEY `idx_creado_por` (`creado_por`),
  CONSTRAINT `fk_registros_empresa`  FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `fk_registros_creador`  FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------
-- TABLA: secuencias_registro
-- Control de numeración automática por empresa + tipo + año.
-- Garantiza unicidad sin race conditions.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `secuencias_registro` (
  `empresa_id`    INT UNSIGNED  NOT NULL,
  `tipo_formato`  VARCHAR(5)    NOT NULL,
  `anio`          SMALLINT      NOT NULL,
  `ultimo_seq`    INT           NOT NULL DEFAULT 0,
  PRIMARY KEY (`empresa_id`, `tipo_formato`, `anio`),
  CONSTRAINT `fk_seq_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------
-- TABLA: tickets
-- Tickets del helpdesk centralizado.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tickets` (
  `id`                    INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `empresa_id`            INT UNSIGNED  NOT NULL COMMENT 'Empresa del usuario que abrió el ticket',
  `numero_ticket`         VARCHAR(15)   NOT NULL COMMENT 'Ej: TK-0042',
  `creado_por`            INT UNSIGNED  NOT NULL,
  `asignado_a`            INT UNSIGNED  DEFAULT NULL COMMENT 'Agente asignado',
  `asunto`                VARCHAR(300)  NOT NULL,
  `descripcion`           TEXT          NOT NULL,
  `categoria`             ENUM('consulta','problema_tecnico','error_registro','solicitud_acceso','otro') NOT NULL DEFAULT 'consulta',
  `prioridad`             ENUM('baja','media','alta','urgente') NOT NULL DEFAULT 'media',
  `estado`                ENUM('nuevo','en_revision','esperando_usuario','resuelto','cerrado') NOT NULL DEFAULT 'nuevo',
  `fecha_creacion`        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_ultima_actividad` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `fecha_cierre`          DATETIME      DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_numero_ticket` (`numero_ticket`),
  KEY `idx_empresa_id` (`empresa_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_asignado_a` (`asignado_a`),
  KEY `idx_prioridad` (`prioridad`),
  KEY `idx_fecha_actividad` (`fecha_ultima_actividad`),
  CONSTRAINT `fk_tickets_empresa`    FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `fk_tickets_creador`    FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_tickets_agente`     FOREIGN KEY (`asignado_a`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------
-- TABLA: ticket_mensajes
-- Hilo de conversación de cada ticket.
-- tipo='publico' → visible para usuario y agente
-- tipo='interno' → solo visible para agentes
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ticket_mensajes` (
  `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `ticket_id`    INT UNSIGNED  NOT NULL,
  `autor_id`     INT UNSIGNED  NOT NULL,
  `tipo`         ENUM('publico','interno') NOT NULL DEFAULT 'publico',
  `contenido`    TEXT          NOT NULL,
  `fecha_creacion` DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ticket_id` (`ticket_id`),
  KEY `idx_autor_id` (`autor_id`),
  CONSTRAINT `fk_mensajes_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_mensajes_autor`  FOREIGN KEY (`autor_id`)  REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------
-- TABLA: ticket_adjuntos
-- Archivos adjuntos en mensajes de tickets.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ticket_adjuntos` (
  `id`                INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `mensaje_id`        INT UNSIGNED  NOT NULL,
  `nombre_original`   VARCHAR(255)  NOT NULL COMMENT 'Nombre como lo subió el usuario',
  `nombre_almacenado` VARCHAR(255)  NOT NULL COMMENT 'UUID + extensión, nombre real en disco',
  `tipo_mime`         VARCHAR(100)  NOT NULL,
  `tamano`            INT UNSIGNED  NOT NULL COMMENT 'Tamaño en bytes',
  `fecha_subida`      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_mensaje_id` (`mensaje_id`),
  CONSTRAINT `fk_adjuntos_mensaje` FOREIGN KEY (`mensaje_id`) REFERENCES `ticket_mensajes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------
-- TABLA: secuencias_ticket
-- Numeración automática de tickets (global, no por empresa).
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `secuencias_ticket` (
  `ultimo_seq`  INT  NOT NULL DEFAULT 0,
  `id`          TINYINT NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `secuencias_ticket` (`id`, `ultimo_seq`) VALUES (1, 0);


-- ------------------------------------------------------------
-- TABLA: notificaciones_email
-- Cola de emails pendientes de envío.
-- El sistema encola aquí y reintenta si falla el SMTP.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notificaciones_email` (
  `id`               BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `destinatario`     VARCHAR(150)     NOT NULL,
  `nombre_destino`   VARCHAR(150)     DEFAULT NULL,
  `asunto`           VARCHAR(300)     NOT NULL,
  `cuerpo_html`      LONGTEXT         NOT NULL,
  `estado`           ENUM('pendiente','enviado','error') NOT NULL DEFAULT 'pendiente',
  `intentos`         TINYINT          NOT NULL DEFAULT 0,
  `error_mensaje`    TEXT             DEFAULT NULL,
  `fecha_programada` DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_envio`      DATETIME         DEFAULT NULL,
  `creado_en`        DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_estado_pendiente` (`estado`, `fecha_programada`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------
-- TABLA: logs_auditoria
-- Log inmutable de todas las acciones del sistema.
-- NUNCA se elimina ningún registro de esta tabla.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `logs_auditoria` (
  `id`               BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `usuario_id`       INT UNSIGNED     DEFAULT NULL COMMENT 'NULL para acciones de sistema',
  `empresa_id`       INT UNSIGNED     DEFAULT NULL,
  `accion`           VARCHAR(100)     NOT NULL COMMENT 'Ej: login, crear_registro, cerrar_ticket',
  `entidad`          VARCHAR(50)      DEFAULT NULL COMMENT 'Ej: registros, tickets, usuarios',
  `entidad_id`       BIGINT UNSIGNED  DEFAULT NULL,
  `datos_anteriores` JSON             DEFAULT NULL COMMENT 'Estado antes del cambio',
  `datos_nuevos`     JSON             DEFAULT NULL COMMENT 'Estado después del cambio',
  `ip`               VARCHAR(45)      DEFAULT NULL,
  `user_agent`       VARCHAR(300)     DEFAULT NULL,
  `timestamp`        DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_empresa_id` (`empresa_id`),
  KEY `idx_accion` (`accion`),
  KEY `idx_timestamp` (`timestamp`),
  KEY `idx_entidad` (`entidad`, `entidad_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- DATOS INICIALES
-- ============================================================

-- Empresa demo (Estudio Palacios Abogados S.A.C.)
INSERT IGNORE INTO `empresas` (`id`, `ruc`, `razon_social`, `direccion`, `email`, `telefono`, `estado`) VALUES
(1, '20601234567', 'Estudio Palacios Abogados S.A.C.', 'Av. Principal 123, Lima', 'admin@palacios.pe', '+51 1 234-5678', 'activo');

-- Super Admin inicial
-- Contraseña: Admin2024!  (cambiar inmediatamente después del primer acceso)
-- Hash generado con: password_hash('Admin2024!', PASSWORD_BCRYPT, ['cost' => 12])
INSERT IGNORE INTO `usuarios` (`id`, `empresa_id`, `nombre`, `email`, `password_hash`, `rol`, `estado`) VALUES
(1, NULL, 'Super Administrador', 'admin@securiform.local',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'super_admin', 'activo');

-- Log inicial
INSERT INTO `logs_auditoria` (`usuario_id`, `accion`, `entidad`, `datos_nuevos`) VALUES
(NULL, 'instalacion', 'sistema', JSON_OBJECT('version', '1.0', 'fecha', NOW()));
