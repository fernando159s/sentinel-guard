<?php

namespace App\Filament\Pages\Auth;

use App\Models\ChecklistPlantilla;
use App\Models\Empresa;
use App\Models\Politica;
use App\Models\User;
use Database\Seeders\PoliticaSeeder;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;
use Illuminate\Support\Str;

class RegisterEmpresa extends RegisterTenant
{
    protected static ?string $title = 'Registrar nueva empresa';

    public static function getLabel(): string
    {
        return 'Registrar empresa';
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('ruc')
                    ->label('RUC')
                    ->required()
                    ->unique(Empresa::class)
                    ->minLength(11)
                    ->maxLength(11)
                    ->regex('/^\d{11}$/')
                    ->validationMessages([
                        'regex' => 'El RUC debe contener exactamente 11 dígitos numéricos.',
                    ]),
                TextInput::make('razon_social')
                    ->label('Razón social')
                    ->required()
                    ->maxLength(200),
                TextInput::make('email')
                    ->label('Email de la empresa')
                    ->email()
                    ->required()
                    ->maxLength(150),
                TextInput::make('direccion')
                    ->label('Dirección')
                    ->maxLength(300),
                TextInput::make('telefono')
                    ->label('Teléfono')
                    ->tel()
                    ->maxLength(30),
                \Filament\Schemas\Components\Section::make('Administrador de la empresa')
                    ->description('Se creará un usuario admin_empresa para gestionar esta empresa')
                    ->schema([
                        TextInput::make('admin_name')
                            ->label('Nombre del administrador')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('admin_email')
                            ->label('Email del administrador')
                            ->email()
                            ->required()
                            ->unique(User::class, 'email')
                            ->maxLength(150),
                        TextInput::make('admin_password')
                            ->label('Contraseña')
                            ->password()
                            ->required()
                            ->minLength(8)
                            ->revealable(),
                    ]),
            ]);
    }

    protected function handleRegistration(array $data): Empresa
    {
        // 1. Crear empresa
        $empresa = Empresa::create([
            'ruc' => $data['ruc'],
            'razon_social' => $data['razon_social'],
            'email' => $data['email'],
            'direccion' => $data['direccion'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'estado' => 'activo',
        ]);

        // 2. Crear admin de la empresa
        $admin = User::create([
            'name' => $data['admin_name'],
            'email' => $data['admin_email'],
            'password' => $data['admin_password'],
            'empresa_id' => $empresa->id,
            'rol' => 'admin_empresa',
            'estado' => 'activo',
        ]);
        $admin->syncRoles('admin_empresa');

        // 3. Crear políticas
        $this->createPoliticas($empresa);

        // 4. Crear plantillas de checklist
        $this->createChecklists($empresa);

        return $empresa;
    }

    private function createPoliticas(Empresa $empresa): void
    {
        $seeder = new PoliticaSeeder;
        $politicas = $seeder->getPoliticas();

        foreach ($politicas as $data) {
            Politica::firstOrCreate(
                [
                    'empresa_id' => $empresa->id,
                    'slug' => Str::slug($data['titulo']),
                ],
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
                'periodicidad' => 'mensual',
                'activa' => true,
                'items' => [
                    ['nombre' => 'Antivirus actualizado', 'descripcion' => 'Definiciones actualizadas (menos de 7 dias)', 'obligatorio' => true],
                    ['nombre' => 'Windows Update al dia', 'descripcion' => 'Sin actualizaciones criticas pendientes', 'obligatorio' => true],
                    ['nombre' => 'BitLocker activo', 'descripcion' => 'Cifrado de disco activado (PSC000-46)', 'obligatorio' => true],
                    ['nombre' => 'Sin software no autorizado', 'descripcion' => 'No hay software fuera de lista aprobada', 'obligatorio' => true],
                    ['nombre' => 'Pantalla de bloqueo configurada', 'descripcion' => 'Bloqueo automatico a 5 min de inactividad', 'obligatorio' => true],
                    ['nombre' => 'Backup reciente', 'descripcion' => 'Copia de seguridad de menos de 30 dias', 'obligatorio' => false],
                    ['nombre' => 'Sin datos sensibles en escritorio', 'descripcion' => 'Sin archivos confidenciales en escritorio', 'obligatorio' => false],
                ],
            ],
            [
                'nombre' => 'Revision trimestral de seguridad',
                'descripcion' => 'Revision trimestral de contrasenas, permisos y red.',
                'periodicidad' => 'trimestral',
                'activa' => true,
                'items' => [
                    ['nombre' => 'Contrasena cambiada en ultimos 90 dias', 'descripcion' => 'Cambio de contrasena en el periodo', 'obligatorio' => true],
                    ['nombre' => 'Permisos de carpetas correctos', 'descripcion' => 'Sin accesos excesivos en carpetas compartidas', 'obligatorio' => true],
                    ['nombre' => 'USB deshabilitado (si aplica)', 'descripcion' => 'Puertos USB deshabilitados via GPO (PSC000-28)', 'obligatorio' => false],
                    ['nombre' => 'Firewall activo', 'descripcion' => 'Firewall de Windows activo y configurado', 'obligatorio' => true],
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
