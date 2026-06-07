<?php

namespace Database\Seeders;

use App\Enums\TipoFormato;
use App\Models\AceptacionPolitica;
use App\Models\BackupEjecucion;
use App\Models\BackupProgramacion;
use App\Models\ChecklistEjecucion;
use App\Models\ChecklistPlantilla;
use App\Models\Empresa;
use App\Models\Equipo;
use App\Models\EquipoAsignacion;
use App\Models\Politica;
use App\Models\Registro;
use App\Models\Ticket;
use App\Models\TicketMensaje;
use App\Models\User;
use App\Services\RegistroNumberService;
use App\Services\TicketNumberService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Seeder demo aislado: crea empresa "Demo Corp" con datos realistas.
 * Idempotente: si la empresa ya existe (por RUC), avisa y aborta sin tocar nada.
 * NO modifica otras empresas.
 *
 * Run: php artisan db:seed --class=DemoCorpSeeder
 */
class DemoCorpSeeder extends Seeder
{
    private const RUC = '20999000001';

    private const DOMINIO = 'democorp.demo';

    private Empresa $empresa;

    private int $adminId;

    public function run(): void
    {
        $this->command->info('Seeding Demo Corp...');

        if (Empresa::where('ruc', self::RUC)->exists()) {
            $this->command->warn("Empresa Demo Corp (RUC " . self::RUC . ") ya existe. Aborting sin cambios.");

            return;
        }

        $this->ensureRolesExist();
        $this->seedEmpresa();
        $this->seedUsuarios();
        $this->seedPoliticas();
        $this->seedChecklistPlantillas();
        $this->seedEquipos();
        $this->seedRegistros();
        $this->seedTickets();
        $this->seedChecklistEjecuciones();
        $this->seedBackups();

        $this->command->newLine();
        $this->command->info("=== Demo Corp seeded OK (empresa_id={$this->empresa->id}) ===");
        $this->command->info("Admin login: admin@" . self::DOMINIO . " / Demo2026!");
        $this->command->newLine();
    }

    private function ensureRolesExist(): void
    {
        if (\Spatie\Permission\Models\Role::count() === 0) {
            $this->command->warn('Roles no encontrados. Ejecutando RolePermissionSeeder...');
            $this->call(RolePermissionSeeder::class);
        }
    }

    private function seedEmpresa(): void
    {
        $this->empresa = Empresa::create([
            'ruc' => self::RUC,
            'razon_social' => 'Demo Corp S.A.C.',
            'nombre_portal' => 'Demo Corp',
            'email' => 'contacto@' . self::DOMINIO,
            'telefono' => '+51 999 000 001',
            'direccion' => 'Av. Demo 123, Lima, Perú',
            'color_primario' => '#2563EB',
            'color_secundario' => '#0EA5E9',
            'color_sidebar' => '#0F172A',
            'estado' => 'activo',
        ]);

        $this->command->info("  + empresa creada (id={$this->empresa->id})");
    }

    private function seedUsuarios(): void
    {
        $empresaId = $this->empresa->id;

        $admin = User::create([
            'name' => 'Admin Demo',
            'email' => 'admin@' . self::DOMINIO,
            'password' => 'Demo2026!',
            'empresa_id' => $empresaId,
            'rol' => 'admin_empresa',
            'estado' => 'activo',
            'dni' => '40000001',
            'puesto' => 'Administrador General',
            'telefono' => '+51 999 000 010',
        ]);
        $admin->syncRoles('admin_empresa');
        $this->adminId = $admin->id;

        $firmantes = [
            ['Ana Torres', 'ana.torres', '40000002', 'Analista Legal'],
            ['Bruno Diaz', 'bruno.diaz', '40000003', 'Ingeniero de Sistemas'],
            ['Carla Mendoza', 'carla.mendoza', '40000004', 'Contadora'],
            ['David Pacheco', 'david.pacheco', '40000005', 'Asistente Administrativo'],
            ['Elena Rivas', 'elena.rivas', '40000006', 'Coordinadora RRHH'],
            ['Felipe Quiroz', 'felipe.quiroz', '40000007', 'Soporte TI'],
        ];

        foreach ($firmantes as $i => [$name, $alias, $dni, $puesto]) {
            $user = User::create([
                'name' => $name,
                'email' => "{$alias}@" . self::DOMINIO,
                'password' => 'Demo2026!',
                'empresa_id' => $empresaId,
                'rol' => 'usuario',
                'estado' => 'activo',
                'dni' => $dni,
                'puesto' => $puesto,
                'telefono' => '+51 999 000 0' . str_pad((string) ($i + 11), 2, '0', STR_PAD_LEFT),
            ]);
            $user->syncRoles('usuario');
        }

        $this->command->info('  + ' . (count($firmantes) + 1) . ' usuarios creados (1 admin + ' . count($firmantes) . ' firmantes)');
    }

