<?php

namespace App\Http\Requests\CategoriaBeneficiario;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('categorias-beneficiario.crear');
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:200|unique:categorias_beneficiario,nombre',
            'grupo'  => 'required|string|max:100',
            'activo' => 'boolean',
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
