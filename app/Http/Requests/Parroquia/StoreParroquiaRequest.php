<?php

namespace App\Http\Requests\Parroquia;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreParroquiaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'canton_id' => ['required', 'uuid', 'exists:cantones,id'],
            'codigo' => ['required', 'string', 'max:10', 'unique:parroquias,codigo'],
            'nombre' => ['required', 'string', 'max:150'],
        ];
    }
}
