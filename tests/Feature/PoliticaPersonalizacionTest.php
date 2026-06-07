<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Politica;
use Database\Seeders\PoliticaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cubre el aislamiento de datos por empresa en las politicas:
 * - PoliticaSeeder::getPoliticas() sustituye los tokens {{...}} por los datos
 *   de la empresa y nunca deja datos del Estudio Palacios en otra empresa.
 * - El comando politicas:reparar-datos repara empresas que ya heredaron los
 *   literales hardcodeados de versiones anteriores del seeder.
 */
class PoliticaPersonalizacionTest extends TestCase
{
    use RefreshDatabase;

    private function empresa(array $overrides = []): Empresa
    {
        return Empresa::create(array_merge([
            'ruc' => '20111111111',
            'razon_social' => 'Alfa Legal S.A.C.',
            'direccion' => 'Av. Alfa 123, Lima',
            'email' => 'contacto@alfa.pe',
        ], $overrides));
    }

    private function contenidoConcatenado(array $politicas): string
    {
        return implode("\n", array_column($politicas, 'contenido'));
    }

    public function test_seeder_personaliza_politicas_con_los_datos_de_la_empresa(): void
    {
        $empresa = $this->empresa();

        $contenido = $this->contenidoConcatenado((new PoliticaSeeder)->getPoliticas($empresa));

        // Datos propios de la empresa presentes.
        $this->assertStringContainsString('Alfa Legal S.A.C.', $contenido);
        $this->assertStringContainsString('ALFA LEGAL S.A.C.', $contenido); // {{razon_social_upper}} en el NDA
        $this->assertStringContainsString('20111111111', $contenido);
        $this->assertStringContainsString('Av. Alfa 123, Lima', $contenido);
        $this->assertStringContainsString('contacto@alfa.pe', $contenido);

        // No quedan tokens sin resolver (los placeholders {var} de una sola
        // llave del NDA son intencionales; los {{...}} de empresa no deben quedar).
        $this->assertStringNotContainsString('{{', $contenido);
    }

    public function test_seeder_no_filtra_datos_del_estudio_palacios_a_otra_empresa(): void
    {
        $empresa = $this->empresa();

        $contenido = $this->contenidoConcatenado((new PoliticaSeeder)->getPoliticas($empresa));

        foreach (['Palacios', '20454292295', 'estudiopalacios.com.pe', 'San Pedro', 'Aranibar'] as $rastro) {
            $this->assertStringNotContainsString($rastro, $contenido, "Se filtro el dato '{$rastro}' del Estudio Palacios.");
        }
    }

    public function test_seeder_usa_textos_neutros_cuando_no_hay_empresa(): void
    {
        $contenido = $this->contenidoConcatenado((new PoliticaSeeder)->getPoliticas(null));

        $this->assertStringContainsString('el estudio', $contenido);
        $this->assertStringNotContainsString('{{', $contenido);
        $this->assertStringNotContainsString('Palacios', $contenido);
    }

    /**
     * Contenido tal como lo guardaban versiones anteriores del seeder: con los
     * literales del Estudio Palacios incrustados. Incluye los 11 literales
     * exactos que repara el comando, para verificar que ninguno sobrevive.
     */
    private function contenidoHeredadoDePalacios(): string
    {
        return '<h2>Manual</h2>'
            . '<p>En el Estudio de Abogados Palacios, la gestion documental se soporta en un Servidor Local.</p>'
            . '<p>Existe correo generico (info@estudiopalacios.com.pe).</p>'
            . '<p>Dotar al Estudio Palacios Abogados de una sistematica de gestion de riesgos.</p>'
            . '<p>**ESTUDIO PALACIOS ABOGADOS S.A.C.**</p>'
            . '<p>que ocupo el puesto de **{puesto}** en el Estudio Palacios Abogados S.A.C., declaro.</p>'
            . '<p>en el ejercicio de mis funciones dentro del Estudio Palacios Abogados S.A.C.</p>'
            . '<p>El SGSI (SGSI) del Estudio Palacios Abogados S.A.C., asegurando la confidencialidad.</p>'
            . '<p>aplica a todas las areas y personal del Estudio Palacios Abogados S.A.C., incluyendo lo fisico.</p>'
            . '<p>El Estudio Palacios Abogados S.A.C. se compromete a proteger la informacion.</p>'
            . '<p>Estudio Palacios Abogados SAC | RUC: 20454292295 | Cal. San Pedro #100E, Arequipa | 6 trabajadores</p>'
            . '<p>Elaborado por: JC Aranibar / Coordinador SGS. Aprobado por: M Palacios / Gerente General.</p>';
    }

    private function crearPolitica(Empresa $empresa, string $contenido): Politica
    {
        return Politica::create([
            'empresa_id' => $empresa->id,
            'titulo' => 'Politica heredada',
            'slug' => 'politica-heredada-' . $empresa->id,
            'contenido' => $contenido,
            'version' => '1.0',
            'obligatoria' => false,
            'activa' => true,
        ]);
    }

    public function test_comando_reemplaza_los_datos_de_palacios_por_los_de_la_empresa(): void
    {
        $empresa = $this->empresa();
        $politica = $this->crearPolitica($empresa, $this->contenidoHeredadoDePalacios());

        $this->artisan('politicas:reparar-datos', ['empresa' => $empresa->id, '--force' => true])
            ->assertSuccessful();

        $contenido = $politica->refresh()->contenido;

        $this->assertStringNotContainsString('Palacios', $contenido);
        $this->assertStringNotContainsString('20454292295', $contenido);
        $this->assertStringNotContainsString('San Pedro', $contenido);
        $this->assertStringNotContainsString('Aranibar', $contenido);

        $this->assertStringContainsString('Alfa Legal S.A.C.', $contenido);
        $this->assertStringContainsString('ALFA LEGAL S.A.C.', $contenido);
        $this->assertStringContainsString('20111111111', $contenido);
        $this->assertStringContainsString('Av. Alfa 123, Lima', $contenido);
    }

    public function test_comando_es_idempotente(): void
    {
        $empresa = $this->empresa();
        $politica = $this->crearPolitica($empresa, $this->contenidoHeredadoDePalacios());

        $this->artisan('politicas:reparar-datos', ['empresa' => $empresa->id, '--force' => true])->assertSuccessful();
        $primerPase = $politica->refresh()->contenido;

        $this->artisan('politicas:reparar-datos', ['empresa' => $empresa->id, '--force' => true])
            ->expectsOutputToContain('Nada que reparar')
            ->assertSuccessful();

        $this->assertSame($primerPase, $politica->refresh()->contenido);
    }

    public function test_comando_resuelve_empresa_por_ruc(): void
    {
        $empresa = $this->empresa();
        $politica = $this->crearPolitica($empresa, $this->contenidoHeredadoDePalacios());

        $this->artisan('politicas:reparar-datos', ['empresa' => $empresa->ruc, '--force' => true])
            ->assertSuccessful();

        $this->assertStringNotContainsString('Palacios', $politica->refresh()->contenido);
    }

    public function test_dry_run_no_persiste_cambios(): void
    {
        $empresa = $this->empresa();
        $original = $this->contenidoHeredadoDePalacios();
        $politica = $this->crearPolitica($empresa, $original);

        $this->artisan('politicas:reparar-datos', ['empresa' => $empresa->id, '--dry-run' => true])
            ->assertSuccessful();

        $this->assertSame($original, $politica->refresh()->contenido);
    }
}
