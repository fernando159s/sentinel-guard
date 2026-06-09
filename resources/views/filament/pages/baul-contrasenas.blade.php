<x-filament-panels::page>
    {{--
        Recibe el valor descifrado despachado por la accion "Copiar" (servidor),
        lo escribe en el portapapeles y lo limpia a los ~20s (best-effort: el
        navegador solo permite limpiar si la pestana sigue enfocada).
    --}}
    <div
        x-data="{
            limpiar: null,
            async copiar(valor) {
                if (! navigator.clipboard) {
                    new FilamentNotification().title('Tu navegador no permite copiar automáticamente').warning().send();
                    return;
                }
                try {
                    await navigator.clipboard.writeText(valor);
                    clearTimeout(this.limpiar);
                    this.limpiar = setTimeout(() => {
                        navigator.clipboard.writeText('').catch(() => {});
                    }, 20000);
                } catch (e) {
                    new FilamentNotification().title('No se pudo copiar al portapapeles').danger().send();
                }
            }
        }"
        x-on:baul-copiar.window="copiar($event.detail.valor)"
    >
        {{ $this->table }}
    </div>
</x-filament-panels::page>
