<x-filament-panels::page>
    <x-filament::section>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Selecciona un reporte para generarlo individualmente, o usa el boton <strong>Reporte completo (PDF)</strong>
            del header para descargar un consolidado con todas las areas en un solo documento.
        </p>
    </x-filament::section>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; margin-top: 16px;">
        @foreach ($this->getReportesVisibles() as $rep)
            @php
                $cardStyle = "text-decoration: none; color: inherit;
                      background: rgba(255,255,255,0.04);
                      border: 1px solid rgba(255,255,255,0.08);
                      border-left: 4px solid {$rep['color']};
                      border-radius: 12px;
                      padding: 18px 20px;
                      display: block;
                      width: 100%;
                      text-align: left;
                      cursor: pointer;
                      font: inherit;
                      transition: transform 0.15s, box-shadow 0.15s;";
                $hoverIn = "this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,0.2)';";
                $hoverOut = "this.style.transform='';this.style.boxShadow='';";
            @endphp

            @if (! empty($rep['action']))
                <button type="button"
                        wire:click="{{ $rep['action'] }}"
                        style="{{ $cardStyle }}"
                        onmouseover="{{ $hoverIn }}"
                        onmouseout="{{ $hoverOut }}">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
                        <div style="width: 40px; height: 40px; border-radius: 8px;
                                    background: {{ $rep['color'] }}20;
                                    display: flex; align-items: center; justify-content: center;">
                            <x-filament::icon :icon="$rep['icon']"
                                              style="width: 22px; height: 22px; color: {{ $rep['color'] }};" />
                        </div>
                        <div style="font-size: 15px; font-weight: 600;">{{ $rep['title'] }}</div>
                    </div>
                    <p style="font-size: 13px; color: #9ca3af; margin: 0; line-height: 1.5;">{{ $rep['desc'] }}</p>
                </button>
            @else
                <a href="{{ $rep['url'] }}"
                   style="{{ $cardStyle }}"
                   onmouseover="{{ $hoverIn }}"
                   onmouseout="{{ $hoverOut }}">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
                        <div style="width: 40px; height: 40px; border-radius: 8px;
                                    background: {{ $rep['color'] }}20;
                                    display: flex; align-items: center; justify-content: center;">
                            <x-filament::icon :icon="$rep['icon']"
                                              style="width: 22px; height: 22px; color: {{ $rep['color'] }};" />
                        </div>
                        <div style="font-size: 15px; font-weight: 600;">{{ $rep['title'] }}</div>
                    </div>
                    <p style="font-size: 13px; color: #9ca3af; margin: 0; line-height: 1.5;">{{ $rep['desc'] }}</p>
                </a>
            @endif
        @endforeach
    </div>
</x-filament-panels::page>
