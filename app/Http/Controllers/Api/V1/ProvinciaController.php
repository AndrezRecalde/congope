<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use App\Http\Resources\ProvinciaDetalleResource;
use App\Http\Resources\ProvinciaResource;
use App\Http\Resources\UsuarioResource;
use App\Models\Provincia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProvinciaController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $provincias = Provincia::query()
            ->when($request->search, fn($q) => $q->where('nombre', 'ilike', "%{$request->search}%"))
            ->orderBy('codigo', 'asc')
            ->get(['id', 'nombre', 'codigo', 'capital']);

        return $this->respondSuccess(
            ProvinciaResource::collection($provincias),
            'Provincias obtenidas'
        );
    }

    public function show(Provincia $provincia): JsonResponse
    {
        $provincia->load([
            'proyectos' => fn($q) => $q->activos()->limit(5),
            'buenasPracticas' => fn($q) => $q->destacadas()->limit(3)
        ]);

        return $this->respondSuccess(
            new ProvinciaDetalleResource($provincia),
            'Provincia obtenida'
        );
    }

    public function usuariosAsignados(Provincia $provincia): JsonResponse
    {
        Gate::authorize('viewAny', \App\Models\User::class);

        $usuarios = $provincia->usuariosAdministradores()
            ->with('roles')
            ->get();

        return $this->respondSuccess(
            UsuarioResource::collection($usuarios),
            'Usuarios de la provincia'
        );
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $provincia = Provincia::findOrFail($id);

        $validated = $request->validate([
            'nombre' => [
                'sometimes',
                'string',
                'max:100',
                'unique:provincias,nombre,' . $id,
            ],
            'capital' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],
        ], [
            'nombre.unique' => 'Ya existe una provincia con ese nombre.',
            'nombre.max' => 'El nombre no puede superar 100 caracteres.',
            'capital.max' => 'La capital no puede superar 100 caracteres.',
        ]);

        $provincia->update($validated);

        \Illuminate\Support\Facades\Cache::forget('provincias.todas');
        \Illuminate\Support\Facades\Cache::forget('portal.mapa.catalogos');

        return response()->json([
            'success' => true,
            'message' => 'Provincia actualizada correctamente',
            'data' => [
                'id' => $provincia->id,
                'nombre' => $provincia->nombre,
                'codigo' => $provincia->codigo,
                'capital' => $provincia->capital,
            ],
        ]);
    }
}
