<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BrandingMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_empresas_table_has_branding_columns(): void
    {
        $this->assertTrue(Schema::hasColumn('empresas', 'color_primario'));
        $this->assertTrue(Schema::hasColumn('empresas', 'color_secundario'));
        $this->assertTrue(Schema::hasColumn('empresas', 'color_sidebar'));
        $this->assertTrue(Schema::hasColumn('empresas', 'nombre_portal'));
    }

    public function test_branding_columns_are_nullable(): void
    {
        $empresa = \App\Models\Empresa::create([
            'ruc' => '20888888888',
            'razon_social' => 'Test Branding Company',
        ]);

        $this->assertDatabaseHas('empresas', [
            'id' => $empresa->id,
            'ruc' => '20888888888',
            'color_primario' => null,
            'color_secundario' => null,
            'color_sidebar' => null,
            'nombre_portal' => null,
        ]);
    }

    public function test_empresa_can_save_branding_configuration(): void
    {
        $empresa = \App\Models\Empresa::create([
            'ruc' => '20777777777',
            'razon_social' => 'Branded Company',
            'color_primario' => '#1e3a5f',
            'color_secundario' => '#f59e0b',
            'color_sidebar' => '#1a4d2e',
            'nombre_portal' => 'Mi Portal Custom',
        ]);

        $this->assertDatabaseHas('empresas', [
            'id' => $empresa->id,
            'color_primario' => '#1e3a5f',
            'color_secundario' => '#f59e0b',
            'color_sidebar' => '#1a4d2e',
            'nombre_portal' => 'Mi Portal Custom',
        ]);
    }

    public function test_empresa_can_update_branding(): void
    {
        $empresa = \App\Models\Empresa::create([
            'ruc' => '20666666666',
            'razon_social' => 'Update Test Company',
        ]);

        $empresa->update([
            'color_primario' => '#ff0000',
            'nombre_portal' => 'Nuevo Nombre',
        ]);

        $empresa->refresh();
        $this->assertEquals('#ff0000', $empresa->color_primario);
        $this->assertEquals('Nuevo Nombre', $empresa->nombre_portal);
    }
}
