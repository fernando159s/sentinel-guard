<x-filament-panels::page>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">

        {{-- LEFT COLUMN --}}
        <div style="display:flex; flex-direction:column; gap:20px;">

            {{-- Datos personales --}}
            <x-filament::section icon="heroicon-o-user" heading="Datos personales" description="Tu informacion basica y de contacto.">
                <form wire:submit="guardarDatos" class="space-y-4">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div>
                            <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Nombre completo *</label>
                            <input type="text" wire:model="name" required
                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                            @error('name') <span class="text-xs text-danger-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Email *</label>
                            <input type="email" wire:model="email" required
                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                            @error('email') <span class="text-xs text-danger-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 dark:text-gray-300">DNI</label>
                            <input type="text" wire:model="dni" maxlength="20"
                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Telefono</label>
                            <input type="tel" wire:model="telefono" maxlength="30"
                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Puesto</label>
                            <input type="text" wire:model="puesto" maxlength="150" placeholder="Ej: Gerente General, Abogado..."
                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Empresa</label>
                            <input type="text" disabled value="{{ auth()->user()->empresa?->razon_social ?? '-' }}"
                                   class="mt-1 block w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-500 dark:border-white/5 dark:bg-white/5 dark:text-gray-400">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Direccion</label>
                        <input type="text" wire:model="direccion" maxlength="500"
                               class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                    </div>
                    <div style="display:flex; justify-content:flex-end;">
                        <x-filament::button type="submit" size="sm" icon="heroicon-m-check">
                            Guardar datos
                        </x-filament::button>
                    </div>
                </form>
            </x-filament::section>

            {{-- Cambiar contrasena --}}
            <x-filament::section icon="heroicon-o-lock-closed" heading="Cambiar contrasena" collapsible collapsed>
                <form wire:submit="cambiarPassword" class="space-y-4">
                    <div>
                        <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Contrasena actual *</label>
                        <input type="password" wire:model="current_password" required
                               class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                        @error('current_password') <span class="text-xs text-danger-600">{{ $message }}</span> @enderror
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div>
                            <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Nueva contrasena *</label>
                            <input type="password" wire:model="new_password" required
                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                            @error('new_password') <span class="text-xs text-danger-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Confirmar contrasena *</label>
                            <input type="password" wire:model="new_password_confirmation" required
                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                        </div>
                    </div>
                    <div style="display:flex; justify-content:flex-end;">
                        <x-filament::button type="submit" size="sm" icon="heroicon-m-lock-closed" color="warning">
                            Cambiar contrasena
                        </x-filament::button>
                    </div>
                </form>
            </x-filament::section>

            {{-- Notificaciones --}}
            <x-filament::section icon="heroicon-o-bell" heading="Notificaciones" description="Elige que notificaciones recibir por email.">
                <form wire:submit="guardarNotificaciones" class="space-y-3">
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                        <input type="checkbox" wire:model="notif_tickets"
                               class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5">
                        <div>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Tickets</span>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Asignacion, respuestas y reapertura de tickets.</p>
                        </div>
                    </label>
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                        <input type="checkbox" wire:model="notif_incidencias"
                               class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5">
                        <div>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Incidencias</span>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Nuevas incidencias de seguridad (F09).</p>
                        </div>
                    </label>
                    <div style="display:flex; justify-content:flex-end;">
                        <x-filament::button type="submit" size="sm" icon="heroicon-m-check">
                            Guardar preferencias
                        </x-filament::button>
                    </div>
                </form>
            </x-filament::section>
        </div>

        {{-- RIGHT COLUMN: Firma --}}
        <div>
            <x-filament::section icon="heroicon-o-pencil" heading="Mi firma" description="Tu firma aparecera en politicas, NDAs y documentos oficiales. Puedes dibujarla o subir una imagen.">

                {{-- Firma actual --}}
                @if ($firmaDataUrl && str_starts_with($firmaDataUrl, 'data:image'))
                    <div style="margin-bottom:16px;">
                        <div class="text-xs font-medium text-gray-700 dark:text-gray-300" style="margin-bottom:6px;">Firma actual</div>
                        <div style="border:1px solid; border-radius:10px; padding:16px; text-align:center;" class="border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
                            <img src="{{ $firmaDataUrl }}" alt="Firma actual" style="max-height:100px; max-width:100%; margin:0 auto; display:block;">
                        </div>
                        <div style="display:flex; justify-content:flex-end; margin-top:6px;">
                            <button wire:click="eliminarFirma" wire:confirm="Eliminar tu firma guardada?" type="button" class="text-xs text-danger-600 dark:text-danger-400 hover:underline">
                                Eliminar firma
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Metodo selector --}}
                <div class="text-xs font-medium text-gray-700 dark:text-gray-300" style="margin-bottom:6px;">
                    {{ $firmaDataUrl ? 'Cambiar firma' : 'Agregar firma' }}
                </div>
                <div style="display:flex; gap:6px; margin-bottom:12px;">
                    <button wire:click="$set('metodoFirma', 'dibujar')" type="button"
                            style="flex:1; padding:8px; border-radius:8px; font-size:12px; font-weight:600; text-align:center; border:1px solid; cursor:pointer;"
                            class="{{ $metodoFirma === 'dibujar' ? 'border-primary-500 bg-primary-50 text-primary-700 dark:border-primary-400 dark:bg-primary-500/10 dark:text-primary-400' : 'border-gray-200 text-gray-500 dark:border-gray-700 dark:text-gray-400' }}">
                        Dibujar firma
                    </button>
                    <button wire:click="$set('metodoFirma', 'subir')" type="button"
                            style="flex:1; padding:8px; border-radius:8px; font-size:12px; font-weight:600; text-align:center; border:1px solid; cursor:pointer;"
                            class="{{ $metodoFirma === 'subir' ? 'border-primary-500 bg-primary-50 text-primary-700 dark:border-primary-400 dark:bg-primary-500/10 dark:text-primary-400' : 'border-gray-200 text-gray-500 dark:border-gray-700 dark:text-gray-400' }}">
                        Subir imagen
                    </button>
                </div>

                {{-- Canvas --}}
                @if ($metodoFirma === 'dibujar')
                    <div x-data="{
                        c: null,
                        x: null,
                        d: false,
                        ps: [],
                        cp: [],
                        fs: false,
                        init() {
                            this.c = this.$refs.canvas;
                            this.x = this.c.getContext('2d');
                            this.applyStyle();
                        },
                        applyStyle() {
                            this.x.strokeStyle = '#111';
                            this.x.lineWidth = this.fs ? 4 : 2.5;
                            this.x.lineCap = 'round';
                            this.x.lineJoin = 'round';
                        },
                        getPos(e) {
                            const r = this.c.getBoundingClientRect();
                            const clientX = e.clientX ?? e.touches?.[0]?.clientX;
                            const clientY = e.clientY ?? e.touches?.[0]?.clientY;
                            return {
                                x: (clientX - r.left) * (this.c.width / r.width),
                                y: (clientY - r.top) * (this.c.height / r.height)
                            };
                        },
                        start(e) {
                            this.d = true;
                            this.cp = [];
                            const p = this.getPos(e);
                            this.cp.push(p);
                            this.x.beginPath();
                            this.x.moveTo(p.x, p.y);
                        },
                        move(e) {
                            if (!this.d) return;
                            e.preventDefault();
                            const p = this.getPos(e);
                            this.cp.push(p);
                            this.x.lineTo(p.x, p.y);
                            this.x.stroke();
                        },
                        end() {
                            if (!this.d) return;
                            this.d = false;
                            if (this.cp.length > 1) this.ps.push([...this.cp]);
                            $wire.set('firmaDataUrl', this.c.toDataURL('image/png'));
                        },
                        clear() {
                            this.ps = [];
                            this.x.clearRect(0, 0, this.c.width, this.c.height);
                            $wire.set('firmaDataUrl', '');
                        },
                        redraw() {
                            this.x.clearRect(0, 0, this.c.width, this.c.height);
                            this.applyStyle();
                            for (const path of this.ps) {
                                if (!path.length) continue;
                                this.x.beginPath();
                                this.x.moveTo(path[0].x, path[0].y);
                                for (let i = 1; i < path.length; i++) {
                                    this.x.lineTo(path[i].x, path[i].y);
                                }
                                this.x.stroke();
                            }
                        },
                        toggleFs() {
                            const oldW = this.c.width, oldH = this.c.height;
                            this.fs = !this.fs;
                            if (this.fs) {
                                document.body.style.overflow = 'hidden';
                            } else {
                                document.body.style.overflow = '';
                            }
                            this.$nextTick(() => {
                                const rect = this.c.getBoundingClientRect();
                                const newW = Math.max(1, Math.round(rect.width));
                                const newH = Math.max(1, Math.round(rect.height));
                                const sx = newW / oldW, sy = newH / oldH;
                                this.ps = this.ps.map(p => p.map(pt => ({ x: pt.x * sx, y: pt.y * sy })));
                                this.c.width = newW;
                                this.c.height = newH;
                                this.redraw();
                                if (this.ps.length) $wire.set('firmaDataUrl', this.c.toDataURL('image/png'));
                            });
                        }
                    }" @firma-cleared.window="clear()" @keydown.escape.window="if (fs) toggleFs()">
                        <div :style="fs ? 'position:fixed; inset:0; z-index:9999; padding:20px; display:flex; flex-direction:column; gap:12px; background:rgba(17,24,39,0.96); backdrop-filter: blur(4px);' : ''">
                            <template x-if="fs">
                                <div style="display:flex; justify-content:space-between; align-items:center; color:#fff;">
                                    <span style="font-size:14px; font-weight:600;">Firma a pantalla completa</span>
                                    <button @click="toggleFs()" type="button" style="padding:6px 12px; border-radius:8px; background:rgba(255,255,255,0.1); color:#fff; font-size:12px; font-weight:600; cursor:pointer;">
                                        Cerrar
                                    </button>
                                </div>
                            </template>
                            <div :style="fs ? 'flex:1; min-height:0;' : ''" style="border:2px dashed; border-radius:10px; overflow:hidden; position:relative;" class="border-gray-300 bg-white dark:border-gray-600 dark:bg-gray-800">
                                <canvas x-ref="canvas" width="500" height="160"
                                        :style="fs ? 'width:100%; height:100%; cursor:crosshair; touch-action:none; display:block;' : 'width:100%; cursor:crosshair; touch-action:none; display:block;'"
                                        @mousedown="start($event)" @mousemove="move($event)" @mouseup="end()" @mouseleave="end()"
                                        @touchstart="start($event)" @touchmove="move($event)" @touchend="end()">
                                </canvas>
                            </div>
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:6px;" :style="fs ? 'color:#fff;' : ''">
                                <span class="text-xs" :class="fs ? '' : 'text-gray-400'">Usa el mouse o el dedo en pantalla tactil</span>
                                <div style="display:flex; gap:12px; align-items:center;">
                                    <button @click="toggleFs()" type="button" class="text-xs hover:underline" :class="fs ? 'text-white' : 'text-primary-600 dark:text-primary-400'">
                                        <span x-text="fs ? 'Reducir' : 'Ampliar a pantalla completa'"></span>
                                    </button>
                                    <button @click="clear()" type="button" class="text-xs text-danger-600 dark:text-danger-400 hover:underline">Limpiar canvas</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Upload --}}
                    <div>
                        <input type="file" wire:model="firmaUpload" accept="image/png,image/jpeg"
                               class="block w-full text-sm text-gray-500 file:mr-3 file:rounded-lg file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-primary-700 dark:file:bg-primary-500/10 dark:file:text-primary-400">
                        <p class="text-xs text-gray-400 mt-1">PNG o JPG, max 1MB. Fondo transparente recomendado.</p>
                        @if ($firmaUpload)
                            <div style="margin-top:10px; border:1px solid; border-radius:10px; padding:12px; text-align:center;" class="border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
                                <img src="{{ $firmaUpload->temporaryUrl() }}" alt="Preview" style="max-height:80px; max-width:100%; margin:0 auto; display:block;">
                            </div>
                        @endif
                    </div>
                @endif

                <div style="display:flex; justify-content:flex-end; margin-top:16px;">
                    <x-filament::button wire:click="guardarFirma" size="sm" icon="heroicon-m-pencil" color="success">
                        Guardar firma
                    </x-filament::button>
                </div>
            </x-filament::section>

            {{-- Info card --}}
            <div style="margin-top:16px; padding:14px 16px; border-radius:10px; border:1px solid;" class="border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
                <div class="text-xs font-semibold text-gray-700 dark:text-gray-300" style="margin-bottom:6px;">Informacion de cuenta</div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;" class="text-xs text-gray-500 dark:text-gray-400">
                    <div>
                        <span class="font-medium">Rol:</span>
                        {{ auth()->user()->roles->first()?->name ?? 'usuario' }}
                    </div>
                    <div>
                        <span class="font-medium">Estado:</span>
                        {{ ucfirst(auth()->user()->estado ?? 'activo') }}
                    </div>
                    <div>
                        <span class="font-medium">Ultimo acceso:</span>
                        {{ auth()->user()->ultimo_acceso?->format('d/m/Y H:i') ?? '-' }}
                    </div>
                    <div>
                        <span class="font-medium">Miembro desde:</span>
                        {{ auth()->user()->created_at?->format('d/m/Y') ?? '-' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
