<?php

namespace App\Helpers;

/**
 * Validator — Validación y sanitización de entradas.
 *
 * Reglas:
 * - htmlspecialchars() en toda salida HTML
 * - Validar fechas, castear numéricos
 * - Validar selects contra whitelist en servidor
 */
class Validator
{
    /** @var array<string, string> Errores acumulados */
    private array $errors = [];

    /** @var array<string, mixed> Datos validados y limpios */
    private array $clean = [];

    /** @var array<string, mixed> Datos de entrada */
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Validar campo string requerido.
     */
    public function required(string $field, string $label): self
    {
        $value = trim((string) ($this->data[$field] ?? ''));
        if ($value === '') {
            $this->errors[$field] = "{$label} es obligatorio.";
        } else {
            $this->clean[$field] = $value;
        }
        return $this;
    }

    /**
     * Validar campo string opcional (si está presente, se limpia).
     */
    public function optional(string $field): self
    {
        $value = trim((string) ($this->data[$field] ?? ''));
        $this->clean[$field] = $value !== '' ? $value : null;
        return $this;
    }

    /**
     * Validar email.
     */
    public function email(string $field, string $label): self
    {
        $value = trim((string) ($this->data[$field] ?? ''));
        if ($value === '') {
            $this->errors[$field] = "{$label} es obligatorio.";
        } elseif (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "{$label} no es un email válido.";
        } else {
            $this->clean[$field] = strtolower($value);
        }
        return $this;
    }

    /**
     * Validar entero positivo.
     */
    public function integer(string $field, string $label, bool $required = true): self
    {
        $value = $this->data[$field] ?? null;
        if ($value === null || $value === '') {
            if ($required) {
                $this->errors[$field] = "{$label} es obligatorio.";
            } else {
                $this->clean[$field] = null;
            }
        } else {
            $intVal = filter_var($value, FILTER_VALIDATE_INT);
            if ($intVal === false) {
                $this->errors[$field] = "{$label} debe ser un número entero.";
            } else {
                $this->clean[$field] = $intVal;
            }
        }
        return $this;
    }

    /**
     * Validar fecha (formato Y-m-d).
     */
    public function date(string $field, string $label, bool $required = true): self
    {
        $value = trim((string) ($this->data[$field] ?? ''));
        if ($value === '') {
            if ($required) {
                $this->errors[$field] = "{$label} es obligatorio.";
            } else {
                $this->clean[$field] = null;
            }
        } else {
            $dt = \DateTimeImmutable::createFromFormat('Y-m-d', $value);
            if (!$dt || $dt->format('Y-m-d') !== $value) {
                $this->errors[$field] = "{$label} no tiene un formato de fecha válido (AAAA-MM-DD).";
            } else {
                $this->clean[$field] = $value;
            }
        }
        return $this;
    }

    /**
     * Validar que el valor está en una lista permitida (whitelist).
     *
     * @param string[] $allowed Valores válidos
     */
    public function inList(string $field, string $label, array $allowed, bool $required = true): self
    {
        $value = trim((string) ($this->data[$field] ?? ''));
        if ($value === '') {
            if ($required) {
                $this->errors[$field] = "{$label} es obligatorio.";
            } else {
                $this->clean[$field] = null;
            }
        } elseif (!in_array($value, $allowed, true)) {
            $this->errors[$field] = "{$label} contiene un valor no permitido.";
        } else {
            $this->clean[$field] = $value;
        }
        return $this;
    }

    /**
     * Validar longitud mínima y máxima.
     */
    public function length(string $field, string $label, int $min, int $max): self
    {
        $value = $this->clean[$field] ?? trim((string) ($this->data[$field] ?? ''));
        $len = mb_strlen($value);
        if ($len < $min || $len > $max) {
            $this->errors[$field] = "{$label} debe tener entre {$min} y {$max} caracteres.";
        }
        return $this;
    }

    /**
     * Verificar si la validación pasó sin errores.
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Obtener errores de validación.
     *
     * @return array<string, string>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Obtener datos validados y limpios.
     *
     * @return array<string, mixed>
     */
    public function validated(): array
    {
        return $this->clean;
    }

    // ============================================================
    // SANITIZACIÓN DE SALIDA (métodos estáticos)
    // ============================================================

    /**
     * Escapar para salida HTML.
     * Usar SIEMPRE al renderizar variables en vistas.
     */
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
