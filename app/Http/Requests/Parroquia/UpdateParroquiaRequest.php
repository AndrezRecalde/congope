<?php

namespace App\Http\Requests\Parroquia;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateParroquiaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $parroquia = $this->route('parroquia');
        
        return [
            'canton_id' => ['sometimes', 'uuid', 'exists:cantones,id'],
            'codigo' => [
                'sometimes',
                'string',
                'max:10',
                Rule::unique('parroquias', 'codigo')->ignore($parroquia)
            ],
            'nombre' => ['sometimes', 'string', 'max:150'],
        ];
    }
}