    private function seedPoliticas(): void
    {
        $politicas = (new PoliticaSeeder)->getPoliticas($this->empresa);
        $firmantes = User::firmantes()->where('empresa_id', $this->empresa->id)->where('estado', 'activo')->get();

        $count = 0;
        foreach ($politicas as $data) {
            $politica = Politica::firstOrCreate(
                ['empresa_id' => $this->empresa->id, 'slug' => Str::slug($data['titulo'])],
                array_merge($data, ['empresa_id' => $this->empresa->id])
            );

            $aceptaron = $firmantes->random(min($firmantes->count(), max(2, $firmantes->count() - 1)));
            foreach ($aceptaron as $user) {
                AceptacionPolitica::firstOrCreate(
                    ['user_id' => $user->id, 'politica_id' => $politica->id],
                    [
                        'version_aceptada' => $politica->version,
                        'fecha_aceptacion' => now()->subDays(rand(5, 90)),
                        'ip_address' => '10.0.0.' . rand(20, 200),
                        'user_agent' => 'Mozilla/5.0 Chrome/121',
                    ]
                );
            }
            $count++;
        }

        $this->command->info("  + {$count} políticas creadas con aceptaciones parciales");
    }

    private function seedChecklistPlantillas(): void
    {
        $plantillas = [
            [
                'nombre' => 'Verificacion mensual de PC',
                'descripcion' => 'Revision mensual de seguridad para equipos. PSC000003 / PSC000004.',
                'periodicidad' => 'mensual',
                'activa' => true,
                'items' => [
                    ['nombre' => 'Antivirus actualizado', 'descripcion' => 'Definiciones de virus < 7 dias', 'obligatorio' => true],
                    ['nombre' => 'Windows Update al dia', 'descripcion' => 'Sin actualizaciones criticas pendientes', 'obligatorio' => true],
                    ['nombre' => 'BitLocker activo', 'descripcion' => 'Cifrado de disco activado (PSC000-46)', 'obligatorio' => true],
                    ['nombre' => 'Sin software no autorizado', 'descripcion' => 'Inventario coincide con lista aprobada', 'obligatorio' => true],
                    ['nombre' => 'Pantalla bloqueo 5 min', 'descripcion' => 'Bloqueo automatico configurado', 'obligatorio' => true],
                    ['nombre' => 'Backup reciente', 'descripcion' => 'Backup de datos < 30 dias', 'obligatorio' => false],
                ],
            ],
            [
                'nombre' => 'Revision trimestral de seguridad',
                'descripcion' => 'Auditoria trimestral profunda.',
                'periodicidad' => 'trimestral',
                'activa' => true,
                'items' => [
                    ['nombre' => 'Contrasena rotada <90 dias', 'descripcion' => 'Rotacion de credenciales en periodo', 'obligatorio' => true],
                    ['nombre' => 'Permisos de carpetas correctos', 'descripcion' => 'Revision de ACLs', 'obligatorio' => true],
                    ['nombre' => 'USB controlado por GPO', 'descripcion' => 'Politica USB aplicada (PSC000-28)', 'obligatorio' => false],
                    ['nombre' => 'Firewall activo', 'descripcion' => 'Firewall configurado', 'obligatorio' => true],
                ],
            ],
        ];

        foreach ($plantillas as $p) {
            ChecklistPlantilla::firstOrCreate(
                ['empresa_id' => $this->empresa->id, 'nombre' => $p['nombre']],
                array_merge($p, ['empresa_id' => $this->empresa->id])
            );
        }

        $this->command->info('  + ' . count($plantillas) . ' plantillas checklist creadas');
    }

