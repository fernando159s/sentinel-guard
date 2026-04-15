<?php

namespace App\Filament\Pages\Auth;

use App\Models\ChecklistPlantilla;
use App\Models\Empresa;
use App\Models\Politica;
use App\Models\User;
use Database\Seeders\PoliticaSeeder;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Tenancy\RegisterTenant;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;

class RegisterEmpresa extends RegisterTenant
{
    use WithFileUploads;

    protected string $view = 'filament.pages.auth.register-empresa';

    protected static ?string $title = 'Registrar nueva empresa';

    protected Width|string|null $maxWidth = '6xl';

    // Empresa
    public string $ruc = '';
    public string $razon_social = '';
    public string $empresa_email = '';
    public string $telefono = '';
    public string $direccion = '';
    public $logo_upload = null;
    public string $color_primario = '';
    public string $color_secundario = '';
    public string $color_sidebar = '';

    // Admin
    public string $admin_name = '';
    public string $admin_email = '';
    public string $admin_password = '';
    public string $firmaDataUrl = '';
    public $firmaUpload = null;
    public string $metodoFirma = 'dibujar';

    // Personalización toggle
    public bool $showPersonalizacion = false;

    public static function getLabel(): string
    {
        return 'Registrar empresa';
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    protected function handleRegistration(array $data): Empresa
    {
        return new Empresa;
    }

    public function registrar(): void
    {
        $this->validate([
            'ruc' => 'required|digits:11|unique:empresas,ruc',
            'razon_social' => 'required|max:200',
            'empresa_email' => 'required|email|max:150',
            'telefono' => 'nullable|max:30',
            'direccion' => 'nullable|max:300',
            'logo_upload' => 'nullable|image|max:2048',
            'admin_name' => 'required|max:100',
            'admin_email' => 'required|email|unique:users,email|max:150',
            'admin_password' => 'required|min:8',
        ]);

        // Logo
        $logoPath = null;
        if ($this->logo_upload) {
            $logoPath = $this->logo_upload->store('/', 'logos');
        }

        // Empresa
        $empresa = Empresa::create([
            'ruc' => $this->ruc,
            'razon_social' => $this->razon_social,
            'email' => $this->empresa_email,
            'telefono' => $this->telefono ?: null,
            'direccion' => $this->direccion ?: null,
            'logo_path' => $logoPath,
            'color_primario' => $this->color_primario ?: null,
            'color_secundario' => $this->color_secundario ?: null,
            'color_sidebar' => $this->color_sidebar ?: null,
            'estado' => 'activo',
        ]);

        // Firma
        $firma = null;
        if ($this->metodoFirma === 'subir' && $this->firmaUpload) {
            $mime = $this->firmaUpload->getMimeType();
            $base64 = base64_encode(file_get_contents($this->firmaUpload->getRealPath()));
            $firma = 'data:' . $mime . ';base64,' . $base64;
        } elseif ($this->firmaDataUrl) {
            $firma = $this->firmaDataUrl;
        }

        // Admin
        $admin = User::create([
            'name' => $this->admin_name,
            'email' => $this->admin_email,
            'password' => $this->admin_password,
            'empresa_id' => $empresa->id,
            'rol' => 'admin_empresa',
            'estado' => 'activo',
            'firma_guardada' => $firma,
        ]);
        $admin->syncRoles('admin_empresa');

        // Datos base
        $this->createPoliticas($empresa);
        $this->createChecklists($empresa);

        Notification::make()
            ->title('Empresa registrada')
            ->body("'{$empresa->razon_social}' esta lista.")
            ->success()
            ->send();

        $this->redirect(Filament::getUrl($empresa));
    }

    public function limpiarCanvas(): void
    {
        $this->firmaDataUrl = '';
        $this->dispatch('firma-cleared');
    }

    private function createPoliticas(Empresa $empresa): void
    {
        $seeder = new PoliticaSeeder;

        foreach ($seeder->getPoliticas() as $data) {
            Politica::firstOrCreate(
                ['empresa_id' => $empresa->id, 'slug' => Str::slug($data['titulo'])],
                array_merge($data, ['empresa_id' => $empresa->id])
            );
        }
    }

    private function createChecklists(Empresa $empresa): void
    {
        $checklists = [
            [
                'nombre' => 'Verificacion mensual de PC',
                'descripcion' => 'Verificacion mensual de seguridad para equipos de computo.',
                'periodicidad' => 'mensual', 'activa' => true,
                'items' => [
                    ['nombre' => 'Antivirus actualizado', 'descripcion' => 'Definiciones actualizadas (menos de 7 dias)', 'obligatorio' => true],
                    ['nombre' => 'Windows Update al dia', 'descripcion' => 'Sin actualizaciones criticas pendientes', 'obligatorio' => true],
                    ['nombre' => 'BitLocker activo', 'descripcion' => 'Cifrado de disco activado', 'obligatorio' => true],
                    ['nombre' => 'Sin software no autorizado', 'descripcion' => 'No hay software fuera de lista aprobada', 'obligatorio' => true],
                    ['nombre' => 'Pantalla de bloqueo configurada', 'descripcion' => 'Bloqueo automatico a 5 min', 'obligatorio' => true],
                    ['nombre' => 'Backup reciente', 'descripcion' => 'Copia de seguridad de menos de 30 dias', 'obligatorio' => false],
                    ['nombre' => 'Sin datos sensibles en escritorio', 'descripcion' => 'Sin archivos confidenciales en escritorio', 'obligatorio' => false],
                ],
            ],
            [
                'nombre' => 'Revision trimestral de seguridad',
                'descripcion' => 'Revision trimestral de contrasenas, permisos y red.',
                'periodicidad' => 'trimestral', 'activa' => true,
                'items' => [
                    ['nombre' => 'Contrasena cambiada en ultimos 90 dias', 'descripcion' => 'Cambio de contrasena en el periodo', 'obligatorio' => true],
                    ['nombre' => 'Permisos de carpetas correctos', 'descripcion' => 'Sin accesos excesivos en carpetas compartidas', 'obligatorio' => true],
                    ['nombre' => 'USB deshabilitado (si aplica)', 'descripcion' => 'Puertos USB deshabilitados via GPO', 'obligatorio' => false],
                    ['nombre' => 'Firewall activo', 'descripcion' => 'Firewall de Windows activo', 'obligatorio' => true],
                    ['nombre' => 'Sin VPN no autorizadas', 'descripcion' => 'Sin clientes VPN no aprobados', 'obligatorio' => true],
                ],
            ],
        ];

        foreach ($checklists as $checklist) {
            ChecklistPlantilla::firstOrCreate(
                ['empresa_id' => $empresa->id, 'nombre' => $checklist['nombre']],
                array_merge($checklist, ['empresa_id' => $empresa->id])
            );
        }
    }
}
