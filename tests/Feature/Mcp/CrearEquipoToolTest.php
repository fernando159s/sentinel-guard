<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Servers\IngestaServer;
use App\Mcp\Tools\CrearEquipoTool;
use App\Models\Empresa;
use App\Models\Equipo;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrearEquipoToolTest extends TestCase
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

    public function test_admin_empresa_crea_equipo_con_codigo_audita_e_ingreso(): void
    {
        $empresa = $this->empresa('20300000001');
        $admin = $this->usuarioCon('admin_empresa', $empresa->id);

        IngestaServer::actingAs($admin)
            ->tool(CrearEquipoTool::class, ['tipo' => 'laptop', 'marca' => 'Dell', 'modelo' => 'Latitude 5540'])
            ->assertOk()
            ->assertSee('EQ-');

        $equipo = Equipo::withoutGlobalScopes()->where('empresa_id', $empresa->id)->first();
        $this->assertNotNull($equipo);
        $this->assertSame('EQ-001', $equipo->codigo_interno);
        $this->assertTrue($equipo->asignaciones()->where('tipo', 'ingreso_nuevo')->exists());

        $this->assertDatabaseHas('audit_logs', ['entidad' => 'equipos', 'accion' => 'crear']);
    }

    public function test_solo_lectura_no_puede_registrar_equipo(): void
    {
        $user = $this->usuarioCon('solo_lectura', $this->empresa('20300000002')->id);

        IngestaServer::actingAs($user)
            ->tool(CrearEquipoTool::class, ['tipo' => 'laptop'])
            ->assertHasErrors();

        $this->assertDatabaseCount('equipos', 0);
    }

    public function test_super_admin_requiere_empresa_id(): void
    {
        $super = $this->usuarioCon('super_admin', null);

        IngestaServer::actingAs($super)
            ->tool(CrearEquipoTool::class, ['tipo' => 'servidor'])
            ->assertHasErrors();

        $empresa = $this->empresa('20300000003');

        IngestaServer::actingAs($super)
            ->tool(CrearEquipoTool::class, ['tipo' => 'servidor', 'empresa_id' => $empresa->id])
            ->assertOk();

        $this->assertDatabaseHas('equipos', ['empresa_id' => $empresa->id, 'tipo' => 'servidor']);
    }
}
