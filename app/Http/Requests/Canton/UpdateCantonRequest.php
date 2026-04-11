<?php

namespace App\Http\Requests\Canton;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCantonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $canton = $this->route('cantone');
        
        return [
            'provincia_id' => ['sometimes', 'uuid', 'exists:provincias,id'],
            'codigo' => [
                'sometimes',
                'string',
                'max:10',
                Rule::unique('cantones', 'codigo')->ignore($canton)
            ],
            'nombre' => ['sometimes', 'string', 'max:100'],
        ];
    }
}
