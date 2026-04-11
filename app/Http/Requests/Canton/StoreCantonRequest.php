<?php

namespace App\Http\Requests\Canton;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCantonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provincia_id' => ['required', 'uuid', 'exists:provincias,id'],
            'codigo' => ['required', 'string', 'max:10', 'unique:cantones,codigo'],
            'nombre' => ['required', 'string', 'max:100'],
        ];
    }
}