    private function seedEquipos(): void
    {
        $empresaId = $this->empresa->id;
        $usuarios = User::where('empresa_id', $empresaId)->where('rol', 'usuario')->pluck('id')->toArray();

        $catalogo = [
            ['laptop', 'Lenovo', 'ThinkPad T14', 'confidencial', 'Oficina Lima - Piso 1'],
            ['laptop', 'HP', 'EliteBook 840 G9', 'confidencial', 'Oficina Lima - Piso 1'],
            ['laptop', 'Dell', 'Latitude 5530', 'interno', 'Oficina Lima - Piso 2'],
            ['pc_escritorio', 'Lenovo', 'ThinkCentre M70q', 'interno', 'Recepcion'],
            ['pc_escritorio', 'HP', 'ProDesk 400 G7', 'publico', 'Recepcion'],
            ['laptop', 'Apple', 'MacBook Air M2', 'confidencial', 'Oficina Lima - Piso 2'],
            ['servidor', 'Dell', 'PowerEdge T150', 'sensible', 'Sala de servidores'],
            ['impresora', 'HP', 'LaserJet Pro M404', 'interno', 'Sala de impresion'],
            ['laptop', 'Lenovo', 'ThinkPad X1 Carbon', 'sensible', 'Oficina Lima - Piso 1'],
            ['laptop', 'HP', 'ProBook 450 G9', 'interno', 'Oficina Lima - Piso 1'],
            ['otro', 'Logitech', 'MX Master 3S', 'publico', 'Oficina Lima - Piso 2'],
            ['otro', 'Jabra', 'Evolve2 75', 'publico', 'Oficina Lima - Piso 1'],
        ];

        $count = 0;
        foreach ($catalogo as $i => [$tipo, $marca, $modelo, $sensibilidad, $ubicacion]) {
            $equipo = Equipo::create([
                'empresa_id' => $empresaId,
                'codigo_interno' => 'DC-' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                'tipo' => $tipo,
                'marca' => $marca,
                'modelo' => $modelo,
                'numero_serie' => 'SN-DC-' . strtoupper(Str::random(8)),
                'sistema_operativo' => in_array($tipo, ['pc_escritorio', 'laptop', 'servidor']) ? 'Windows 11 Pro' : null,
                'procesador' => in_array($tipo, ['pc_escritorio', 'laptop']) ? 'Intel Core i7-13th Gen' : null,
                'ram_gb' => in_array($tipo, ['pc_escritorio', 'laptop']) ? collect([8, 16, 16, 32])->random() : null,
                'disco_gb' => in_array($tipo, ['pc_escritorio', 'laptop']) ? collect([256, 512, 512, 1024])->random() : null,
                'estado' => $i === 9 ? 'mantenimiento' : 'activo',
                'ubicacion' => $ubicacion,
                'nivel_sensibilidad' => $sensibilidad,
                'fecha_adquisicion' => now()->subMonths(rand(6, 24))->toDateString(),
                'fecha_garantia' => now()->addMonths(rand(6, 24))->toDateString(),
            ]);

            if ($equipo->estado === 'activo' && ! empty($usuarios)) {
                EquipoAsignacion::create([
                    'equipo_id' => $equipo->id,
                    'user_id' => $usuarios[$i % count($usuarios)],
                    'tipo' => 'asignacion',
                    'fecha_inicio' => now()->subMonths(rand(1, 5)),
                    'condicion_entrega' => 'bueno',
                    'asignado_por' => $this->adminId,
                    'notas' => 'Asignacion inicial demo',
                ]);
            }
            $count++;
        }

        $this->command->info("  + {$count} equipos creados y asignados");
    }

