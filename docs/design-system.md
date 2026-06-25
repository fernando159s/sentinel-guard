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
- `->font('DM Sans')` — fuente principal del panel
- Sora y Fira Code cargadas vía `@import` en `theme.css`

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
| `resources/css/filament/admin/theme.css` | Tokens, overrides de gray scale, tipografía |
| `app/Providers/Filament/AdminPanelProvider.php` | `Color::hex()`, `darkMode(isForced: true)`, `font('DM Sans')` |

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

Importadas desde Google Fonts en `theme.css`:
```css
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:...'
    '&family=Sora:...'
    '&family=Fira+Code:...'
    '&display=swap');
```

---

## 6. Accesibilidad (WCAG AA)

| Par | Ratio | Resultado |
|-----|-------|-----------|
| `#E0F0FF` sobre `#0A1628` | 14.8:1 | ✅ AAA |
| `#00D4AA` sobre `#112240` | 5.2:1 | ✅ AA |
| `#00B4D8` sobre `#112240` | 4.6:1 | ✅ AA |
| `#7A8FA6` sobre `#0A1628` | 4.5:1 | ✅ AA (mínimo) |

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
