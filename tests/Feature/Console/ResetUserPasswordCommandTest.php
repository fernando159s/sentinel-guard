<?php

namespace Tests\Feature\Console;

use App\Models\Empresa;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ResetUserPasswordCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function usuario(): User
    {
        $empresa = Empresa::create(['ruc' => '20100000030', 'razon_social' => 'Torreblanca']);
        $user = User::create([
            'name' => 'Administración Torreblanca',
            'email' => 'administracion@torreblancamarmanillo.com',
            'password' => 'claveAntigua123',
            'empresa_id' => $empresa->id,
            'estado' => 'activo',
        ]);
        $user->syncRoles('admin_empresa');

        return $user;
    }

    public function test_restablece_la_contrasena_y_la_hashea_una_sola_vez(): void
    {
        $user = $this->usuario();

        $this->artisan('user:reset-password', ['email' => $user->email])
            ->expectsConfirmation('¿Restablecer la contraseña de este usuario?', 'yes')
            ->expectsQuestion('Nueva contraseña (no se mostrará)', 'NuevaClaveSegura123')
            ->expectsQuestion('Confirma la nueva contraseña', 'NuevaClaveSegura123')
            ->assertExitCode(0);

        // El cast 'hashed' debe dejar un hash verificable (no doble hash).
        $this->assertTrue(Hash::check('NuevaClaveSegura123', $user->fresh()->password));
    }

    public function test_falla_si_las_contrasenas_no_coinciden(): void
    {
        $user = $this->usuario();

        $this->artisan('user:reset-password', ['email' => $user->email])
            ->expectsConfirmation('¿Restablecer la contraseña de este usuario?', 'yes')
            ->expectsQuestion('Nueva contraseña (no se mostrará)', 'NuevaClaveSegura123')
            ->expectsQuestion('Confirma la nueva contraseña', 'otraDistinta456')
            ->assertExitCode(1);

        $this->assertTrue(Hash::check('claveAntigua123', $user->fresh()->password));
    }

    public function test_rechaza_contrasenas_cortas(): void
    {
        $user = $this->usuario();

        $this->artisan('user:reset-password', ['email' => $user->email])
            ->expectsConfirmation('¿Restablecer la contraseña de este usuario?', 'yes')
            ->expectsQuestion('Nueva contraseña (no se mostrará)', 'corta')
            ->assertExitCode(1);

        $this->assertTrue(Hash::check('claveAntigua123', $user->fresh()->password));
    }

    public function test_falla_si_el_usuario_no_existe(): void
    {
        $this->artisan('user:reset-password', ['email' => 'noexiste@x.com'])
            ->assertExitCode(1);
    }
}
