<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Servers\IngestaServer;
use App\Mcp\Tools\CrearActivoDigitalTool;
use App\Models\Empresa;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrearActivoDigitalToolTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function empresa(string $ruc): Empresa
    {
        return Empresa::create(['ruc' => $ruc, 'razon_social' => 'Empresa '.$ruc]);
    }

    private function usuarioCon(string $rol, ?int $empresaId): User
    {
        $user = User::create([
            'name' => ucfirst($rol),
            'email' => $rol.'-'.uniqid().'@test.local',
            'password' => 'secret123',
            'empresa_id' => $empresaId,
            'estado' => 'activo',
        ]);
        $user->syncRoles($rol);

        return $user;
    }

    public function test_admin_empresa_crea_activo_en_su_empresa_y_audita(): void
    {
        $empresa = $this->empresa('20100000001');
        $admin = $this->usuarioCon('admin_empresa', $empresa->id);

        IngestaServer::actingAs($admin)
            ->tool(CrearActivoDigitalTool::class, [
                'nombre' => 'WhatsApp Ventas',
                'tipo' => 'whatsapp',
                'modalidad_pago' => 'mensual',
                'costo' => 120,
            ])
            ->assertOk()
            ->assertSee('Activo digital creado');

        $this->assertDatabaseHas('activos_digitales', [
            'empresa_id' => $empresa->id,
            'nombre' => 'WhatsApp Ventas',
            'tipo' => 'whatsapp',
            'estado' => 'activo', // default aplicado por el servicio
        ]);

        // La creación quedó auditada por AuditableObserver.
        $this->assertDatabaseHas('audit_logs', [
            'entidad' => 'activos_digitales',
            'accion' => 'crear',
        ]);
    }

    public function test_usuario_sin_rol_de_gestion_es_rechazado(): void
    {
        $empresa = $this->empresa('20100000002');
        $usuario = $this->usuarioCon('usuario', $empresa->id);

        IngestaServer::actingAs($usuario)
            ->tool(CrearActivoDigitalTool::class, [
                'nombre' => 'No permitido',
                'tipo' => 'otro',
            ])
            ->assertHasErrors();

        $this->assertDatabaseCount('activos_digitales', 0);
    }

    public function test_admin_empresa_no_puede_crear_en_otra_empresa(): void
    {
        $propia = $this->empresa('20100000003');
        $otra = $this->empresa('20100000004');
        $admin = $this->usuarioCon('admin_empresa', $propia->id);

        IngestaServer::actingAs($admin)
            ->tool(CrearActivoDigitalTool::class, [
                'nombre' => 'Intento cross-tenant',
                'tipo' => 'suscripcion_saas',
                'empresa_id' => $otra->id, // debe ignorarse
            ])
            ->assertOk();

        $this->assertDatabaseHas('activos_digitales', [
            'nombre' => 'Intento cross-tenant',
            'empresa_id' => $propia->id,
        ]);
        $this->assertDatabaseMissing('activos_digitales', [
            'nombre' => 'Intento cross-tenant',
            'empresa_id' => $otra->id,
        ]);
    }

    public function test_super_admin_requiere_empresa_id_explicito(): void
    {
        $super = $this->usuarioCon('super_admin', null);

        IngestaServer::actingAs($super)
            ->tool(CrearActivoDigitalTool::class, [
                'nombre' => 'Sin empresa',
                'tipo' => 'otro',
            ])
            ->assertHasErrors();

        $empresa = $this->empresa('20100000005');

        IngestaServer::actingAs($super)
            ->tool(CrearActivoDigitalTool::class, [
                'nombre' => 'Con empresa',
                'tipo' => 'otro',
                'empresa_id' => $empresa->id,
            ])
            ->assertOk();

        $this->assertDatabaseHas('activos_digitales', [
            'nombre' => 'Con empresa',
            'empresa_id' => $empresa->id,
        ]);
    }
}
