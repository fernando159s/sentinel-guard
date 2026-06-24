<?php

namespace Tests\Feature\Asistente;

use App\Models\Empresa;
use App\Models\User;
use App\Services\Asistente\AsistenteSecuriForm;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AsistenteSecuriFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        config(['services.deepseek.api_key' => 'test-key']);
    }

    private function adminConEmpresa(): User
    {
        $empresa = Empresa::create(['ruc' => '20100000020', 'razon_social' => 'ACME']);
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin-'.uniqid().'@test.local',
            'password' => 'secret123',
            'empresa_id' => $empresa->id,
            'estado' => 'activo',
        ]);
        $user->syncRoles('admin_empresa');

        return $user;
    }

    private function mensajeLlm(?string $content, array $toolCalls = []): array
    {
        $message = ['role' => 'assistant', 'content' => $content];

        if ($toolCalls !== []) {
            $message['tool_calls'] = $toolCalls;
        }

        return ['choices' => [['message' => $message]]];
    }

    public function test_ejecuta_la_tool_del_mcp_y_crea_el_activo_como_el_usuario(): void
    {
        $admin = $this->adminConEmpresa();
        $this->actingAs($admin);

        Http::fake([
            'api.deepseek.com/*' => Http::sequence()
                ->push($this->mensajeLlm(null, [[
                    'id' => 'call_1',
                    'type' => 'function',
                    'function' => [
                        'name' => 'crear_activo_digital',
                        'arguments' => json_encode(['nombre' => 'WhatsApp Ventas', 'tipo' => 'whatsapp']),
                    ],
                ]]))
                ->push($this->mensajeLlm('Listo, registré la cuenta WhatsApp Ventas.')),
        ]);

        $resultado = app(AsistenteSecuriForm::class)->responder(
            [['role' => 'user', 'content' => 'registra una cuenta de WhatsApp para ventas']],
            $admin,
        );

        $this->assertStringContainsString('WhatsApp Ventas', $resultado['texto']);
        $this->assertContains('crear_activo_digital', $resultado['herramientas']);

        $this->assertDatabaseHas('activos_digitales', [
            'empresa_id' => $admin->empresa_id,
            'nombre' => 'WhatsApp Ventas',
            'tipo' => 'whatsapp',
        ]);

        // Dos llamadas: la del tool-call y la de la respuesta final con el resultado.
        Http::assertSentCount(2);
    }

    public function test_envia_el_system_prompt_que_acota_el_contexto(): void
    {
        $admin = $this->adminConEmpresa();
        $this->actingAs($admin);

        Http::fake([
            'api.deepseek.com/*' => Http::response($this->mensajeLlm('Solo puedo ayudarte con SecuriForm.')),
        ]);

        $resultado = app(AsistenteSecuriForm::class)->responder(
            [['role' => 'user', 'content' => '¿quién ganó el mundial?']],
            $admin,
        );

        $this->assertSame('Solo puedo ayudarte con SecuriForm.', $resultado['texto']);
        $this->assertSame([], $resultado['herramientas']);

        Http::assertSent(function (Request $request) {
            $mensajes = $request->data()['messages'] ?? [];
            $system = $mensajes[0] ?? [];

            return ($system['role'] ?? null) === 'system'
                && str_contains($system['content'] ?? '', 'EXCLUSIVAMENTE')
                && str_contains($system['content'] ?? '', 'SecuriForm');
        });
    }
}
