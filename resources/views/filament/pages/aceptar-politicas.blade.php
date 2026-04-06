<x-filament-panels::page>
    <div class="mx-auto max-w-3xl">
        @if ($politicaActual)
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary-50 dark:bg-primary-500/10">
                        <x-heroicon-o-shield-check class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $politicaActual->titulo }}</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Version {{ $politicaActual->version }}</p>
                    </div>
                </div>

                {{-- Policy content --}}
                <div class="prose prose-sm dark:prose-invert max-h-[35vh] overflow-y-auto rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                    {!! $politicaActual->contenido !!}
                </div>

                {{-- Signature section --}}
                <div class="mt-5 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Firma del documento</h3>

                    {{-- Name + Position --}}
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Nombre completo</label>
                            <input type="text" wire:model="firmaNombre" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Cargo (opcional)</label>
                            <input type="text" wire:model="firmaCargo" placeholder="Ej: Abogado, Asistente..." class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                        </div>
                    </div>

                    {{-- Method toggle --}}
                    <div class="flex gap-2 mb-3">
                        <button wire:click="$set('metodoFirma', 'dibujar')" class="rounded-lg px-3 py-1.5 text-xs font-medium transition {{ $metodoFirma === 'dibujar' ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                            Dibujar firma
                        </button>
                        <button wire:click="$set('metodoFirma', 'subir')" class="rounded-lg px-3 py-1.5 text-xs font-medium transition {{ $metodoFirma === 'subir' ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                            Subir imagen
                        </button>
                    </div>

                    {{-- Draw signature --}}
                    @if ($metodoFirma === 'dibujar')
                        <div x-data="{
                            canvas: null,
                            ctx: null,
                            drawing: false,
                            paths: [],
                            currentPath: [],
                            neonColor: '#00ff88',
                            init() {
                                this.canvas = this.$refs.signaturePad;
                                this.ctx = this.canvas.getContext('2d');
                                this.setupCtx();

                                const saved = @js($firmaDataUrl);
                                if (saved) {
                                    const img = new Image();
                                    img.onload = () => { this.ctx.drawImage(img, 0, 0); };
                                    img.src = saved;
                                }
                            },
                            setupCtx() {
                                this.ctx.strokeStyle = this.neonColor;
                                this.ctx.lineWidth = 3;
                                this.ctx.lineCap = 'round';
                                this.ctx.lineJoin = 'round';
                                this.ctx.shadowColor = this.neonColor;
                                this.ctx.shadowBlur = 6;
                            },
                            getPos(e) {
                                const rect = this.canvas.getBoundingClientRect();
                                const scaleX = this.canvas.width / rect.width;
                                const scaleY = this.canvas.height / rect.height;
                                const cx = (e.clientX || e.touches?.[0]?.clientX) - rect.left;
                                const cy = (e.clientY || e.touches?.[0]?.clientY) - rect.top;
                                return { x: cx * scaleX, y: cy * scaleY };
                            },
                            startDraw(e) {
                                this.drawing = true;
                                this.currentPath = [];
                                const p = this.getPos(e);
                                this.currentPath.push(p);
                                this.ctx.beginPath();
                                this.ctx.moveTo(p.x, p.y);
                            },
                            draw(e) {
                                if (!this.drawing) return;
                                e.preventDefault();
                                const p = this.getPos(e);
                                this.currentPath.push(p);
                                this.ctx.lineTo(p.x, p.y);
                                this.ctx.stroke();
                            },
                            stopDraw() {
                                if (!this.drawing) return;
                                this.drawing = false;
                                if (this.currentPath.length > 1) {
                                    this.paths.push([...this.currentPath]);
                                }
                                this.exportBlack();
                            },
                            exportBlack() {
                                // Redraw all paths in black on a temp canvas for export
                                const tmp = document.createElement('canvas');
                                tmp.width = this.canvas.width;
                                tmp.height = this.canvas.height;
                                const tCtx = tmp.getContext('2d');
                                tCtx.strokeStyle = '#000000';
                                tCtx.lineWidth = 2.5;
                                tCtx.lineCap = 'round';
                                tCtx.lineJoin = 'round';
                                for (const path of this.paths) {
                                    tCtx.beginPath();
                                    tCtx.moveTo(path[0].x, path[0].y);
                                    for (let i = 1; i < path.length; i++) {
                                        tCtx.lineTo(path[i].x, path[i].y);
                                    }
                                    tCtx.stroke();
                                }
                                $wire.set('firmaDataUrl', tmp.toDataURL('image/png'));
                            },
                            clear() {
                                this.paths = [];
                                this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                                $wire.call('limpiarFirma');
                            }
                        }">
                            <div class="rounded-lg border-2 border-dashed border-gray-300 bg-white dark:border-gray-600 dark:bg-gray-950" style="padding:2px;">
                                <canvas x-ref="signaturePad" width="600" height="150"
                                        class="w-full rounded cursor-crosshair"
                                        style="touch-action:none; background: rgba(0,0,0,0.02);"
                                        @mousedown="startDraw($event)"
                                        @mousemove="draw($event)"
                                        @mouseup="stopDraw()"
                                        @mouseleave="stopDraw()"
                                        @touchstart="startDraw($event)"
                                        @touchmove="draw($event)"
                                        @touchend="stopDraw()">
                                </canvas>
                            </div>
                            <div class="mt-1.5 flex items-center justify-between">
                                <span class="text-[10px] text-gray-400 dark:text-gray-500">Dibuja tu firma con el mouse o dedo</span>
                                <button @click="clear()" class="rounded px-2 py-0.5 text-[10px] font-medium text-danger-600 transition hover:bg-danger-50 dark:text-danger-400 dark:hover:bg-danger-500/10">Limpiar</button>
                            </div>
                        </div>
                    @else
                        {{-- Upload signature --}}
                        <div>
                            <input type="file" wire:model="firmaUpload" accept="image/png,image/jpeg" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-600 hover:file:bg-primary-100 dark:file:bg-primary-500/10 dark:file:text-primary-400">
                            <p class="mt-1 text-[10px] text-gray-400">PNG o JPG, fondo transparente recomendado</p>
                            @if ($firmaUpload)
                                <div class="mt-2 rounded-lg border border-gray-200 bg-white p-2 dark:border-gray-700 dark:bg-gray-900">
                                    <img src="{{ $firmaUpload->temporaryUrl() }}" alt="Preview" class="h-16 object-contain">
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Accept --}}
                <div class="mt-5 border-t border-gray-200 pt-4 dark:border-gray-700">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" wire:model="acepto"
                               class="mt-0.5 rounded border border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-white/10 dark:bg-white/5">
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            He leido y acepto esta politica en su totalidad. Confirmo que la firma es mia y entiendo las consecuencias legales.
                        </span>
                    </label>

                    <div class="mt-4 flex items-center justify-between">
                        <x-filament::link
                            :href="route('politicas.pdf', $politicaActual)"
                            target="_blank"
                            size="sm"
                            color="gray"
                            icon="heroicon-m-arrow-down-tray">
                            Descargar PDF
                        </x-filament::link>

                        <x-filament::button wire:click="aceptar" wire:loading.attr="disabled" icon="heroicon-m-pencil" size="lg">
                            Firmar y aceptar
                        </x-filament::button>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-12 text-gray-500">
                <x-heroicon-o-check-circle class="mx-auto h-12 w-12 text-success-500 mb-3" />
                <p class="text-lg font-medium">No tienes politicas pendientes</p>
            </div>
        @endif
    </div>
</x-filament-panels::page>