    private function seedRegistros(): void
    {
        $empresaId = $this->empresa->id;
        $creadores = User::where('empresa_id', $empresaId)->pluck('id')->toArray();
        $formatos = TipoFormato::cases();
        $count = 0;

        for ($monthOffset = 4; $monthOffset >= 0; $monthOffset--) {
            $base = now()->subMonths($monthOffset);
            $registrosMes = rand(8, 14);

            for ($j = 0; $j < $registrosMes; $j++) {
                $tipo = (rand(1, 4) === 1) ? TipoFormato::F09 : $formatos[array_rand($formatos)];
                $fecha = $base->copy()->day(rand(1, min(28, $base->daysInMonth)))->setTime(rand(8, 18), rand(0, 59));
                $numero = RegistroNumberService::generate($empresaId, $tipo);

                Registro::create([
                    'empresa_id' => $empresaId,
                    'tipo_formato' => $tipo->value,
                    'numero_registro' => $numero,
                    'datos' => $this->buildDatosForTipo($tipo, $fecha),
                    'estado' => 'activo',
                    'creado_por' => $creadores[array_rand($creadores)],
                    'created_at' => $fecha,
                    'updated_at' => $fecha,
                ]);
                $count++;
            }
        }

        $this->command->info("  + {$count} registros creados (5 meses)");
    }

