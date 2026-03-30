-- ============================================================
-- SecuriForm — Datos iniciales de prueba (desarrollo)
-- Ejecutar DESPUÉS de install.sql
-- Ejecutar: mysql -u usuario -p nombre_bd < seed.sql
-- ============================================================
-- NOTA: install.sql ya inserta la empresa demo y el Super Admin.
-- Este script agrega datos adicionales para pruebas en desarrollo.
-- ============================================================

SET NAMES utf8mb4;

-- ------------------------------------------------------------
-- Empresa adicional de prueba
-- ------------------------------------------------------------
INSERT IGNORE INTO `empresas` (`id`, `ruc`, `razon_social`, `direccion`, `email`, `telefono`, `estado`) VALUES
(2, '20509876543', 'Consultora TechSoft S.A.C.', 'Jr. Tecnología 456, Lima', 'admin@techsoft.pe', '+51 1 987-6543', 'activo');

-- ------------------------------------------------------------
-- Usuarios de prueba
-- Contraseña para todos: Test2024!
-- Hash: password_hash('Test2024!', PASSWORD_BCRYPT, ['cost' => 12])
-- ------------------------------------------------------------
INSERT IGNORE INTO `usuarios` (`id`, `empresa_id`, `nombre`, `email`, `password_hash`, `rol`, `estado`) VALUES
-- Admin de empresa demo
(2, 1, 'Carlos Palacios', 'carlos@palacios.pe',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'admin_empresa', 'activo'),

-- Usuario regular de empresa demo
(3, 1, 'María García', 'maria@palacios.pe',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'usuario', 'activo'),

-- Solo lectura de empresa demo
(4, 1, 'Pedro López', 'pedro@palacios.pe',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'solo_lectura', 'activo'),

-- Agente helpdesk (sin empresa)
(5, NULL, 'Ana Soporte', 'ana@securiform.local',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'agente_helpdesk', 'activo'),

-- Admin empresa 2
(6, 2, 'Luis Torres', 'luis@techsoft.pe',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'admin_empresa', 'activo'),

-- Usuario empresa 2
(7, 2, 'Rosa Mendoza', 'rosa@techsoft.pe',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'usuario', 'activo');
