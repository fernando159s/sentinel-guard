<?php

namespace App\Helpers;

use PDO;
use PDOStatement;

/**
 * Database — Singleton PDO para acceso a datos.
 *
 * Regla ABSOLUTA: 100% prepared statements.
 * NUNCA concatenar variables en SQL.
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ]);
    }

    /**
     * Obtener instancia Singleton.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Ejecutar SELECT y retornar todas las filas.
     *
     * @param string $sql    Query con placeholders ?
     * @param array  $params Parámetros a bindear
     * @return array<int, array<string, mixed>>
     */
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Ejecutar SELECT y retornar una sola fila (o null).
     *
     * @param string $sql    Query con placeholders ?
     * @param array  $params Parámetros a bindear
     * @return array<string, mixed>|null
     */
    public function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /**
     * Ejecutar INSERT, UPDATE, DELETE.
     * Retorna el número de filas afectadas.
     *
     * @param string $sql    Query con placeholders ?
     * @param array  $params Parámetros a bindear
     * @return int Filas afectadas
     */
    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Retornar el último ID insertado.
     */
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Iniciar una transacción.
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Confirmar transacción.
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Revertir transacción.
     */
    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * Obtener la instancia PDO directa (solo para casos excepcionales).
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    // Prevenir clonación y deserialización
    private function __clone() {}
    public function __wakeup()
    {
        throw new \RuntimeException('Cannot unserialize Database singleton');
    }
}
