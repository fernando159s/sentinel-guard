<x-filament-panels::page>
    @if ($politicaActual)
        <div class="mx-auto max-w-7xl px-4 pb-4">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-5" style="min-height: calc(100vh - 10rem);">

                {{-- LEFT: Document (3/5) --}}
                <div class="lg:col-span-3 flex flex-col rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">

                    {{-- Header --}}
                    <div class="flex items-center gap-3 border-b border-gray-200 px-6 py-4 dark:border-white/10">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary-50 dark:bg-primary-500/10">
                            <x-heroicon-o-shield-check class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ $politicaActual->titulo }}</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Version {{ $politicaActual->version }} · {{ $politicaActual->created_at->format('d/m/Y') }}</p>
                        </div>
                        <x-filament::link :href="route('politicas.pdf', $politicaActual)" target="_blank" size="sm" color="gray" icon="heroicon-m-arrow-down-tray">
                            PDF
                        </x-filament::link>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 overflow-y-auto px-8 py-6">
                        <div class="prose prose-sm dark:prose-invert max-w-none prose-headings:text-gray-900 dark:prose-headings:text-white prose-p:text-gray-700 dark:prose-p:text-gray-300">
                            {!! $politicaActual->contenido !!}
                        </div>
                    </div>
                </div>

                {{-- RIGHT: Signature (2/5) --}}
                <div class="lg:col-span-2 flex flex-col rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">

                    {{-- Header --}}
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-white/10">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Firmar documento</h3>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Completa los datos y firma para aceptar</p>
                    </div>

                    {{-- Form --}}
                    <div class="flex-1 space-y-5 overflow-y-auto px-6 py-5">

                        {{-- Name --}}
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">Nombre completo *</label>
                            <input type="text" wire:model="firmaNombre" required
                                   class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                        </div>

                        {{-- Cargo --}}
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">Cargo <span class="text-gray-400">(opcional)</span></label>
                            <input type="text" wire:model="firmaCargo" placeholder="Ej: Abogado, Asistente..."
                                   class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                        </div>

                        {{-- Method --}}
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">Metodo de firma</label>
                            <div class="flex gap-2">
                                <button wire:click="$set('metodoFirma', 'dibujar')"
                                        class="flex-1 rounded-lg border px-3 py-2 text-center text-xs font-medium transition {{ $metodoFirma === 'dibujar' ? 'border-primary-500 bg-primary-50 text-primary-700 dark:border-primary-500 dark:bg-primary-500/10 dark:text-primary-400' : 'border-gray-200 bg-gray-50 text-gray-600 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700' }}">
                                    &#9999;&#65039; Dibujar
                                </button>
                                <button wire:click="$set('metodoFirma', 'subir')"
                                        class="flex-1 rounded-lg border px-3 py-2 text-center text-xs font-medium transition {{ $metodoFirma === 'subir' ? 'border-primary-500 bg-primary-50 text-primary-700 dark:border-primary-500 dark:bg-primary-500/10 dark:text-primary-400' : 'border-gray-200 bg-gray-50 text-gray-600 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700' }}">
                                    &#128193; Subir imagen
                                </button>
                            </div>
                        </div>

                        {{-- Draw --}}
                        @if ($metodoFirma === 'dibujar')
                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">Tu firma</label>
                                <div x-data="{
                                    canvas: null, ctx: null, drawing: false, paths: [], cur: [],
                                    init() {
                                        this.canvas = this.$refs.pad;
                                        this.ctx = this.canvas.getContext('2d');
                                        this.ctx.strokeStyle = '#111827';
                                        this.ctx.lineWidth = 2.5;
                                        this.ctx.lineCap = 'round';
                                        this.ctx.lineJoin = 'round';
                                        const s = @js($firmaDataUrl);
                                        if (s) { const i = new Image(); i.onload = () => this.ctx.drawImage(i, 0, 0); i.src = s; }
                                    },
                                    pos(e) { const r = this.canvas.getBoundingClientRect(); return { x: ((e.clientX||e.touches?.[0]?.clientX)-r.left)*(this.canvas.width/r.width), y: ((e.clientY||e.touches?.[0]?.clientY)-r.top)*(this.canvas.height/r.height) }; },
                                    dn(e) { this.drawing=true; this.cur=[]; const p=this.pos(e); this.cur.push(p); this.ctx.beginPath(); this.ctx.moveTo(p.x,p.y); },
                                    mv(e) { if(!this.drawing)return; e.preventDefault(); const p=this.pos(e); this.cur.push(p); this.ctx.lineTo(p.x,p.y); this.ctx.stroke(); },
                                    up() { if(!this.drawing)return; this.drawing=false; if(this.cur.length>1) this.paths.push([...this.cur]); $wire.set('firmaDataUrl', this.canvas.toDataURL('image/png')); },
                                    clr() { this.paths=[]; this.ctx.clearRect(0,0,this.canvas.width,this.canvas.height); $wire.call('limpiarFirma'); }
                                }">
                                    <div class="overflow-hidden rounded-lg border border-gray-300 bg-white dark:border-gray-600">
                                        <canvas x-ref="pad" width="500" height="160" class="w-full cursor-crosshair" style="touch-action:none;"
                                            @mousedown="dn($event)" @mousemove="mv($event)" @mouseup="up()" @mouseleave="up()"
                                            @touchstart="dn($event)" @touchmove="mv($event)" @touchend="up()"></canvas>
                                    </div>
                                    <div class="mt-1.5 flex items-center justify-between">
                                        <span class="text-[10px] text-gray-400 dark:text-gray-500">Dibuja con mouse o dedo</span>
                                        <button @click="clr()" type="button" class="text-[10px] font-medium text-danger-600 hover:underline dark:text-danger-400">Limpiar</button>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">Imagen de firma</label>
                                <input type="file" wire:model="firmaUpload" accept="image/png,image/jpeg"
                                       class="block w-full text-sm text-gray-500 file:mr-3 file:rounded-lg file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-primary-500/10 dark:file:text-primary-400">
                                <p class="mt-1 text-[10px] text-gray-400">PNG o JPG, fondo blanco o transparente</p>
                                @if ($firmaUpload)
                                    <div class="mt-2 rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700">
                                        <img src="{{ $firmaUpload->temporaryUrl() }}" alt="Preview" class="h-16 object-contain">
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Checkbox --}}
                        <label class="flex items-start gap-3 cursor-pointer rounded-lg border border-gray-200 bg-gray-50 p-4 transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-750">
                            <input type="checkbox" wire:model="acepto"
                                   class="mt-0.5 rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-white/10 dark:bg-white/5">
                            <span class="text-xs leading-relaxed text-gray-700 dark:text-gray-300">
                                He leido y acepto esta politica en su totalidad. Confirmo que la firma es mia y entiendo las consecuencias legales y laborales.
                            </span>
                        </label>
                    </div>

                    {{-- Button --}}
                    <div class="border-t border-gray-200 px-6 py-4 dark:border-white/10">
                        <x-filament::button wire:click="aceptar" wire:loading.attr="disabled" icon="heroicon-m-pencil" class="w-full" size="lg">
                            Firmar y aceptar
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="flex items-center justify-center py-20">
            <div class="text-center">
                <x-heroicon-o-check-circle class="mx-auto h-16 w-16 text-success-500 mb-3" />
                <p class="text-lg font-medium text-gray-900 dark:text-white">No tienes politicas pendientes</p>
                <p class="mt-1 text-sm text-gray-500">Todas las politicas han sido aceptadas.</p>
            </div>
        </div>
    @endif
</x-filament-panels::page>
