<?php

namespace Database\Seeders;

use App\Enums\TipoFormato;
use App\Models\AceptacionPolitica;
use App\Models\ChecklistEjecucion;
use App\Models\ChecklistPlantilla;
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

class DemoDataSeeder extends Seeder
{
    private int $empresaId = 1; // Estudio Palacios

    public function run(): void
    {
        $this->command->info('Seeding demo data for 5 months...');

        $this->seedUsuarios();
        $this->seedEquipos();
        $this->seedRegistros();
        $this->seedTickets();
        $this->seedPoliticas();
        $this->seedChecklists();

        $this->command->info('Demo data seeded successfully!');
    }

    private function seedUsuarios(): void
    {
        $users = [
            ['name' => 'Jorge Ramirez', 'email' => 'jorge@palacios.pe', 'empresa_id' => 1, 'rol' => 'usuario'],
            ['name' => 'Lucia Fernandez', 'email' => 'lucia@palacios.pe', 'empresa_id' => 1, 'rol' => 'usuario'],
            ['name' => 'Diego Castillo', 'email' => 'diego@palacios.pe', 'empresa_id' => 1, 'rol' => 'usuario'],
            ['name' => 'Carmen Rojas', 'email' => 'carmen@palacios.pe', 'empresa_id' => 1, 'rol' => 'usuario'],
            ['name' => 'Fernando Vilca', 'email' => 'fernando@palacios.pe', 'empresa_id' => 1, 'rol' => 'usuario'],
            ['name' => 'Sofia Paredes', 'email' => 'sofia@palacios.pe', 'empresa_id' => 1, 'rol' => 'usuario'],
            ['name' => 'Roberto Agente', 'email' => 'roberto@securiform.local', 'empresa_id' => null, 'rol' => 'agente_helpdesk'],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                array_merge($data, ['password' => 'Test2024!', 'estado' => 'activo'])
            );
            $user->syncRoles($data['rol']);
        }

