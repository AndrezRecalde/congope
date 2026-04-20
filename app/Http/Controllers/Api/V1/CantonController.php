<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Canton\StoreCantonRequest;
use App\Http\Requests\Canton\UpdateCantonRequest;
use App\Http\Resources\CantonResource;
use App\Models\Canton;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CantonController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $cantones = Canton::query()
            ->with('provincia')
            ->when($request->search, fn($q) => $q->where('nombre', 'ilike', "%{$request->search}%"))
            ->when($request->provincia_id, fn($q) => $q->where('provincia_id', $request->provincia_id))
            ->orderBy('codigo', 'asc')
            ->paginate($request->per_page ?? 15);

        return $this->respondPaginated(
            CantonResource::collection($cantones),
            'Cantones obtenidos exitosamente'
        );
    }

    public function show(string $id): JsonResponse
    {
        $canton = Canton::with([
            'provincia:id,nombre,codigo,capital',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Cantón obtenido exitosamente',
            'data' => $this->formatCanton($canton),
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $canton = Canton::findOrFail($id);

        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:150',
                \Illuminate\Validation\Rule::unique('cantones', 'nombre')
                    ->where('provincia_id', $canton->provincia_id)
                    ->ignore($id),
            ],
        ], [
            'nombre.required' => 'El nombre del cantón es requerido.',
            'nombre.unique' => 'Ya existe un cantón con ese nombre en esta provincia.',
            'nombre.max' => 'El nombre no puede superar 150 caracteres.',
        ]);

        $canton->update($validated);

        $canton->load('provincia:id,nombre,codigo,capital');

        \Illuminate\Support\Facades\Cache::forget('cantones.todas');
        \Illuminate\Support\Facades\Cache::forget('portal.mapa.catalogos');

        return response()->json([
            'success' => true,
            'message' => 'Cantón actualizado correctamente',
            'data' => $this->formatCanton($canton),
        ]);
    }

    private function formatCanton(Canton $canton): array
    {
        return [
            'id' => $canton->id,
            'provincia_id' => $canton->provincia_id,
            'codigo' => $canton->codigo,
            'nombre' => $canton->nombre,
            'provincia' => $canton->provincia
                ? [
                    'id' => $canton->provincia->id,
                    'nombre' => $canton->provincia->nombre,
                    'codigo' => $canton->provincia->codigo,
                    'capital' => $canton->provincia->capital,
                ]
                : null,
            'creado_el' => $canton->created_at?->toIso8601String(),
            'actualizado_el' => $canton->updated_at?->toIso8601String(),
        ];
    }
}