    private function buildDatosForTipo(TipoFormato $tipo, Carbon $fecha): array
    {
        $personas = ['Ana Torres', 'Bruno Diaz', 'Carla Mendoza', 'David Pacheco', 'Elena Rivas', 'Felipe Quiroz', 'Admin Demo'];

        return match ($tipo) {
            TipoFormato::F01 => [
                'tipo' => collect(['interna', 'externa'])->random(),
                'fecha' => $fecha->format('Y-m-d'),
                'responsable' => $personas[array_rand($personas)],
                'resultado' => collect(['conforme', 'no_conforme', 'con_observaciones'])->random(),
                'acciones_correctivas' => collect(['si', 'no'])->random(),
                'observaciones' => 'Auditoria PSC000001 ejecutada en Demo Corp.',
            ],
            TipoFormato::F02 => [
                'nombre_bd' => collect(['BD Clientes', 'BD Facturacion', 'BD Empleados', 'BD CRM'])->random(),
                'codigo_registro' => 'REG-DC-' . rand(100, 999),
                'descripcion' => 'Base de datos registrada ante la APDP.',
                'areas_acceso' => collect(['Legal', 'Contabilidad', 'RRHH', 'TI'])->random(rand(1, 3))->toArray(),
                'categoria' => collect(['interna', 'confidencial', 'sensible'])->random(),
            ],
            TipoFormato::F03 => [
                'razon_social' => collect(['Cloud Hosting SAC', 'Servicios IT EIRL', 'Backups Cloud SA'])->random(),
                'ruc' => '20' . rand(100000000, 999999999),
                'servicio' => collect(['Hosting', 'Mantenimiento', 'Backup cloud'])->random(),
                'fecha_acceso' => $fecha->format('Y-m-d'),
                'nivel_acceso' => collect(['lectura', 'lectura_escritura'])->random(),
            ],
            TipoFormato::F04 => [
                'tipo_dato' => collect(['datos de salud', 'datos biometricos', 'datos financieros', 'datos de menores'])->random(),
                'banco_datos' => 'BD Clientes',
                'medidas_proteccion' => 'Cifrado AES-256, acceso por MFA.',
                'fecha' => $fecha->format('Y-m-d'),
            ],
            TipoFormato::F05 => [
                'persona' => $personas[array_rand($personas)],
                'banco_datos' => collect(['BD Clientes', 'BD Empleados'])->random(),
                'tipo_acceso' => collect(['lectura', 'lectura_escritura'])->random(),
                'fecha_autorizacion' => $fecha->format('Y-m-d'),
                'autoriza' => 'Admin Demo',
            ],
            TipoFormato::F06 => [
                'fecha_evento' => $fecha->format('Y-m-d H:i'),
                'persona_intento' => 'Externo no identificado',
                'sistema' => collect(['Servidor BD', 'Carpeta compartida', 'VPN'])->random(),
                'accion_tomada' => 'Acceso bloqueado, evento registrado en SIEM.',
            ],
            TipoFormato::F07 => [
                'soporte' => collect(['USB 32GB', 'Disco externo 1TB', 'Cinta LTO-8'])->random(),
                'serie' => 'SP-' . strtoupper(Str::random(6)),
                'ubicacion' => 'Bóveda TI',
                'contenido' => 'Backups cifrados',
                'fecha_inventario' => $fecha->format('Y-m-d'),
            ],
            TipoFormato::F08 => [
                'soporte' => collect(['USB 32GB', 'Disco externo 1TB'])->random(),
                'movimiento' => collect(['ingreso', 'salida'])->random(),
                'persona' => $personas[array_rand($personas)],
                'fecha' => $fecha->format('Y-m-d H:i'),
                'motivo' => 'Traslado autorizado a sede externa.',
            ],
            TipoFormato::F09 => [
                'fecha_evento' => $fecha->format('Y-m-d H:i:s'),
                'tipo_incidencia' => collect(['acceso_no_autorizado', 'perdida_datos', 'fuga_informacion', 'malware', 'fallo_sistema', 'otro'])->random(),
                'sistema_equipo' => collect(['Servidor principal', 'PC Recepcion', 'Laptop legal', 'Red WiFi', 'CRM'])->random(),
                'banco_datos' => collect(['BD Clientes', 'BD Facturacion', '', ''])->random(),
                'descripcion' => collect([
                    'Detectado intento de acceso no autorizado al CRM.',
                    'Fallo en disco del servidor de backups.',
                    'Alerta de malware en equipo de recepcion.',
                    'Phishing reportado por usuario via helpdesk.',
                    'Software no autorizado en laptop de practicante.',
                ])->random(),
                'medidas_inmediatas' => 'Equipo aislado, contrasenas rotadas, notificacion a responsable.',
                'personas_notificadas' => collect($personas)->random(rand(1, 3))->toArray(),
                'impacto_potencial' => collect(['Alto - datos sensibles', 'Medio - servicio interrumpido', 'Bajo - sin datos comprometidos'])->random(),
                'comunica_nombre' => $personas[array_rand($personas)],
                'severidad' => collect(['alta', 'media', 'baja'])->random(),
            ],
            TipoFormato::F10 => [
                'incidencia_ref' => 'INC-' . now()->year . '-' . str_pad((string) rand(1, 30), 3, '0', STR_PAD_LEFT),
                'fecha_cierre' => $fecha->format('Y-m-d H:i:s'),
                'clasificacion' => collect(['alta', 'media', 'baja'])->random(),
                'requirio_recuperacion' => (bool) rand(0, 1),
                'medidas_adoptadas' => 'Parche aplicado, politica de contrasenas reforzada.',
                'resultado_verificacion' => 'Incidente no se reproduce tras 72h de monitoreo.',
                'ejecuto' => $personas[array_rand($personas)],
                'firma_responsable' => 'Admin Demo',
                'acciones_preventivas' => 'Capacitacion al personal sobre phishing.',
            ],
            TipoFormato::F11 => [
                'fecha_recuperacion' => $fecha->format('Y-m-d'),
                'origen_backup' => collect(['Backup diario', 'Backup semanal'])->random(),
                'datos_recuperados' => 'Archivos del directorio /clientes',
                'tiempo_inactividad_horas' => rand(1, 12),
                'responsable' => $personas[array_rand($personas)],
            ],
            TipoFormato::F12 => [
                'nombre_backup' => collect(['Backup diario servidor', 'Backup semanal BD', 'Backup mensual completo'])->random(),
                'descripcion_contenido' => 'Copia completa de ' . collect(['BD', 'archivos', 'emails'])->random(),
                'fecha_copia' => $fecha->format('Y-m-d'),
                'periodicidad' => collect(['diaria', 'semanal', 'mensual'])->random(),
            ],
            TipoFormato::F13 => [
                'fecha_destruccion' => $fecha->format('Y-m-d'),
                'descripcion_activo' => collect(['Disco duro 500GB danado', 'USB 32GB obsoleto', 'Documentos caducados', 'Laptop fin de vida'])->random(),
                'metodo' => collect(['borrado_seguro', 'destruccion_fisica', 'trituracion'])->random(),
                'responsable' => $personas[array_rand($personas)],
                'autoriza' => 'Admin Demo',
            ],
        };
    }

