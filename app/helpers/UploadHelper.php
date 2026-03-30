<?php

namespace App\Helpers;

/**
 * UploadHelper — Manejo seguro de archivos subidos.
 *
 * Reglas:
 * - Validar MIME type + extensión en servidor (no solo cliente)
 * - Renombrar a UUID
 * - Almacenar en UPLOAD_PATH con .htaccess que bloquea ejecución
 */
class UploadHelper
{
    /** Extensiones permitidas por categoría */
    private const ALLOWED = [
        'images' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'mimes'      => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        ],
        'documents' => [
            'extensions' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
            'mimes'      => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ],
        ],
        'archives' => [
            'extensions' => ['zip'],
            'mimes'      => ['application/zip', 'application/x-zip-compressed'],
        ],
    ];

    /**
     * Subir archivo de forma segura.
     *
     * @param array  $file       Elemento de $_FILES (ej: $_FILES['adjunto'])
     * @param string $subdir     Subdirectorio dentro de uploads (ej: 'logos', 'tickets')
     * @param string[] $categories Categorías permitidas: 'images', 'documents', 'archives'
     * @return array{success: bool, filename?: string, original_name?: string, mime?: string, size?: int, error?: string}
     */
    public static function upload(array $file, string $subdir, array $categories = ['images', 'documents']): array
    {
        // Verificar errores de PHP
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => self::getUploadError($file['error'])];
        }

        // Verificar tamaño
        if ($file['size'] > MAX_UPLOAD_SIZE) {
            $maxMb = round(MAX_UPLOAD_SIZE / 1048576, 1);
            return ['success' => false, 'error' => "El archivo excede el tamaño máximo permitido ({$maxMb} MB)."];
        }

        // Obtener extensión del nombre original
        $originalName = basename($file['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        // Validar extensión contra categorías permitidas
        $allowedExtensions = [];
        $allowedMimes = [];
        foreach ($categories as $cat) {
            if (isset(self::ALLOWED[$cat])) {
                $allowedExtensions = array_merge($allowedExtensions, self::ALLOWED[$cat]['extensions']);
                $allowedMimes = array_merge($allowedMimes, self::ALLOWED[$cat]['mimes']);
            }
        }

        if (!in_array($extension, $allowedExtensions, true)) {
            return ['success' => false, 'error' => 'Tipo de archivo no permitido.'];
        }

        // Validar MIME type real del archivo (no confiar en el Content-Type del request)
        $detectedMime = mime_content_type($file['tmp_name']);
        if ($detectedMime === false || !in_array($detectedMime, $allowedMimes, true)) {
            return ['success' => false, 'error' => 'El tipo MIME del archivo no coincide con la extensión.'];
        }

        // Generar nombre UUID
        $uuid = self::generateUuid();
        $newFilename = $uuid . '.' . $extension;

        // Asegurar que el directorio destino existe
        $destDir = rtrim(UPLOAD_PATH, '/\\') . '/' . $subdir;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $destPath = $destDir . '/' . $newFilename;

        // Mover archivo
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return ['success' => false, 'error' => 'Error al guardar el archivo en el servidor.'];
        }

        return [
            'success'       => true,
            'filename'      => $newFilename,
            'original_name' => $originalName,
            'mime'          => $detectedMime,
            'size'          => $file['size'],
        ];
    }

    /**
     * Eliminar archivo de uploads.
     */
    public static function delete(string $subdir, string $filename): bool
    {
        $path = rtrim(UPLOAD_PATH, '/\\') . '/' . $subdir . '/' . basename($filename);
        if (file_exists($path)) {
            return unlink($path);
        }
        return false;
    }

    /**
     * Generar UUID v4.
     */
    private static function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // version 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // variant
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Traducir código de error de upload a mensaje.
     */
    private static function getUploadError(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo permitido.',
            UPLOAD_ERR_PARTIAL    => 'El archivo se subió parcialmente.',
            UPLOAD_ERR_NO_FILE    => 'No se seleccionó ningún archivo.',
            UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal del servidor.',
            UPLOAD_ERR_CANT_WRITE => 'Error de escritura en disco.',
            UPLOAD_ERR_EXTENSION  => 'Una extensión de PHP detuvo la subida.',
            default               => 'Error desconocido al subir el archivo.',
        };
    }
}
