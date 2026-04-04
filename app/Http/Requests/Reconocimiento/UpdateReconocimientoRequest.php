<?php

namespace App\Http\Requests\Reconocimiento;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateReconocimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('reconocimientos.editar');
    }

    public function rules(): array
    {
        return [
            'titulo'              => 'sometimes|required|string|max:300',
            'organismo_otorgante' => 'sometimes|required|string|max:300',
            'ambito'              => 'sometimes|required|in:Nacional,Internacional',
            'anio'                => 'sometimes|required|integer|min:1990|max:' . date('Y'),
            'descripcion'         => 'sometimes|nullable|string',
        ];
    }
}
