<?php

namespace App\Http\Requests\Documento;

use Illuminate\Foundation\Http\FormRequest;

class SubirDocumentoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('documentos.subir');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'entidad_tipo'      => 'required|in:proyecto,actor,red,evento',
            'entidad_id'        => 'required|uuid',
            'titulo'            => 'required|string|max:300',
            'categoria'         => 'required|in:Convenio,Informe,Acta,Anexo,Normativa,Comunicación',
            'archivo'           => 'required|file|max:20480|mimes:pdf,docx,xlsx,doc,xls,pptx,ppt,jpg,jpeg,png,zip',
            'es_publico'        => 'sometimes|boolean',
            'fecha_vencimiento' => 'nullable|date|after:today',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'entidad_tipo' => 'tipo de entidad',
            'entidad_id' => 'ID de la entidad',
            'titulo' => 'título',
            'categoria' => 'categoría',
            'archivo' => 'archivo adjunto',
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
            'entidad_tipo.required' => 'El tipo de entidad es obligatorio.',
            'entidad_tipo.in' => 'El tipo de entidad seleccionado no es válido.',
            'entidad_id.required' => 'El ID de la entidad es obligatorio.',
            'entidad_id.uuid' => 'El ID de la entidad debe ser un UUID válido.',
            'titulo.required' => 'El título es obligatorio.',
            'titulo.max' => 'El título no debe superar los 300 caracteres.',
            'categoria.required' => 'La categoría es obligatoria.',
            'categoria.in' => 'La categoría seleccionada no es válida.',
            'archivo.required' => 'El archivo es obligatorio.',
            'archivo.file' => 'Debe adjuntar un archivo válido.',
            'archivo.max' => 'El archivo no debe exceder los 20MB.',
            'archivo.mimes' => 'El archivo debe ser de un formato permitido (pdf, docx, etc).',
            'es_publico.boolean' => 'El valor de visibilidad debe ser booleano.',
            'fecha_vencimiento.date' => 'La fecha de vencimiento debe ser una fecha válida.',
            'fecha_vencimiento.after' => 'La fecha de vencimiento debe ser posterior a hoy.',
        ];
    }
}
