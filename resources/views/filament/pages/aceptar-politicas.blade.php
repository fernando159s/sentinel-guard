<x-filament-panels::page>
    <style>
        .fi-header { display: none !important; }
        .fi-main, .fi-page, .fi-page-main, body { overflow: hidden !important; max-height: 100vh !important; }
    </style>

    @if ($politicaActual)
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-5" style="height: calc(100vh - 5rem);">

            {{-- LEFT: Document content (3/5) --}}
            <div class="lg:col-span-3 flex flex-col rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">

                {{-- Header --}}
                <div class="border-b border-gray-200 dark:border-white/10 px-5 py-3">
                    <div class="flex items-center gap-3">
                        <x-heroicon-o-shield-check class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                        <div>
                            <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ $politicaActual->titulo }}</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Version {{ $politicaActual->version }} · {{ $politicaActual->obligatoria ? 'Obligatoria' : 'Opcional' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Document body --}}
                <div class="flex-1 overflow-y-auto px-6 py-4">
                    <div class="prose prose-sm dark:prose-invert max-w-none">
                        {!! $politicaActual->contenido !!}
                    </div>
                </div>

                {{-- Download --}}
                <div class="border-t border-gray-200 dark:border-white/10 px-5 py-2 flex items-center justify-between">
                    <x-filament::link :href="route('politicas.pdf', $politicaActual)" target="_blank" size="sm" color="gray" icon="heroicon-m-arrow-down-tray">
                        Descargar PDF
                    </x-filament::link>
                    <span class="text-[10px] text-gray-400">Scroll para leer el documento completo</span>
                </div>
            </div>

            {{-- RIGHT: Signature panel (2/5) --}}
            <div class="lg:col-span-2 flex flex-col rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">

                <div class="border-b border-gray-200 dark:border-white/10 px-5 py-3">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Firma del documento</h3>
                </div>

                <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">

                    {{-- Name + Cargo --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Nombre completo</label>
                        <input type="text" wire:model="firmaNombre" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Cargo (opcional)</label>
                        <input type="text" wire:model="firmaCargo" placeholder="Ej: Abogado, Asistente..." class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                    </div>

                    {{-- Method toggle --}}
                    <div class="flex gap-2">
                        <button wire:click="$set('metodoFirma', 'dibujar')" class="flex-1 rounded-lg px-3 py-1.5 text-xs font-medium text-center transition {{ $metodoFirma === 'dibujar' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' }}">
                            Dibujar
                        </button>
                        <button wire:click="$set('metodoFirma', 'subir')" class="flex-1 rounded-lg px-3 py-1.5 text-xs font-medium text-center transition {{ $metodoFirma === 'subir' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' }}">
                            Subir imagen
                        </button>
                    </div>

                    {{-- Draw signature --}}
                    @if ($metodoFirma === 'dibujar')
                        <div x-data="{
                            canvas: null, ctx: null, drawing: false, paths: [], currentPath: [],
                            init() {
                                this.canvas = this.$refs.pad;
                                this.ctx = this.canvas.getContext('2d');
                                this.ctx.strokeStyle = '#111827';
                                this.ctx.lineWidth = 2.5;
                                this.ctx.lineCap = 'round';
                                this.ctx.lineJoin = 'round';
                                const saved = @js($firmaDataUrl);
                                if (saved) { const img = new Image(); img.onload = () => this.ctx.drawImage(img, 0, 0); img.src = saved; }
                            },
                            getPos(e) {
                                const r = this.canvas.getBoundingClientRect();
                                const sx = this.canvas.width / r.width, sy = this.canvas.height / r.height;
                                return { x: ((e.clientX || e.touches?.[0]?.clientX) - r.left) * sx, y: ((e.clientY || e.touches?.[0]?.clientY) - r.top) * sy };
                            },
                            start(e) { this.drawing = true; this.currentPath = []; const p = this.getPos(e); this.currentPath.push(p); this.ctx.beginPath(); this.ctx.moveTo(p.x, p.y); },
                            move(e) { if (!this.drawing) return; e.preventDefault(); const p = this.getPos(e); this.currentPath.push(p); this.ctx.lineTo(p.x, p.y); this.ctx.stroke(); },
                            stop() { if (!this.drawing) return; this.drawing = false; if (this.currentPath.length > 1) this.paths.push([...this.currentPath]); $wire.set('firmaDataUrl', this.canvas.toDataURL('image/png')); },
                            clear() { this.paths = []; this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height); $wire.call('limpiarFirma'); }
                        }">
                            <div class="rounded-lg border-2 border-dashed border-gray-300 bg-white dark:border-gray-600 dark:bg-white" style="padding:2px;">
                                <canvas x-ref="pad" width="500" height="160" class="w-full rounded cursor-crosshair" style="touch-action:none;"
                                    @mousedown="start($event)" @mousemove="move($event)" @mouseup="stop()" @mouseleave="stop()"
                                    @touchstart="start($event)" @touchmove="move($event)" @touchend="stop()"></canvas>
                            </div>
                            <div class="mt-1 flex justify-between">
                                <span class="text-[10px] text-gray-400">Dibuja tu firma</span>
                                <button @click="clear()" class="text-[10px] font-medium text-danger-600 dark:text-danger-400 hover:underline">Limpiar</button>
                            </div>
                        </div>
                    @else
                        {{-- Upload --}}
                        <div>
                            <input type="file" wire:model="firmaUpload" accept="image/png,image/jpeg" class="block w-full text-sm text-gray-500 file:mr-3 file:rounded-lg file:border-0 file:bg-primary-50 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-primary-600 hover:file:bg-primary-100 dark:file:bg-primary-500/10 dark:file:text-primary-400">
                            <p class="mt-1 text-[10px] text-gray-400">PNG o JPG, fondo blanco o transparente</p>
                            @if ($firmaUpload)
                                <div class="mt-2 rounded-lg border border-gray-200 bg-white p-2 dark:border-gray-700">
                                    <img src="{{ $firmaUpload->temporaryUrl() }}" alt="Preview" class="h-16 object-contain">
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Checkbox --}}
                    <label class="flex items-start gap-3 cursor-pointer rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800">
                        <input type="checkbox" wire:model="acepto" class="mt-0.5 rounded border border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5">
                        <span class="text-xs text-gray-700 dark:text-gray-300 leading-relaxed">
                            He leido y acepto esta politica en su totalidad. Confirmo que la firma es mia y entiendo las consecuencias legales y laborales.
                        </span>
                    </label>
                </div>

                {{-- Sign button --}}
                <div class="border-t border-gray-200 dark:border-white/10 px-5 py-3">
                    <x-filament::button wire:click="aceptar" wire:loading.attr="disabled" icon="heroicon-m-pencil" class="w-full" size="lg">
                        Firmar y aceptar
                    </x-filament::button>
                </div>
            </div>
        </div>
    @else
        <div class="flex items-center justify-center" style="height: calc(100vh - 8rem);">
            <div class="text-center">
                <x-heroicon-o-check-circle class="mx-auto h-16 w-16 text-success-500 mb-3" />
                <p class="text-lg font-medium text-gray-900 dark:text-white">No tienes politicas pendientes</p>
            </div>
        </div>
    @endif
</x-filament-panels::page>
