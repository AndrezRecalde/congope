<?php

namespace App\Http\Requests\CategoriaBeneficiario;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('categorias-beneficiario.editar');
    }

    public function rules(): array
    {
        $id = $this->route('categorias_beneficiario')?->id;

        return [
            'nombre' => 'sometimes|required|string|max:200|unique:categorias_beneficiario,nombre,' . $id,
            'grupo'  => 'sometimes|required|string|max:100',
            'activo' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.unique'   => 'Ya existe una categoría con ese nombre',
            'grupo.required'  => 'El grupo es obligatorio',
        ];
    }
}
