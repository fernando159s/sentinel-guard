<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = $this->getTemplates();

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                [
                    'nombre' => $template['nombre'],
                    'asunto' => $template['asunto'],
                    'contenido' => $template['contenido'],
                    'variables_disponibles' => $template['variables_disponibles'],
                    'plantilla_default_asunto' => $template['asunto'],
                    'plantilla_default_contenido' => $template['contenido'],
                    'activo' => true,
                ]
            );
        }
    }

    private function getTemplates(): array
    {
        return [
            [
                'slug' => 'bienvenida',
                'nombre' => 'Bienvenida (nuevo usuario)',
                'asunto' => 'Bienvenido a SecuriForm, {{nombre}}',
                'variables_disponibles' => ['nombre', 'empresa', 'email', 'enlace_login'],
                'contenido' => <<<'HTML'
<p>Hola <strong>{{nombre}}</strong>,</p>
<p>Tu cuenta en SecuriForm ha sido creada exitosamente para la empresa <strong>{{empresa}}</strong>.</p>
<p>Tus datos de acceso son:</p>
<ul>
    <li><strong>Email:</strong> {{email}}</li>
    <li><strong>Contrasena:</strong> La proporcionada por tu administrador</li>
</ul>
<p>Para ingresar al sistema, haz clic en el siguiente enlace:</p>
<p><a href="{{enlace_login}}">Iniciar sesion</a></p>
<p>Te recomendamos cambiar tu contrasena en tu primer inicio de sesion desde la seccion de perfil.</p>
HTML,
            ],
            [
                'slug' => 'reset_password',
                'nombre' => 'Recuperacion de contrasena',
                'asunto' => 'Restablecer contrasena - SecuriForm',
                'variables_disponibles' => ['nombre', 'enlace_reset', 'minutos_expiracion'],
                'contenido' => <<<'HTML'
<p>Hola <strong>{{nombre}}</strong>,</p>
<p>Recibimos una solicitud para restablecer la contrasena de tu cuenta en SecuriForm.</p>
<p>Haz clic en el siguiente enlace para crear una nueva contrasena:</p>
<p><a href="{{enlace_reset}}">Restablecer contrasena</a></p>
<p>Este enlace expirara en <strong>{{minutos_expiracion}} minutos</strong>.</p>
<p>Si no solicitaste este cambio, puedes ignorar este correo. Tu contrasena actual seguira siendo la misma.</p>
HTML,
            ],
            [
                'slug' => 'ticket_creado',
                'nombre' => 'Ticket creado',
                'asunto' => 'Ticket {{numero_ticket}} creado: {{asunto}}',
                'variables_disponibles' => ['nombre', 'numero_ticket', 'asunto', 'enlace_ticket'],
                'contenido' => <<<'HTML'
<p>Hola <strong>{{nombre}}</strong>,</p>
<p>Tu ticket de soporte ha sido creado exitosamente.</p>
<p><strong>Numero de ticket:</strong> {{numero_ticket}}<br>
<strong>Asunto:</strong> {{asunto}}</p>
<p>Nuestro equipo de soporte revisara tu solicitud y te responderemos a la brevedad posible.</p>
<p>Puedes seguir el estado de tu ticket en el siguiente enlace:</p>
<p><a href="{{enlace_ticket}}">Ver ticket</a></p>
HTML,
            ],
            [
                'slug' => 'respuesta_ticket',
                'nombre' => 'Respuesta en ticket',
                'asunto' => 'Nueva respuesta en ticket {{numero_ticket}}: {{asunto}}',
                'variables_disponibles' => ['nombre', 'numero_ticket', 'asunto', 'quien_responde', 'enlace_ticket'],
                'contenido' => <<<'HTML'
<p>Hola <strong>{{nombre}}</strong>,</p>
<p><strong>{{quien_responde}}</strong> ha respondido en tu ticket de soporte.</p>
<p><strong>Numero de ticket:</strong> {{numero_ticket}}<br>
<strong>Asunto:</strong> {{asunto}}</p>
<p>Para ver la respuesta completa y continuar la conversacion:</p>
<p><a href="{{enlace_ticket}}">Ver conversacion</a></p>
HTML,
            ],
            [
                'slug' => 'ticket_resuelto',
                'nombre' => 'Ticket resuelto',
                'asunto' => 'Ticket {{numero_ticket}} resuelto: {{asunto}}',
                'variables_disponibles' => ['nombre', 'numero_ticket', 'asunto', 'enlace_ticket'],
                'contenido' => <<<'HTML'
<p>Hola <strong>{{nombre}}</strong>,</p>
<p>Tu ticket de soporte ha sido marcado como <strong>resuelto</strong>.</p>
<p><strong>Numero de ticket:</strong> {{numero_ticket}}<br>
<strong>Asunto:</strong> {{asunto}}</p>
<p>Si consideras que el problema no fue resuelto, puedes reabrir el ticket dentro de los proximos 7 dias desde el siguiente enlace:</p>
<p><a href="{{enlace_ticket}}">Ver ticket</a></p>
<p>Gracias por contactarnos.</p>
HTML,
            ],
            [
                'slug' => 'nueva_incidencia',
                'nombre' => 'Nueva incidencia (F09)',
                'asunto' => '[{{severidad}}] Nueva incidencia {{numero_incidencia}}: {{tipo}}',
                'variables_disponibles' => ['nombre', 'numero_incidencia', 'tipo', 'severidad', 'enlace_registro'],
                'contenido' => <<<'HTML'
<p>Hola <strong>{{nombre}}</strong>,</p>
<p>Se ha registrado una nueva incidencia de seguridad en el sistema.</p>
<p><strong>Numero:</strong> {{numero_incidencia}}<br>
<strong>Tipo:</strong> {{tipo}}<br>
<strong>Severidad:</strong> {{severidad}}</p>
<p>Por favor revisa los detalles de la incidencia y toma las acciones correspondientes:</p>
<p><a href="{{enlace_registro}}">Ver incidencia</a></p>
<p>Recuerda que las incidencias de severidad Alta requieren atencion inmediata segun la politica PSC000-25.</p>
HTML,
            ],
            [
                'slug' => 'ticket_reabierto',
                'nombre' => 'Ticket reabierto',
                'asunto' => 'Ticket {{numero_ticket}} reabierto: {{asunto}}',
                'variables_disponibles' => ['nombre', 'numero_ticket', 'asunto', 'quien_reabrio', 'motivo', 'enlace_ticket'],
                'contenido' => <<<'HTML'
<p>Hola <strong>{{nombre}}</strong>,</p>
<p>El ticket <strong>{{numero_ticket}}</strong> ha sido <strong>reabierto</strong> por <strong>{{quien_reabrio}}</strong>.</p>
<p><strong>Asunto:</strong> {{asunto}}<br>
<strong>Motivo de reapertura:</strong> {{motivo}}</p>
<p>Por favor revisa el ticket y toma las acciones necesarias:</p>
<p><a href="{{enlace_ticket}}">Ver ticket</a></p>
HTML,
            ],
            [
                'slug' => 'nueva_politica',
                'nombre' => 'Nueva politica publicada',
                'asunto' => 'Nueva politica: {{titulo_politica}} (v{{version}})',
                'variables_disponibles' => ['nombre', 'titulo_politica', 'version', 'enlace_plataforma'],
                'contenido' => <<<'HTML'
<p>Hola <strong>{{nombre}}</strong>,</p>
<p>Se ha publicado una nueva politica de seguridad que requiere tu aceptacion:</p>
<p><strong>{{titulo_politica}}</strong> (version {{version}})</p>
<p>Es necesario que leas y aceptes esta politica para poder seguir usando la plataforma.</p>
<p><a href="{{enlace_plataforma}}">Ir a aceptar</a></p>
HTML,
            ],
            [
                'slug' => 'activo-por-vencer',
                'nombre' => 'Activo digital por vencer',
                'asunto' => 'Activo digital por vencer en {{dias}} dias: {{nombre_activo}}',
                'variables_disponibles' => ['nombre', 'nombre_activo', 'codigo', 'proveedor', 'fecha_vencimiento', 'dias', 'empresa'],
                'contenido' => <<<'HTML'
<p>Hola <strong>{{nombre}}</strong>,</p>
<p>El siguiente activo digital esta por vencer y requiere renovacion:</p>
<p><strong>{{nombre_activo}}</strong> ({{codigo}})<br>
<strong>Proveedor:</strong> {{proveedor}}<br>
<strong>Vence:</strong> {{fecha_vencimiento}} (en {{dias}} dias)</p>
<p>Por favor coordina la renovacion o el pago para evitar la suspension del servicio.</p>
HTML,
            ],
            [
                'slug' => 'activo-vencido',
                'nombre' => 'Activo digital vencido',
                'asunto' => 'Activo digital VENCIDO: {{nombre_activo}}',
                'variables_disponibles' => ['nombre', 'nombre_activo', 'codigo', 'proveedor', 'fecha_vencimiento', 'empresa'],
                'contenido' => <<<'HTML'
<p>Hola <strong>{{nombre}}</strong>,</p>
<p>El siguiente activo digital ha <strong>vencido</strong>:</p>
<p><strong>{{nombre_activo}}</strong> ({{codigo}})<br>
<strong>Proveedor:</strong> {{proveedor}}<br>
<strong>Vencio el:</strong> {{fecha_vencimiento}}</p>
<p>El estado de la cuenta se marco como <strong>Vencido</strong>. Regulariza el pago a la brevedad.</p>
HTML,
            ],
        ];
    }
}
