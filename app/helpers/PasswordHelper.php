<?php

namespace App\Helpers;

/**
 * PasswordHelper — Gestión segura de contraseñas.
 *
 * Reglas:
 * - password_hash() con PASSWORD_BCRYPT cost 12
 * - Contraseñas temporales de 12 caracteres
 * - NUNCA almacenar ni loguear contraseñas en texto plano
 */
class PasswordHelper
{
    private const BCRYPT_COST = 12;
    private const TEMP_PASSWORD_LENGTH = 12;

    /**
     * Hashear contraseña con bcrypt.
     */
    public static function hash(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, [
            'cost' => self::BCRYPT_COST,
        ]);
    }

    /**
     * Verificar contraseña contra hash.
     */
    public static function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Verificar si el hash necesita ser re-hasheado
     * (por cambio de cost u algoritmo).
     */
    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, [
            'cost' => self::BCRYPT_COST,
        ]);
    }

    /**
     * Generar contraseña temporal segura.
     * Contiene mayúsculas, minúsculas, números y un carácter especial.
     */
    public static function generateTemporary(): string
    {
        $upper   = 'ABCDEFGHIJKLMNPQRSTUVWXYZ';
        $lower   = 'abcdefghijkmnpqrstuvwxyz';
        $digits  = '23456789';
        $special = '!@#$%&*';

        // Garantizar al menos uno de cada tipo
        $password  = $upper[random_int(0, strlen($upper) - 1)];
        $password .= $lower[random_int(0, strlen($lower) - 1)];
        $password .= $digits[random_int(0, strlen($digits) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];

        // Rellenar hasta la longitud deseada
        $all = $upper . $lower . $digits . $special;
        for ($i = 4; $i < self::TEMP_PASSWORD_LENGTH; $i++) {
            $password .= $all[random_int(0, strlen($all) - 1)];
        }

        // Mezclar los caracteres
        return str_shuffle($password);
    }

    /**
     * Validar que una contraseña cumple requisitos mínimos.
     * Mínimo 8 caracteres, al menos 1 mayúscula, 1 minúscula, 1 número.
     *
     * @return string|null Mensaje de error, o null si es válida
     */
    public static function validate(string $password): ?string
    {
        if (mb_strlen($password) < 8) {
            return 'La contraseña debe tener al menos 8 caracteres.';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return 'La contraseña debe contener al menos una letra mayúscula.';
        }
        if (!preg_match('/[a-z]/', $password)) {
            return 'La contraseña debe contener al menos una letra minúscula.';
        }
        if (!preg_match('/[0-9]/', $password)) {
            return 'La contraseña debe contener al menos un número.';
        }
        return null;
    }
}
