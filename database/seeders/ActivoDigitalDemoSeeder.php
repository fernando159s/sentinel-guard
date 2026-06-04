<?php

namespace Database\Seeders;

use App\Enums\EstadoActivoDigital;
use App\Enums\ModalidadPago;
use App\Enums\TipoActivoDigital;
use App\Models\ActivoDigital;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Datos demo realistas para el módulo de Activos Digitales (US-1907 / SEN-133).
 *
 * Cubre todos los tipos y modalidades de pago con vencimientos VARIADOS
 * (vencidos, por vencer dentro de 30 días, sin vencimiento), credenciales
 * cifradas y pagos históricos. Las fechas se calculan relativas a now()
 * para que el demo siempre muestre alertas frescas, sin importar cuándo
 * se ejecute el seeder.
 *
 * Es idempotente: updateOrCreate por (empresa_id, codigo_interno) y
 * firstOrCreate para credenciales/pagos. Se puede correr de forma aislada:
 *   php artisan db:seed --class=ActivoDigitalDemoSeeder
 *
 * Los responsables se eligen a propósito para que el QA por rol sea
 * demostrable (ver matriz en ActivoDigitalCredencialPolicy):
 *  - maria@palacios.pe (usuario)      → responsable de WhatsApp y Canva
 *  - pedro@palacios.pe (solo_lectura) → responsable de la licencia Adobe
 */
class ActivoDigitalDemoSeeder extends Seeder
{
    /** @var array<string,int> email => user id */
    private array $usuarios = [];

    public function run(): void
    {
        $this->usuarios = User::query()->pluck('id', 'email')->all();

        $total = 0;
        $total += $this->seedEmpresa(1, $this->definicionesEmpresaPalacios());

        // Segunda empresa (si existe) para poder validar aislamiento por tenant en el QA.
        if (Empresa::query()->whereKey(2)->exists()) {
            $total += $this->seedEmpresa(2, $this->definicionesEmpresaTechsoft());
        }

        $this->command->info("  + {$total} activos digitales demo cargados (con credenciales, responsables y pagos)");
    }

    /**
     * @param  array<int,array<string,mixed>>  $definiciones
     */
    private function seedEmpresa(int $empresaId, array $definiciones): int
    {
        $count = 0;

        foreach ($definiciones as $def) {
            $venceEnDias = $def['vence_en_dias'] ?? null;
            $vencimiento = $venceEnDias === null
                ? null
                : Carbon::today()->addDays($venceEnDias);

            $activo = ActivoDigital::withoutGlobalScopes()->updateOrCreate(
                ['empresa_id' => $empresaId, 'codigo_interno' => $def['codigo']],
                [
                    'nombre' => $def['nombre'],
                    'tipo' => $def['tipo']->value,
                    'proveedor' => $def['proveedor'] ?? null,
                    'url' => $def['url'] ?? null,
                    'identificador' => $def['identificador'] ?? null,
                    'estado' => $def['estado']->value,
                    'nivel_sensibilidad' => $def['sensibilidad'] ?? 'interno',
                    'responsable_id' => $this->userId($def['responsables'][0] ?? null),
                    'modalidad_pago' => $def['modalidad']->value,
                    'costo' => $def['costo'] ?? null,
                    'moneda' => $def['moneda'] ?? 'PEN',
                    'metodo_pago' => $def['metodo_pago'] ?? null,
                    'renovacion_automatica' => $def['renovacion'] ?? false,
                    'fecha_adquisicion' => $def['adquisicion'] ?? null,
                    'fecha_vencimiento' => $vencimiento?->toDateString(),
                    'observaciones' => $def['observaciones'] ?? null,
                ]
            );

            $this->seedCredencial($activo, $def['credencial'] ?? null);
            $this->seedResponsables($activo, $def['responsables'] ?? []);
            $this->seedPagos($activo, $def, $vencimiento);

            $count++;
        }

        return $count;
    }

    private function seedCredencial(ActivoDigital $activo, ?array $cred): void
    {
        // Autoritativo: el seeder es dueño del estado demo de esta cuenta, así
        // que reemplaza sus credenciales por la canónica en cada corrida. Esto
        // mantiene el demo idéntico aunque la cuenta ya tuviera credenciales.
        $activo->credenciales()->delete();

        if (! $cred) {
            return;
        }

        $activo->credenciales()->create([
            'etiqueta' => $cred['etiqueta'],
            'usuario' => $cred['usuario'] ?? null,
            'password' => $cred['password'] ?? null,
            'dato_2fa' => $cred['dato_2fa'] ?? null,
            'recovery' => $cred['recovery'] ?? null,
            'notas' => $cred['notas'] ?? null,
        ]);
    }

