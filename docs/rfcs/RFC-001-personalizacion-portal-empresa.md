# RFC-001: Personalizacion de Portal por Empresa

- **Estado**: Borrador
- **Autor**: Fernando + Claude AI
- **Fecha**: 2026-04-01
- **Epic Linear**: EP-05 Panel Filament Admin

---

## Problema

Actualmente todas las empresas ven el mismo branding en el panel: nombre "SecuriForm" con
color Indigo. Esto genera una experiencia generica que no transmite identidad a cada cliente.

- **Quien tiene este problema?** Los administradores de empresa y sus usuarios, que ven un
  portal sin su identidad corporativa.
- **Con que frecuencia ocurre?** Cada vez que un usuario entra al sistema (100% de las sesiones).
- **Que impacto tiene no resolverlo?** Percepcion de producto generico, menor adopcion,
  falta de profesionalismo ante el cliente final.

Ademas, el super administrador necesita poder configurar estos portales de forma centralizada
sin intervencion tecnica.

## Propuesta

El super administrador podra configurar por cada empresa:

- **Logo** (ya existe el campo `logo_path`)
- **Color primario** (header, botones, enlaces)
- **Color secundario** (acentos, badges)
- **Color del sidebar** (fondo de la barra lateral)
- **Nombre del portal** (reemplaza "SecuriForm" en el sidebar)

Al ingresar, cada usuario vera el portal con el branding de su empresa.
Si una empresa no tiene configuracion, se usa el tema por defecto (Indigo + "SecuriForm").

### Mockup: Formulario de configuracion (super admin)

```
+-----------------------------------------------------------------------+
| SecuriForm > Empresas > Editar: Estudio Palacios SAC                  |
+-----------------------------------------------------------------------+
|                                                                       |
|  == Datos de la empresa ==                                            |
|  +------------------+  +----------------------------+                 |
|  | RUC              |  | Razon Social               |                 |
|  | 20123456789      |  | Estudio Palacios Abog. SAC |                 |
|  +------------------+  +----------------------------+                 |
|  +------------------+  +----------------------------+                 |
|  | Email            |  | Telefono                   |                 |
|  | info@estudio.pe  |  | +51 1 234 5678             |                 |
|  +------------------+  +----------------------------+                 |
|                                                                       |
|  == Personalizacion del Portal ==                    [SECCION NUEVA]  |
|  +------------------+  +----------------------------+                 |
|  | Logo             |  | Nombre del portal          |                 |
|  | [logo.png]       |  | Estudio Palacios           |                 |
|  | [Subir archivo]  |  |                            |                 |
|  +------------------+  +----------------------------+                 |
|  +------------------+  +--------------+  +---------+                  |
|  | Color primario   |  | Color secund.|  | Color   |                  |
|  | [#] #1e3a5f      |  | [#] #f59e0b  |  | sidebar |                  |
|  |  +-----------+   |  | +---------+  |  | [#]     |                  |
|  |  | (preview) |   |  | |(preview)|  |  | #1a4d2e |                  |
|  |  +-----------+   |  | +---------+  |  +---------+                  |
|  +------------------+  +--------------+                               |
|                                                                       |
|                                         [ Cancelar ] [ Guardar ]      |
+-----------------------------------------------------------------------+
```

### Mockup: Portal personalizado (lo que ve el usuario de empresa)

