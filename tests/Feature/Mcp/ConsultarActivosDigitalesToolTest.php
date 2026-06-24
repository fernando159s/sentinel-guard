<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Servers\IngestaServer;
use App\Mcp\Tools\ConsultarActivosDigitalesTool;
use App\Models\ActivoDigital;
use App\Models\Empresa;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsultarActivosDigitalesToolTest extends TestCase
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

    public function test_la_herramienta_tiene_un_nombre_estable_para_el_llm(): void
    {
        $this->assertSame('consultar_activos_digitales', (new ConsultarActivosDigitalesTool)->name());
    }

    public function test_admin_solo_ve_activos_de_su_empresa(): void
    {
        $propia = $this->empresa('20100000010');
        $otra = $this->empresa('20100000011');
        $admin = $this->usuarioCon('admin_empresa', $propia->id);

        ActivoDigital::create(['empresa_id' => $propia->id, 'nombre' => 'WhatsApp Propio', 'tipo' => 'whatsapp']);
        ActivoDigital::create(['empresa_id' => $otra->id, 'nombre' => 'Dominio Ajeno', 'tipo' => 'dominio']);

        IngestaServer::actingAs($admin)
            ->tool(ConsultarActivosDigitalesTool::class, [])
            ->assertOk()
            ->assertSee('WhatsApp Propio')
            ->assertDontSee('Dominio Ajeno');
    }

    public function test_filtra_por_texto(): void
    {
        $empresa = $this->empresa('20100000012');
        $admin = $this->usuarioCon('admin_empresa', $empresa->id);

        ActivoDigital::create(['empresa_id' => $empresa->id, 'nombre' => 'Google Workspace', 'tipo' => 'suscripcion_saas']);
        ActivoDigital::create(['empresa_id' => $empresa->id, 'nombre' => 'Adobe Creative', 'tipo' => 'suscripcion_saas']);

        IngestaServer::actingAs($admin)
            ->tool(ConsultarActivosDigitalesTool::class, ['texto' => 'Google'])
            ->assertOk()
            ->assertSee('Google Workspace')
            ->assertDontSee('Adobe Creative');
    }

    public function test_sin_resultados_devuelve_mensaje_vacio(): void
    {
        $empresa = $this->empresa('20100000013');
        $admin = $this->usuarioCon('admin_empresa', $empresa->id);

        IngestaServer::actingAs($admin)
            ->tool(ConsultarActivosDigitalesTool::class, [])
            ->assertOk()
            ->assertSee('No se encontraron activos digitales');
    }
}
