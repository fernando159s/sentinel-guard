<div class="space-y-4">
    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800">
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Asunto:</p>
        <p class="text-base font-semibold text-gray-900 dark:text-white">{{ $asunto }}</p>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
        <iframe
            srcdoc="@include('emails.securiform', ['saludo' => 'Hola Juan Pérez,', 'cuerpo' => $cuerpo, 'actionUrl' => '#', 'actionLabel' => 'Ver en SecuriForm', 'piePagina' => 'Vista previa con datos de ejemplo', 'asunto' => $asunto])"
            class="w-full rounded-lg"
            style="height: 500px; border: none;"
            sandbox="allow-same-origin"
        ></iframe>
    </div>
</div>
