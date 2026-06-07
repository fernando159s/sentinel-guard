<?php

namespace App\Services;

use App\Enums\TipoFormato;
use Illuminate\Validation\Rule;

/**
 * Deriva, desde la fuente única FormatoFieldsService (los componentes Filament
 * de cada uno de los 13 formatos), una metadata plana reutilizable fuera de
 * Filament: para describir los campos a un cliente MCP y para validar el
 * array `datos` en la ingesta. Así no se duplica la definición de campos.
 */
class RegistroSchemaService
{
    /**
     * @return array<int,array{name:string,label:string,required:bool,type:string,options:array<int,string>}>
     */
    public static function fields(TipoFormato $tipo): array
    {
        $out = [];

        foreach (FormatoFieldsService::getFields($tipo) as $component) {
            $options = method_exists($component, 'getOptions')
                ? array_keys($component->getOptions() ?: [])
                : [];

            $out[] = [
                'name' => str_replace('datos.', '', (string) $component->getName()),
                'label' => (string) $component->getLabel(),
                'required' => (bool) $component->isRequired(),
                'type' => class_basename($component),
                'options' => array_values(array_map('strval', $options)),
            ];
        }

        return $out;
    }

    /**
     * Reglas de validación Laravel para el array `datos` del formato.
     *
     * @return array<string,array<int,mixed>>
     */
    public static function rules(TipoFormato $tipo): array
    {
        $rules = [];

        foreach (self::fields($tipo) as $field) {
            $rule = [$field['required'] ? 'required' : 'nullable'];

            $rule = match ($field['type']) {
                'DatePicker', 'DateTimePicker' => [...$rule, 'date'],
                'Toggle' => [...$rule, 'boolean'],
                'TagsInput' => [...$rule, 'array'],
                default => $rule, // TimePicker/TextInput/Textarea/RichEditor/Select: string libre
            };

            if ($field['options']) {
                $rule[] = Rule::in($field['options']);
            }

            $rules["datos.{$field['name']}"] = $rule;
        }

        return $rules;
    }
}
