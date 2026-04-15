<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seleccionar empresa - SentinelGuard</title>
    @vite(['resources/css/filament/admin/theme.css'])
    <style>
        body { margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center; font-family:'Inter',system-ui,sans-serif; }
        .light body { background:#f8fafc; }
        body { background:#0f172a; color:#e2e8f0; }
        .container { width:100%; max-width:900px; padding:20px; }
        .header { text-align:center; margin-bottom:32px; }
        .header h1 { font-size:22px; font-weight:700; margin:0 0 6px; }
        .header p { font-size:13px; opacity:.6; margin:0; }
        .grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:14px; }
        .card { border-radius:12px; padding:20px; text-decoration:none; cursor:pointer; transition:all .15s ease; border:1px solid rgba(255,255,255,.08); background:rgba(255,255,255,.03); }
        .card:hover { border-color:rgba(139,92,246,.4); box-shadow:0 4px 20px rgba(139,92,246,.08); transform:translateY(-1px); }
        .card-top { display:flex; align-items:center; gap:12px; }
        .avatar { height:42px; width:42px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:700; flex-shrink:0; background:rgba(139,92,246,.12); color:#a78bfa; }
        .avatar img { height:42px; width:42px; border-radius:10px; object-fit:cover; }
        .card-name { font-size:14px; font-weight:600; color:#f1f5f9; margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .card-ruc { font-size:11px; color:#94a3b8; margin:3px 0 0; }
        .card-footer { display:flex; align-items:center; justify-content:space-between; margin-top:14px; padding-top:12px; border-top:1px solid rgba(255,255,255,.05); }
        .badge { display:inline-flex; align-items:center; padding:2px 8px; border-radius:20px; font-size:11px; font-weight:500; }
        .badge-activo { background:rgba(34,197,94,.1); color:#4ade80; }
        .badge-inactivo { background:rgba(255,255,255,.05); color:#94a3b8; }
        .meta { font-size:11px; color:#64748b; }
        .arrow { width:16px; height:16px; opacity:.4; }
        .card-new { border:2px dashed rgba(255,255,255,.08); background:transparent; display:flex; align-items:center; justify-content:center; min-height:120px; text-align:center; }
        .card-new:hover { border-color:rgba(139,92,246,.4); }
        .card-new svg { width:28px; height:28px; color:#64748b; margin-bottom:6px; }
        .card-new span { font-size:13px; font-weight:500; color:#94a3b8; }
        .empty { text-align:center; padding:60px 20px; }
        .empty svg { width:48px; height:48px; color:#475569; margin:0 auto 16px; }
        .empty p { font-size:14px; color:#64748b; margin:0 0 20px; }
        .empty a { display:inline-flex; align-items:center; gap:6px; padding:10px 20px; border-radius:10px; background:#8b5cf6; color:#fff; font-size:13px; font-weight:600; text-decoration:none; transition:background .15s; }
        .empty a:hover { background:#7c3aed; }
        .logout { display:block; text-align:center; margin-top:24px; font-size:12px; color:#64748b; text-decoration:none; }
        .logout:hover { color:#94a3b8; }
    </style>
</head>
<body>
    @php
        $user = auth()->user();
        $empresas = $user->getTenants(\Filament\Facades\Filament::getDefaultPanel());
        $isSuperAdmin = $user->hasRole('super_admin');
    @endphp

    <div class="container">
        <div class="header">
            <h1>SentinelGuard</h1>
            <p>Hola {{ $user->name }} — selecciona una empresa para continuar</p>
        </div>

        @if ($empresas->isEmpty())
            <div class="empty">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3H21m-3.75 3H21"/></svg>
                <p>No hay empresas registradas aun.</p>
                @if ($isSuperAdmin)
                    <a href="/admin/new">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Registrar primera empresa
                    </a>
                @endif
            </div>
        @else
            <div class="grid">
                @foreach ($empresas as $empresa)
                    <a href="{{ \Filament\Facades\Filament::getUrl($empresa) }}" class="card">
                        <div class="card-top">
                            @if ($empresa->logo_path)
                                <div class="avatar">
                                    <img src="{{ route('logos.show', $empresa->logo_path) }}" alt="">
                                </div>
                            @else
                                <div class="avatar">
                                    {{ mb_strtoupper(mb_substr($empresa->razon_social, 0, 2)) }}
                                </div>
                            @endif
                            <div style="min-width:0;">
                                <p class="card-name">{{ $empresa->razon_social }}</p>
                                <p class="card-ruc">RUC: {{ $empresa->ruc }}</p>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <span class="badge {{ $empresa->estado === 'activo' ? 'badge-activo' : 'badge-inactivo' }}">
                                    {{ ucfirst($empresa->estado) }}
                                </span>
                                <span class="meta">{{ $empresa->users()->count() }} {{ $empresa->users()->count() === 1 ? 'usuario' : 'usuarios' }}</span>
                            </div>
                            <svg class="arrow" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        </div>
                    </a>
                @endforeach

                @if ($isSuperAdmin)
                    <a href="/admin/new" class="card card-new">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="margin:0 auto;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            <span>Nueva empresa</span>
                        </div>
                    </a>
                @endif
            </div>
        @endif

        <a href="/admin/logout" class="logout"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            Cerrar sesion
        </a>
        <form id="logout-form" action="/admin/logout" method="POST" style="display:none;">
            @csrf
        </form>
    </div>
</body>
</html>
