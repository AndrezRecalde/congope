<?php

namespace App\Http\Requests\Documento;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('documentos.editar');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'titulo'            => 'sometimes|string|max:300',
            'categoria'         => 'sometimes|in:Convenio,Informe,Acta,Anexo,Normativa,Comunicación',
            'es_publico'        => 'sometimes|boolean',
            'fecha_vencimiento' => 'nullable|date',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'titulo' => 'título',
            'categoria' => 'categoría',
            'es_publico' => 'visibilidad',
            'fecha_vencimiento' => 'fecha de vencimiento',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'titulo.string' => 'El título debe ser texto.',
            'titulo.max' => 'El título no debe superar los 300 caracteres.',
            'categoria.in' => 'La categoría seleccionada no es válida.',
            'es_publico.boolean' => 'El valor de visibilidad debe ser booleano.',
            'fecha_vencimiento.date' => 'La fecha de vencimiento debe ser una fecha válida.',
        ];
    }
}