    /**
     * @param  array<int,string>  $emails
     */
    private function seedResponsables(ActivoDigital $activo, array $emails): void
    {
        $ids = collect($emails)
            ->map(fn (string $email) => $this->userId($email))
            ->filter()
            ->unique()
            ->all();

        // sync() exacto: el conjunto de responsables del demo es canónico.
        $activo->responsables()->sync($ids);
    }

    /**
     * Genera pagos históricos coherentes con la modalidad, anclados al
     * vencimiento para que el último período cubra el ciclo vigente.
     *
     * @param  array<string,mixed>  $def
     */
    private function seedPagos(ActivoDigital $activo, array $def, ?Carbon $vencimiento): void
    {
        $modalidad = $def['modalidad'];
        $costo = $def['costo'] ?? null;
        $nPagos = $def['pagos'] ?? 0;

        // Autoritativo: reemplaza el historial de pagos demo en cada corrida.
        $activo->pagos()->delete();

        if ($nPagos < 1 || ! $costo || $modalidad === ModalidadPago::Gratuito) {
            return;
        }

        $moneda = $def['moneda'] ?? 'PEN';
        $metodo = $def['metodo_pago'] ?? 'tarjeta';
        $registradoPor = $this->userId($def['responsables'][0] ?? null);

        for ($k = 0; $k < $nPagos; $k++) {
            [$fecha, $desde, $hasta] = $this->periodoPago($modalidad, $vencimiento, $def['adquisicion'] ?? null, $k);

            $activo->pagos()->create([
                'fecha_pago' => $fecha->toDateString(),
                'monto' => $costo,
                'moneda' => $moneda,
                'metodo' => $metodo,
                'periodo_desde' => $desde?->toDateString(),
                'periodo_hasta' => $hasta?->toDateString(),
                'registrado_por' => $registradoPor,
            ]);
        }
    }

    /**
     * @return array{0:Carbon,1:?Carbon,2:?Carbon} [fecha_pago, periodo_desde, periodo_hasta]
     */
    private function periodoPago(ModalidadPago $modalidad, ?Carbon $vencimiento, ?Carbon $adquisicion, int $k): array
    {
        // Pago único: un solo pago en la fecha de adquisición.
        if (! $modalidad->esRecurrente()) {
            $fecha = ($adquisicion ?? Carbon::today())->copy();

            return [$fecha, null, null];
        }

        // Recurrente: el pago k=0 cubre el período vigente (termina en el vencimiento).
        $ancla = ($vencimiento ?? Carbon::today())->copy();

        if ($modalidad === ModalidadPago::Anual) {
            $hasta = $ancla->copy()->subYears($k);
            $desde = $hasta->copy()->subYear();
        } else { // Mensual
            $hasta = $ancla->copy()->subMonthsNoOverflow($k);
            $desde = $hasta->copy()->subMonthNoOverflow();
        }

        return [$desde->copy(), $desde, $hasta];
    }

    private function userId(?string $email): ?int
    {
        return $email ? ($this->usuarios[$email] ?? null) : null;
    }