    private function seedTickets(): void
    {
        $empresaId = $this->empresa->id;
        $creadores = User::where('empresa_id', $empresaId)->where('rol', 'usuario')->pluck('id')->toArray();
        $agentes = User::role(['super_admin', 'agente_helpdesk'])->pluck('id')->toArray();

        if (empty($agentes)) {
            $agentes = [$this->adminId];
        }

        $categorias = ['consulta', 'problema_tecnico', 'error_registro', 'solicitud_acceso', 'otro'];
        $prioridades = ['baja', 'media', 'media', 'alta', 'urgente'];
        $asuntos = [
            'No puedo acceder al sistema', 'Error al generar PDF', 'Solicitud de acceso a BD Clientes',
            'Mi PC se reinicia sola', 'No llegan los correos', 'Problema con la impresora',
            'Necesito cambio de contrasena', 'Error en formato F09', 'Pantalla azul frecuente',
            'Solicitud de nuevo usuario', 'Lentitud en el sistema', 'Acceso denegado a carpeta',
            'Virus detectado', 'Recuperar archivo borrado', 'Problema con VPN', 'USB no reconocido',
        ];
        $count = 0;

        for ($monthOffset = 4; $monthOffset >= 0; $monthOffset--) {
            $base = now()->subMonths($monthOffset);
            $ticketsMes = rand(5, 9);

            for ($j = 0; $j < $ticketsMes; $j++) {
                $fecha = $base->copy()->day(rand(1, min(28, $base->daysInMonth)))->setTime(rand(8, 18), rand(0, 59));
                $estado = $monthOffset > 1
                    ? collect(['resuelto', 'resuelto', 'cerrado', 'cerrado'])->random()
                    : collect(['nuevo', 'en_revision', 'esperando_usuario', 'resuelto', 'cerrado'])->random();

                $agenteId = $estado !== 'nuevo' ? $agentes[array_rand($agentes)] : null;
                $fechaCierre = in_array($estado, ['resuelto', 'cerrado']) ? $fecha->copy()->addDays(rand(1, 5)) : null;

                $ticket = Ticket::create([
                    'empresa_id' => $empresaId,
                    'numero_ticket' => TicketNumberService::generate(),
                    'creado_por' => $creadores[array_rand($creadores)],
                    'asignado_a' => $agenteId,
                    'asunto' => $asuntos[array_rand($asuntos)],
                    'descripcion' => '<p>Descripcion del problema reportado por usuario de Demo Corp.</p>',
                    'categoria' => $categorias[array_rand($categorias)],
                    'prioridad' => $prioridades[array_rand($prioridades)],
                    'estado' => $estado,
                    'fecha_ultima_actividad' => $fechaCierre ?? $fecha->copy()->addHours(rand(1, 48)),
                    'fecha_cierre' => $fechaCierre,
                    'created_at' => $fecha,
                    'updated_at' => $fecha,
                ]);

                if ($agenteId) {
                    TicketMensaje::create([
                        'ticket_id' => $ticket->id,
                        'autor_id' => $agenteId,
                        'tipo' => 'publico',
                        'contenido' => collect([
                            'Revisando tu solicitud. Te mantendremos informado.',
                            'Identificado el problema, procedemos con la solucion.',
                            'Necesito mas informacion para continuar.',
                            'Problema resuelto. Verifica por favor.',
                        ])->random(),
                        'created_at' => $fecha->copy()->addHours(rand(1, 12)),
                    ]);
                }
                $count++;
            }
        }

        $this->command->info("  + {$count} tickets creados con mensajes (5 meses)");
    }

