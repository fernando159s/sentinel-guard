<?php

namespace App\Console\Commands;

use App\Models\Empresa;
use App\Models\Politica;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Repara las politicas que heredaron datos hardcodeados del Estudio Palacios
 * (versiones anteriores del PoliticaSeeder copiaban el texto literal a cada
 * empresa). Reemplaza esos literales por los datos propios de cada empresa.
 *
 * Es idempotente: tras el primer pase los literales de Palacios ya no existen,
 * por lo que volver a ejecutarlo no cambia nada.
 *
 * Uso:
 *   php artisan politicas:reparar-datos <empresaIdOrRuc> --dry-run   (previsualizar una)
 *   php artisan politicas:reparar-datos <empresaIdOrRuc> --force     (aplicar una sin preguntar)
 *   php artisan politicas:reparar-datos --all --dry-run             (previsualizar todas)
 *   php artisan politicas:reparar-datos --all --force              (aplicar a todas sin preguntar)
 */
class RepararDatosPoliticasEmpresa extends Command
{
    protected $signature = 'politicas:reparar-datos
        {empresa? : ID o RUC de la empresa a reparar (omitir si usas --all)}
        {--all : Repara TODAS las empresas registradas}
        {--dry-run : Muestra los cambios sin guardarlos}
        {--force : Aplica sin pedir confirmacion}';

    protected $description = 'Reemplaza datos heredados del Estudio Palacios en las politicas por los datos propios de cada empresa (una o --all).';

