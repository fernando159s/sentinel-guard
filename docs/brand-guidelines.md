# Sentinel Guard — Brand Guidelines

> Versión 1.0 · Abril 2026  
> Documento de identidad de marca para uso interno y producción

---

## 1. Identidad de Marca

### Nombre

**Sentinel Guard**

Siempre escrito como dos palabras, con mayúscula inicial en cada una. Nunca abreviar como "SG" en comunicaciones formales. En interfaces técnicas (rutas, slugs, IDs) usar `sentinel-guard`.

```
✅  Sentinel Guard
✅  sentinel-guard   (solo en código/rutas)
❌  SentinelGuard
❌  sentinelguard
❌  Sentinel guard
❌  SENTINEL GUARD
```

### Tagline

> **"Seguridad documentada, cumplimiento garantizado"**

El tagline va siempre en minúscula excepto la primera letra. Se usa completo — no se trunca ni adapta. Es el único tagline oficial.

```
✅  Seguridad documentada, cumplimiento garantizado
❌  Seguridad documentada
❌  "Cumplimiento garantizado"
❌  SEGURIDAD DOCUMENTADA, CUMPLIMIENTO GARANTIZADO
```

### Misión del Producto

Sentinel Guard permite a organizaciones documentar, gestionar y auditar sus procesos de seguridad de la información de forma centralizada, garantizando el cumplimiento de las políticas PSC vigentes.

---

## 2. Logo

### Anatomía

El logo de Sentinel Guard se compone de dos elementos:

```
┌─────────────────────────────────────┐
│  [ESCUDO]   SENTINEL GUARD          │
│             ───────────────         │
│             Seguridad documentada,  │
│             cumplimiento garantizado│
└─────────────────────────────────────┘
```

**El escudo** — Forma pentagonal clásica con degradado azul eléctrico (`#3B82F6`) en la parte superior hacia azul marino (`#1E3A5F`) en la base. Interior con una "S" blanca en bold, centrada verticalmente.

**El wordmark** — "Sentinel Guard" en Inter 800 (ExtraBold), color navy `#1E3A5F`. El tagline en Inter 400, gris slate `#64748B`, en un tamaño 40% menor al wordmark.

### Versiones del Logo

| Versión | Uso | Fondo permitido |
|---------|-----|-----------------|
| **Horizontal** (escudo + wordmark) | Cabeceras, documentos, landing | Blanco, superficie clara |
| **Solo ícono** (escudo) | Favicon, avatar, espacios reducidos | Cualquier fondo |
| **Negativo** (blanco sobre navy) | Fondos oscuros, sidebar, emails | Navy `#1E3A5F`, azul oscuro |

### Espacio de protección

El logo siempre debe tener un espacio libre alrededor equivalente a **la altura de la "S" del escudo**. Ningún texto, imagen o elemento gráfico puede entrar en esa zona.

```
    ╔═══════════════════════════╗
    ║                           ║  ← zona de protección
    ║   [Escudo] SENTINEL GUARD ║
    ║                           ║
    ╚═══════════════════════════╝
```

### Tamaño mínimo

| Aplicación | Tamaño mínimo del escudo |
|------------|--------------------------|
| Impresión | 12mm de alto |
| Digital (pantalla) | 24px de alto |
| Favicon | 16×16px (solo escudo, sin texto) |

### Lo que NO hacer con el logo

```
❌  Cambiar los colores del escudo
❌  Usar el wordmark sin el escudo
❌  Distorsionar o escalar no proporcionalmente
❌  Agregar sombras, brillos o efectos 3D
❌  Colocar sobre fondos que no contrasten (gris claro sobre blanco)
❌  Rotar el logo
❌  Cambiar la tipografía del wordmark
```

---

## 3. Paleta de Colores

### Colores Primarios

