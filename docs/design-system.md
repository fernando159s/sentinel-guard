# Sentinel Guard — Design System: Sentinel Teal

> Versión 1.0 · Junio 2026  
> Sistema de diseño oficial para SecuriForm (Filament 5.4 + Laravel 12)

---

## 1. Identidad visual

**Dirección elegida:** Sentinel Teal — dark navy base con teal vibrante como color de marca.  
Esta dirección prioriza confianza, profesionalismo de seguridad y alto contraste para uso prolongado.

---

## 2. Paleta de colores

### Colores de superficie (dark theme base)

| Token CSS | Nombre | Hex | Uso |
|-----------|--------|-----|-----|
| `--st-bg` | Background | `#0A1628` | Fondo general del body |
| `--st-sidebar` | Sidebar | `#0D1E35` | Fondo de la barra lateral |
| `--st-card` | Card | `#112240` | Fondo de tarjetas y paneles |
| `--st-border` | Border | `#1E3A5F` | Bordes de componentes |

### Colores de marca

| Token CSS | Nombre | Hex | WCAG AA sobre `--st-card` |
|-----------|--------|-----|--------------------------|
| `--st-teal` | Brand Teal | `#00D4AA` | ✅ 5.2:1 |
| `--st-accent` | Accent Blue | `#00B4D8` | ✅ 4.6:1 |

### Colores de texto

| Token CSS | Nombre | Hex | Uso |
|-----------|--------|-----|-----|
| `--st-text` | Text Primary | `#E0F0FF` | Texto principal |
| `--st-muted` | Text Muted | `#7A8FA6` | Texto secundario, labels |

### Colores semánticos (Filament)

| Rol Filament | Color | Hex base |
|--------------|-------|---------|
| `primary` | Brand Teal | `#00D4AA` |
| `info` | Accent Blue | `#00B4D8` |
| `danger` | Red | Filament `Color::Red` |
| `success` | Emerald | Filament `Color::Emerald` |
| `warning` | Amber | Filament `Color::Amber` |

---

## 3. Tipografía

| Rol | Familia | Peso(s) | Uso |
|-----|---------|---------|-----|
| Display / Headings | **Sora** | 400, 600, 700, 800 | Títulos de página, headings principales |
| UI / Body | **DM Sans** | 300, 400, 500, 600, 700 | Todo el texto de interfaz |
| Código / IDs | **Fira Code** | 400, 500 | Identificadores de registro, código, rutas |

**Configuración Filament:**
- `->font('DM Sans')` — fuente principal del panel (Filament inyecta su `<link>` de Google Fonts)
- Sora y Fira Code cargadas vía `<link>` en el `renderHook('panels::head.end')` del `AdminPanelProvider`

**Escala tipográfica:**

| Nombre | Tamaño | Peso | Uso |
|--------|--------|------|-----|
| Display | 32px | 800 Sora | Hero de página |
| Heading | 20px | 700 Sora | Títulos de sección |
| Subheading | 14px | 600 DM Sans | Labels de campo |
| Body | 14px | 400 DM Sans | Texto general |
| Caption | 11px | 500 DM Sans | Badges, metadatos |
| Code | 13px | 400 Fira Code | IDs, código |

---

## 4. Forma y espaciado

### Border radius

| Token | Valor | Uso |
|-------|-------|-----|
| `--st-radius` | `4px` | Base: botones, inputs, badges |
| — | `8px` | Tarjetas compactas |
| — | `12px` | Tarjetas estándar, modales |
| — | `0.5rem` | Items del sidebar |

### Focus ring

```css
outline-color: #00D4AA;
box-shadow: 0 0 0 3px rgba(0, 212, 170, 0.25);
```

---

## 5. Implementación en Filament 5.4

### Archivos modificados

| Archivo | Cambio |
|---------|--------|
| `resources/css/filament/admin/theme.css` | Tokens, override de gray scale, tipografía, botones accesibles |
| `app/Providers/Filament/AdminPanelProvider.php` | `Color::hex()`, `darkMode(isForced: true)`, `font('DM Sans')`, fuentes vía `<link>` |
| `composer.json` / `composer.lock` | Eliminada la dependencia `openplain/filament-shadcn-theme` (ya no se usa) |

### Modo oscuro

El panel fuerza `dark mode` siempre activo vía `->darkMode(isForced: true)`.  
El gray scale de Tailwind se remapea en `.dark` para coincidir con la paleta Sentinel Teal:

```
Tailwind gray-950 → #0A1628 (body bg)
Tailwind gray-900 → #0D1E35 (sidebar)
Tailwind gray-800 → #112240 (cards)
Tailwind gray-700 → #1E3A5F (borders)
```

### Carga de fuentes

- **DM Sans** (UI): la registra `->font('DM Sans')`, que inyecta su propio `<link>` de Google Fonts.
- **Sora** (display) y **Fira Code** (mono): se cargan con un `<link>` adicional en el `renderHook('panels::head.end')` del `AdminPanelProvider` (no se duplica DM Sans):

```php
'<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Fira+Code:wght@400;500&display=swap">'
```

### Botones accesibles (WCAG AA)

El panel fuerza modo oscuro, donde Filament usaría texto blanco sobre el relleno teal/cyan de los botones `primary`/`info` (~3.4:1, **falla AA**). El tema fija un **fondo brillante (shade 500) + texto navy `#0A1628`** para esos botones, logrando ~7.7:1. Esto reemplaza el fix de texto de botón que antes aportaba el tema shadcn (ya eliminado).

---

## 6. Accesibilidad (WCAG AA)

> Ratios calculados con el helper `Filament\Support\Colors\Color::calculateContrastRatio()`.

| Par | Uso | Ratio | Resultado |
|-----|-----|-------|-----------|
| `#E0F0FF` sobre `#0A1628` | Texto principal sobre fondo | 16.6:1 | ✅ AAA |
| `#E0F0FF` sobre `#112240` | Texto sobre card | 14.5:1 | ✅ AAA |
| `#00D4AA` sobre `#112240` | Teal como texto/acento sobre card | 8.9:1 | ✅ AAA |
| `#00B4D8` sobre `#112240` | Cyan como texto/acento sobre card | 6.9:1 | ✅ AAA |
| `#7A8FA6` sobre `#0A1628` | Texto muted sobre fondo | 5.9:1 | ✅ AA |
| `#0A1628` sobre teal-500 | Texto navy en botón `primary` | 7.7:1 | ✅ AAA |
| `#0A1628` sobre cyan-500 | Texto navy en botón `info` | 7.6:1 | ✅ AAA |

---

## 7. Scope de esta versión

Esta versión cubre únicamente la **base/fundación** del tema global:
- Paleta y tokens CSS
- Configuración del panel Filament
- Tipografía base
- Modo oscuro forzado

Los rediseños por componente o página son tickets independientes.

---

*Sentinel Guard Design System v1.0 · Estudio Palacios Abogados S.A.C. · Uso interno*