    public function handle(): int
    {
        $empresas = $this->resolverObjetivo();

        if ($empresas === null) {
            return self::FAILURE;
        }

        // Primer pase: calcular cambios sin escribir, agrupados por empresa.
        $cambios = [];
        $totalReemplazos = 0;
        $empresasConCambios = 0;

        foreach ($empresas as $empresa) {
            $this->avisarDatosFaltantes($empresa);

            $cambiosEmpresa = $this->calcularCambios($empresa);

            if (empty($cambiosEmpresa)) {
                continue;
            }

            $empresasConCambios++;
            $reemplazosEmpresa = array_sum(array_column($cambiosEmpresa, 'n'));
            $totalReemplazos += $reemplazosEmpresa;

            $this->line("• #{$empresa->id} {$empresa->razon_social} — " . count($cambiosEmpresa) . " politica(s), {$reemplazosEmpresa} reemplazo(s)");
            foreach ($cambiosEmpresa as $cambio) {
                $this->line("    - [{$cambio['politica']->slug}] {$cambio['n']} reemplazo(s)");
            }

            $cambios = array_merge($cambios, $cambiosEmpresa);
        }

        if (empty($cambios)) {
            $this->info('No se encontraron datos de Palacios en las politicas. Nada que reparar.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->info("{$empresasConCambios} empresa(s), " . count($cambios) . " politica(s) con cambios, {$totalReemplazos} reemplazo(s) en total.");

        if ($this->option('dry-run')) {
            $this->comment('DRY-RUN: no se guardo nada.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('¿Aplicar estos cambios a la base de datos?')) {
            $this->comment('Cancelado. No se guardo nada.');

            return self::SUCCESS;
        }

        DB::beginTransaction();

        try {
            foreach ($cambios as $cambio) {
                $cambio['politica']->contenido = $cambio['nuevo'];
                // saveQuietly: no dispara observers ni notificaciones (solo cambia contenido,
                // no la version, por lo que no debe avisarse a los usuarios).
                $cambio['politica']->saveQuietly();
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Error: se revirtieron todos los cambios. ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->info('Listo: ' . count($cambios) . " politica(s) actualizada(s) en {$empresasConCambios} empresa(s).");
        $this->comment('Nota: el contenido se actualizo sin cambiar la "version", por lo que no se notifico a los usuarios ni se creo snapshot de version. Si quieres dejar traza de version, subela manualmente desde el panel.');

        return self::SUCCESS;
    }

    /**
     * Resuelve las empresas objetivo segun el argumento/--all. Devuelve null
     * (e imprime el error) si la invocacion es invalida o no hay empresas.
     *
     * @return \Illuminate\Support\Collection<int,Empresa>|null
     */
    private function resolverObjetivo(): ?\Illuminate\Support\Collection
    {
        $todas = (bool) $this->option('all');
        $identificador = $this->argument('empresa');

        if ($todas && $identificador !== null) {
            $this->error('Usa una empresa O --all, no ambos.');

            return null;
        }

        if (! $todas && $identificador === null) {
            $this->error('Indica una empresa (ID o RUC) o usa --all para reparar todas.');

            return null;
        }

        if ($todas) {
            $empresas = Empresa::withoutGlobalScopes()->orderBy('id')->get();

            if ($empresas->isEmpty()) {
                $this->warn('No hay empresas registradas. Nada que hacer.');

                return null;
            }

            $this->info("Objetivo: TODAS las empresas ({$empresas->count()}).");

            return $empresas;
        }

        $empresa = $this->resolverEmpresa((string) $identificador);

        if (! $empresa) {
            $this->error('No se encontro la empresa indicada (se busca por ID o RUC).');

            return null;
        }

        $this->info("Empresa objetivo: #{$empresa->id} — {$empresa->razon_social} (RUC {$empresa->ruc})");

        return collect([$empresa]);
    }

    /**
     * Calcula los cambios pendientes de una empresa sin escribir.
     *
     * @return array<int,array{politica:Politica,nuevo:string,n:int}>
     */
    private function calcularCambios(Empresa $empresa): array
    {
        $reemplazos = $this->mapeoReemplazos($empresa);

        $politicas = Politica::withoutGlobalScopes()
            ->where('empresa_id', $empresa->id)
            ->get();

        $cambios = [];

        foreach ($politicas as $politica) {
            $original = (string) $politica->contenido;
            $nuevo = strtr($original, $reemplazos);

            if ($nuevo === $original) {
                continue;
            }

            $cambios[] = [
                'politica' => $politica,
                'nuevo' => $nuevo,
                'n' => $this->contarCoincidencias($original, $reemplazos),
            ];
        }

        return $cambios;
    }

    private function avisarDatosFaltantes(Empresa $empresa): void
    {
        foreach (['email' => 'correo', 'direccion' => 'direccion'] as $campo => $etiqueta) {
            if (blank($empresa->{$campo})) {
                $this->warn("  ⚠  #{$empresa->id} {$empresa->razon_social}: no tiene {$etiqueta} registrado; se usara un texto neutro.");
            }
        }
    }

    private function resolverEmpresa(string $identificador): ?Empresa
    {
        return Empresa::withoutGlobalScopes()
            ->where(function ($q) use ($identificador) {
                $q->where('id', $identificador)
                    ->orWhere('ruc', $identificador);
            })
            ->first();
    }

    private function contarCoincidencias(string $contenido, array $reemplazos): int
    {
        $total = 0;

        foreach (array_keys($reemplazos) as $buscar) {
            $total += substr_count($contenido, $buscar);
        }

        return $total;
    }

    /**
     * Literales heredados del Estudio Palacios (hardcodeados en versiones
     * anteriores del PoliticaSeeder) mapeados a los datos propios de la empresa.
     * El lado derecho coincide con lo que produce hoy PoliticaSeeder::getPoliticas().
     */
    private function mapeoReemplazos(Empresa $empresa): array
    {
        $razon = $empresa->razon_social ?: 'el estudio';
        $razonUpper = Str::upper($razon);
        $ruc = $empresa->ruc ?: 'RUC no registrado';
        $direccion = $empresa->direccion ?: 'direccion no registrada';
        $email = $empresa->email ?: 'correo corporativo no registrado';

        return [
            'En el Estudio de Abogados Palacios, la gestion documental'
                => "En {$razon}, la gestion documental",
            'Existe correo generico (info@estudiopalacios.com.pe).'
                => "Existe correo generico ({$email}).",
            'Dotar al Estudio Palacios Abogados de una sistematica'
                => "Dotar a {$razon} de una sistematica",
            '**ESTUDIO PALACIOS ABOGADOS S.A.C.**'
                => "**{$razonUpper}**",
            '**{puesto}** en el Estudio Palacios Abogados S.A.C., '
                => "**{puesto}** en {$razon}, ",
            'en el ejercicio de mis funciones dentro del Estudio Palacios Abogados S.A.C.'
                => "en el ejercicio de mis funciones dentro de {$razon}",
            '(SGSI) del Estudio Palacios Abogados S.A.C., asegurando'
                => "(SGSI) de {$razon}, asegurando",
            'areas y personal del Estudio Palacios Abogados S.A.C., incluyendo'
                => "areas y personal de {$razon}, incluyendo",
            '<p>El Estudio Palacios Abogados S.A.C. se compromete a proteger'
                => "<p>{$razon} se compromete a proteger",
            'Estudio Palacios Abogados SAC | RUC: 20454292295 | Cal. San Pedro #100E, Arequipa | 6 trabajadores'
                => "{$razon} | RUC: {$ruc} | {$direccion}",
            'Elaborado por: JC Aranibar / Coordinador SGS. Aprobado por: M Palacios / Gerente General.'
                => 'Elaborado por: Coordinador SGS. Aprobado por: Gerente General.',
        ];
    }
}