    /**
     * Catálogo demo del Estudio Palacios (empresa 1). Cubre todos los tipos
     * y modalidades, con un mix de vencimientos: 1 vencido, 3 por vencer
     * (<30 días), 2 futuros lejanos y 2 sin vencimiento.
     *
     * @return array<int,array<string,mixed>>
     */
    private function definicionesEmpresaPalacios(): array
    {
        return [
            [
                'codigo' => 'AD-001',
                'tipo' => TipoActivoDigital::Whatsapp,
                'nombre' => 'WhatsApp Business - Atención',
                'proveedor' => 'Meta',
                'identificador' => '+51 987 654 321',
                'url' => 'https://business.facebook.com/wa/manage',
                'modalidad' => ModalidadPago::Mensual,
                'costo' => 120.00,
                'moneda' => 'PEN',
                'metodo_pago' => 'Visa ***4821',
                'estado' => EstadoActivoDigital::Activo,
                'sensibilidad' => 'confidencial',
                'renovacion' => true,
                'adquisicion' => Carbon::today()->subMonths(8),
                'vence_en_dias' => 30, // dispara alerta de 30 días
                'responsables' => ['maria@palacios.pe', 'carlos@palacios.pe'],
                'pagos' => 3,
                'observaciones' => 'Línea de atención al cliente. Número verificado por Meta.',
                'credencial' => [
                    'etiqueta' => 'Acceso WhatsApp Business',
                    'usuario' => 'ventas@palacios.pe',
                    'password' => 'Demo*Wa2026!',
                    'dato_2fa' => 'PIN de verificación en dos pasos: 0000 (DEMO)',
                    'recovery' => 'Correo de recuperación: ti@palacios.pe',
                    'notas' => 'Credencial DEMO — no usar en producción.',
                ],
            ],
            [
                'codigo' => 'AD-002',
                'tipo' => TipoActivoDigital::Meta,
                'nombre' => 'Meta Business Manager',
                'proveedor' => 'Meta',
                'identificador' => 'Business ID 100948213',
                'url' => 'https://business.facebook.com',
                'modalidad' => ModalidadPago::Mensual,
                'costo' => 350.00,
                'moneda' => 'PEN',
                'metodo_pago' => 'Visa ***4821',
                'estado' => EstadoActivoDigital::Activo,
                'sensibilidad' => 'sensible',
                'renovacion' => true,
                'adquisicion' => Carbon::today()->subMonths(10),
                'vence_en_dias' => 7, // dispara alerta de 7 días
                'responsables' => ['carlos@palacios.pe'],
                'pagos' => 3,
                'observaciones' => 'Administra páginas, campañas publicitarias e Instagram corporativo.',
                'credencial' => [
                    'etiqueta' => 'Administrador Business Manager',
                    'usuario' => 'marketing@palacios.pe',
                    'password' => 'Demo*Meta2026!',
                    'dato_2fa' => 'Authenticator (Google) — DEMO',
                    'recovery' => 'Códigos de respaldo guardados en bóveda física',
                    'notas' => 'Credencial DEMO.',
                ],
            ],
            [
                'codigo' => 'AD-003',
                'tipo' => TipoActivoDigital::SuscripcionSaas,
                'nombre' => 'Google Workspace',
                'proveedor' => 'Google',
                'identificador' => 'palacios.pe',
                'url' => 'https://admin.google.com',
                'modalidad' => ModalidadPago::Anual,
                'costo' => 1320.00,
                'moneda' => 'PEN',
                'metodo_pago' => 'Transferencia BCP',
                'estado' => EstadoActivoDigital::Activo,
                'sensibilidad' => 'confidencial',
                'renovacion' => true,
                'adquisicion' => Carbon::today()->subYears(3)->subMonths(2),
                'vence_en_dias' => 240,
                'responsables' => ['carlos@palacios.pe'],
                'pagos' => 3,
                'observaciones' => 'Correo corporativo, Drive y Meet para todo el estudio (12 licencias).',
                'credencial' => [
                    'etiqueta' => 'Consola de administración',
                    'usuario' => 'admin@palacios.pe',
                    'password' => 'Demo*Gws2026!',
                    'dato_2fa' => 'Llave de seguridad FIDO2 — DEMO',
                    'recovery' => 'Teléfono de recuperación del super admin',
                    'notas' => 'Credencial DEMO.',
                ],
            ],
            [
                'codigo' => 'AD-004',
                'tipo' => TipoActivoDigital::Dominio,
                'nombre' => 'Dominio palacios.pe',
                'proveedor' => 'Punto.pe',
                'identificador' => 'palacios.pe',
                'url' => 'https://www.punto.pe',
                'modalidad' => ModalidadPago::Anual,
                'costo' => 90.00,
                'moneda' => 'PEN',
                'metodo_pago' => 'PayPal',
                'estado' => EstadoActivoDigital::Activo,
                'sensibilidad' => 'interno',
                'renovacion' => true,
                'adquisicion' => Carbon::today()->subYears(2)->subMonths(5),
                'vence_en_dias' => 199,
                'responsables' => ['carlos@palacios.pe'],
                'pagos' => 2,
                'observaciones' => 'Dominio institucional. Renovación anual con NIC.pe.',
                'credencial' => [
                    'etiqueta' => 'Panel del registrador',
                    'usuario' => 'ti@palacios.pe',
                    'password' => 'Demo*Dom2026!',
                    'dato_2fa' => null,
                    'recovery' => null,
                    'notas' => 'Credencial DEMO.',
                ],
            ],
            [
                'codigo' => 'AD-005',
                'tipo' => TipoActivoDigital::SuscripcionSaas,
                'nombre' => 'Microsoft 365 Business',
                'proveedor' => 'Microsoft',
                'identificador' => 'palacios.onmicrosoft.com',
                'url' => 'https://admin.microsoft.com',
                'modalidad' => ModalidadPago::Anual,
                'costo' => 980.00,
                'moneda' => 'PEN',
                'metodo_pago' => 'Transferencia BCP',
                'estado' => EstadoActivoDigital::Vencido,
                'sensibilidad' => 'confidencial',
                'renovacion' => false,
                'adquisicion' => Carbon::today()->subYears(2)->subMonths(1),
                'vence_en_dias' => -6,
                'responsables' => ['carlos@palacios.pe'],
                'pagos' => 2,
                'observaciones' => 'Suscripción VENCIDA — pendiente de renovación o migración a Workspace.',
                'credencial' => [
                    'etiqueta' => 'Centro de administración M365',
                    'usuario' => 'admin@palacios.onmicrosoft.com',
                    'password' => 'Demo*M3652026!',
                    'dato_2fa' => 'Microsoft Authenticator — DEMO',
                    'recovery' => null,
                    'notas' => 'Credencial DEMO.',
                ],
            ],
            [
                'codigo' => 'AD-006',
                'tipo' => TipoActivoDigital::LicenciaUnica,
                'nombre' => 'Licencia Adobe Acrobat Pro',
                'proveedor' => 'Adobe',
                'identificador' => 'Serial AOEM-2024-XXXX',
                'url' => null,
                'modalidad' => ModalidadPago::PagoUnico,
                'costo' => 850.00,
                'moneda' => 'PEN',
                'metodo_pago' => 'Factura única',
                'estado' => EstadoActivoDigital::Activo,
                'sensibilidad' => 'interno',
                'renovacion' => false,
                'adquisicion' => Carbon::today()->subMonths(7),
                'vence_en_dias' => null,
                'responsables' => ['pedro@palacios.pe', 'carlos@palacios.pe'],
                'pagos' => 1,
                'observaciones' => 'Licencia perpetua para firma y edición de PDF del área legal.',
                'credencial' => [
                    'etiqueta' => 'Cuenta Adobe',
                    'usuario' => 'legal@palacios.pe',
                    'password' => 'Demo*Adobe2026!',
                    'dato_2fa' => null,
                    'recovery' => null,
                    'notas' => 'Serial DEMO.',
                ],
            ],
            [
                'codigo' => 'AD-007',
                'tipo' => TipoActivoDigital::RedesSociales,
                'nombre' => 'LinkedIn Company Page',
                'proveedor' => 'LinkedIn',
                'identificador' => 'company/estudio-palacios',
                'url' => 'https://www.linkedin.com/company/estudio-palacios',
                'modalidad' => ModalidadPago::Gratuito,
                'costo' => null,
                'moneda' => 'PEN',
                'metodo_pago' => null,
                'estado' => EstadoActivoDigital::Activo,
                'sensibilidad' => 'publico',
                'renovacion' => false,
                'adquisicion' => Carbon::today()->subYears(1),
                'vence_en_dias' => null,
                'responsables' => ['carlos@palacios.pe'],
                'pagos' => 0,
                'observaciones' => 'Página institucional gratuita. Sin costo ni vencimiento.',
                'credencial' => [
                    'etiqueta' => 'Administrador de la página',
                    'usuario' => 'marketing@palacios.pe',
                    'password' => 'Demo*Li2026!',
                    'dato_2fa' => null,
                    'recovery' => null,
                    'notas' => 'Credencial DEMO.',
                ],
            ],
            [
                'codigo' => 'AD-008',
                'tipo' => TipoActivoDigital::SuscripcionSaas,
                'nombre' => 'Canva Pro Equipo',
                'proveedor' => 'Canva',
                'identificador' => 'team/palacios',
                'url' => 'https://www.canva.com',
                'modalidad' => ModalidadPago::Mensual,
                'costo' => 55.00,
                'moneda' => 'PEN',
                'metodo_pago' => 'Visa ***4821',
                'estado' => EstadoActivoDigital::Activo,
                'sensibilidad' => 'interno',
                'renovacion' => true,
                'adquisicion' => Carbon::today()->subMonths(5),
                'vence_en_dias' => 1, // dispara alerta de 1 día (renovación inminente)
                'responsables' => ['maria@palacios.pe'],
                'pagos' => 3,
                'observaciones' => 'Diseño de piezas para redes y documentos institucionales.',
                'credencial' => [
                    'etiqueta' => 'Cuenta de equipo Canva',
                    'usuario' => 'marketing@palacios.pe',
                    'password' => 'Demo*Canva2026!',
                    'dato_2fa' => null,
                    'recovery' => null,
                    'notas' => 'Credencial DEMO.',
                ],
            ],
        ];
    }

