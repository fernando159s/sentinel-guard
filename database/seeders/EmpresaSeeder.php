<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmpresaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('empresas')->insertOrIgnore([
            [
                'id' => 1,
                'ruc' => '20601234567',
                'razon_social' => 'Estudio Palacios Abogados S.A.C.',
                'direccion' => 'Av. Principal 123, Lima',
                'email' => 'admin@palacios.pe',
                'telefono' => '+51 1 234-5678',
                'estado' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'ruc' => '20509876543',
                'razon_social' => 'Consultora TechSoft S.A.C.',
                'direccion' => 'Jr. Tecnología 456, Lima',
                'email' => 'admin@techsoft.pe',
                'telefono' => '+51 1 987-6543',
                'estado' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
