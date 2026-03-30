<?php

namespace App\Helpers;

/**
 * AuditLogger — Log inmutable de auditoría.
 *
 * Registra: login/logout, crear/editar/desactivar registro,
 * cambios de estado ticket, gestión de usuarios.
 *
 * NUNCA se elimina un registro de logs_auditoria.
 */
class AuditLogger
{
    /**
     * Registrar una acción en el log de auditoría.
     *
     * @param string     $accion          Ej: 'login', 'crear_registro', 'cerrar_ticket'
     * @param string|null $entidad         Ej: 'registros', 'tickets', 'usuarios'
     * @param int|null    $entidadId       ID del registro afectado
     * @param array|null  $datosAnteriores Estado antes del cambio
     * @param array|null  $datosNuevos     Estado después del cambio
     */
    public static function log(
        string $accion,
        ?string $entidad = null,
        ?int $entidadId = null,
        ?array $datosAnteriores = null,
        ?array $datosNuevos = null
    ): void {
        $db = Database::getInstance();

        $db->execute(
            "INSERT INTO logs_auditoria
                (usuario_id, empresa_id, accion, entidad, entidad_id, datos_anteriores, datos_nuevos, ip, user_agent)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                Auth::isLoggedIn() ? Auth::userId() : null,
                Auth::isLoggedIn() ? (Auth::empresaId() ?: null) : null,
                $accion,
                $entidad,
                $entidadId,
                $datosAnteriores !== null ? json_encode($datosAnteriores, JSON_UNESCAPED_UNICODE) : null,
                $datosNuevos !== null ? json_encode($datosNuevos, JSON_UNESCAPED_UNICODE) : null,
                $_SERVER['REMOTE_ADDR'] ?? null,
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 300),
            ]
        );
    }

    /**
     * Registrar login exitoso.
     */
    public static function logLogin(int $userId): void
    {
        $db = Database::getInstance();
        $db->execute(
            "INSERT INTO logs_auditoria
                (usuario_id, accion, ip, user_agent)
             VALUES (?, 'login', ?, ?)",
            [
                $userId,
                $_SERVER['REMOTE_ADDR'] ?? null,
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 300),
            ]
        );
    }

    /**
     * Registrar intento de login fallido.
     */
    public static function logLoginFailed(string $email): void
    {
        $db = Database::getInstance();
        $db->execute(
            "INSERT INTO logs_auditoria
                (accion, entidad, datos_nuevos, ip, user_agent)
             VALUES ('login_fallido', 'usuarios', ?, ?, ?)",
            [
                json_encode(['email' => $email]),
                $_SERVER['REMOTE_ADDR'] ?? null,
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 300),
            ]
        );
    }

    /**
     * Registrar logout.
     */
    public static function logLogout(): void
    {
        self::log('logout');
    }

    /**
     * Consultar logs con filtros (para vista de Super Admin).
     *
     * @param array{
     *   empresa_id?: int,
     *   usuario_id?: int,
     *   accion?: string,
     *   entidad?: string,
     *   fecha_desde?: string,
     *   fecha_hasta?: string,
     * } $filters
     * @param int $limit
     * @param int $offset
     * @return array{logs: array, total: int}
     */
    public static function search(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $db = Database::getInstance();
        $where = [];
        $params = [];

        if (!empty($filters['empresa_id'])) {
            $where[] = 'l.empresa_id = ?';
            $params[] = $filters['empresa_id'];
        }

        if (!empty($filters['usuario_id'])) {
            $where[] = 'l.usuario_id = ?';
            $params[] = $filters['usuario_id'];
        }

        if (!empty($filters['accion'])) {
            $where[] = 'l.accion = ?';
            $params[] = $filters['accion'];
        }

        if (!empty($filters['entidad'])) {
            $where[] = 'l.entidad = ?';
            $params[] = $filters['entidad'];
        }

        if (!empty($filters['fecha_desde'])) {
            $where[] = 'l.timestamp >= ?';
            $params[] = $filters['fecha_desde'] . ' 00:00:00';
        }

        if (!empty($filters['fecha_hasta'])) {
            $where[] = 'l.timestamp <= ?';
            $params[] = $filters['fecha_hasta'] . ' 23:59:59';
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        // Total de resultados
        $countRow = $db->queryOne(
            "SELECT COUNT(*) as total FROM logs_auditoria l {$whereClause}",
            $params
        );
        $total = (int) ($countRow['total'] ?? 0);

        // Resultados paginados
        $paramsWithLimit = array_merge($params, [$limit, $offset]);
        $logs = $db->query(
            "SELECT l.*, u.nombre AS usuario_nombre, u.email AS usuario_email
             FROM logs_auditoria l
             LEFT JOIN usuarios u ON l.usuario_id = u.id
             {$whereClause}
             ORDER BY l.timestamp DESC
             LIMIT ? OFFSET ?",
            $paramsWithLimit
        );

        return ['logs' => $logs, 'total' => $total];
    }
}