    /**
     * Catálogo demo de TechSoft (empresa 2) para validar aislamiento por tenant.
     *
     * @return array<int,array<string,mixed>>
     */
    private function definicionesEmpresaTechsoft(): array
    {
        return [
            [
                'codigo' => 'AD-001',
                'tipo' => TipoActivoDigital::Whatsapp,
                'nombre' => 'WhatsApp Soporte TechSoft',
                'proveedor' => 'Meta',
                'identificador' => '+51 912 345 678',
                'url' => 'https://business.facebook.com/wa/manage',
                'modalidad' => ModalidadPago::Mensual,
                'costo' => 120.00,
                'moneda' => 'PEN',
                'metodo_pago' => 'Mastercard ***7711',
                'estado' => EstadoActivoDigital::Activo,
                'sensibilidad' => 'confidencial',
                'renovacion' => true,
                'adquisicion' => Carbon::today()->subMonths(6),
                'vence_en_dias' => 14,
                'responsables' => ['luis@techsoft.pe'],
                'pagos' => 2,
                'observaciones' => 'Canal de soporte técnico a clientes.',
                'credencial' => [
                    'etiqueta' => 'Acceso WhatsApp',
                    'usuario' => 'soporte@techsoft.pe',
                    'password' => 'Demo*WaTs2026!',
                    'dato_2fa' => 'PIN: 0000 (DEMO)',
                    'recovery' => null,
                    'notas' => 'Credencial DEMO.',
                ],
            ],
            [
                'codigo' => 'AD-002',
                'tipo' => TipoActivoDigital::SuscripcionSaas,
                'nombre' => 'GitHub Team',
                'proveedor' => 'GitHub',
                'identificador' => 'org/techsoft',
                'url' => 'https://github.com/orgs/techsoft',
                'modalidad' => ModalidadPago::Mensual,
                'costo' => 160.00,
                'moneda' => 'PEN',
                'metodo_pago' => 'Mastercard ***7711',
                // Vencido pero aún en estado "activo": el comando activos:check-vencimientos
                // debe detectarlo, marcarlo Vencido y enviar la alerta correspondiente.
                'estado' => EstadoActivoDigital::Activo,
                'sensibilidad' => 'sensible',
                'renovacion' => false,
                'adquisicion' => Carbon::today()->subYears(1)->subMonths(3),
                'vence_en_dias' => -3,
                'responsables' => ['luis@techsoft.pe'],
                'pagos' => 2,
                'observaciones' => 'Repositorios privados — vencido, pendiente de marcar/renovar.',
                'credencial' => [
                    'etiqueta' => 'Owner de la organización',
                    'usuario' => 'devops@techsoft.pe',
                    'password' => 'Demo*Gh2026!',
                    'dato_2fa' => 'TOTP — DEMO',
                    'recovery' => 'Recovery codes en bóveda',
                    'notas' => 'Credencial DEMO.',
                ],
            ],
            [
                'codigo' => 'AD-003',
                'tipo' => TipoActivoDigital::SuscripcionSaas,
                'nombre' => 'AWS Cuenta Principal',
                'proveedor' => 'Amazon Web Services',
                'identificador' => 'Account 4815-1623-4290',
                'url' => 'https://console.aws.amazon.com',
                'modalidad' => ModalidadPago::Mensual,
                'costo' => 1450.00,
                'moneda' => 'PEN',
                'metodo_pago' => 'Mastercard ***7711',
                'estado' => EstadoActivoDigital::Activo,
                'sensibilidad' => 'sensible',
                'renovacion' => true,
                'adquisicion' => Carbon::today()->subYears(2),
                'vence_en_dias' => 20,
                'responsables' => ['luis@techsoft.pe', 'rosa@techsoft.pe'],
                'pagos' => 3,
                'observaciones' => 'Infraestructura productiva. Acceso restringido.',
                'credencial' => [
                    'etiqueta' => 'Usuario root AWS',
                    'usuario' => 'root@techsoft.pe',
                    'password' => 'Demo*Aws2026!',
                    'dato_2fa' => 'MFA virtual (root) — DEMO',
                    'recovery' => 'Llave física en caja fuerte',
                    'notas' => 'Credencial DEMO — acceso root, máxima criticidad.',
                ],
            ],
        ];
    }
}
