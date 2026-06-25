<?php

namespace Tests\Feature;

use App\Filament\Pages\AuditoriaBaul;
use App\Filament\Pages\BaulContrasenas;
use App\Filament\Resources\Baul\CredencialBaulResource;
use App\Models\ActivoDigital;
use App\Models\ActivoDigitalCredencial;
use App\Models\Empresa;
use App\Models\User;
use App\Services\AuditService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BaulContrasenasTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresaA;

    private Empresa $empresaB;

    private User $superAdmin;

    private User $adminA;

    private User $usuarioResponsable; // responsable solo de la cuenta A1

    private User $usuarioSinAcceso;   // de empresa A, sin responsabilidad

    private ActivoDigital $activoA1;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->empresaA = Empresa::create(['ruc' => '20100000001', 'razon_social' => 'Empresa A']);
        $this->empresaB = Empresa::create(['ruc' => '20100000002', 'razon_social' => 'Empresa B']);

        $this->superAdmin = $this->usuario('super_admin', null);
        $this->adminA = $this->usuario('admin_empresa', $this->empresaA->id);
        $this->usuarioResponsable = $this->usuario('usuario', $this->empresaA->id);
        $this->usuarioSinAcceso = $this->usuario('usuario', $this->empresaA->id);

        // Empresa A: dos cuentas con credencial. Empresa B: una cuenta.
        $this->activoA1 = $this->activoConCredencial($this->empresaA->id, 'Cuenta A1', 'Demo*A1clave');
        $this->activoConCredencial($this->empresaA->id, 'Cuenta A2', 'Demo*A2clave');
        $this->activoConCredencial($this->empresaB->id, 'Cuenta B1', 'Demo*B1clave');

        // El usuario "responsable" solo gestiona la cuenta A1.
        $this->activoA1->responsables()->attach($this->usuarioResponsable->id);
    }

    private function usuario(string $rol, ?int $empresaId): User
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

    private function activoConCredencial(int $empresaId, string $nombre, string $password): ActivoDigital
    {
        // Se crea sin sesion activa: el EmpresaScope no filtra y el empresa_id es explicito.
        $activo = ActivoDigital::create([
            'empresa_id' => $empresaId,
            'nombre' => $nombre,
            'tipo' => 'suscripcion_saas',
        ]);

        $activo->credenciales()->create([
            'etiqueta' => 'Administrador',
            'usuario' => 'admin@empresa'.$empresaId.'.test',
            'password' => $password,
        ]);

        return $activo;
    }

    /** @return Collection<int, ActivoDigitalCredencial> */
    private function visibles(User $user, ?int $empresaId): Collection
    {
        return ActivoDigitalCredencial::visiblesPara($user, $empresaId)->get();
    }

    public function test_super_admin_ve_todas_las_credenciales_pero_acotadas_al_tenant(): void
    {
        $this->actingAs($this->superAdmin);

        $this->assertCount(2, $this->visibles($this->superAdmin, $this->empresaA->id));
        $this->assertCount(1, $this->visibles($this->superAdmin, $this->empresaB->id));
    }

    public function test_admin_empresa_solo_ve_las_de_su_empresa(): void
    {
        $this->actingAs($this->adminA);

        $this->assertCount(2, $this->visibles($this->adminA, $this->empresaA->id));

        // No puede ver otra empresa ni aunque se le pase el id de la empresa ajena.
        $this->assertCount(0, $this->visibles($this->adminA, $this->empresaB->id));
    }

    public function test_usuario_responsable_solo_ve_las_cuentas_a_su_cargo(): void
    {
        $this->actingAs($this->usuarioResponsable);

        $visibles = $this->visibles($this->usuarioResponsable, $this->empresaA->id);

        $this->assertCount(1, $visibles);
        $this->assertSame($this->activoA1->id, $visibles->first()->activo_digital_id);
    }

    public function test_usuario_sin_responsabilidad_no_ve_ninguna_credencial(): void
    {
        $this->actingAs($this->usuarioSinAcceso);

        $this->assertCount(0, $this->visibles($this->usuarioSinAcceso, $this->empresaA->id));
    }

    public function test_acceso_al_baul_por_rol_y_responsabilidad(): void
    {
        $this->actingAs($this->superAdmin);
        $this->assertTrue(BaulContrasenas::canAccess());

        $this->actingAs($this->adminA);
        $this->assertTrue(BaulContrasenas::canAccess());

        $this->actingAs($this->usuarioResponsable);
        $this->assertTrue(BaulContrasenas::canAccess());

        $this->actingAs($this->usuarioSinAcceso);
        $this->assertFalse(BaulContrasenas::canAccess());
    }

    public function test_auditoria_del_baul_solo_para_administradores(): void
    {
        $this->actingAs($this->superAdmin);
        $this->assertTrue(AuditoriaBaul::canAccess());

        $this->actingAs($this->adminA);
        $this->assertTrue(AuditoriaBaul::canAccess());

        $this->actingAs($this->usuarioResponsable);
        $this->assertFalse(AuditoriaBaul::canAccess());

        $this->actingAs($this->usuarioSinAcceso);
        $this->assertFalse(AuditoriaBaul::canAccess());
    }

    public function test_revelar_muestra_el_secreto_y_lo_audita_en_la_empresa_de_la_credencial(): void
    {
        // El super_admin no tiene empresa propia; aun asi el log debe atribuirse a la empresa A.
        $this->actingAs($this->superAdmin);

        $credencial = $this->activoA1->credenciales()->first();

        // El contenido del modal muestra el valor descifrado (render puro, sin auditar).
        $contenido = new \ReflectionMethod(BaulContrasenas::class, 'contenidoRevelado');
        $contenido->setAccessible(true);
        $this->assertStringContainsString('Demo*A1clave', (string) $contenido->invoke(null, $credencial));

        // La auditoria se registra al montar la accion (una sola vez por apertura).
        $auditar = new \ReflectionMethod(BaulContrasenas::class, 'auditarRevelacion');
        $auditar->setAccessible(true);
        $auditar->invoke(null, $credencial);

        // Queda auditado y atribuido a la empresa de la credencial (no a la del actor).
        $this->assertDatabaseHas('audit_logs', [
            'accion' => 'credencial_revelada',
            'entidad' => 'ActivoDigitalCredencial',
            'entidad_id' => $credencial->id,
            'empresa_id' => $this->empresaA->id,
        ]);

        // Conserva el nombre del actor de forma denormalizada (sobrevive al tenant-scoping).
        $log = \App\Models\AuditLog::where('entidad_id', $credencial->id)->latest('id')->first();
        $this->assertSame($this->superAdmin->name, $log->datos_nuevos['por']);
    }

    public function test_audit_service_permite_atribuir_empresa_explicita(): void
    {
        $this->actingAs($this->adminA); // empresa A

        AuditService::log(accion: 'prueba_empresa_explicita', empresaId: $this->empresaB->id);
        $this->assertDatabaseHas('audit_logs', [
            'accion' => 'prueba_empresa_explicita',
            'empresa_id' => $this->empresaB->id,
        ]);

        // Sin override, usa la empresa del actor.
        AuditService::log(accion: 'prueba_empresa_actor');
        $this->assertDatabaseHas('audit_logs', [
            'accion' => 'prueba_empresa_actor',
            'empresa_id' => $this->empresaA->id,
        ]);
    }

    public function test_busqueda_global_acceso_igual_que_el_baul(): void
    {
        $this->actingAs($this->superAdmin);
        $this->assertTrue(CredencialBaulResource::canAccess());

        $this->actingAs($this->usuarioResponsable);
        $this->assertTrue(CredencialBaulResource::canAccess());

        $this->actingAs($this->usuarioSinAcceso);
        $this->assertFalse(CredencialBaulResource::canAccess());
    }

    public function test_busqueda_global_respeta_responsables(): void
    {
        // El responsable solo encuentra credenciales de las cuentas a su cargo.
        $this->actingAs($this->usuarioResponsable);
        $visibles = CredencialBaulResource::getGlobalSearchEloquentQuery()->get();
        $this->assertCount(1, $visibles);
        $this->assertSame($this->activoA1->id, $visibles->first()->activo_digital_id);

        // Quien no es responsable de ninguna cuenta no encuentra nada.
        $this->actingAs($this->usuarioSinAcceso);
        $this->assertCount(0, CredencialBaulResource::getGlobalSearchEloquentQuery()->get());
    }

    public function test_busqueda_global_nunca_expone_secretos(): void
    {
        // Los campos cifrados no son buscables (no se puede buscar por contraseña/usuario/2FA).
        $buscables = CredencialBaulResource::getGloballySearchableAttributes();
        foreach (['password', 'usuario', 'dato_2fa', 'recovery', 'notas'] as $sensible) {
            $this->assertNotContains($sensible, $buscables);
            $this->assertNotContains("activoDigital.{$sensible}", $buscables);
        }

        // Ni el título ni los detalles del resultado contienen el secreto descifrado.
        $credencial = $this->activoA1->credenciales()->first();
        $titulo = CredencialBaulResource::getGlobalSearchResultTitle($credencial);
        $detalles = implode(' ', CredencialBaulResource::getGlobalSearchResultDetails($credencial));

        $this->assertStringNotContainsString('Demo*A1clave', (string) $titulo);
        $this->assertStringNotContainsString('Demo*A1clave', $detalles);
    }
}
