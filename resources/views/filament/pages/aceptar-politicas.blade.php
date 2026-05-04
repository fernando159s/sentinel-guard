<x-filament-panels::page>
    <style>
        .wiki-content h1 { font-size:1.5em; font-weight:700; margin:0 0 .6em; }
        .wiki-content h2 { font-size:1.25em; font-weight:600; margin:1.2em 0 .5em; }
        .wiki-content h3 { font-size:1.1em; font-weight:600; margin:1em 0 .4em; }
        .wiki-content p { margin:0 0 .8em; }
        .wiki-content ul, .wiki-content ol { padding-left:1.5em; margin:0 0 .8em; }
        .wiki-content li { margin:0 0 .3em; }
        .wiki-content table { width:100%; border-collapse:collapse; margin:1em 0; font-size:.9em; }
        .wiki-content th, .wiki-content td { border:1px solid rgba(128,128,128,.25); padding:6px 10px; text-align:left; }
        .wiki-content th { font-weight:600; }
        .wiki-content strong { font-weight:700; }
        .wiki-content em { font-style:italic; }
        .wiki-content blockquote { border-left:3px solid rgba(128,128,128,.3); padding-left:12px; margin:0 0 .8em; font-style:italic; }
    </style>

    @if ($politicaActual)
        <div style="display:grid; grid-template-columns: 3fr 2fr; gap:20px; height:calc(100vh - 9rem);">

            {{-- LEFT: Document --}}
            <div style="display:flex; flex-direction:column; border-radius:12px; overflow:hidden;" class="bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">

                <div style="padding:16px 20px; border-bottom:1px solid rgba(128,128,128,0.15); display:flex; align-items:center; justify-content:space-between;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        @if ($politicaActual->es_nda)
                            <x-heroicon-o-lock-closed style="width:20px; height:20px;" class="text-warning-600 dark:text-warning-400" />
                        @else
                            <x-heroicon-o-shield-check style="width:20px; height:20px;" class="text-primary-600 dark:text-primary-400" />
                        @endif
                        <div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $politicaActual->titulo }}
                                @if ($politicaActual->es_nda)
                                    <span style="font-size:10px; padding:2px 6px; border-radius:4px; margin-left:6px;" class="bg-warning-100 text-warning-700 dark:bg-warning-500/10 dark:text-warning-400">NDA</span>
                                @endif
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                v{{ $politicaActual->version }} · {{ $politicaActual->created_at->format('d/m/Y') }}
                                @if ($politicaActual->es_nda && $politicaActual->vigencia_meses)
                                    · Vigencia: {{ $politicaActual->vigencia_meses }} meses
                                @endif
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('politicas.pdf', $politicaActual) }}" target="_blank" class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" style="white-space:nowrap;">
                        Descargar PDF
                    </a>
                </div>

                <div style="flex:1; overflow-y:auto; padding:24px;">
                    <div class="wiki-content" style="font-size:14px; line-height:1.7; color:inherit;">
                        {!! $this->getContenidoRenderizado() !!}
                    </div>
                </div>
            </div>

            {{-- RIGHT: Signature --}}
            <div style="display:flex; flex-direction:column; border-radius:12px; overflow:hidden;" class="bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">

                <div style="padding:16px 20px; border-bottom:1px solid rgba(128,128,128,0.15);">
                    <div class="text-sm font-semibold text-gray-900 dark:text-white">Firmar documento</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Completa y firma para aceptar</div>
                </div>

                <div style="flex:1; overflow-y:auto; padding:20px; display:flex; flex-direction:column; gap:14px;">

                    <div>
                        <div class="text-xs font-medium text-gray-700 dark:text-gray-300" style="margin-bottom:4px;">Nombre completo *</div>
                        <input type="text" wire:model="firmaNombre" required
                               class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-white/10 dark:bg-white/5 dark:text-white">
                    </div>

                    <div>
                        <div class="text-xs font-medium text-gray-700 dark:text-gray-300" style="margin-bottom:4px;">Cargo <span class="text-gray-400">(opcional)</span></div>
                        <input type="text" wire:model="firmaCargo" placeholder="Ej: Abogado, Asistente..."
                               class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-white/10 dark:bg-white/5 dark:text-white">
                    </div>

                    <div>
                        <div class="text-xs font-medium text-gray-700 dark:text-gray-300" style="margin-bottom:4px;">Firma</div>
                        <div style="display:flex; gap:6px; margin-bottom:8px;">
                            <button wire:click="$set('metodoFirma', 'dibujar')" style="flex:1; padding:6px; border-radius:8px; font-size:11px; font-weight:600; text-align:center; border:1px solid;" class="{{ $metodoFirma === 'dibujar' ? 'border-primary-500 bg-primary-50 text-primary-700 dark:border-primary-400 dark:bg-primary-500/10 dark:text-primary-400' : 'border-gray-200 text-gray-500 dark:border-gray-700 dark:text-gray-400' }}">Dibujar</button>
                            <button wire:click="$set('metodoFirma', 'subir')" style="flex:1; padding:6px; border-radius:8px; font-size:11px; font-weight:600; text-align:center; border:1px solid;" class="{{ $metodoFirma === 'subir' ? 'border-primary-500 bg-primary-50 text-primary-700 dark:border-primary-400 dark:bg-primary-500/10 dark:text-primary-400' : 'border-gray-200 text-gray-500 dark:border-gray-700 dark:text-gray-400' }}">Subir imagen</button>
                        </div>
                        @if ($metodoFirma === 'dibujar')
                            <div x-data="{
                                c:null,x:null,d:false,ps:[],cp:[],fs:false,
                                init(){this.c=this.$refs.p;this.x=this.c.getContext('2d');this.applyStyle();const s=@js($firmaDataUrl);if(s){const i=new Image();i.onload=()=>this.x.drawImage(i,0,0);i.src=s;}},
                                applyStyle(){this.x.strokeStyle='#111';this.x.lineWidth=this.fs?4:2.5;this.x.lineCap='round';this.x.lineJoin='round';},
                                g(e){const r=this.c.getBoundingClientRect();const cx=e.clientX??e.touches?.[0]?.clientX;const cy=e.clientY??e.touches?.[0]?.clientY;return{x:(cx-r.left)*(this.c.width/r.width),y:(cy-r.top)*(this.c.height/r.height)};},
                                s(e){this.d=true;this.cp=[];const p=this.g(e);this.cp.push(p);this.x.beginPath();this.x.moveTo(p.x,p.y);},
                                m(e){if(!this.d)return;e.preventDefault();const p=this.g(e);this.cp.push(p);this.x.lineTo(p.x,p.y);this.x.stroke();},
                                u(){if(!this.d)return;this.d=false;if(this.cp.length>1)this.ps.push([...this.cp]);$wire.set('firmaDataUrl',this.c.toDataURL('image/png'));},
                                cl(){this.ps=[];this.x.clearRect(0,0,this.c.width,this.c.height);$wire.set('firmaDataUrl','');},
                                redraw(){this.x.clearRect(0,0,this.c.width,this.c.height);this.applyStyle();for(const path of this.ps){if(!path.length)continue;this.x.beginPath();this.x.moveTo(path[0].x,path[0].y);for(let i=1;i<path.length;i++)this.x.lineTo(path[i].x,path[i].y);this.x.stroke();}},
                                toggleFs(){const oldW=this.c.width,oldH=this.c.height;this.fs=!this.fs;document.body.style.overflow=this.fs?'hidden':'';this.$nextTick(()=>{const r=this.c.getBoundingClientRect();const nW=Math.max(1,Math.round(r.width));const nH=Math.max(1,Math.round(r.height));const sx=nW/oldW,sy=nH/oldH;this.ps=this.ps.map(p=>p.map(pt=>({x:pt.x*sx,y:pt.y*sy})));this.c.width=nW;this.c.height=nH;this.redraw();if(this.ps.length)$wire.set('firmaDataUrl',this.c.toDataURL('image/png'));});}
                            }" @firma-cleared.window="cl()" @keydown.escape.window="if (fs) toggleFs()">
                                <div :style="fs ? 'position:fixed; inset:0; z-index:9999; padding:20px; display:flex; flex-direction:column; gap:12px; background:rgba(17,24,39,0.96); backdrop-filter: blur(4px);' : ''">
                                    <template x-if="fs">
                                        <div style="display:flex; justify-content:space-between; align-items:center; color:#fff;">
                                            <span style="font-size:14px; font-weight:600;">Firma a pantalla completa</span>
                                            <button @click="toggleFs()" type="button" style="padding:6px 12px; border-radius:8px; background:rgba(255,255,255,0.1); color:#fff; font-size:12px; font-weight:600; cursor:pointer;">Cerrar</button>
                                        </div>
                                    </template>
                                    <div :style="fs ? 'flex:1; min-height:0;' : ''" style="border:1px solid; border-radius:8px; overflow:hidden;" class="border-gray-300 bg-white dark:border-gray-600">
                                        <canvas x-ref="p" width="400" height="100"
                                                :style="fs ? 'width:100%; height:100%; cursor:crosshair; touch-action:none; display:block;' : 'width:100%; cursor:crosshair; touch-action:none; display:block;'"
                                                @mousedown="s($event)" @mousemove="m($event)" @mouseup="u()" @mouseleave="u()" @touchstart="s($event)" @touchmove="m($event)" @touchend="u()"></canvas>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:4px;">
                                        <span style="font-size:10px;" :class="fs ? 'text-white/70' : 'text-gray-400'">Mouse o dedo</span>
                                        <div style="display:flex; gap:10px; align-items:center;">
                                            <button @click="toggleFs()" type="button" style="font-size:10px;" class="hover:underline" :class="fs ? 'text-white' : 'text-primary-600 dark:text-primary-400'">
                                                <span x-text="fs ? 'Reducir' : 'Pantalla completa'"></span>
                                            </button>
                                            <button @click="cl()" type="button" style="font-size:10px;" class="text-danger-600 dark:text-danger-400 hover:underline">Limpiar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <input type="file" wire:model="firmaUpload" accept="image/png,image/jpeg" class="block w-full text-sm text-gray-500 file:mr-3 file:rounded-lg file:border-0 file:bg-primary-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-primary-700 dark:file:bg-primary-500/10 dark:file:text-primary-400">
                            @if ($firmaUpload)
                                <img src="{{ $firmaUpload->temporaryUrl() }}" alt="Preview" style="height:50px; margin-top:8px; object-fit:contain;">
                            @endif
                        @endif
                    </div>

                    <label style="display:flex; align-items:flex-start; gap:10px; padding:12px; border-radius:8px; cursor:pointer;" class="border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
                        <input type="checkbox" wire:model="acepto" style="margin-top:2px;" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5">
                        <span style="font-size:11px; line-height:1.5;" class="text-gray-600 dark:text-gray-300">He leido y acepto esta politica. La firma es mia y entiendo las consecuencias legales.</span>
                    </label>
                </div>

                <div style="padding:14px 20px; border-top:1px solid rgba(128,128,128,0.15);">
                    <x-filament::button wire:click="aceptar" wire:loading.attr="disabled" icon="heroicon-m-pencil" class="w-full">
                        Firmar y aceptar
                    </x-filament::button>
                </div>
            </div>
        </div>
    @else
        <div style="display:flex; align-items:center; justify-content:center; padding:80px 0;">
            <div style="text-align:center;">
                <x-heroicon-o-check-circle style="width:64px; height:64px; margin:0 auto 12px;" class="text-success-500" />
                <p class="text-lg font-medium text-gray-900 dark:text-white">No tienes politicas pendientes</p>
            </div>
        </div>
    @endif
</x-filament-panels::page>
