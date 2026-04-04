<?php

namespace App\Http\Requests\Reconocimiento;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreReconocimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('reconocimientos.crear');
    }

    public function rules(): array
    {
        return [
            'titulo'              => 'required|string|max:300',
            'organismo_otorgante' => 'required|string|max:300',
            'ambito'              => 'required|in:Nacional,Internacional',
            'anio'                => 'required|integer|min:1990|max:' . date('Y'),
            'descripcion'         => 'nullable|string',
        ];
    }
}
