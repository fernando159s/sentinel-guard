<?php

namespace App\Services\Asistente;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Cliente mínimo para la API de DeepSeek (compatible con el formato de
 * chat-completions de OpenAI, incluido function-calling). Solo cubre lo que el
 * asistente necesita: una llamada a /chat/completions con herramientas.
 */
class DeepSeekClient
{
    /**
     * @param  array<int, array<string, mixed>>  $messages
     * @param  array<int, array<string, mixed>>  $tools
     * @param  array<string, mixed>  $opciones
     * @return array<string, mixed>  Respuesta JSON cruda de la API.
     */
    public function chat(array $messages, array $tools = [], array $opciones = []): array
    {
        $config = config('services.deepseek');

        if (empty($config['api_key'])) {
            throw new RuntimeException(
                'Falta DEEPSEEK_API_KEY en el .env. Configúrala para usar el asistente.'
            );
        }

        $payload = array_filter([
            'model' => $opciones['model'] ?? $config['model'],
            'messages' => $messages,
            'tools' => $tools ?: null,
            'tool_choice' => $tools ? 'auto' : null,
            'temperature' => $opciones['temperature'] ?? config('asistente.temperatura'),
            'max_tokens' => $opciones['max_tokens'] ?? config('asistente.max_tokens'),
        ], fn ($v) => ! is_null($v));

        $respuesta = $this->http($config)
            ->post('/chat/completions', $payload);

        $respuesta->throw();

        return $respuesta->json();
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function http(array $config): PendingRequest
    {
        return Http::baseUrl(rtrim($config['base_url'], '/'))
            ->withToken($config['api_key'])
            ->acceptJson()
            ->asJson()
            ->timeout((int) ($config['timeout'] ?? 60))
            ->retry(2, 500, throw: false);
    }
}
