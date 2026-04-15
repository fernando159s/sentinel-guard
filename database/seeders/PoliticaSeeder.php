<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Politica;
use Illuminate\Database\Seeder;

class PoliticaSeeder extends Seeder
{
    public function run(): void
    {
        $empresas = Empresa::all();

        if ($empresas->isEmpty()) {
            $this->command->warn('No hay empresas registradas. Ejecute EmpresaSeeder primero.');

            return;
        }

        $politicas = $this->getPoliticas();

        foreach ($empresas as $empresa) {
            foreach ($politicas as $data) {
                Politica::firstOrCreate(
                    [
                        'empresa_id' => $empresa->id,
                        'slug' => \Illuminate\Support\Str::slug($data['titulo']),
                    ],
                    array_merge($data, ['empresa_id' => $empresa->id])
                );
            }

            $this->command->info("  + " . count($politicas) . " politicas creadas para: {$empresa->razon_social}");
        }

        $this->command->info('PoliticaSeeder completado: ' . count($politicas) . ' politicas por empresa.');
    }

    private function getPoliticas(): array
    {
        return [
            // ──────────────────────────────────────────────────────────
            // 1. POLITICA DE LINEA BASE DE SEGURIDAD (SGSI-PL-0038)
            // ──────────────────────────────────────────────────────────
            [
                'titulo' => 'Politica de Linea Base de Seguridad',
                'contenido' => '<h2>Politica de Linea Base de Seguridad</h2><p><strong>Codigo:</strong> SGSI-PL-0038 | <strong>Version:</strong> 1 | <strong>Fecha:</strong> 10/01/2026</p>'
                    . '<h3>1. Introduccion</h3>'
                    . '<p>El estudio de abogados gestiona informacion sensible y confidencial que demanda una proteccion solida frente a amenazas ciberneticas. En este contexto, la politica de ciberseguridad constituye un pilar esencial para asegurar que los sistemas, redes, dispositivos y datos del estudio operen bajo estandares elevados de seguridad. El presente documento define medidas tecnicas, procedimientos y buenas practicas que deben adoptarse para reducir el riesgo de accesos no autorizados, perdida de informacion y otras amenazas que puedan afectar la integridad operativa del estudio.</p>'
                    . '<p>En el Estudio de Abogados Palacios, la gestion documental se soporta en un Servidor Local para edicion y administracion de documentos, por lo que la politica prioriza la seguridad de accesos, permisos, registros de auditoria y continuidad operativa en esta infraestructura.</p>'
                    . '<h3>2. Objetivo</h3>'
                    . '<p>Proteger los sistemas y datos criticos del estudio mediante la aplicacion de medidas tecnicas y organizativas minimas, tanto a nivel de hardware como de software. Garantizar que los equipos y aplicaciones utilizados se mantengan actualizados y operen con un desempeno adecuado. Promover la vida util del hardware, asegurando que los dispositivos puedan soportar futuras actualizaciones.</p>'
                    . '<h3>3. Alcance</h3>'
                    . '<ul><li><strong>Equipos de trabajo:</strong> Todos los dispositivos utilizados por el personal (computadoras de escritorio y laptops). Deben cumplir los requerimientos minimos de hardware.</li>'
                    . '<li><strong>Redes y comunicaciones:</strong> Infraestructura de red incluyendo routers, switches y puntos de acceso.</li>'
                    . '<li><strong>Software y aplicaciones:</strong> Todo el software instalado: sistemas operativos, antivirus y herramientas de productividad. Se prioriza el uso de software con licencias vigentes.</li>'
                    . '<li><strong>Usuarios y empleados:</strong> Capacitacion y sensibilizacion del personal en buenas practicas de ciberseguridad.</li></ul>'
                    . '<h3>4. Requerimientos Minimos de Hardware</h3>'
                    . '<h4>4.1 Procesador</h4>'
                    . '<p>Se recomienda como minimo procesadores Intel i3-12100 o AMD Ryzen 3-4100. Se sugiere optar por Intel i5 o Ryzen 5 cuando sea posible.</p>'
                    . '<h4>4.2 Almacenamiento</h4>'
                    . '<p>Minimo 512 GB de almacenamiento en formato SSD.</p>'
                    . '<h4>4.3 Memoria RAM</h4>'
                    . '<p>Al menos 8 GB de memoria RAM. En perfiles con multitarea intensiva se recomienda ampliar a 16 GB.</p>'
                    . '<h3>5. Requerimientos Minimos de Software</h3>'
                    . '<h4>5.1 Sistema Operativo</h4>'
                    . '<p>Windows 10 Pro o Windows 11 Pro (las ediciones Pro incorporan herramientas avanzadas de seguridad, administracion y control de actualizaciones).</p>'
                    . '<h4>5.2 Software Preinstalado</h4>'
                    . '<ul><li><strong>Adobe Acrobat Reader:</strong> Visualizacion y anotaciones sobre documentos PDF.</li>'
                    . '<li><strong>Microsoft Office 365:</strong> Suite de productividad con colaboracion en tiempo real y cifrado de datos.</li>'
                    . '<li><strong>Norton:</strong> Proteccion contra malware, phishing. Incluye Firewall inteligente, prevencion de intrusiones y LiveUpdate.</li>'
                    . '<li><strong>Outlook:</strong> Gestion de correo, calendarios y contactos con cifrado de mensajes.</li>'
                    . '<li><strong>Google Chrome:</strong> Navegador web con proteccion contra phishing y malware.</li></ul>'
                    . '<h3>6. Estandares Minimos para Equipos de Red</h3>'
                    . '<h4>6.1 Router</h4>'
                    . '<ul><li>Firewall incorporado para bloquear trafico no autorizado.</li>'
                    . '<li>QoS (Calidad de Servicio) para priorizar trafico de aplicaciones criticas.</li></ul>'
                    . '<h4>6.2 Switch</h4>'
                    . '<ul><li>Minimo 24 puertos considerando ampliaciones futuras.</li>'
                    . '<li>Soporte para VLANs para segmentar la red.</li>'
                    . '<li>QoS para priorizar trafico de servicios criticos.</li></ul>'
                    . '<h3>7. Mantenimiento y Auditoria</h3>'
                    . '<ul><li>Parches de seguridad de forma automatica, programados fuera del horario laboral.</li>'
                    . '<li>Ejecucion regular de LiveUpdate de Norton en las 9 terminales.</li>'
                    . '<li>Auditorias de seguridad semestrales y extraordinarias ante cambios relevantes.</li></ul>',
                'version' => '1.0',
                'obligatoria' => true,
                'activa' => true,
                'es_nda' => false,
                'vigencia_meses' => null,
            ],

            // ──────────────────────────────────────────────────────────
            // 2. POLITICA DE BLOQUEO DE CORREOS MASIVOS (PSC000-35)
            // ──────────────────────────────────────────────────────────
            [
                'titulo' => 'Politica de Bloqueo de Correos Masivos',
                'contenido' => '<h2>Politica de Bloqueo de Correos Masivos</h2><p><strong>Codigo:</strong> PSC000-35 | <strong>Version:</strong> 01 | <strong>Fecha:</strong> 05.01.2024</p>'
                    . '<h3>1. Objetivo</h3>'
                    . '<p>Establecer los lineamientos para el bloqueo y control de correos electronicos masivos no autorizados (spam), con el fin de proteger la infraestructura de comunicaciones del estudio y prevenir amenazas de phishing, malware y otros ataques basados en correo electronico.</p>'
                    . '<h3>2. Alcance</h3>'
                    . '<p>Esta politica aplica a todos los usuarios del sistema de correo electronico del estudio, incluyendo cuentas corporativas y dispositivos desde los cuales se accede al correo.</p>'
                    . '<h3>3. Responsables</h3>'
                    . '<ul><li>Gerente General</li><li>Coordinador SGS</li><li>Todo el personal</li></ul>'
                    . '<h3>4. Lineamientos</h3>'
                    . '<ul><li>Se prohibe el envio de correos masivos no autorizados desde cuentas corporativas.</li>'
                    . '<li>Todo correo masivo debe ser autorizado por el Coordinador SGS.</li>'
                    . '<li>Se deben configurar filtros antispam en el servidor de correo.</li>'
                    . '<li>Los usuarios deben reportar correos sospechosos al Coordinador SGS.</li>'
                    . '<li>Se realizaran revisiones periodicas de las listas de bloqueo y filtros.</li>'
                    . '<li>Queda prohibido suscribirse a listas de distribucion no relacionadas con el trabajo usando el correo corporativo.</li></ul>'
                    . '<h3>5. Sanciones</h3>'
                    . '<p>El incumplimiento de esta politica sera sancionado de acuerdo con el reglamento interno de trabajo y la legislacion vigente.</p>',
                'version' => '1.0',
                'obligatoria' => true,
                'activa' => true,
                'es_nda' => false,
                'vigencia_meses' => null,
            ],

            // ──────────────────────────────────────────────────────────
            // 3. POLITICA DE COMUNICACIONES (SGSI-PR-03)
            // ──────────────────────────────────────────────────────────
            [
                'titulo' => 'Procedimiento de Comunicacion',
                'contenido' => '<h2>Procedimiento de Comunicacion</h2><p><strong>Codigo:</strong> SGSI-PR-03 | <strong>Version:</strong> 01 | <strong>Fecha:</strong> 05.02.2024</p>'
                    . '<h3>1. Objetivo</h3>'
                    . '<p>Establecer la metodologia para recibir, documentar y responder las comunicaciones internas y externas del Sistema de gestion y definir el mecanismo para llevar a cabo la participacion y consulta a los trabajadores.</p>'
                    . '<h3>2. Alcance</h3>'
                    . '<p>Cubre las comunicaciones internas y externas en la organizacion entre diferentes y/o iguales niveles y funciones de la empresa y otras partes interesadas.</p>'
                    . '<h3>3. Responsables</h3>'
                    . '<ul><li>Gerente General</li><li>Jefe de area</li><li>Supervisor</li><li>Coordinador SGS</li></ul>'
                    . '<h3>4. Comunicacion Interna</h3>'
                    . '<p>La organizacion dispone de herramientas de correo electronico para la comunicacion interna entre todos los usuarios, ademas de la comunicacion externa con partes interesadas.</p>'
                    . '<ul><li>Gestion de noticias de interes para la organizacion accesibles a todos los usuarios.</li>'
                    . '<li>Difusion interna de comunicados: Auditoria Interna, Politicas, Objetivos, Planes, Procedimientos, Eventos internos y Novedades.</li>'
                    . '<li>Gestor de correo interno para contacto frecuente entre personal y con personal externo.</li>'
                    . '<li>Canales adicionales: Software interno, Cartas circulares, Reuniones.</li></ul>'
                    . '<h3>5. Comunicacion Externa</h3>'
                    . '<ul><li>Correo electronico: Cada cliente posee el correo del personal asignado. Existe correo generico (info@estudiopalacios.com.pe).</li></ul>',
                'version' => '1.0',
                'obligatoria' => true,
                'activa' => true,
                'es_nda' => false,
                'vigencia_meses' => null,
            ],

            // ──────────────────────────────────────────────────────────
            // 4. POLITICA DE CONTROL DE ACCESO A DISPOSITIVOS (PSC000-28)
            // ──────────────────────────────────────────────────────────
            [
                'titulo' => 'Politica de Control de Acceso a Dispositivos',
                'contenido' => '<h2>Politica de Control de Acceso a Dispositivos</h2><p><strong>Codigo:</strong> PSC000-28 | <strong>Version:</strong> 01 | <strong>Fecha:</strong> 05.01.2024</p>'
                    . '<h3>1. Objetivo</h3>'
                    . '<p>Establecer los controles necesarios para gestionar el acceso a los dispositivos informaticos del estudio, garantizando que solo el personal autorizado pueda utilizar los equipos y acceder a la informacion contenida en ellos.</p>'
                    . '<h3>2. Alcance</h3>'
                    . '<p>Aplica a todos los dispositivos informaticos del estudio: computadoras de escritorio, laptops, tablets, telefonos corporativos y cualquier otro dispositivo que procese informacion del estudio.</p>'
                    . '<h3>3. Lineamientos</h3>'
                    . '<ul><li>Cada usuario tendra credenciales unicas e intransferibles de acceso a su equipo.</li>'
                    . '<li>Las contrasenas deben cumplir con la politica de contrasenas vigente (minimo 8 caracteres, combinacion de mayusculas, minusculas, numeros y caracteres especiales).</li>'
                    . '<li>Los equipos deben configurarse con bloqueo automatico de pantalla despues de 5 minutos de inactividad.</li>'
                    . '<li>Queda prohibido el uso de dispositivos USB no autorizados.</li>'
                    . '<li>Todo acceso a los dispositivos debe quedar registrado mediante logs del sistema.</li>'
                    . '<li>Los dispositivos moviles corporativos deben tener cifrado de disco habilitado.</li>'
                    . '<li>Se prohibe la conexion de dispositivos personales a la red corporativa sin autorizacion.</li></ul>'
                    . '<h3>4. Control de Acceso Fisico</h3>'
                    . '<ul><li>Los equipos deben estar ubicados en areas con acceso controlado.</li>'
                    . '<li>El servidor debe estar en un area restringida con llave.</li>'
                    . '<li>Se debe mantener un registro de acceso a la sala de servidores.</li></ul>',
                'version' => '1.0',
                'obligatoria' => true,
                'activa' => true,
                'es_nda' => false,
                'vigencia_meses' => null,
            ],

            // ──────────────────────────────────────────────────────────
            // 5. POLITICA DE CONTROL DE ACCESO A PAGINAS WEB (PSC000-30)
            // ──────────────────────────────────────────────────────────
            [
                'titulo' => 'Politica de Control de Acceso a Paginas Web No Autorizadas',
                'contenido' => '<h2>Politica de Control de Acceso a Paginas Web No Autorizadas</h2><p><strong>Codigo:</strong> PSC000-30 | <strong>Version:</strong> 01 | <strong>Fecha:</strong> 05.01.2024</p>'
                    . '<h3>1. Objetivo</h3>'
                    . '<p>Establecer los lineamientos para controlar y restringir el acceso a paginas web no autorizadas desde los equipos del estudio, con el fin de prevenir la exposicion a amenazas de seguridad, optimizar el uso del ancho de banda y asegurar la productividad del personal.</p>'
                    . '<h3>2. Alcance</h3>'
                    . '<p>Aplica a todos los dispositivos conectados a la red del estudio y a todo el personal que utilice los recursos de internet del estudio.</p>'
                    . '<h3>3. Lineamientos</h3>'
                    . '<ul><li>Se implementaran filtros de contenido web en el router/firewall del estudio.</li>'
                    . '<li>Las categorias bloqueadas incluyen: redes sociales (salvo autorizacion), sitios de entretenimiento, descarga de software no autorizado, sitios de apuestas, contenido para adultos y sitios de almacenamiento en la nube no corporativos.</li>'
                    . '<li>Las excepciones deben ser solicitadas por escrito al Coordinador SGS y aprobadas por el Gerente General.</li>'
                    . '<li>Se mantendra un registro de los sitios web visitados para auditorias de seguridad.</li>'
                    . '<li>El uso de VPN personales o herramientas para evadir los filtros esta estrictamente prohibido.</li></ul>',
                'version' => '1.0',
                'obligatoria' => true,
                'activa' => true,
                'es_nda' => false,
                'vigencia_meses' => null,
            ],

            // ──────────────────────────────────────────────────────────
            // 6. POLITICA DE CONTROL DE CAMBIOS (PSC000-37)
            // ──────────────────────────────────────────────────────────
            [
                'titulo' => 'Politica de Control de Cambios',
                'contenido' => '<h2>Politica de Control de Cambios</h2><p><strong>Codigo:</strong> PSC000-37 | <strong>Version:</strong> 01 | <strong>Fecha:</strong> 05.01.2024</p>'
                    . '<h3>1. Objetivo</h3>'
                    . '<p>Establecer un procedimiento formal para gestionar los cambios en la infraestructura tecnologica, sistemas de informacion y procesos del estudio, minimizando el riesgo de interrupciones no planificadas y garantizando la continuidad operativa.</p>'
                    . '<h3>2. Alcance</h3>'
                    . '<p>Aplica a todos los cambios en hardware, software, configuraciones de red, politicas de seguridad y procesos criticos del estudio.</p>'
                    . '<h3>3. Proceso de Gestion de Cambios</h3>'
                    . '<ul><li><strong>Solicitud:</strong> Todo cambio debe ser solicitado formalmente mediante el formato de Gestion del Cambio.</li>'
                    . '<li><strong>Evaluacion:</strong> El Coordinador SGS evalua el impacto, riesgos y recursos necesarios.</li>'
                    . '<li><strong>Aprobacion:</strong> Los cambios criticos requieren aprobacion del Gerente General.</li>'
                    . '<li><strong>Implementacion:</strong> Se ejecuta el cambio siguiendo el plan aprobado.</li>'
                    . '<li><strong>Verificacion:</strong> Se verifica que el cambio funciona correctamente y no genera efectos secundarios.</li>'
                    . '<li><strong>Documentacion:</strong> Se documenta el cambio realizado y se actualiza la documentacion afectada.</li></ul>'
                    . '<h3>4. Cambios de Emergencia</h3>'
                    . '<p>En situaciones de emergencia, el cambio puede implementarse con aprobacion verbal del Gerente General, pero debe documentarse formalmente dentro de las 24 horas siguientes.</p>',
                'version' => '1.0',
                'obligatoria' => true,
                'activa' => true,
                'es_nda' => false,
                'vigencia_meses' => null,
            ],

            // ──────────────────────────────────────────────────────────
            // 7. POLITICA DE CONTROL DE ENVIO DE DATOS SENSIBLES (PSC000-29)
            // ──────────────────────────────────────────────────────────
            [
                'titulo' => 'Politica de Control de Envio de Datos Sensibles',
                'contenido' => '<h2>Politica de Control de Envio de Datos Sensibles</h2><p><strong>Codigo:</strong> PSC000-29 | <strong>Version:</strong> 01 | <strong>Fecha:</strong> 05.01.2024</p>'
                    . '<h3>1. Objetivo</h3>'
                    . '<p>Establecer los controles y procedimientos para el envio seguro de datos sensibles y confidenciales, tanto interna como externamente, previniendo la fuga de informacion y garantizando el cumplimiento de la normativa de proteccion de datos personales.</p>'
                    . '<h3>2. Alcance</h3>'
                    . '<p>Aplica a todo el personal del estudio que maneje o transmita informacion clasificada como sensible o confidencial, por cualquier medio electronico o fisico.</p>'
                    . '<h3>3. Clasificacion de Datos</h3>'
                    . '<ul><li><strong>Publico:</strong> Informacion que puede ser divulgada sin restricciones.</li>'
                    . '<li><strong>Interno:</strong> Informacion de uso interno del estudio.</li>'
                    . '<li><strong>Confidencial:</strong> Informacion cuya divulgacion puede causar dano al estudio o sus clientes.</li>'
                    . '<li><strong>Sensible:</strong> Datos personales sensibles segun la Ley de Proteccion de Datos Personales.</li></ul>'
                    . '<h3>4. Lineamientos para el Envio</h3>'
                    . '<ul><li>Los datos sensibles deben enviarse cifrados (archivos protegidos con contrasena como minimo).</li>'
                    . '<li>La contrasena debe comunicarse por un canal diferente al del envio del archivo.</li>'
                    . '<li>Se prohibe el envio de datos sensibles por aplicaciones de mensajeria personal (WhatsApp, Telegram, etc.).</li>'
                    . '<li>Los correos con datos sensibles deben incluir aviso de confidencialidad.</li>'
                    . '<li>Se debe mantener un registro de envios de datos sensibles.</li>'
                    . '<li>Solo el personal autorizado puede enviar datos sensibles fuera del estudio.</li></ul>',
                'version' => '1.0',
                'obligatoria' => true,
                'activa' => true,
                'es_nda' => false,
                'vigencia_meses' => null,
            ],

            // ──────────────────────────────────────────────────────────
            // 8. POLITICA DE GESTION DE ACCESOS A LA RED (PSC000-20)
            // ──────────────────────────────────────────────────────────
            [
                'titulo' => 'Politica de Gestion de Accesos a la Red',
                'contenido' => '<h2>Politica de Gestion de Accesos a la Red</h2><p><strong>Codigo:</strong> PSC000-20 | <strong>Version:</strong> 01 | <strong>Fecha:</strong> 05.01.2024</p>'
                    . '<h3>1. Objetivo</h3>'
                    . '<p>Establecer los lineamientos para la gestion y control de accesos a la red informatica del estudio, asegurando que solo usuarios y dispositivos autorizados puedan conectarse a la infraestructura de red.</p>'
                    . '<h3>2. Alcance</h3>'
                    . '<p>Aplica a toda la infraestructura de red del estudio: red cableada, red inalambrica, VPN y cualquier punto de acceso a la red corporativa.</p>'
                    . '<h3>3. Lineamientos</h3>'
                    . '<ul><li>El acceso a la red debe ser autenticado mediante credenciales unicas.</li>'
                    . '<li>La red inalambrica debe usar cifrado WPA3 o superior.</li>'
                    . '<li>Se implementaran VLANs para segmentar el trafico de red.</li>'
                    . '<li>Los dispositivos de visitantes deben conectarse a una red separada (red de invitados) sin acceso a recursos internos.</li>'
                    . '<li>Se debe mantener un inventario actualizado de todos los dispositivos conectados a la red.</li>'
                    . '<li>El acceso remoto (VPN) requiere autenticacion de dos factores.</li>'
                    . '<li>Se realizaran escaneos periodicos de la red para detectar dispositivos no autorizados.</li></ul>',
                'version' => '1.0',
                'obligatoria' => true,
                'activa' => true,
                'es_nda' => false,
                'vigencia_meses' => null,
            ],

            // ──────────────────────────────────────────────────────────
            // 9. POLITICA DE GESTION DE RIESGOS Y OPORTUNIDADES (SGSI-PR-04)
            // ──────────────────────────────────────────────────────────
            [
                'titulo' => 'Procedimiento de Gestion de Riesgos y Oportunidades',
                'contenido' => '<h2>Procedimiento de Gestion de Riesgos y Oportunidades</h2><p><strong>Codigo:</strong> SGSI-PR-04 | <strong>Version:</strong> 02 | <strong>Fecha:</strong> 06.01.2025</p>'
                    . '<h3>1. Objetivo</h3>'
                    . '<p>Dotar al Estudio Palacios Abogados de una sistematica de gestion de riesgos y oportunidades para asegurar que el SGSI pueda lograr sus resultados previstos, aumentar los efectos deseables, prevenir o reducir efectos indeseados, lograr la mejora continua y evaluar la eficacia de las acciones.</p>'
                    . '<h3>2. Alcance</h3>'
                    . '<p>Toda la organizacion. Determina las cuestiones externas e internas pertinentes para su proposito y direccion estrategica.</p>'
                    . '<h3>3. Proceso</h3>'
                    . '<ul><li>Analisis del contexto interno y externo de la organizacion.</li>'
                    . '<li>Identificacion de riesgos y oportunidades.</li>'
                    . '<li>Analisis de riesgos y oportunidades.</li>'
                    . '<li>Valoracion de los riesgos.</li>'
                    . '<li>Tratamiento de los riesgos y las oportunidades.</li></ul>'
                    . '<h3>4. Analisis de Riesgos</h3>'
                    . '<p>Se determina la Severidad del riesgo mediante la formula: <strong>S = P x C</strong> (Severidad = Probabilidad x Consecuencia).</p>'
                    . '<h4>Categorias de Probabilidad</h4>'
                    . '<ul><li><strong>Casi certeza (5):</strong> 90% a 100% de probabilidad.</li>'
                    . '<li><strong>Probable (4):</strong> 66% a 89%.</li>'
                    . '<li><strong>Moderado (3):</strong> 31% a 65%.</li>'
                    . '<li><strong>Improbable (2):</strong> 11% a 30%.</li>'
                    . '<li><strong>Muy improbable (1):</strong> 1% a 10%.</li></ul>'
                    . '<h4>Categorias de Consecuencia</h4>'
                    . '<ul><li><strong>Catastroficas (5):</strong> Finalizacion de la actividad empresarial.</li>'
                    . '<li><strong>Mayores (4):</strong> Perdidas financieras importantes.</li>'
                    . '<li><strong>Moderadas (3):</strong> Perdidas financieras moderadas.</li>'
                    . '<li><strong>Menores (2):</strong> Perdidas financieras menores.</li>'
                    . '<li><strong>Insignificantes (1):</strong> Sin perdidas financieras.</li></ul>'
                    . '<h3>5. Tratamiento del Riesgo</h3>'
                    . '<p>Se debe actuar sobre riesgos con severidad Extremo o Alto mediante: evitar riesgos, asumir riesgos para perseguir oportunidades, eliminar la fuente, cambiar probabilidad o consecuencias, compartir el riesgo, o mantener el riesgo mediante decisiones informadas.</p>',
                'version' => '2.0',
                'obligatoria' => true,
                'activa' => true,
                'es_nda' => false,
                'vigencia_meses' => null,
            ],

            // ──────────────────────────────────────────────────────────
            // 10. POLITICA DE GESTION DE VULNERABILIDADES (PSC000-15)
            // ──────────────────────────────────────────────────────────
            [
                'titulo' => 'Politica de Gestion de Vulnerabilidades',
                'contenido' => '<h2>Politica de Gestion de Vulnerabilidades</h2><p><strong>Codigo:</strong> PSC000-15 | <strong>Version:</strong> 01 | <strong>Fecha:</strong> 05.01.2024</p>'
                    . '<h3>1. Objetivo</h3>'
                    . '<p>Establecer un proceso sistematico para la identificacion, evaluacion, tratamiento y seguimiento de vulnerabilidades tecnicas en los sistemas de informacion del estudio.</p>'
                    . '<h3>2. Alcance</h3>'
                    . '<p>Aplica a todos los activos tecnologicos del estudio: servidores, estaciones de trabajo, equipos de red, software y aplicaciones.</p>'
                    . '<h3>3. Proceso de Gestion de Vulnerabilidades</h3>'
                    . '<ul><li><strong>Identificacion:</strong> Escaneos periodicos de vulnerabilidades, revision de boletines de seguridad de fabricantes y suscripcion a alertas de seguridad.</li>'
                    . '<li><strong>Evaluacion:</strong> Clasificacion de vulnerabilidades por severidad (critica, alta, media, baja) y evaluacion del impacto potencial.</li>'
                    . '<li><strong>Tratamiento:</strong> Aplicacion de parches de seguridad, actualizaciones de software, reconfiguraciones o implementacion de controles compensatorios.</li>'
                    . '<li><strong>Seguimiento:</strong> Verificacion de la efectividad de las medidas aplicadas y documentacion del proceso.</li></ul>'
                    . '<h3>4. Plazos de Remediacion</h3>'
                    . '<ul><li>Vulnerabilidades criticas: 24 a 48 horas.</li>'
                    . '<li>Vulnerabilidades altas: 7 dias.</li>'
                    . '<li>Vulnerabilidades medias: 30 dias.</li>'
                    . '<li>Vulnerabilidades bajas: proximo ciclo de mantenimiento.</li></ul>',
                'version' => '1.0',
                'obligatoria' => true,
                'activa' => true,
                'es_nda' => false,
                'vigencia_meses' => null,
            ],

            // ──────────────────────────────────────────────────────────
            // 11. POLITICA DE MEJORA CONTINUA (SGSI-PR-05)
            // ──────────────────────────────────────────────────────────
            [
                'titulo' => 'Procedimiento de Mejora Continua',
                'contenido' => '<h2>Procedimiento de Mejora Continua</h2><p><strong>Codigo:</strong> SGSI-PR-05 | <strong>Version:</strong> 01 | <strong>Fecha:</strong> 05.02.2024</p>'
                    . '<h3>1. Objetivo</h3>'
                    . '<p>Establecer, implementar y mantener procedimientos para la identificacion continua de peligros, evaluacion de riesgos y la determinacion de los controles necesarios.</p>'
                    . '<p>Actividades: Identificacion de la Necesidad del Cambio, Obtencion y Analisis de Datos, Establecimiento de Plan de Accion, Seguimiento y Evaluacion de Resultados.</p>'
                    . '<h3>2. Alcance</h3>'
                    . '<p>Toda la organizacion. Determina las cuestiones externas e internas pertinentes para su proposito y direccion estrategica.</p>'
                    . '<h3>3. Proceso</h3>'
                    . '<h4>Etapa 1: Identificacion de Necesidades de Cambio</h4>'
                    . '<p>Conocer por que la organizacion tiene necesidad de cambiar. Se procede a la apertura del INFORME DE MEJORA CAMBIO con la justificacion del motivo.</p>'
                    . '<h4>Etapa 2: Obtencion y Analisis de Datos</h4>'
                    . '<p>Recoleccion de datos para determinar las causas principales. El informe debe incluir: orden de la causa, detalle, porcentaje de ocurrencia del error.</p>'
                    . '<h4>Etapa 3: Plan de Accion</h4>'
                    . '<p>Disenar planes de accion para incidir sobre las causas. Debe incluir: orden de la accion, accion, responsable y periodo.</p>'
                    . '<h4>Etapa 4: Seguimiento del Plan de Accion</h4>'
                    . '<p>Seguimiento mediante graficos diarios y verificacion en el area de trabajo. Incluye: orden de la accion, accion de seguimiento realizada y fecha.</p>',
                'version' => '1.0',
                'obligatoria' => true,
                'activa' => true,
                'es_nda' => false,
                'vigencia_meses' => null,
            ],

            // ──────────────────────────────────────────────────────────
            // 12. POLITICA DE RRHH - SELECCION Y CONTRATACION (SGSI-PR-07)
            // ──────────────────────────────────────────────────────────
            [
                'titulo' => 'Procedimiento de Seleccion y Contratacion de Personal',
                'contenido' => '<h2>Procedimiento de Seleccion y Contratacion de Personal</h2><p><strong>Codigo:</strong> SGSI-PR-07 | <strong>Version:</strong> 01 | <strong>Fecha:</strong> 05.02.2024</p>'
                    . '<h3>1. Objetivo</h3>'
                    . '<p>Garantizar la seleccion e integracion de recursos humanos idoneos que satisfagan las necesidades de los puestos de la organizacion.</p>'
                    . '<h3>2. Alcance</h3>'
                    . '<p>Desde la identificacion de una vacante hasta la integracion del personal nuevo al area solicitante.</p>'
                    . '<h3>3. Proceso</h3>'
                    . '<h4>5.1 Solicitud de Contratacion</h4>'
                    . '<p>El jefe y/o supervisor presenta al Gerente General el formato Solicitud y seleccion de personal, indicando cantidad, puesto y plazo maximo.</p>'
                    . '<h4>5.2 Convocatoria y Perfil de Puesto</h4>'
                    . '<p>El responsable verifica las competencias del puesto. La convocatoria se realiza por: personal propio (promocion), recomendaciones personales, anuncios en internet.</p>'
                    . '<h4>5.3 Evaluacion de Hojas de Vida y Entrevista</h4>'
                    . '<p>Las hojas de vida se comparan con el perfil del puesto. Los postulantes aptos son convocados para entrevista personal. Se realiza entrevista psicologica (resultado enviado solo al Gerente General).</p>'
                    . '<h4>5.4 Evaluacion Medica</h4>'
                    . '<p>Evaluacion medica pre-ocupacional. Si es no apto, se comunican los motivos y se continua con los siguientes postulantes.</p>'
                    . '<h4>5.5 Orientacion y Adiestramiento</h4>'
                    . '<p>Previo al inicio de labores, el Supervisor brinda induccion y adiestramiento segun el formato Orientacion inicial de personal.</p>',
                'version' => '1.0',
                'obligatoria' => true,
                'activa' => true,
                'es_nda' => false,
                'vigencia_meses' => null,
            ],

            // ──────────────────────────────────────────────────────────
            // 13. POLITICA DE SEGURIDAD PARA LA PROTECCION DE DATOS (PSC000-46)
            // ──────────────────────────────────────────────────────────
            [
                'titulo' => 'Politica de Seguridad para la Proteccion de Datos Personales',
                'contenido' => '<h2>Politica de Seguridad para la Proteccion de Datos Personales</h2><p><strong>Codigo:</strong> PSC000-46 | <strong>Version:</strong> 01 | <strong>Fecha:</strong> 05.01.2024</p>'
                    . '<h3>1. Objetivo</h3>'
                    . '<p>Establecer las directrices para la proteccion de datos personales en cumplimiento de la Ley N° 29733 - Ley de Proteccion de Datos Personales y su Reglamento, garantizando los derechos de los titulares de datos personales.</p>'
                    . '<h3>2. Alcance</h3>'
                    . '<p>Aplica a todo tratamiento de datos personales realizado por el estudio, ya sea en formato digital o fisico, incluyendo datos de clientes, empleados, proveedores y cualquier persona cuya informacion sea tratada.</p>'
                    . '<h3>3. Principios</h3>'
                    . '<ul><li><strong>Legalidad:</strong> El tratamiento de datos se realiza conforme a la ley.</li>'
                    . '<li><strong>Consentimiento:</strong> Se obtiene consentimiento libre, previo, expreso e informado del titular.</li>'
                    . '<li><strong>Finalidad:</strong> Los datos se recopilan con fines determinados, explicitos y licitos.</li>'
                    . '<li><strong>Proporcionalidad:</strong> El tratamiento es adecuado, relevante y no excesivo.</li>'
                    . '<li><strong>Calidad:</strong> Los datos deben ser exactos, actualizados y completos.</li>'
                    . '<li><strong>Seguridad:</strong> Se adoptan medidas tecnicas y organizativas para garantizar la seguridad.</li>'
                    . '<li><strong>Nivel de proteccion adecuado:</strong> Para transferencias internacionales se garantiza nivel adecuado.</li></ul>'
                    . '<h3>4. Derechos ARCO</h3>'
                    . '<p>Se garantizan los derechos de Acceso, Rectificacion, Cancelacion y Oposicion de los titulares de datos personales. Las solicitudes se atienden en un plazo maximo de 20 dias habiles.</p>'
                    . '<h3>5. Medidas de Seguridad</h3>'
                    . '<ul><li>Control de acceso a los sistemas que contienen datos personales.</li>'
                    . '<li>Cifrado de datos sensibles en transito y en reposo.</li>'
                    . '<li>Registro de accesos a datos personales.</li>'
                    . '<li>Copias de seguridad periodicas.</li>'
                    . '<li>Destruccion segura de datos cuando ya no sean necesarios.</li></ul>',
                'version' => '1.0',
                'obligatoria' => true,
                'activa' => true,
                'es_nda' => false,
                'vigencia_meses' => null,
            ],

            // ──────────────────────────────────────────────────────────
            // 14. PRUEBA DE PLANES Y RESPUESTA (PSC000-25)
            // ──────────────────────────────────────────────────────────
            [
                'titulo' => 'Procedimiento de Prueba de Planes y Respuesta a Incidentes',
                'contenido' => '<h2>Procedimiento de Prueba de Planes y Respuesta a Incidentes</h2><p><strong>Codigo:</strong> PSC000-25 | <strong>Version:</strong> 01 | <strong>Fecha:</strong> 05.01.2024</p>'
                    . '<h3>1. Objetivo</h3>'
                    . '<p>Establecer el procedimiento para la ejecucion periodica de pruebas del plan de respuesta a incidentes de seguridad de la informacion, verificando la efectividad de los procedimientos y la preparacion del personal.</p>'
                    . '<h3>2. Alcance</h3>'
                    . '<p>Aplica a todos los planes de respuesta a incidentes del SGSI, incluyendo: plan de respuesta a incidentes de seguridad, plan de continuidad del negocio y plan de recuperacion ante desastres.</p>'
                    . '<h3>3. Tipos de Prueba</h3>'
                    . '<ul><li><strong>Ejercicio de mesa (tabletop):</strong> Simulacion teorica de escenarios de incidentes con el equipo de respuesta.</li>'
                    . '<li><strong>Simulacro parcial:</strong> Prueba de componentes especificos del plan (ejemplo: restauracion de backups).</li>'
                    . '<li><strong>Simulacro completo:</strong> Ejecucion integral del plan en condiciones controladas.</li></ul>'
                    . '<h3>4. Frecuencia</h3>'
                    . '<ul><li>Ejercicios de mesa: trimestralmente.</li>'
                    . '<li>Simulacros parciales: semestralmente.</li>'
                    . '<li>Simulacros completos: anualmente.</li></ul>'
                    . '<h3>5. Documentacion</h3>'
                    . '<p>Cada prueba debe documentarse con: fecha, participantes, escenario probado, resultados, lecciones aprendidas y acciones correctivas identificadas.</p>',
                'version' => '1.0',
                'obligatoria' => true,
                'activa' => true,
                'es_nda' => false,
                'vigencia_meses' => null,
            ],

            // ──────────────────────────────────────────────────────────
            // 15. ACUERDO DE CONFIDENCIALIDAD - NDA RRHH
            // ──────────────────────────────────────────────────────────
            [
                'titulo' => 'Acuerdo de Confidencialidad (NDA)',
                'contenido' => "## Acuerdo de Confidencialidad y No Divulgacion\n\n"
                    . "**ESTUDIO PALACIOS ABOGADOS S.A.C.**\n\n"
                    . "Yo, **{nombre_completo}**, identificado(a) con DNI N° **{dni}**, domiciliado(a) en **{direccion}**, "
                    . "telefono **{telefono}**, que ocupo el puesto de **{puesto}** en el Estudio Palacios Abogados S.A.C., "
                    . "declaro y me comprometo a lo siguiente:\n\n"
                    . "### 1. Objeto del Acuerdo\n"
                    . "El presente acuerdo tiene por objeto proteger la informacion confidencial a la que tenga acceso "
                    . "en el ejercicio de mis funciones dentro del Estudio Palacios Abogados S.A.C.\n\n"
                    . "### 2. Definicion de Informacion Confidencial\n"
                    . "Se considera informacion confidencial toda aquella informacion, sea oral, escrita, electronica "
                    . "o en cualquier otro formato, que incluya pero no se limite a:\n"
                    . "- Datos personales de clientes y sus expedientes legales.\n"
                    . "- Estrategias legales y procesales.\n"
                    . "- Informacion financiera del estudio y de sus clientes.\n"
                    . "- Propiedad intelectual y know-how del estudio.\n"
                    . "- Informacion de sistemas de informacion, contrasenas y accesos.\n"
                    . "- Documentos internos, manuales y procedimientos.\n\n"
                    . "### 3. Obligaciones\n"
                    . "- Mantener en estricta confidencialidad toda la informacion a la que tenga acceso.\n"
                    . "- No divulgar, copiar, reproducir ni transmitir informacion confidencial a terceros.\n"
                    . "- Utilizar la informacion exclusivamente para los fines laborales asignados.\n"
                    . "- Devolver toda la informacion y documentacion al termino de la relacion laboral.\n"
                    . "- Notificar inmediatamente cualquier uso no autorizado o divulgacion de informacion.\n\n"
                    . "### 4. Vigencia\n"
                    . "Esta obligacion de confidencialidad subsiste durante la relacion laboral y por un periodo "
                    . "de **dos (2) anos** despues de terminada la misma.\n\n"
                    . "### 5. Sanciones\n"
                    . "El incumplimiento del presente acuerdo dara lugar a las acciones legales correspondientes, "
                    . "incluyendo indemnizacion por danos y perjuicios, sin perjuicio de las sanciones laborales aplicables.\n\n"
                    . "### 6. Prohibiciones Especiales\n"
                    . "- Esta prohibido obtener copias de cualquier expediente asignado al estudio.\n"
                    . "- Esta prohibido sacar documentos fuera de las instalaciones del estudio, salvo para su "
                    . "presentacion al juzgado respectivo, debiendo ser devueltos al estudio o cliente.\n\n"
                    . "Firmo el presente acuerdo en senal de conformidad.",
                'version' => '2.0',
                'obligatoria' => true,
                'activa' => true,
                'es_nda' => true,
                'vigencia_meses' => 24,
            ],

            // ──────────────────────────────────────────────────────────
            // 16. ANEXO - POLITICA DE ANTIVIRUS
            // ──────────────────────────────────────────────────────────
            [
                'titulo' => 'Politica de Antivirus y Proteccion contra Malware',
                'contenido' => '<h2>Politica de Antivirus y Proteccion contra Malware</h2><p><strong>Tipo:</strong> Anexo SGSI</p>'
                    . '<h3>1. Objetivo</h3>'
                    . '<p>Establecer los lineamientos para la proteccion de los equipos del estudio contra software malicioso (malware), incluyendo virus, troyanos, ransomware, spyware y otras amenazas.</p>'
                    . '<h3>2. Software Antivirus Autorizado</h3>'
                    . '<p>El estudio utiliza Norton como solucion de seguridad corporativa. Todo equipo del estudio debe tener Norton instalado y activo con las siguientes funcionalidades habilitadas:</p>'
                    . '<ul><li>Antivirus y proteccion en tiempo real.</li>'
                    . '<li>Firewall inteligente.</li>'
                    . '<li>Prevencion de intrusiones.</li>'
                    . '<li>Prevencion de puntos vulnerables.</li>'
                    . '<li>Web segura y extensiones de navegador.</li>'
                    . '<li>LiveUpdate (actualizacion automatica de definiciones).</li></ul>'
                    . '<h3>3. Lineamientos</h3>'
                    . '<ul><li>Norton debe estar configurado para ejecutar LiveUpdate automaticamente al menos una vez al dia.</li>'
                    . '<li>Se deben realizar analisis completos del sistema al menos una vez por semana.</li>'
                    . '<li>Ningun usuario puede desactivar o modificar la configuracion del antivirus.</li>'
                    . '<li>Los archivos en cuarentena deben ser revisados por el Coordinador SGS antes de ser eliminados o restaurados.</li>'
                    . '<li>Se debe revisar el Historial de seguridad de Norton periodicamente como evidencia de auditoria.</li>'
                    . '<li>Esta prohibido instalar software antivirus adicional o alternativo sin autorizacion.</li></ul>',
                'version' => '1.0',
                'obligatoria' => true,
                'activa' => true,
                'es_nda' => false,
                'vigencia_meses' => null,
            ],

            // ──────────────────────────────────────────────────────────
            // 17. ANEXO - POLITICA DE CAMBIO DE MFA
            // ──────────────────────────────────────────────────────────
            [
                'titulo' => 'Politica de Autenticacion Multifactor (MFA)',
                'contenido' => '<h2>Politica de Autenticacion Multifactor (MFA)</h2><p><strong>Tipo:</strong> Anexo SGSI</p>'
                    . '<h3>1. Objetivo</h3>'
                    . '<p>Establecer los lineamientos para la implementacion y gestion de la autenticacion multifactor (MFA) en los sistemas del estudio, agregando una capa adicional de seguridad mas alla de la contrasena.</p>'
                    . '<h3>2. Alcance</h3>'
                    . '<p>Aplica a todos los sistemas criticos del estudio que soporten MFA: correo corporativo, acceso a servidor, sistemas en la nube y VPN.</p>'
                    . '<h3>3. Lineamientos</h3>'
                    . '<ul><li>Todo usuario debe tener MFA habilitado en su cuenta de correo corporativo.</li>'
                    . '<li>El segundo factor preferido es una aplicacion de autenticacion (Microsoft Authenticator, Google Authenticator).</li>'
                    . '<li>Se permite SMS como segundo factor solo como respaldo si la aplicacion no esta disponible.</li>'
                    . '<li>Los codigos de recuperacion deben almacenarse de forma segura y no compartirse.</li>'
                    . '<li>El cambio o desactivacion del MFA debe ser autorizado por el Coordinador SGS.</li>'
                    . '<li>En caso de perdida del dispositivo de autenticacion, se debe notificar inmediatamente al Coordinador SGS.</li></ul>'
                    . '<h3>4. Procedimiento de Cambio de MFA</h3>'
                    . '<ul><li>Solicitar el cambio al Coordinador SGS indicando el motivo.</li>'
                    . '<li>Verificar la identidad del solicitante de forma presencial.</li>'
                    . '<li>Desactivar el MFA anterior y configurar el nuevo dispositivo.</li>'
                    . '<li>Registrar el cambio en el log de seguridad.</li></ul>',
                'version' => '1.0',
                'obligatoria' => true,
                'activa' => true,
                'es_nda' => false,
                'vigencia_meses' => null,
            ],

            // ──────────────────────────────────────────────────────────
            // 18. MANUAL DE SEGURIDAD DE LA INFORMACION
            // ──────────────────────────────────────────────────────────
            [
                'titulo' => 'Manual del Sistema de Gestion de Seguridad de la Informacion',
                'contenido' => '<h2>Manual del Sistema de Gestion de Seguridad de la Informacion</h2><p><strong>Codigo:</strong> SGSI-MA-01 | <strong>Version:</strong> 02 | <strong>Fecha:</strong> 06.01.2025</p>'
                    . '<h3>1. Objetivo</h3>'
                    . '<p>Establecer, implementar, mantener y mejorar continuamente el Sistema de Gestion de Seguridad de la Informacion (SGSI) del Estudio Palacios Abogados S.A.C., asegurando la confidencialidad, integridad y disponibilidad de la informacion.</p>'
                    . '<h3>2. Alcance</h3>'
                    . '<p>El SGSI aplica a todos los procesos, areas y personal del Estudio Palacios Abogados S.A.C., incluyendo la informacion en formato fisico y digital, los sistemas de informacion, la infraestructura tecnologica y las comunicaciones.</p>'
                    . '<h3>3. Politica de Seguridad de la Informacion</h3>'
                    . '<p>El Estudio Palacios Abogados S.A.C. se compromete a proteger el recurso informacion de una amplia gama de amenazas, con el fin de asegurar la continuidad del negocio, minimizar el dano y cumplir su mision y objetivos estrategicos.</p>'
                    . '<h3>4. Estructura Organizacional del SGSI</h3>'
                    . '<ul><li><strong>Gerente General:</strong> Responsable de la aprobacion y revision del SGSI.</li>'
                    . '<li><strong>Coordinador SGS:</strong> Responsable de la implementacion, mantenimiento y mejora continua del SGSI.</li>'
                    . '<li><strong>Jefes de Area:</strong> Responsables de la implementacion de controles en sus respectivas areas.</li>'
                    . '<li><strong>Todo el personal:</strong> Responsable de cumplir con las politicas y procedimientos del SGSI.</li></ul>'
                    . '<h3>5. Gestion de Activos de Informacion</h3>'
                    . '<p>Todos los activos de informacion deben ser identificados, clasificados y protegidos de acuerdo con su nivel de sensibilidad: Publico, Interno, Confidencial, Sensible.</p>'
                    . '<h3>6. Control de Acceso</h3>'
                    . '<p>El acceso a los sistemas de informacion se otorga basado en el principio de minimo privilegio. Todo acceso debe ser autorizado, registrado y revisado periodicamente.</p>'
                    . '<h3>7. Gestion de Incidentes</h3>'
                    . '<p>Todo incidente de seguridad debe ser reportado, registrado, investigado y resuelto. Se mantiene un proceso de lecciones aprendidas para prevenir la recurrencia.</p>'
                    . '<h3>8. Continuidad del Negocio</h3>'
                    . '<p>Se mantienen planes de continuidad del negocio y recuperacion ante desastres, los cuales son probados periodicamente.</p>'
                    . '<h3>9. Cumplimiento</h3>'
                    . '<p>El SGSI cumple con la legislacion vigente, incluyendo la Ley de Proteccion de Datos Personales (Ley 29733) y los requisitos contractuales de los clientes.</p>',
                'version' => '2.0',
                'obligatoria' => true,
                'activa' => true,
                'es_nda' => false,
                'vigencia_meses' => null,
            ],

            // ──────────────────────────────────────────────────────────
            // 19. MANUAL DE ORGANIZACION Y FUNCIONES (MOF)
            // ──────────────────────────────────────────────────────────
            [
                'titulo' => 'Manual de Organizacion y Funciones (MOF)',
                'contenido' => '<h2>Manual de Organizacion y Funciones - MOF</h2><p><strong>Codigo:</strong> SGSI-MA-03 | <strong>Version:</strong> 01 | <strong>Fecha:</strong> 05.02.2024</p>'
                    . '<h3>1. Objetivo</h3>'
                    . '<p>Definir las responsabilidades, funciones, autoridad e interrelaciones de las areas de puestos del estudio de abogados.</p>'
                    . '<h3>2. Alcance</h3>'
                    . '<p>Toda la organizacion. Comprende las responsabilidades, autoridad e interrelaciones del personal que dirige, realiza y verifica los servicios que brinda el estudio.</p>'
                    . '<h3>3. Descripcion de Funciones</h3>'
                    . '<h4>3.1 Gerente General</h4>'
                    . '<p>Reporta a: Junta de Socios. Areas dependientes: Contabilidad, RRHH, Administracion, Area de Cobranza, Sistemas de Gestion.</p>'
                    . '<ul><li>Proveer al personal con implementos necesarios.</li>'
                    . '<li>Realizar coordinaciones con empresas-clientes.</li>'
                    . '<li>Revisar el plan de capacitacion.</li>'
                    . '<li>Asegurar que se establezca y mantenga el SGSI.</li>'
                    . '<li>Programar auditorias internas.</li></ul>'
                    . '<h4>3.2 Contador</h4>'
                    . '<p>Reporta a: Gerente General. Area: Contabilidad.</p>'
                    . '<ul><li>Llevar la contabilidad de la empresa.</li>'
                    . '<li>Gestionar polizas de seguro.</li>'
                    . '<li>Hacer efectiva la remuneracion del personal.</li>'
                    . '<li>Convocar requerimientos de personal.</li></ul>'
                    . '<h4>3.3 Asistente de Recursos Humanos</h4>'
                    . '<p>Reporta a: Gerente General y Contador. Area: RRHH.</p>'
                    . '<ul><li>Aplicar instrumentos de registro de informacion de cargo.</li>'
                    . '<li>Verificar referencias de aspirantes.</li>'
                    . '<li>Registrar asistencia del personal.</li>'
                    . '<li>Chequear control de asistencia y detectar fallas.</li></ul>'
                    . '<h4>3.4 Auxiliar Administrativo</h4>'
                    . '<p>Reporta a: Gerente General y Contador. Area: Administracion.</p>'
                    . '<ul><li>Preparar y organizar informacion.</li>'
                    . '<li>Atender llamadas telefonicas.</li>'
                    . '<li>Apoyar en tramites administrativos.</li></ul>'
                    . '<h4>3.5 Abogado Senior</h4>'
                    . '<p>Reporta a: Gerente General. Area: Cobranzas.</p>'
                    . '<ul><li>Planeamiento, direccion y ejecucion de actividades juridicas.</li>'
                    . '<li>Supervisar la labor del asesor de servicios.</li>'
                    . '<li>Analizar expedientes, contestar demandas y presentar recursos.</li></ul>'
                    . '<h4>3.6 Asesor de Servicios</h4>'
                    . '<p>Reporta a: Gerente General y Abogado Senior. Area: Cobranzas.</p>'
                    . '<ul><li>Realizar cobranza para clientes (bancos).</li>'
                    . '<li>Responder llamadas entrantes y ayudar a clientes.</li>'
                    . '<li>Proporcionar servicio al cliente personalizado.</li></ul>'
                    . '<h4>3.7 Coordinador SGS</h4>'
                    . '<p>Reporta a: Gerente General. Area: Sistema de Gestion.</p>'
                    . '<ul><li>Elaborar, difundir y velar el cumplimiento del plan de Calidad, SST y Seguridad de la Informacion.</li>'
                    . '<li>Organizar y evaluar cursos de capacitacion.</li>'
                    . '<li>Estar preparado para auditorias internas y externas.</li></ul>'
                    . '<h3>4. Nota General</h3>'
                    . '<p>Todo el personal: Esta prohibido obtener copias de cualquier expediente asignado al estudio y prohibido sacar documentos fuera de las instalaciones, salvo para presentacion al juzgado respectivo.</p>',
                'version' => '1.0',
                'obligatoria' => true,
                'activa' => true,
                'es_nda' => false,
                'vigencia_meses' => null,
            ],

            // ──────────────────────────────────────────────────────────
            // 20. EVIDENCIA - LISTADO DE ACTIVOS DE LA EMPRESA (SGSI-FOR-011)
            // ──────────────────────────────────────────────────────────
            [
                'titulo' => 'Formato de Listado de Activos de la Empresa',
                'contenido' => '<h2>Listado de Activos de la Empresa</h2><p><strong>Codigo:</strong> SGSI-FOR-011 | <strong>Version:</strong> 01 | <strong>Fecha:</strong> 05.01.2024</p>'
                    . '<h3>Descripcion</h3>'
                    . '<p>Este formato registra el inventario completo de activos informaticos de la empresa, incluyendo equipos de computo, su codigo interno, direccion MAC y estado (Bueno/Mantenimiento).</p>'
                    . '<h3>Campos del Formato</h3>'
                    . '<ul><li><strong>Item:</strong> Numero correlativo.</li>'
                    . '<li><strong>Cantidad:</strong> Unidades del activo.</li>'
                    . '<li><strong>Descripcion:</strong> Nombre descriptivo del equipo (ej. PC Gerente General, PC Asesor 01).</li>'
                    . '<li><strong>Codigo Interno:</strong> Codigo de identificacion del equipo (ej. PC 01, PC 02).</li>'
                    . '<li><strong>Estado:</strong> B (Bueno) o M (Mantenimiento).</li>'
                    . '<li><strong>Serie - MAC:</strong> Direccion MAC del equipo.</li></ul>'
                    . '<h3>Responsable</h3>'
                    . '<p>Elaborado por: Coordinador SGS. Aprobado por: Gerente General.</p>',
                'version' => '1.0',
                'obligatoria' => false,
                'activa' => true,
                'es_nda' => false,
                'vigencia_meses' => null,
            ],

            // ──────────────────────────────────────────────────────────
            // 21. EVIDENCIA - CAMBIO DE PASSWORDS (SGSI-FOR-013)
            // ──────────────────────────────────────────────────────────
            [
                'titulo' => 'Formato de Registro de Cambio de Contrasenas',
                'contenido' => '<h2>Registro de Cambio de Contrasenas</h2><p><strong>Codigo:</strong> SGSI-FOR-013 | <strong>Version:</strong> 01 | <strong>Fecha:</strong> 05.01.2024</p>'
                    . '<h3>Descripcion</h3>'
                    . '<p>Este formato registra mensualmente el cambio de contrasenas de todos los equipos del estudio, como evidencia de cumplimiento de la politica de seguridad. Se debe completar cada mes indicando si se realizo el cambio de contrasena en cada equipo.</p>'
                    . '<h3>Campos del Formato</h3>'
                    . '<ul><li><strong>Fecha:</strong> Fecha del registro de cambio.</li>'
                    . '<li><strong>Nombre del Responsable / Puesto:</strong> Coordinador SGS responsable de la supervision.</li>'
                    . '<li><strong>Descripcion:</strong> Nombre del equipo (ej. PC Gerente General).</li>'
                    . '<li><strong>Serie - MAC:</strong> Direccion MAC del equipo.</li>'
                    . '<li><strong>Codigo Interno:</strong> Codigo del equipo (ej. PC 01).</li>'
                    . '<li><strong>Cambio de Contrasena (SI/NO):</strong> Confirmacion del cambio realizado.</li>'
                    . '<li><strong>Firma Usuario:</strong> Firma del usuario que confirma el cambio.</li></ul>'
                    . '<h3>Frecuencia</h3>'
                    . '<p>Mensual. Se debe completar el primer dia habil de cada mes.</p>',
                'version' => '1.0',
                'obligatoria' => false,
                'activa' => true,
                'es_nda' => false,
                'vigencia_meses' => null,
            ],

            // ──────────────────────────────────────────────────────────
            // 22. EVIDENCIA - PROGRAMA ANUAL DE CAPACITACIONES (SGSI-PGR-001)
            // ──────────────────────────────────────────────────────────
            [
                'titulo' => 'Programa Anual de Capacitaciones SGSI',
                'contenido' => '<h2>Programa Anual de Capacitaciones SGSI</h2><p><strong>Codigo:</strong> SGSI-PGR-001 | <strong>Version:</strong> 1 | <strong>Fecha:</strong> 05.01.2024</p>'
                    . '<h3>Datos del Empleador</h3>'
                    . '<p>Estudio Palacios Abogados SAC | RUC: 20454292295 | Cal. San Pedro #100E, Arequipa | 6 trabajadores</p>'
                    . '<h3>Compromiso de la Politica</h3>'
                    . '<p>Proteger el recurso informacion de una amplia gama de amenazas, con el fin de asegurar la continuidad del negocio, minimizar el dano y cumplir su mision y objetivos estrategicos.</p>'
                    . '<h3>Objetivo General</h3>'
                    . '<p>Brindar a nuestro personal el conocimiento teorico y practico que ayude en el logro de nuestros objetivos.</p>'
                    . '<h3>Programa de Capacitaciones 2025</h3>'
                    . '<ol><li><strong>Enero:</strong> Capacitacion Sistema de Gestion en la Seguridad de la Informacion.</li>'
                    . '<li><strong>Febrero:</strong> Capacitacion Gestion por Procesos.</li>'
                    . '<li><strong>Marzo:</strong> Manual del Sistema de Gestion de Seguridad de la Informacion.</li>'
                    . '<li><strong>Abril:</strong> Identificacion y Analisis de Riesgos.</li>'
                    . '<li><strong>Mayo:</strong> Capacitacion en Mejora Continua.</li>'
                    . '<li><strong>Junio:</strong> Capacitacion Atencion al Cliente y Manejo de Conflictos.</li>'
                    . '<li><strong>Julio:</strong> Gestion de la Calidad.</li>'
                    . '<li><strong>Agosto:</strong> Practica de Identificacion y Analisis de Riesgos.</li>'
                    . '<li><strong>Septiembre:</strong> Manual del Sistema de Gestion de Seguridad de la Informacion.</li>'
                    . '<li><strong>Octubre:</strong> Ciberseguridad.</li>'
                    . '<li><strong>Noviembre:</strong> Amenazas Informaticas.</li>'
                    . '<li><strong>Diciembre:</strong> Codigo de Etica.</li></ol>'
                    . '<p>Responsable de ejecucion: Coordinador SGSI. Meta: 100% de capacitaciones ejecutadas.</p>',
                'version' => '1.0',
                'obligatoria' => false,
                'activa' => true,
                'es_nda' => false,
                'vigencia_meses' => null,
            ],

            // ──────────────────────────────────────────────────────────
            // 23. EVIDENCIA - REGISTRO DE COPIA DE SEGURIDAD (SGSI-FOR-012)
            // ──────────────────────────────────────────────────────────
            [
                'titulo' => 'Formato de Registro de Copia de Seguridad',
                'contenido' => '<h2>Registro de Copia de Seguridad</h2><p><strong>Codigo:</strong> SGSI-FOR-012 | <strong>Version:</strong> 01 | <strong>Fecha:</strong> 05.01.2024</p>'
                    . '<h3>Descripcion</h3>'
                    . '<p>Este formato registra la realizacion de copias de seguridad de los equipos del estudio, verificando que cada equipo tiene su backup actualizado y documentando el dispositivo utilizado para el respaldo.</p>'
                    . '<h3>Campos del Formato</h3>'
                    . '<ul><li><strong>Fecha:</strong> Fecha de realizacion de la copia.</li>'
                    . '<li><strong>Nombre del Responsable / Puesto:</strong> Coordinador SGS.</li>'
                    . '<li><strong>Descripcion:</strong> Nombre del equipo.</li>'
                    . '<li><strong>Serie - MAC:</strong> Direccion MAC del equipo.</li>'
                    . '<li><strong>Codigo Interno:</strong> Codigo del equipo.</li>'
                    . '<li><strong>Estado Copia de Seguridad (SI/NO):</strong> Confirmacion de que la copia fue realizada.</li>'
                    . '<li><strong>Dispositivo de Copia de Seguridad:</strong> Medio utilizado (ej. Disco Duro Externo Backup 001).</li></ul>'
                    . '<h3>Dispositivo de Respaldo</h3>'
                    . '<p>Disco Duro Externo Backup 001 asignado para las copias de seguridad de todos los equipos del estudio.</p>',
                'version' => '1.0',
                'obligatoria' => false,
                'activa' => true,
                'es_nda' => false,
                'vigencia_meses' => null,
            ],

            // ──────────────────────────────────────────────────────────
            // 24. EVIDENCIA - MATRIZ DE RIESGOS (SGSI-FOR-013)
            // ──────────────────────────────────────────────────────────
            [
                'titulo' => 'Matriz de Riesgos de Seguridad de la Informacion',
                'contenido' => '<h2>Matriz de Riesgos</h2><p><strong>Codigo:</strong> SGSI-FOR-013 | <strong>Version:</strong> 01 | <strong>Fecha:</strong> 05.01.2024</p>'
                    . '<h3>Descripcion</h3>'
                    . '<p>Matriz que identifica los principales activos de informacion del estudio, las amenazas asociadas, vulnerabilidades, impacto, probabilidad, nivel de riesgo y controles aplicables.</p>'
                    . '<h3>Riesgos Identificados</h3>'
                    . '<table><thead><tr><th>Activo</th><th>Amenaza</th><th>Vulnerabilidad</th><th>Impacto</th><th>Probabilidad</th><th>Nivel</th><th>Controles</th></tr></thead><tbody>'
                    . '<tr><td>Informacion de clientes</td><td>Acceso no autorizado</td><td>Contrasenas debiles / falta de cifrado</td><td>Alto</td><td>Alto</td><td>Critico</td><td>Control de acceso, Cifrado, Proteccion comunicaciones</td></tr>'
                    . '<tr><td>Sistema de gestion de casos</td><td>Falla de sistema / perdida de datos</td><td>No se realiza respaldo periodico</td><td>Alto</td><td>Medio</td><td>Alto</td><td>Copias de respaldo, Continuidad del negocio</td></tr>'
                    . '<tr><td>Correo electronico corporativo</td><td>Phishing o malware</td><td>Falta de capacitacion / filtros de seguridad</td><td>Alto</td><td>Alto</td><td>Critico</td><td>Concienciacion, Proteccion contra malware</td></tr>'
                    . '<tr><td>Contratos y archivos fisicos</td><td>Robo / perdida de documentos</td><td>Almacenamiento inseguro</td><td>Alto</td><td>Medio</td><td>Alto</td><td>Seguridad fisica, Control de acceso a oficinas</td></tr>'
                    . '<tr><td>Computadoras y laptops</td><td>Robo o extravio</td><td>Sin cifrado en disco ni control de acceso</td><td>Alto</td><td>Medio</td><td>Alto</td><td>Gestion de activos, Seguridad fisica y ambiental</td></tr>'
                    . '<tr><td>Personal del estudio</td><td>Error humano</td><td>Falta de formacion en seguridad</td><td>Medio</td><td>Alto</td><td>Medio-Alto</td><td>Capacitacion, Politica de seguridad</td></tr>'
                    . '<tr><td>Servicios en la nube</td><td>Divulgacion accidental</td><td>Sin revision de politicas de uso</td><td>Alto</td><td>Medio</td><td>Alto</td><td>Proteccion de comunicaciones, Cumplimiento</td></tr>'
                    . '<tr><td>Comunicaciones con clientes</td><td>Intercepcion o manipulacion</td><td>No uso de canales cifrados</td><td>Alto</td><td>Alto</td><td>Critico</td><td>Cifrado, Seguridad en las comunicaciones</td></tr>'
                    . '</tbody></table>'
                    . '<h3>Responsable</h3>'
                    . '<p>Elaborado por: JC Aranibar / Coordinador SGS. Aprobado por: M Palacios / Gerente General.</p>',
                'version' => '1.0',
                'obligatoria' => false,
                'activa' => true,
                'es_nda' => false,
                'vigencia_meses' => null,
            ],
        ];
    }
}