```
+-----------------------------------------------------------------------+
| localhost:8080/admin/20123456789/                                      |
+-----------------------------------------------------------------------+
| +------------+ +------------------------------------------------------+
| | [LOGO EP]  | | Dashboard                          Juan Perez  [v]  |
| |            | +------------------------------------------------------+
| | Estudio    | |                                                      |
| | Palacios   | |  +----------+  +----------+  +----------+           |
| |            | |  |    12    |  |     3    |  |     5    |           |
| | ---------- | |  | Registros|  | Tickets  |  | Usuarios |           |
| | Dashboard  | |  +----------+  +----------+  +----------+           |
| | Registros  | |                                                      |
| | Tickets    | |  Ultimos registros                                   |
| | Reportes   | |  +------+------------+----------+--------+          |
| |            | |  |Codigo|  Formato   |  Fecha   | Estado |          |
| |            | |  +------+------------+----------+--------+          |
| |            | |  |INC-  | Incidencias| 01/04/26 | Activo |          |
| |            | |  |2026- |            |          |        |          |
| |            | |  |004   |            |          |        |          |
| |            | |  +------+------------+----------+--------+          |
| |            | |  |BAK-  | Copias seg.| 31/03/26 | Activo |          |
| |            | |  |2026- |            |          |        |          |
| |            | |  |012   |            |          |        |          |
| +------------+ |  +------+------------+----------+--------+          |
|   sidebar con  +------------------------------------------------------+
|   color_sidebar     header con color_primario
|   (#1a4d2e)         (#1e3a5f)
|
|   * NO hay selector de empresa (usuario normal solo ve la suya)
|   * NO aparece "SecuriForm", aparece "Estudio Palacios"
|   * Logo de la empresa en el sidebar
```

### Mockup: Panel super admin (ve todas las empresas)

```
+-----------------------------------------------------------------------+
| localhost:8080/admin/                                                  |
+-----------------------------------------------------------------------+
| +------------+ +------------------------------------------------------+
| | SecuriForm | | Empresas                           Admin  [v]       |
| |            | +------------------------------------------------------+
| | ---------- | |                                                      |
| | Dashboard  | |  +-----+---------------------+-------+------+       |
| | Empresas   | |  | RUC | Razon Social        |Usuar. |Estado|       |
| | Usuarios   | |  +-----+---------------------+-------+------+       |
| | Registros  | |  |2012 | Estudio Palacios SAC|   5   |Activo|       |
| | Tickets    | |  |3456 |                     |       |      |       |
| | Reportes   | |  |789  |                     |       |      |       |
| |            | |  +-----+---------------------+-------+------+       |
| |            | |  |2098 | Corp Financiera ABC |   3   |Activo|       |
| |            | |  |7654 |                     |       |      |       |
| |            | |  |321  |                     |       |      |       |
| |            | |  +-----+---------------------+-------+------+       |
| |            | |                                                      |
| | [Cambiar   | |  * Super admin ve TODAS las empresas                 |
| |  empresa v]| |  * Puede editar branding de cada una                 |
| +------------+ |  * Ve todos los registros y tickets al cambiar tenant|
|                +------------------------------------------------------+
```

## Diseno Tecnico

### Modelos / Migraciones

```php
// Nueva migracion: add_branding_to_empresas_table
Schema::table('empresas', function (Blueprint $table) {
    $table->string('color_primario', 7)->nullable()->after('logo_path');
    $table->string('color_secundario', 7)->nullable()->after('color_primario');
    $table->string('color_sidebar', 7)->nullable()->after('color_secundario');
    $table->string('nombre_portal', 100)->nullable()->after('color_sidebar');
});

// Empresa model - agregar a $fillable:
'color_primario', 'color_secundario', 'color_sidebar', 'nombre_portal'
```

### Servicios

```php
// No se requiere un servicio nuevo.
// La logica de theming se aplica directamente en el AdminPanelProvider
// usando Filament::getTenant() para obtener los colores del tenant activo.
```

### UI (Filament)

```php
// EmpresaForm.php - Nueva seccion en el formulario:
Forms\Components\Section::make('Personalizacion del Portal')
    ->schema([
        Forms\Components\FileUpload::make('logo_path')  // ya existe, mover aqui
            ->image()
            ->disk('logos')
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/svg+xml'])
            ->maxSize(2048),
        Forms\Components\TextInput::make('nombre_portal')
            ->maxLength(100)
            ->placeholder('Nombre visible en el portal'),
        Forms\Components\ColorPicker::make('color_primario')
            ->label('Color primario (header/botones)'),
        Forms\Components\ColorPicker::make('color_secundario')
            ->label('Color secundario (acentos)'),
        Forms\Components\ColorPicker::make('color_sidebar')
            ->label('Color del sidebar'),
    ])
    ->columns(2)
    ->collapsible();

// AdminPanelProvider.php - Theming dinamico:
// Opcion: usar tenantMiddleware o renderHook para aplicar
// colores CSS custom variables basados en Filament::getTenant()
```

