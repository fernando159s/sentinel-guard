<?php

namespace App\Mcp\Tools;

use App\Services\Ingesta\UsuarioIngestaService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('crear-usuario')]
#[Description(
    'Crea un usuario en SecuriForm. Solo super admin y admin de empresa pueden usarlo. '.
    'Un admin de empresa solo crea usuarios de SU empresa con roles de empresa (admin_empresa, usuario, solo_lectura). '.
    'Si no envías contraseña se genera una y el usuario deberá establecerla con "Olvidé mi contraseña". '.
    'Nunca se devuelve la contraseña.'
)]
class CrearUsuarioTool extends Tool
{
    public function handle(Request $request, UsuarioIngestaService $service): Response
    {
        $user = $request->user();

        if (! $user) {
            return Response::error('No autenticado. Configura un token de API válido para usar esta herramienta.');
        }

        try {
            $nuevo = $service->crear($request->all(), $user);
        } catch (AuthorizationException $e) {
            return Response::error($e->getMessage());
        } catch (ValidationException $e) {
            return Response::error('No se pudo crear el usuario: '.collect($e->errors())->flatten()->implode(' '));
        }

        $passwordPropia = filled($request->get('password'));
        $nota = $passwordPropia
            ? 'Contraseña establecida.'
            : 'Sin contraseña enviada: el usuario debe establecerla con "Olvidé mi contraseña".';

        return Response::text(sprintf(
            '✅ Usuario creado: %s <%s> — rol %s, %s. %s',
            $nuevo->name,
            $nuevo->email,
            $nuevo->rol,
            $nuevo->empresa_id ? "empresa #{$nuevo->empresa_id}" : 'sin empresa (staff)',
            $nota,
        ));
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Nombre completo del usuario.')->required(),
            'email' => $schema->string()->description('Correo (único).')->required(),
            'rol' => $schema->string()
                ->enum(['super_admin', 'agente_helpdesk', 'admin_empresa', 'usuario', 'solo_lectura'])
                ->description('Rol. Un admin de empresa solo puede asignar admin_empresa, usuario o solo_lectura.')
                ->required(),
            'estado' => $schema->string()->enum(['activo', 'inactivo'])->description('Estado. Por defecto activo.'),
            'password' => $schema->string()->description('Contraseña inicial (mín. 8). Si la omites se genera una y no se devuelve.'),
            'empresa_id' => $schema->integer()->description('Empresa del usuario. Solo super admin la indica; un admin de empresa siempre crea en la suya. Los roles de staff (super_admin/agente_helpdesk) van sin empresa.'),
            'dni' => $schema->string()->description('DNI / documento. Opcional.'),
            'telefono' => $schema->string()->description('Teléfono. Opcional.'),
            'puesto' => $schema->string()->description('Puesto / cargo. Opcional.'),
            'direccion' => $schema->string()->description('Dirección. Opcional.'),
        ];
    }
}
