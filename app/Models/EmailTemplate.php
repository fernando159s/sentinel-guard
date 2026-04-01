<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class EmailTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'slug',
        'nombre',
        'asunto',
        'contenido',
        'variables_disponibles',
        'plantilla_default_asunto',
        'plantilla_default_contenido',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'variables_disponibles' => 'array',
            'activo' => 'boolean',
        ];
    }

    /**
     * Render the template subject replacing placeholders with actual values.
     */
    public function renderAsunto(array $variables = []): string
    {
        return $this->replacePlaceholders($this->asunto, $variables);
    }

    /**
     * Render the template body replacing placeholders with actual values.
     */
    public function renderContenido(array $variables = []): string
    {
        return $this->replacePlaceholders($this->contenido, $variables);
    }

    /**
     * Restore the template to its default content.
     */
    public function restaurarDefault(): void
    {
        $this->update([
            'asunto' => $this->plantilla_default_asunto,
            'contenido' => $this->plantilla_default_contenido,
        ]);
    }

    /**
     * Find an active template by slug, or null.
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->where('activo', true)->first();
    }

    /**
     * Replace {{variable}} placeholders with provided values.
     */
    private function replacePlaceholders(string $text, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $text = Str::replace("{{{$key}}}", (string) $value, $text);
        }

        return $text;
    }
}
