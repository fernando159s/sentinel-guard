<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Servers\IngestaServer;
use App\Mcp\Tools\CrearRegistroTool;
use App\Mcp\Tools\FormatosRegistroTool;
use App\Models\Empresa;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrearRegistroToolTest extends TestCase
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

    /** @return array<string,string> */
    private function datosF12(): array
    {
        return [
            'nombre_backup' => 'Backup diario servidor',
            'fecha_copia' => '2026-06-01',
            'periodicidad' => 'diaria',
            'descripcion_contenido' => 'Copia completa de la base de datos',
        ];
    }

    public function test_usuario_crea_registro_f12_con_numero_y_audita(): void
    {
        $empresa = $this->empresa('20200000001');
        $user = $this->usuarioCon('usuario', $empresa->id);

        IngestaServer::actingAs($user)
            ->tool(CrearRegistroTool::class, ['tipo_formato' => 'F12', 'datos' => $this->datosF12()])
            ->assertOk()
            ->assertSee('BAK-'); // prefijo de F12

        $this->assertDatabaseHas('registros', [
            'empresa_id' => $empresa->id,
            'tipo_formato' => 'F12',
            'estado' => 'activo',
            'creado_por' => $user->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'entidad' => 'registros',
            'accion' => 'crear',
        ]);
    }

    public function test_falta_campo_obligatorio_es_rechazado(): void
    {
        $user = $this->usuarioCon('usuario', $this->empresa('20200000002')->id);
        $datos = $this->datosF12();
        unset($datos['nombre_backup']);

        IngestaServer::actingAs($user)
            ->tool(CrearRegistroTool::class, ['tipo_formato' => 'F12', 'datos' => $datos])
            ->assertHasErrors();

        $this->assertDatabaseCount('registros', 0);
    }

    public function test_opcion_invalida_es_rechazada(): void
    {
        $user = $this->usuarioCon('usuario', $this->empresa('20200000003')->id);
        $datos = $this->datosF12();
        $datos['periodicidad'] = 'cuando-sea';

        IngestaServer::actingAs($user)
            ->tool(CrearRegistroTool::class, ['tipo_formato' => 'F12', 'datos' => $datos])
            ->assertHasErrors();
    }

    public function test_solo_lectura_no_puede_crear(): void
    {
        $user = $this->usuarioCon('solo_lectura', $this->empresa('20200000004')->id);

        IngestaServer::actingAs($user)
            ->tool(CrearRegistroTool::class, ['tipo_formato' => 'F12', 'datos' => $this->datosF12()])
            ->assertHasErrors();

        $this->assertDatabaseCount('registros', 0);
    }

    public function test_super_admin_requiere_empresa_id(): void
    {
        $super = $this->usuarioCon('super_admin', null);

        IngestaServer::actingAs($super)
            ->tool(CrearRegistroTool::class, ['tipo_formato' => 'F12', 'datos' => $this->datosF12()])
            ->assertHasErrors();

        $empresa = $this->empresa('20200000005');

        IngestaServer::actingAs($super)
            ->tool(CrearRegistroTool::class, ['tipo_formato' => 'F12', 'datos' => $this->datosF12(), 'empresa_id' => $empresa->id])
            ->assertOk();

        $this->assertDatabaseHas('registros', ['empresa_id' => $empresa->id, 'tipo_formato' => 'F12']);
    }

    public function test_formatos_registro_lista_y_describe(): void
    {
        $user = $this->usuarioCon('usuario', $this->empresa('20200000006')->id);

        IngestaServer::actingAs($user)
            ->tool(FormatosRegistroTool::class, [])
            ->assertOk()
            ->assertSee('F09'); // el catálogo incluye los 13

        IngestaServer::actingAs($user)
            ->tool(FormatosRegistroTool::class, ['tipo_formato' => 'F12'])
            ->assertOk()
            ->assertSee('nombre_backup'); // describe los campos del formato
    }
}