    private function seedChecklistEjecuciones(): void
    {
        $plantillas = ChecklistPlantilla::where('empresa_id', $this->empresa->id)->where('activa', true)->get();
        $equipos = Equipo::where('empresa_id', $this->empresa->id)->where('estado', 'activo')->get();

        $count = 0;
        foreach ($equipos as $equipo) {
            foreach ($plantillas as $plantilla) {
                $numEjecuciones = rand(1, 3);
                for ($k = 0; $k < $numEjecuciones; $k++) {
                    $fecha = now()->subMonths(rand(0, 4))->subDays(rand(0, 28));
                    $resultados = collect($plantilla->items)->map(function ($item) {
                        $cumple = rand(1, 10) <= 8;

                        return [
                            'item' => $item['nombre'],
                            'cumple' => $cumple,
                            'observacion' => $cumple ? '' : collect(['Pendiente', 'Requiere atencion', 'No configurado'])->random(),
                        ];
                    })->toArray();

                    $obligatoriosFallidos = collect($plantilla->items)
                        ->zip($resultados)
                        ->filter(fn ($pair) => ($pair[0]['obligatorio'] ?? false) && ! $pair[1]['cumple'])
                        ->count();

                    ChecklistEjecucion::create([
                        'checklist_plantilla_id' => $plantilla->id,
                        'equipo_id' => $equipo->id,
                        'ejecutado_por' => $this->adminId,
                        'fecha_ejecucion' => $fecha,
                        'resultados' => $resultados,
                        'estado' => $obligatoriosFallidos > 0 ? 'con_observaciones' : 'completo',
                        'observaciones_generales' => rand(1, 3) === 1 ? 'Revision sin novedades.' : null,
                        'created_at' => $fecha,
                        'updated_at' => $fecha,
                    ]);
                    $count++;
                }
            }
        }

        $this->command->info("  + {$count} ejecuciones de checklist creadas");
    }

    private function seedBackups(): void
    {
        $empresaId = $this->empresa->id;
        $servidor = Equipo::where('empresa_id', $empresaId)->where('tipo', 'servidor')->first();

        $programas = [
            ['nombre' => 'Backup diario CRM', 'periodicidad' => 'diaria'],
            ['nombre' => 'Backup semanal BD', 'periodicidad' => 'semanal'],
            ['nombre' => 'Backup mensual completo', 'periodicidad' => 'mensual'],
        ];

        $count = 0;
        foreach ($programas as $p) {
            $prog = BackupProgramacion::create([
                'empresa_id' => $empresaId,
                'equipo_id' => $servidor?->id,
                'nombre' => $p['nombre'],
                'periodicidad' => $p['periodicidad'],
                'proximo_backup' => now()->addDays(rand(1, 7))->toDateString(),
                'descripcion' => 'Backup automatizado de Demo Corp.',
                'activo' => true,
                'creado_por' => $this->adminId,
            ]);

            $numEjecuciones = match ($p['periodicidad']) {
                'diaria' => 8,
                'semanal' => 4,
                'mensual' => 2,
                default => 1,
            };

            for ($i = 0; $i < $numEjecuciones; $i++) {
                BackupEjecucion::create([
                    'programacion_id' => $prog->id,
                    'fecha_ejecucion' => now()->subDays($i * ($p['periodicidad'] === 'diaria' ? 1 : ($p['periodicidad'] === 'semanal' ? 7 : 30))),
                    'ejecutado_por' => $this->adminId,
                    'estado' => rand(1, 10) <= 9 ? 'ejecutado' : 'atrasado',
                    'notas' => 'Ejecucion automatica',
                ]);
            }
            $count++;
        }

        $this->command->info("  + {$count} programaciones de backup con ejecuciones");
    }
}