## Analisis de Impacto

### Clasificacion

- [ ] **Tipo A: Aditivo** — No toca codigo existente, bajo riesgo
- [x] **Tipo B: Modificativo** — Modifica codigo existente, riesgo medio
- [ ] **Tipo C: Estructural** — Cambia arquitectura/modelos, alto riesgo

### Areas afectadas

| Area | Impacto | Tests existentes |
|------|---------|-----------------|
| Empresa model | Agregar 4 campos a $fillable | No |
| Migracion empresas | Agregar 4 columnas nullable | No |
| EmpresaForm | Nueva seccion + mover logo_path | No |
| AdminPanelProvider | Theming dinamico por tenant | No |

### Riesgos

- **Riesgo 1:** Colores configurados generan problemas de legibilidad (texto claro sobre fondo claro).
  - **Mitigacion:** Validar contraste minimo o documentar colores recomendados. Para MVP, confiar en que el super admin elige colores coherentes.

- **Riesgo 2:** Performance al cargar colores del tenant en cada request.
  - **Mitigacion:** Los colores vienen del tenant ya cargado por Filament (sin query extra). Cache automatico de Filament.

## Criterios de Aceptacion

- [ ] Super admin puede configurar logo, 3 colores y nombre de portal por empresa
- [ ] Colores se aplican via ColorPicker de Filament con preview de color
- [ ] Usuarios de empresa ven el portal con su branding personalizado (sidebar, header, botones)
- [ ] Logo de empresa visible en el sidebar del panel
- [ ] Nombre del portal reemplaza "SecuriForm" en la barra lateral
- [ ] Si la empresa no tiene colores configurados, se usa el tema por defecto (Indigo)
- [ ] Super admin sigue viendo "SecuriForm" como branding base al usar el tenant switcher
- [ ] No hay regresion en funcionalidades existentes (login, registros, exports)

## Alternativas Consideradas

### Alternativa A: Dos paneles separados (SuperAdmin + Empresa)

- **Pro:** Separacion total de experiencia, mas facil personalizar
- **Con:** Duplicar Resources, mantener dos paneles, mayor complejidad de codigo

### Alternativa B: Un solo panel con theming dinamico (Elegida)

- **Pro:** Reutiliza toda la infraestructura existente, menos codigo, Filament lo soporta nativamente
- **Con:** Logica de theming en el PanelProvider, todos los roles comparten estructura

### Alternativa C: CSS custom por empresa (archivo externo)

- **Pro:** Maxima flexibilidad visual
- **Con:** Dificil de mantener, requiere conocimiento CSS, riesgo de romper el layout

## Estimacion

| Tarea | Estimacion |
|-------|-----------|
| Migracion + modelo (campos branding) | 1 pt |
| Formulario Filament (seccion personalizacion) | 2 pts |
| Theming dinamico por tenant | 3 pts |
| Tests | 2 pts |
| **Total** | **8 pts** |

## Plan de Implementacion

1. **Sprint actual:**
   - Issue 1: Migracion + modelo (1 pt)
   - Issue 2: Formulario EmpresaResource (2 pts)
   - Issue 3: Theming dinamico (3 pts)
   - Issue 4: Tests (2 pts)

Todas las tareas son para un solo sprint (8 pts total, dentro del target de 40-50 pts).

## Referencias

- [SEN-55](https://linear.app/genniality-nerve/issue/SEN-55) — Agregar campos de branding a tabla empresas (1 pt)
- [SEN-56](https://linear.app/genniality-nerve/issue/SEN-56) — Agregar seccion de personalizacion en EmpresaResource (2 pts)
- [SEN-57](https://linear.app/genniality-nerve/issue/SEN-57) — Aplicar branding dinamico segun empresa activa (3 pts)
- [SEN-58](https://linear.app/genniality-nerve/issue/SEN-58) — Tests para personalizacion de portal (2 pts)
- Dependencias: SEN-55 blocks SEN-56 blocks SEN-57 blocks SEN-58
- Filament Tenancy docs: https://filamentphp.com/docs/3.x/panels/tenancy
- Filament Themes docs: https://filamentphp.com/docs/3.x/panels/themes