| Token | Nombre | Hex | RGB | Uso principal |
|-------|--------|-----|-----|---------------|
| `--sg-navy` | Navy | `#1E3A5F` | `30, 58, 95` | Primario de marca, sidebar, titulares |
| `--sg-blue` | Blue | `#3B82F6` | `59, 130, 246` | Acento, botones CTA, highlights, links |
| `--sg-white` | White | `#FFFFFF` | `255, 255, 255` | Fondos de tarjetas, texto sobre oscuro |

### Colores Secundarios

| Token | Nombre | Hex | RGB | Uso |
|-------|--------|-----|-----|-----|
| `--sg-slate` | Slate | `#64748B` | `100, 116, 139` | Texto secundario, descripciones, bordes |
| `--sg-surface` | Surface | `#F8FAFC` | `248, 250, 252` | Fondo general de la UI |
| `--sg-border` | Border | `#E2E8F0` | `226, 232, 240` | Bordes de tarjetas, divisores |
| `--sg-light-blue` | Light Blue | `#EFF6FF` | `239, 246, 255` | Fondos de highlights, badges, chips |

### Colores Semánticos

| Token | Hex | Uso |
|-------|-----|-----|
| `--sg-success` | `#059669` | Confirmaciones, éxito, permisos activos |
| `--sg-warning` | `#D97706` | Advertencias, estado pendiente |
| `--sg-danger` | `#DC2626` | Errores, acciones destructivas, prohibiciones |
| `--sg-info` | `#3B82F6` | Información, notas, tips |

### Colores por Rol

Cada rol del sistema tiene un color identificador usado en documentación y UI de role-awareness:

| Rol | Color | Hex |
|-----|-------|-----|
| Super Administrador | Navy | `#1E3A5F` |
| Admin de Empresa | Blue | `#1D4ED8` |
| Usuario | Green | `#047857` |
| Agente Helpdesk | Violet | `#6D28D9` |
| Solo Lectura | Slate | `#334155` |

### Uso de color

**Contraste mínimo para texto:**
- Texto sobre fondo blanco: mínimo **4.5:1** (WCAG AA)
- Texto grande (>18px) sobre fondo blanco: mínimo **3:1**
- Texto blanco sobre navy `#1E3A5F`: ratio 10.4:1 ✅
- Texto navy sobre blanco: ratio 10.4:1 ✅
- Texto slate sobre blanco: ratio 4.6:1 ✅

**Gradiente del logo:**

```css
background: linear-gradient(135deg, #3B82F6 0%, #1E3A5F 100%);
```

---

## 4. Tipografía

### Fuente principal — Inter

Inter es la única fuente de Sentinel Guard. Se usa en todos los pesos para texto de UI, documentación y materiales de marca.

**Carga web:**
```html
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
```

### Escala tipográfica

| Nombre | Tamaño | Peso | Line-height | Uso |
|--------|--------|------|-------------|-----|
| Display | 64px | 900 | 1.05 | Hero principal, landing |
| H1 | 40px | 800 | 1.1 | Títulos de página |
| H2 | 28px | 700 | 1.2 | Títulos de sección |
| H3 | 20px | 700 | 1.3 | Subtítulos, tarjetas |
| H4 | 16px | 600 | 1.4 | Labels, nombres de campo |
| Body Large | 18px | 400 | 1.6 | Párrafos principales |
| Body | 14px | 400 | 1.6 | Texto general de UI |
| Body Small | 12px | 400 | 1.5 | Descripciones, notas |
| Caption | 11px | 500 | 1.4 | Labels, badges, chips |
| Overline | 11px | 700 | 1 | Eyebrows, categorías (ALL CAPS) |

### Reglas tipográficas

```
✅  Usar Inter en todos los contextos
✅  Preferir pesos 400, 600 y 700 para legibilidad
✅  Letter-spacing negativo en títulos grandes (-0.5px a -2px)
✅  Overlines siempre en MAYÚSCULAS con letter-spacing 1-1.5px
❌  No usar cursiva en UI (solo en citas o términos técnicos)
❌  No mezclar con otras fuentes display o serif
❌  No usar pesos 100 o 200 en texto pequeño (ilegible)
```

