<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Servers\IngestaServer;
use App\Mcp\Tools\CrearUsuarioTool;
use App\Models\Empresa;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrearUsuarioToolTest extends TestCase
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

    public function test_admin_empresa_crea_usuario_en_su_empresa_sin_exponer_password(): void
    {
        $empresa = $this->empresa('20400000001');
        $admin = $this->usuarioCon('admin_empresa', $empresa->id);

        IngestaServer::actingAs($admin)
            ->tool(CrearUsuarioTool::class, ['name' => 'Juan Perez', 'email' => 'juan@acme.test', 'rol' => 'usuario'])
            ->assertOk()
            ->assertSee('Olvidé mi contraseña'); // no se envió password → debe resetear

        $nuevo = User::where('email', 'juan@acme.test')->first();
        $this->assertNotNull($nuevo);
        $this->assertSame($empresa->id, $nuevo->empresa_id);
        $this->assertTrue($nuevo->hasRole('usuario'));
    }

    public function test_admin_empresa_no_puede_crear_super_admin(): void
    {
        $admin = $this->usuarioCon('admin_empresa', $this->empresa('20400000002')->id);

        IngestaServer::actingAs($admin)
            ->tool(CrearUsuarioTool::class, ['name' => 'Hacker', 'email' => 'hacker@acme.test', 'rol' => 'super_admin'])
            ->assertHasErrors();

        $this->assertDatabaseMissing('users', ['email' => 'hacker@acme.test']);
    }

    public function test_admin_empresa_no_puede_crear_en_otra_empresa(): void
    {
        $propia = $this->empresa('20400000003');
        $otra = $this->empresa('20400000004');
        $admin = $this->usuarioCon('admin_empresa', $propia->id);

        IngestaServer::actingAs($admin)
            ->tool(CrearUsuarioTool::class, ['name' => 'Cross', 'email' => 'cross@acme.test', 'rol' => 'usuario', 'empresa_id' => $otra->id])
            ->assertOk();

        $this->assertDatabaseHas('users', ['email' => 'cross@acme.test', 'empresa_id' => $propia->id]);
    }

    public function test_super_admin_crea_admin_de_otra_empresa(): void
    {
        $super = $this->usuarioCon('super_admin', null);
        $empresa = $this->empresa('20400000005');

        IngestaServer::actingAs($super)
            ->tool(CrearUsuarioTool::class, ['name' => 'Admin X', 'email' => 'adminx@acme.test', 'rol' => 'admin_empresa', 'empresa_id' => $empresa->id])
            ->assertOk();

        $nuevo = User::where('email', 'adminx@acme.test')->first();
        $this->assertSame($empresa->id, $nuevo->empresa_id);
        $this->assertTrue($nuevo->hasRole('admin_empresa'));
    }

    public function test_email_duplicado_es_rechazado(): void
    {
        $admin = $this->usuarioCon('admin_empresa', $this->empresa('20400000006')->id);

        IngestaServer::actingAs($admin)
            ->tool(CrearUsuarioTool::class, ['name' => 'Dup', 'email' => $admin->email, 'rol' => 'usuario'])
            ->assertHasErrors();
    }

    public function test_usuario_normal_no_puede_crear_usuarios(): void
    {
        $user = $this->usuarioCon('usuario', $this->empresa('20400000007')->id);

        IngestaServer::actingAs($user)
            ->tool(CrearUsuarioTool::class, ['name' => 'X', 'email' => 'x@acme.test', 'rol' => 'usuario'])
            ->assertHasErrors();

        $this->assertDatabaseMissing('users', ['email' => 'x@acme.test']);
    }
}
