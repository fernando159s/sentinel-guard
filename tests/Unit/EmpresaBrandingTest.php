<?php

namespace Tests\Unit;

use App\Http\Middleware\ApplyTenantBranding;
use App\Models\Empresa;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Tests\TestCase;

class EmpresaBrandingTest extends TestCase
{
    public function test_empresa_model_has_branding_fields_in_fillable(): void
    {
        $empresa = new Empresa;
        $fillable = $empresa->getFillable();

        $this->assertContains('color_primario', $fillable);
        $this->assertContains('color_secundario', $fillable);
        $this->assertContains('color_sidebar', $fillable);
        $this->assertContains('nombre_portal', $fillable);
    }

    public function test_empresa_branding_fields_are_nullable(): void
    {
        $empresa = new Empresa([
            'ruc' => '20999999999',
            'razon_social' => 'Test Company',
        ]);

        $this->assertNull($empresa->color_primario);
        $this->assertNull($empresa->color_secundario);
        $this->assertNull($empresa->color_sidebar);
        $this->assertNull($empresa->nombre_portal);
    }

    public function test_empresa_accepts_branding_values(): void
    {
        $empresa = new Empresa([
            'ruc' => '20999999999',
            'razon_social' => 'Test Company',
            'color_primario' => '#1e3a5f',
            'color_secundario' => '#f59e0b',
            'color_sidebar' => '#1a4d2e',
            'nombre_portal' => 'Mi Portal',
        ]);

        $this->assertEquals('#1e3a5f', $empresa->color_primario);
        $this->assertEquals('#f59e0b', $empresa->color_secundario);
        $this->assertEquals('#1a4d2e', $empresa->color_sidebar);
        $this->assertEquals('Mi Portal', $empresa->nombre_portal);
    }

    public function test_filament_name_returns_razon_social(): void
    {
        $empresa = new Empresa([
            'ruc' => '20999999999',
            'razon_social' => 'Estudio Palacios SAC',
            'nombre_portal' => 'Estudio Palacios',
        ]);

        $this->assertEquals('Estudio Palacios SAC', $empresa->getFilamentName());
    }

    public function test_apply_tenant_branding_middleware_handles_no_tenant(): void
    {
        $middleware = new ApplyTenantBranding;
        $request = Request::create('/admin');

        Filament::shouldReceive('getTenant')->andReturn(null);

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
    }
}