### Fuente monoespaciada

Para rutas, código, IDs de registro, comandos y referencias técnicas:

```
JetBrains Mono — o alternativa del sistema: Consolas, monospace
Tamaño: siempre -1px respecto al texto circundante
Color: --sg-blue (#3B82F6) sobre fondo --sg-light-blue (#EFF6FF)
```

**Ejemplo de referencia a ruta:**
```
Admin → Seguridad → Registros → Nuevo
```

---

## 5. Iconografía

### Sistema de iconos

Sentinel Guard usa **Heroicons** (outline variant, stroke-width: 1.8) como sistema de iconos principal. Es el mismo sistema que usa Filament v3 internamente.

```
Estilo:     Outline (no filled)
Stroke:     1.8px
Color:      Hereda del contexto (currentColor)
Tamaño base: 20×20px en UI general, 24×24px en headers
```

### Iconos de rol

Cada rol tiene un ícono Heroicons asignado:

| Rol | Ícono Heroicons | Descripción |
|-----|-----------------|-------------|
| Super Administrador | `ShieldCheckIcon` | Escudo con check |
| Admin de Empresa | `BuildingOffice2Icon` | Edificio de oficinas |
| Usuario | `UserIcon` | Silueta de persona |
| Agente Helpdesk | `ChatBubbleLeftRightIcon` | Burbujas de chat |
| Solo Lectura | `EyeIcon` | Ojo |

### Contenedores de ícono

Los íconos en tarjetas y features se envuelven en un contenedor cuadrado redondeado:

```css
.icon-container {
  width: 36px;           /* compact */
  width: 48px;           /* default */
  width: 64px;           /* large */
  border-radius: 10px;   /* compact */
  border-radius: 14px;   /* default */
  border-radius: 20px;   /* large */
  background: var(--sg-light-blue);
  display: flex;
  align-items: center;
  justify-content: center;
}
```

---

## 6. Voz y Tono

### Personalidad de marca

Sentinel Guard habla como un **profesional de seguridad experimentado y confiable** — no como un robot corporativo, pero tampoco como una startup informal. Es directo, técnico cuando es necesario, y nunca condescendiente.

### Los 4 principios de voz

| Principio | Qué significa | Ejemplo ✅ | Ejemplo ❌ |
|-----------|---------------|------------|------------|
| **Directo** | Ve al punto, sin rodeos | "Guarda el registro para continuar." | "Por favor, tenga en cuenta que para poder continuar con el proceso será necesario que proceda a guardar el registro." |
| **Preciso** | Usa términos exactos del sistema | "Haz clic en 'Nuevo registro' → selecciona F09." | "Crea algo nuevo en algún lado." |
| **Respetuoso** | Trata al usuario como profesional | "No tienes permisos para esta acción." | "¡Ups! Parece que no puedes hacer eso 😅" |
| **Informativo** | Explica el por qué cuando importa | "Esta acción no se puede deshacer. Los registros archivados siguen siendo recuperables." | "¡Cuidado! Acción irreversible." |

### Tono según el contexto

| Contexto | Tono | Ejemplo |
|----------|------|---------|
| Onboarding | Claro y orientador | "Bienvenido. Tu primer paso es aceptar las políticas de tu empresa." |
| Error de validación | Directo y útil | "El RUC debe tener 11 dígitos." |
| Acción destructiva | Serio y explícito | "Archivarás este registro. No aparecerá en las listas principales, pero puede restaurarse." |
| Confirmación de éxito | Breve y afirmativo | "Registro guardado correctamente." |
| Sin datos | Neutro y orientador | "No hay registros en este período. Crea el primero desde 'Nuevo registro'." |

### Terminología oficial

Estos son los términos exactos que se usan en la UI. No usar sinónimos informales:

| Término oficial | ❌ No usar |
|----------------|-----------|
| Empresa | Organización, cliente, tenant |
| Registro | Formulario, formato, documento |
| Ticket | Solicitud, caso, issue |
| Archivar | Eliminar, borrar, deshabilitar |
| Restaurar | Recuperar, reactivar |
| Agente | Soporte, helpdesk, operador |
| Panel de Agente | Centro de soporte, bandeja |
| Cumplimiento | Compliance (en inglés) |
| Política | NDA, acuerdo (cuando no es NDA) |

---

## 7. Espaciado y Layout

### Escala de espaciado

Basada en múltiplos de 4px:

```
4px   — xs   (gaps internos mínimos)
8px   — sm   (padding de badges, chips)
12px  — md   (padding interno de elementos pequeños)
16px  — lg   (padding de tarjetas compactas)
20px  — xl   (padding de componentes)
24px  — 2xl  (separación entre secciones menores)
32px  — 3xl  (separación entre secciones)
48px  — 4xl  (padding de páginas/layouts)
64px  — 5xl  (secciones hero)
```

### Radios de borde (border-radius)

```
4px   — elementos inline (badges, tags, chips)
8px   — botones, inputs, elementos pequeños
10px  — tarjetas compactas
12px  — tarjetas estándar
14px  — tarjetas grandes
16px  — modales, paneles
24px  — overlays grandes
999px — pills, badges redondeados
```

### Elevación / Sombras

```css
/* Nivel 1 — tarjetas en reposo */
box-shadow: 0 1px 3px rgba(0,0,0,0.07), 0 1px 2px rgba(0,0,0,0.05);

/* Nivel 2 — tarjetas en hover */
box-shadow: 0 8px 24px rgba(0,0,0,0.08);

/* Nivel 3 — modales, dropdowns */
box-shadow: 0 20px 40px rgba(0,0,0,0.12);

/* Logo */
filter: drop-shadow(0 8px 8px rgba(30,58,95,0.25));
```

---

## 8. Componentes UI Clave

### Botón primario

```css
background: #1E3A5F;
color: #FFFFFF;
padding: 9px 20px;
border-radius: 8px;
font-size: 13px;
font-weight: 600;
border: none;
transition: opacity 0.15s;

:hover { opacity: 0.88; }
:disabled { opacity: 0.35; cursor: not-allowed; }
```

### Botón secundario (ghost)

```css
background: #F8FAFC;
color: #64748B;
border: 1px solid #E2E8F0;
padding: 9px 20px;
border-radius: 8px;
font-size: 13px;
font-weight: 600;
transition: background 0.15s;

:hover { background: #E2E8F0; }
```

### Badge / Chip

```css
/* Genérico */
display: inline-block;
font-size: 10px;
font-weight: 700;
padding: 3px 9px;
border-radius: 999px;
text-transform: uppercase;
letter-spacing: 0.5px;

/* Variantes de color */
.blue   { background: #EFF6FF; color: #3B82F6; }
.green  { background: #ECFDF5; color: #059669; }
.purple { background: #F5F3FF; color: #7C3AED; }
.amber  { background: #FFFBEB; color: #D97706; }
.red    { background: #FEF2F2; color: #DC2626; }
.slate  { background: #F1F5F9; color: #475569; }
```

### Tarjeta estándar

```css
background: #FFFFFF;
border: 1px solid #E2E8F0;
border-radius: 12px;
padding: 18px;
transition: transform 0.2s ease, box-shadow 0.2s ease;

:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 32px rgba(0,0,0,0.09);
}
```

### Eyebrow (label de sección)

```
Font: Inter 700, 11px, UPPERCASE
Letter-spacing: 1px
Color: --sg-blue (#3B82F6)
Background: --sg-light-blue (#EFF6FF)
Padding: 5px 14px
Border-radius: 999px
```

### Tip / Info box

```css
background: #FFFBEB;
border: 1px solid #FDE68A;
border-radius: 10px;
padding: 14px 16px;
font-size: 12px;
color: #78350F;
line-height: 1.5;
```

---

## 9. Animaciones

### Principios