        $this->command->info('  + ' . count($users) . ' usuarios creados');
    }

    private function seedEquipos(): void
    {
        $tipos = ['pc_escritorio', 'laptop', 'laptop', 'pc_escritorio', 'laptop', 'impresora', 'servidor', 'laptop', 'pc_escritorio', 'laptop', 'laptop', 'laptop'];
        $marcas = [
            ['Lenovo', 'ThinkPad T14'], ['HP', 'ProBook 450 G9'], ['Dell', 'Latitude 5530'],
            ['Lenovo', 'ThinkCentre M70q'], ['HP', 'EliteBook 840 G9'], ['HP', 'LaserJet Pro M404'],
            ['Dell', 'PowerEdge T150'], ['Lenovo', 'ThinkPad X1 Carbon'], ['HP', 'ProDesk 400 G7'],
            ['Dell', 'Inspiron 15'], ['Lenovo', 'IdeaPad 5'], ['HP', 'Pavilion 15'],
        ];
        $ubicaciones = [
            'Oficina principal - Piso 1', 'Oficina principal - Piso 1', 'Oficina principal - Piso 2',
            'Recepcion', 'Oficina principal - Piso 2', 'Sala de impresion - Piso 1',
            'Sala de servidores', 'Oficina principal - Piso 2', 'Recepcion',
            'Oficina principal - Piso 1', 'Oficina principal - Piso 1', 'Oficina principal - Piso 2',
        ];
        $sensibilidades = ['confidencial', 'confidencial', 'interno', 'publico', 'sensible', 'interno', 'sensible', 'confidencial', 'publico', 'interno', 'interno', 'confidencial'];

        $usuarios = User::where('empresa_id', $this->empresaId)->where('estado', 'activo')->pluck('id')->toArray();
        $adminId = User::where('email', 'carlos@palacios.pe')->first()?->id ?? 1;

        $count = 0;
        foreach ($tipos as $i => $tipo) {
            $equipo = Equipo::firstOrCreate(
                ['empresa_id' => $this->empresaId, 'codigo_interno' => 'EQ-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT)],
                [
                    'tipo' => $tipo,
                    'marca' => $marcas[$i][0],
                    'modelo' => $marcas[$i][1],
                    'numero_serie' => 'SN-PAL-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                    'sistema_operativo' => in_array($tipo, ['pc_escritorio', 'laptop', 'servidor']) ? 'Windows 11 Pro' : null,
                    'procesador' => in_array($tipo, ['pc_escritorio', 'laptop']) ? 'Intel Core i7-13th Gen' : null,
                    'ram_gb' => in_array($tipo, ['pc_escritorio', 'laptop']) ? collect([8, 16, 16, 32])->random() : null,
                    'disco_gb' => in_array($tipo, ['pc_escritorio', 'laptop']) ? collect([256, 512, 512, 1024])->random() : null,
                    'estado' => $i < 10 ? 'activo' : ($i === 10 ? 'mantenimiento' : 'activo'),
                    'ubicacion' => $ubicaciones[$i],
                    'nivel_sensibilidad' => $sensibilidades[$i],
                    'fecha_adquisicion' => now()->subMonths(rand(6, 24))->toDateString(),
                    'fecha_garantia' => now()->addMonths(rand(6, 24))->toDateString(),
                ]
            );

            // Assign to a user if active
            if ($equipo->estado === 'activo' && isset($usuarios[$i % count($usuarios)])) {
                $fechaAsignacion = now()->subMonths(rand(1, 5));
                EquipoAsignacion::firstOrCreate(
                    ['equipo_id' => $equipo->id, 'user_id' => $usuarios[$i % count($usuarios)], 'fecha_fin' => null],
                    [
                        'tipo' => 'asignacion',
                        'fecha_inicio' => $fechaAsignacion,
                        'condicion_entrega' => 'bueno',
                        'asignado_por' => $adminId,
                        'notas' => 'Asignacion inicial',
                    ]
                );
            }
            $count++;
        }

        $this->command->info("  + {$count} equipos creados y asignados");
    }

    private function seedRegistros(): void
    {
        $formatos = [
            TipoFormato::F01, TipoFormato::F02, TipoFormato::F03, TipoFormato::F04,
            TipoFormato::F05, TipoFormato::F06, TipoFormato::F07, TipoFormato::F08,
            TipoFormato::F09, TipoFormato::F10, TipoFormato::F11, TipoFormato::F12, TipoFormato::F13,
        ];

        $creadores = User::where('empresa_id', $this->empresaId)->pluck('id')->toArray();
        $count = 0;

        // Distribute registros across 5 months with realistic volume
        for ($monthOffset = 4; $monthOffset >= 0; $monthOffset--) {
            $baseDate = now()->subMonths($monthOffset);
            $registrosEsteMes = rand(5, 15);

            for ($j = 0; $j < $registrosEsteMes; $j++) {
                $tipo = $formatos[array_rand($formatos)];
                $fecha = $baseDate->copy()->day(rand(1, min(28, $baseDate->daysInMonth)))->setTime(rand(8, 18), rand(0, 59));

                // Weight F09 more heavily (incidencias are common)
                if (rand(1, 4) === 1) {
                    $tipo = TipoFormato::F09;
                }

                $numero = RegistroNumberService::generate($this->empresaId, $tipo);
                $datos = $this->buildDatosForTipo($tipo, $fecha);

                Registro::create([
                    'empresa_id' => $this->empresaId,
                    'tipo_formato' => $tipo->value,
                    'numero_registro' => $numero,
                    'datos' => $datos,
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
        $personas = ['Jorge Ramirez', 'Lucia Fernandez', 'Diego Castillo', 'Carmen Rojas', 'Carlos Palacios', 'Maria Garcia'];

        return match ($tipo) {
            TipoFormato::F01 => [
                'tipo' => collect(['interna', 'externa'])->random(),
                'fecha' => $fecha->format('Y-m-d'),
                'responsable' => $personas[array_rand($personas)],
                'resultado' => collect(['conforme', 'no_conforme', 'con_observaciones'])->random(),
                'acciones_correctivas' => collect(['si', 'no'])->random(),
                'observaciones' => 'Auditoria realizada segun procedimiento PSC000001.',
            ],
            TipoFormato::F02 => [
                'nombre_bd' => collect(['BD Clientes', 'BD Expedientes', 'BD Proveedores', 'BD Empleados', 'BD Facturacion'])->random(),
                'codigo_registro' => 'REG-' . rand(100, 999),
                'descripcion' => 'Base de datos registrada ante la APDP.',
                'areas_acceso' => collect(['Legal', 'Administracion', 'Contabilidad', 'Recursos Humanos'])->random(rand(1, 3))->toArray(),
                'categoria' => collect(['interna', 'confidencial', 'sensible'])->random(),
            ],
            TipoFormato::F09 => [
                'fecha_evento' => $fecha->format('Y-m-d H:i:s'),
                'tipo_incidencia' => collect(['acceso_no_autorizado', 'perdida_datos', 'fuga_informacion', 'malware', 'fallo_sistema', 'otro'])->random(),
                'sistema_equipo' => collect(['Servidor principal', 'PC Recepcion', 'Laptop juridico', 'Red WiFi', 'Sistema contable'])->random(),
                'banco_datos' => collect(['BD Clientes', 'BD Expedientes', '', ''])->random(),
                'descripcion' => collect([
                    'Se detecto acceso no autorizado al sistema de expedientes.',
                    'Fallo en disco duro del servidor de backups.',
                    'Alerta de malware detectada por el antivirus en equipo de recepcion.',
                    'Perdida de conexion a la base de datos por corte electrico.',
                    'Intento de phishing reportado por usuario.',
                    'Software no autorizado detectado en equipo de practicante.',
                ])->random(),
                'medidas_inmediatas' => 'Se aislo el equipo y se notifico al responsable de seguridad.',
                'personas_notificadas' => collect($personas)->random(rand(1, 3))->toArray(),
                'impacto_potencial' => collect(['Impacto alto - datos sensibles expuestos', 'Impacto medio - servicio interrumpido', 'Impacto bajo - sin datos comprometidos'])->random(),
                'comunica_nombre' => $personas[array_rand($personas)],
                'severidad' => collect(['alta', 'media', 'baja'])->random(),
            ],
            TipoFormato::F10 => [
                'incidencia_ref' => 'INC-2026-' . str_pad(rand(1, 50), 3, '0', STR_PAD_LEFT),
                'fecha_cierre' => $fecha->format('Y-m-d H:i:s'),
                'clasificacion' => collect(['alta', 'media', 'baja'])->random(),
                'requirio_recuperacion' => (bool) rand(0, 1),
                'medidas_adoptadas' => 'Se aplico parche de seguridad y se reforzo la politica de contrasenas.',
                'resultado_verificacion' => 'Verificado que el problema no se reproduce.',
                'ejecuto' => $personas[array_rand($personas)],
                'firma_responsable' => 'Carlos Palacios',
                'acciones_preventivas' => 'Capacitacion al personal sobre buenas practicas.',
            ],
            TipoFormato::F12 => [
                'nombre_backup' => collect(['Backup diario servidor', 'Backup semanal BD', 'Backup mensual completo', 'Backup expedientes'])->random(),
                'descripcion_contenido' => 'Copia completa de ' . collect(['base de datos', 'archivos del servidor', 'expedientes digitales', 'emails'])->random(),
                'fecha_copia' => $fecha->format('Y-m-d'),
                'periodicidad' => collect(['diaria', 'semanal', 'mensual'])->random(),
            ],
            TipoFormato::F13 => [
                'fecha_destruccion' => $fecha->format('Y-m-d'),
                'descripcion_activo' => collect(['Disco duro danado 500GB', 'USB 32GB con datos obsoletos', 'Documentos fisicos caducados', 'Laptop obsoleta HP ProBook'])->random(),
                'metodo' => collect(['borrado_seguro', 'destruccion_fisica', 'trituracion'])->random(),
                'responsable' => $personas[array_rand($personas)],
                'autoriza' => 'Carlos Palacios',
            ],
            default => [
                'descripcion' => 'Registro generado para demo.',
                'fecha' => $fecha->format('Y-m-d'),
                'responsable' => $personas[array_rand($personas)],
            ],
        };
    }

    private function seedTickets(): void
    {
        $creadores = User::where('empresa_id', $this->empresaId)->pluck('id')->toArray();
        $agentes = User::role(['super_admin', 'agente_helpdesk'])->pluck('id')->toArray();
        $categorias = ['consulta', 'problema_tecnico', 'error_registro', 'solicitud_acceso', 'otro'];
        $prioridades = ['baja', 'media', 'media', 'alta', 'urgente']; // media weighted
        $asuntos = [
            'No puedo acceder al sistema', 'Error al generar PDF', 'Solicitud de acceso a BD Expedientes',
            'Mi PC se reinicia sola', 'No llegan los correos', 'Problema con la impresora del piso 2',
            'Necesito cambio de contrasena', 'Error en formato F09', 'Pantalla azul frecuente',
            'Solicitud de nuevo usuario', 'Lentitud en el sistema', 'Error al cargar archivo adjunto',
            'Acceso denegado a carpeta compartida', 'Actualizar datos de empresa', 'USB no reconocido',
            'Virus detectado en mi equipo', 'Recuperar archivo borrado', 'Cambio de equipo',
            'Software necesario no instalado', 'Problema con VPN',
        ];
        $count = 0;

        for ($monthOffset = 4; $monthOffset >= 0; $monthOffset--) {
            $baseDate = now()->subMonths($monthOffset);
            $ticketsEsteMes = rand(4, 10);

            for ($j = 0; $j < $ticketsEsteMes; $j++) {
                $fecha = $baseDate->copy()->day(rand(1, min(28, $baseDate->daysInMonth)))->setTime(rand(8, 18), rand(0, 59));
                $estado = collect(['nuevo', 'en_revision', 'esperando_usuario', 'resuelto', 'cerrado'])->random();

                // Older tickets more likely resolved
                if ($monthOffset > 1) {
                    $estado = collect(['resuelto', 'resuelto', 'cerrado', 'cerrado'])->random();
                }

                $creadorId = $creadores[array_rand($creadores)];
                $agenteId = $estado !== 'nuevo' ? $agentes[array_rand($agentes)] : null;
                $fechaCierre = in_array($estado, ['resuelto', 'cerrado']) ? $fecha->copy()->addDays(rand(1, 5)) : null;

                $ticket = Ticket::create([
                    'empresa_id' => $this->empresaId,
                    'numero_ticket' => TicketNumberService::generate(),
                    'creado_por' => $creadorId,
                    'asignado_a' => $agenteId,
                    'asunto' => $asuntos[array_rand($asuntos)],
                    'descripcion' => '<p>Descripcion del problema reportado por el usuario.</p>',
                    'categoria' => $categorias[array_rand($categorias)],
                    'prioridad' => $prioridades[array_rand($prioridades)],
                    'estado' => $estado,
                    'fecha_ultima_actividad' => $fechaCierre ?? $fecha->copy()->addHours(rand(1, 48)),
                    'fecha_cierre' => $fechaCierre,
                    'created_at' => $fecha,
                    'updated_at' => $fecha,
                ]);

                // Add messages
                if ($agenteId) {
                    TicketMensaje::create([
                        'ticket_id' => $ticket->id,
                        'autor_id' => $agenteId,
                        'tipo' => 'publico',
                        'contenido' => collect([
                            'Estamos revisando tu solicitud. Te mantendremos informado.',
                            'He identificado el problema. Procedemos con la solucion.',
                            'Necesito mas informacion para poder ayudarte.',
                            'El problema fue resuelto. Por favor verifica.',
                            'Se realizo el cambio solicitado. Confirma si todo esta bien.',
                        ])->random(),
                        'created_at' => $fecha->copy()->addHours(rand(1, 12)),
                    ]);
                }

                $count++;
            }
        }

        $this->command->info("  + {$count} tickets creados con mensajes (5 meses)");
    }

    private function seedPoliticas(): void
    {
        $politicas = [
            [
                'titulo' => 'Acuerdo de Confidencialidad (NDA)',
                'contenido' => '<h2>Acuerdo de Confidencialidad</h2><p>El trabajador se compromete a mantener en estricta confidencialidad toda la informacion a la que tenga acceso en el ejercicio de sus funciones.</p><p>Esta obligacion subsiste incluso despues de terminada la relacion laboral.</p><p>El incumplimiento de este acuerdo puede resultar en acciones legales segun la legislacion vigente.</p><h3>Alcance</h3><ul><li>Datos personales de clientes</li><li>Informacion financiera</li><li>Propiedad intelectual</li><li>Procesos internos</li><li>Documentos legales</li></ul>',
                'version' => '2.0',
            ],
            [
                'titulo' => 'Politica de Uso Aceptable de Equipos',
                'contenido' => '<h2>Politica de Uso Aceptable</h2><p>Los equipos informaticos proporcionados por la empresa son para uso exclusivamente laboral.</p><h3>Obligaciones del usuario</h3><ul><li>Bloquear la pantalla al ausentarse</li><li>No instalar software no autorizado</li><li>No conectar dispositivos USB sin autorizacion</li><li>Reportar inmediatamente cualquier incidente de seguridad</li><li>No almacenar datos personales en el disco local</li></ul><h3>Prohibiciones</h3><ul><li>Uso de la red para fines personales</li><li>Descarga de contenido no relacionado al trabajo</li><li>Compartir credenciales de acceso</li></ul>',
                'version' => '1.0',
            ],
            [
                'titulo' => 'Politica de Proteccion de Datos Personales',
                'contenido' => '<h2>Proteccion de Datos Personales</h2><p>En cumplimiento de la Ley de Proteccion de Datos Personales, todo el personal debe seguir estas directrices:</p><ul><li>Los datos personales solo pueden ser usados para los fines autorizados</li><li>No se deben copiar datos a dispositivos externos sin autorizacion</li><li>Los documentos con datos sensibles deben ser destruidos de forma segura</li><li>Cualquier brecha de seguridad debe ser reportada inmediatamente</li></ul>',
                'version' => '1.0',
            ],
        ];

        $usuarios = User::where('empresa_id', $this->empresaId)->where('estado', 'activo')->get();

        foreach ($politicas as $data) {
            $politica = Politica::firstOrCreate(
                ['empresa_id' => $this->empresaId, 'slug' => \Illuminate\Support\Str::slug($data['titulo'])],
                array_merge($data, ['empresa_id' => $this->empresaId, 'obligatoria' => true, 'activa' => true])
            );

            // Some users have accepted
            $aceptaron = $usuarios->random(min($usuarios->count(), rand(3, $usuarios->count())));
            foreach ($aceptaron as $user) {
                AceptacionPolitica::firstOrCreate(
                    ['user_id' => $user->id, 'politica_id' => $politica->id],
                    [
                        'version_aceptada' => $politica->version,
                        'fecha_aceptacion' => now()->subDays(rand(1, 60)),
                        'ip_address' => '192.168.1.' . rand(10, 200),
                        'user_agent' => 'Mozilla/5.0 Chrome/120',
                    ]
                );
            }
        }

        $this->command->info('  + ' . count($politicas) . ' politicas creadas con aceptaciones parciales');
    }

    private function seedChecklists(): void
    {
        $plantillas = ChecklistPlantilla::where('empresa_id', $this->empresaId)->where('activa', true)->get();
        $equipos = Equipo::where('empresa_id', $this->empresaId)->where('estado', 'activo')->get();
        $ejecutores = User::where('empresa_id', $this->empresaId)->role(['admin_empresa'])->pluck('id')->toArray();
        $adminId = $ejecutores[0] ?? 1;

        $count = 0;
        foreach ($equipos as $equipo) {
            foreach ($plantillas as $plantilla) {
                // Create 1-3 executions per equipo per plantilla across different months
                $numEjecuciones = rand(1, 3);
                for ($k = 0; $k < $numEjecuciones; $k++) {
                    $fecha = now()->subMonths(rand(0, 4))->subDays(rand(0, 28));
                    $resultados = collect($plantilla->items)->map(function ($item) {
                        $cumple = rand(1, 10) <= 8; // 80% pass rate

                        return [
                            'item' => $item['nombre'],
                            'cumple' => $cumple,
                            'observacion' => $cumple ? '' : collect(['Pendiente de actualizar', 'Requiere atencion', 'No configurado', 'Deshabilitado'])->random(),
                        ];
                    })->toArray();

                    $obligatoriosFallidos = collect($plantilla->items)
                        ->zip($resultados)
                        ->filter(fn ($pair) => ($pair[0]['obligatorio'] ?? false) && ! $pair[1]['cumple'])
                        ->count();

                    ChecklistEjecucion::create([
                        'checklist_plantilla_id' => $plantilla->id,
                        'equipo_id' => $equipo->id,
                        'ejecutado_por' => $adminId,
                        'fecha_ejecucion' => $fecha,
                        'resultados' => $resultados,
                        'estado' => $obligatoriosFallidos > 0 ? 'con_observaciones' : 'completo',
                        'observaciones_generales' => rand(1, 3) === 1 ? 'Revision realizada sin novedades.' : null,
                        'created_at' => $fecha,
                        'updated_at' => $fecha,
                    ]);
                    $count++;
                }
            }
        }

        $this->command->info("  + {$count} ejecuciones de checklist creadas");
    }
}
