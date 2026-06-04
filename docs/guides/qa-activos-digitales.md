# QA — Activos Digitales (US-1907 / SEN-133)

Checklist de QA por rol para el módulo de Activos Digitales. Las verificaciones
marcadas se comprobaron de forma automatizada al cargar el seeder demo
`ActivoDigitalDemoSeeder` sobre el Estudio Palacios (empresa 1) y TechSoft
(empresa 2).

## Datos demo

```bash
# Solo activos digitales (sobre una BD ya seedeada con roles/usuarios base)
php artisan db:seed --class=ActivoDigitalDemoSeeder

# O todo el set demo (usuarios, equipos, registros, tickets, activos…)
php artisan db:seed --class=DemoDataSeeder
```

El seeder es **idempotente y autoritativo**: cada corrida deja el mismo estado
canónico (11 activos, 11 credenciales, 24 pagos, 14 responsables) sin importar
el estado previo. Las fechas de vencimiento se calculan relativas a *hoy*, por
lo que el demo siempre muestra un mix fresco de vencimientos:

| Cobertura | Valores |
|-----------|---------|
| Tipos | WhatsApp (2), Meta (1), SaaS (5), Dominio (1), Licencia única (1), Redes sociales (1) |
| Modalidades | Mensual (6), Anual (3), Pago único (1), Gratuito (1) |
| Vencimientos | Vencido resting (1), +30d, +7d, +1d, +14d, +20d, futuros (+199/+240d), sin vencimiento (2), 1 vencido-no-marcado |

## Usuarios de prueba (empresa 1 = Estudio Palacios)

| Rol | Email | Password |
|-----|-------|----------|
| super_admin | `admin@securiform.local` | `Admin2024!` |
| admin_empresa | `carlos@palacios.pe` | `Test2024!` |
| usuario | `maria@palacios.pe` | `Test2024!` (responsable de AD-001 y AD-008) |
| solo_lectura | `pedro@palacios.pe` | `Test2024!` (responsable de AD-006) |

## 1. Visibilidad de credenciales por rol ✅

Matriz verificada vía `ActivoDigitalCredencialPolicy` (Gate). De 11 credenciales:

| Rol | Credenciales visibles | Esperado |
|-----|----------------------|----------|
| super_admin | 11 / 11 (todas, incl. empresa 2) | Llave maestra |
| admin_empresa (carlos) | 8 / 11 (solo empresa 1) | Solo su empresa — **0 de empresa 2** |
| usuario (maria) | 2 / 11 (AD-001, AD-008) | Solo donde es responsable |
| solo_lectura (pedro) | 1 / 11 (AD-006) | Solo donde es responsable |

- [x] super_admin descifra cualquier credencial.
- [x] admin_empresa descifra solo las de su empresa (aislamiento por tenant).
- [x] usuario/solo_lectura descifran **únicamente** las cuentas donde figuran como responsables.
- [x] Las credenciales se guardan cifradas (cast `encrypted`) y están en `$hidden`.

## 2. Alertas de vencimiento ✅

```bash
php artisan activos:check-vencimientos   # revisar bandeja en http://localhost:8025
```

- [x] Alerta de **30 días**: AD-001 WhatsApp → admins + responsables.
- [x] Alerta de **7 días**: AD-002 Meta → admin.
- [x] Alerta de **1 día**: AD-008 Canva → admin + responsable.
- [x] **Auto-marcado**: GitHub Team (empresa 2), vencido pero aún "activo", pasa a `vencido` y envía la alerta de vencido.
- [x] Los correos llegan a admins de la empresa + responsables de la cuenta (deduplicados).
- [x] AD-005 Microsoft 365 figura como `vencido` en panel/reportes sin necesidad de correr el cron.

## 3. Reportes PDF ✅

Centro de Reportes → Activos Digitales (inventario, vencimientos, ficha individual).

- [x] Inventario, vencimientos y ficha generan PDF válido con branding de la empresa.
- [x] Los PDF **nunca** incluyen credenciales (usuario/password/2FA); la ficha muestra el aviso de seguridad.
- [x] Visible para roles con acceso a reportes (super_admin / admin_empresa).

## Notas

- Todas las credenciales del demo son ficticias (prefijo `Demo*`, nota "Credencial DEMO").
- El seeder no corre en producción (`DatabaseSeeder` solo invoca el set demo fuera de `production`).
