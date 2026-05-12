<x-filament-panels::page.simple>
    <form wire:submit="registrar">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">

            {{-- ── EMPRESA ── --}}
            <x-filament::section icon="heroicon-o-building-office-2" heading="Datos de la empresa">
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div>
                            <label class="text-xs font-medium text-gray-700 dark:text-gray-300">RUC *</label>
                            <input type="text" wire:model="ruc" maxlength="11" placeholder="Ej: 20XXXXXXXXX"
                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                            @error('ruc') <span class="text-xs text-danger-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Telefono</label>
                            <input type="tel" wire:model="telefono" placeholder="+51 XXX XXX XXX"
                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Razon social *</label>
                        <input type="text" wire:model="razon_social" maxlength="200" placeholder="Nombre de la empresa"
                               class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                        @error('razon_social') <span class="text-xs text-danger-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Email *</label>
                        <input type="email" wire:model="empresa_email" placeholder="correo@miempresa.com"
                               class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                        @error('empresa_email') <span class="text-xs text-danger-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Direccion</label>
                        <input type="text" wire:model="direccion" placeholder="Direccion de la empresa"
                               class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                    </div>

                    {{-- Logo inline --}}
                    <div>
                        <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Logo de la empresa</label>
                        <div class="mt-1 flex items-center gap-3">
                            @if ($logo_upload)
                                <img src="{{ $logo_upload->temporaryUrl() }}" alt="Logo" style="height:36px; width:36px; border-radius:8px; object-fit:cover;">
                            @else
                                <div style="height:36px; width:36px; border-radius:8px; display:flex; align-items:center; justify-content:center;" class="bg-gray-100 dark:bg-white/5">
                                    <x-heroicon-o-photo class="w-4 h-4 text-gray-400"/>
                                </div>
                            @endif
                            <input type="file" wire:model="logo_upload" accept="image/jpeg,image/png,image/svg+xml"
                                   class="block text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 dark:file:bg-white/10 dark:file:text-gray-300">
                        </div>
                        @error('logo_upload') <span class="text-xs text-danger-600 mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
            </x-filament::section>

            {{-- ── ADMIN ── --}}
            <x-filament::section icon="heroicon-o-user-plus" heading="Administrador de la empresa" description="Se creara un usuario con rol admin_empresa">
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div>
                            <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Nombre *</label>
                            <input type="text" wire:model="admin_name" placeholder="Nombre completo"
                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                            @error('admin_name') <span class="text-xs text-danger-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Contrasena *</label>
                            <input type="password" wire:model="admin_password" placeholder="Min. 8 caracteres"
                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                            @error('admin_password') <span class="text-xs text-danger-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Email *</label>
                        <input type="email" wire:model="admin_email" placeholder="admin@miempresa.com"
                               class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                        @error('admin_email') <span class="text-xs text-danger-600">{{ $message }}</span> @enderror
                    </div>

                    {{-- Firma --}}
                    <div>
                        <div style="display:flex; align-items:center; justify-content:space-between;">
                            <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Firma (opcional)</label>
                            <div style="display:flex; gap:2px;">
                                <button type="button" wire:click="$set('metodoFirma', 'dibujar')"
                                        class="px-2.5 py-1 rounded-md text-xs transition-colors {{ $metodoFirma === 'dibujar' ? 'bg-primary-500/10 text-primary-600 font-medium dark:text-primary-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">
                                    Dibujar
                                </button>
                                <button type="button" wire:click="$set('metodoFirma', 'subir')"
                                        class="px-2.5 py-1 rounded-md text-xs transition-colors {{ $metodoFirma === 'subir' ? 'bg-primary-500/10 text-primary-600 font-medium dark:text-primary-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">
                                    Subir imagen
                                </button>
                            </div>
                        </div>

                        @if ($metodoFirma === 'dibujar')
                            <div class="mt-1.5" x-data="{
                                c: null, x: null, d: false, cp: [],
                                init() {
                                    this.c = this.$refs.canvas;
                                    this.x = this.c.getContext('2d');
                                    this.x.strokeStyle = getComputedStyle(document.documentElement).getPropertyValue('color-scheme') === 'dark' ? '#e5e7eb' : '#111';
                                    this.x.lineWidth = 2; this.x.lineCap = 'round'; this.x.lineJoin = 'round';
                                },
                                getPos(e) { const r = this.c.getBoundingClientRect(); return { x: ((e.clientX||e.touches?.[0]?.clientX) - r.left) * (this.c.width/r.width), y: ((e.clientY||e.touches?.[0]?.clientY) - r.top) * (this.c.height/r.height) }; },
                                start(e) { this.d = true; this.cp = []; const p = this.getPos(e); this.cp.push(p); this.x.beginPath(); this.x.moveTo(p.x, p.y); },
                                move(e) { if (!this.d) return; e.preventDefault(); const p = this.getPos(e); this.cp.push(p); this.x.lineTo(p.x, p.y); this.x.stroke(); },
                                end() { if (!this.d) return; this.d = false; $wire.set('firmaDataUrl', this.c.toDataURL('image/png')); },
                                clear() { this.x.clearRect(0, 0, this.c.width, this.c.height); $wire.set('firmaDataUrl', ''); }
                            }" @firma-cleared.window="clear()">
                                <div style="border:1.5px dashed; border-radius:8px; overflow:hidden; position:relative;" class="border-gray-300 bg-white dark:border-white/10 dark:bg-white/5">
                                    <canvas x-ref="canvas" width="460" height="160" style="width:100%; cursor:crosshair; touch-action:none; display:block;"
                                            @mousedown="start($event)" @mousemove="move($event)" @mouseup="end()" @mouseleave="end()"
                                            @touchstart="start($event)" @touchmove="move($event)" @touchend="end()"></canvas>
                                </div>
                                <div style="display:flex; justify-content:flex-end; margin-top:4px;">
                                    <button @click="clear()" type="button" class="text-xs text-danger-600 dark:text-danger-400 hover:underline">Limpiar</button>
                                </div>
                            </div>
                        @else
                            <div class="mt-1.5">
                                <input type="file" wire:model="firmaUpload" accept="image/png,image/jpeg"
                                       class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 dark:file:bg-white/10 dark:file:text-gray-300">
                                @if ($firmaUpload)
                                    <div class="mt-2 p-2 rounded-lg bg-gray-50 dark:bg-white/5">
                                        <img src="{{ $firmaUpload->temporaryUrl() }}" alt="Preview" style="max-height:60px; max-width:100%; margin:0 auto; display:block;">
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </x-filament::section>
        </div>

        {{-- ── PERSONALIZACION (colapsable) ── --}}
        <div style="margin-top:20px;" x-data="{ open: @entangle('showPersonalizacion') }">
            <x-filament::section icon="heroicon-o-paint-brush" heading="Personalizacion del portal" description="Opcional: colores personalizados para esta empresa"
                                 :collapsible="true" :collapsed="true">
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
                    <div>
                        <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Color primario</label>
                        <div class="mt-1 flex items-center gap-2">
                            <input type="color" wire:model="color_primario" style="height:34px; width:34px; padding:0; border:none; border-radius:6px; cursor:pointer;">
                            <input type="text" wire:model="color_primario" placeholder="#8b5cf6" maxlength="7"
                                   class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-900 shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Color secundario</label>
                        <div class="mt-1 flex items-center gap-2">
                            <input type="color" wire:model="color_secundario" style="height:34px; width:34px; padding:0; border:none; border-radius:6px; cursor:pointer;">
                            <input type="text" wire:model="color_secundario" placeholder="#6366f1" maxlength="7"
                                   class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-900 shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Color sidebar</label>
                        <div class="mt-1 flex items-center gap-2">
                            <input type="color" wire:model="color_sidebar" style="height:34px; width:34px; padding:0; border:none; border-radius:6px; cursor:pointer;">
                            <input type="text" wire:model="color_sidebar" placeholder="#1e1b4b" maxlength="7"
                                   class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-900 shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                        </div>
                    </div>
                </div>
            </x-filament::section>
        </div>

        {{-- ── SUBMIT ── --}}
        <div style="display:flex; justify-content:flex-end; margin-top:20px;">
            <x-filament::button type="submit" size="lg" icon="heroicon-o-rocket-launch">
                Registrar empresa
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page.simple>