- **Propósito sobre decoración:** cada animación comunica algo (carga, transición, confirmación)
- **Duración corta:** entre 300ms y 500ms para la mayoría de transiciones
- **Easing natural:** `easeOutCubic` para entradas, `easeInCubic` para salidas
- **Sin bloqueo:** las animaciones no deben bloquear la interacción del usuario

### Valores estándar

```javascript
// Entrada de elemento
anime({
  targets: el,
  opacity: [0, 1],
  translateY: [20, 0],
  duration: 400,
  easing: 'easeOutCubic'
});

// Stagger en lista de elementos
anime({
  targets: '.sg-card',
  opacity: [0, 1],
  translateY: [30, 0],
  delay: anime.stagger(80),
  duration: 400,
  easing: 'easeOutCubic'
});

// Pulso de ícono de marca (loop)
anime({
  targets: '.brand-icon',
  scale: [1, 1.04, 1],
  direction: 'alternate',
  loop: true,
  duration: 2500,
  easing: 'easeInOutSine'
});

// Transición entre slides
effect: 'fade',
fadeEffect: { crossFade: true },
speed: 380
```

### Librería oficial

**anime.js v3** (`cdn.jsdelivr.net/npm/animejs@3.2.1/lib/anime.min.js`) para animaciones de UI y elementos individuales.

**Swiper.js v11** (`cdn.jsdelivr.net/npm/swiper@11`) para carruseles y slide decks.

---

## 10. Aplicaciones de Marca

### Sidebar de la aplicación

```
Fondo:         Color del rol activo (navy para super_admin, etc.)
Logo:          Versión negativa (blanco sobre color)
Texto nav:     rgba(255,255,255,0.65) en reposo
               #FFFFFF en estado activo
Item activo:   rgba(255,255,255,0.15) como fondo
               Color acento del rol como número/indicador
```

### Encabezados de página (Filament)

```
Fondo:         #FFFFFF
Borde inferior: 1px solid #E2E8F0
Título:        Inter 700, 20px, #0F172A
Subtítulo:     Inter 400, 13px, #64748B
```

### Emails del sistema

```
Ancho máximo:  600px
Fondo email:   #F8FAFC
Contenido:     #FFFFFF, border-radius 12px
Header:        Logo horizontal + color navy de fondo
CTA principal: Botón primario navy
Footer:        Tagline + "Sentinel Guard" en gris
```

### Documentación (como este documento)

```
Formato:       Markdown con tablas y bloques de código
Estructura:    Secciones numeradas, jerarquía clara
Tono:          Técnico y preciso
Versionado:    X.Y en el encabezado + mes y año
```

---

## 11. Checklist de Uso Correcto

Antes de publicar cualquier material con la marca Sentinel Guard, verifica:

- [ ] El nombre está escrito correctamente: **Sentinel Guard** (dos palabras, mayúscula inicial)
- [ ] El logo no está distorsionado ni recoloreado
- [ ] Los colores usados pertenecen a la paleta oficial
- [ ] La fuente es Inter (o su fallback de sistema)
- [ ] El tagline está completo y en minúsculas excepto la primera letra
- [ ] Hay espacio de protección alrededor del logo
- [ ] El contraste de texto cumple WCAG AA (4.5:1 mínimo)
- [ ] Las animaciones tienen duración ≤ 500ms
- [ ] La terminología oficial se usa consistentemente

---

## 12. Archivos de Referencia

| Recurso | Ubicación |
|---------|-----------|
| Logo SVG (inline) | Disponible en todos los archivos `public/docs/*.html` |
| Documentación por rol | `public/docs/` |
| Colores CSS como variables | `--sg-*` tokens en cada archivo de docs |
| Iconos (Heroicons) | https://heroicons.com — variante outline |
| Fuente Inter | https://fonts.google.com/specimen/Inter |
| anime.js | https://animejs.com |

---

*Sentinel Guard Brand Guidelines v1.0 — Estudio Palacios Abogados S.A.C. · Uso interno*
