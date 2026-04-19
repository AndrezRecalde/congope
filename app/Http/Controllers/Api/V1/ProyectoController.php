<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use App\Models\Proyecto;
use App\Services\ProyectoService;
use App\Http\Requests\Proyecto\StoreProyectoRequest;
use App\Http\Requests\Proyecto\UpdateProyectoRequest;
use App\Http\Resources\ProyectoResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\JsonResponse;
use App\Models\RegistroAuditoria;

class ProyectoController extends ApiController
{
    public function __construct(
        protected ProyectoService $service
    ) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Proyecto::class);

        $filtros = $request->only(['search', 'estado', 'actor_id']);
        $proyectos = $this->service->listar($filtros, $request->user());

        return $this->respondPaginated(ProyectoResource::collection($proyectos), 'Proyectos listados correctamente');
    }

    public function store(StoreProyectoRequest $request)
    {
        $proyecto = $this->service->crear($request->validated(), $request->user());

        return $this->respondCreated(new ProyectoResource($proyecto), 'Proyecto creado exitosamente');
    }

    public function show(Request $request, string $id)
    {
        $proyecto = $this->service->obtener($id, $request->user());
        Gate::authorize('view', $proyecto);

        return $this->respondSuccess(new ProyectoResource($proyecto), 'Proyecto obtenido correctamente');
    }

    public function update(UpdateProyectoRequest $request, string $id)
    {
        $proyecto = $this->service->obtener($id, $request->user());
        Gate::authorize('update', $proyecto);

        $proyecto = $this->service->actualizar($proyecto, $request->validated());

        return $this->respondSuccess(new ProyectoResource($proyecto), 'Proyecto actualizado exitosamente');
    }

    public function destroy(Request $request, string $id)
    {
        $proyecto = $this->service->obtener($id, $request->user());
        Gate::authorize('delete', $proyecto);

        $this->service->eliminar($proyecto);

        return $this->respondSuccess(null, 'Proyecto eliminado exitosamente');
    }

    public function cambiarEstado(Request $request, string $id)
    {
        $proyecto = $this->service->obtener($id, $request->user());
        Gate::authorize('cambiarEstado', $proyecto);

        $request->validate([
            'estado' => 'required|string|max:50'
        ]);

        $this->service->cambiarEstado($proyecto, $request->estado);

        return $this->respondSuccess(new ProyectoResource($proyecto), 'Estado del proyecto cambiado exitosamente');
    }

    public function exportar(Request $request)
    {
        Gate::authorize('exportar', Proyecto::class);

        $filtros = $request->only(['search', 'estado', 'actor_id']);
        return $this->service->exportarExcel($filtros);
    }

    /**
     * GET /api/v1/proyectos/:id/historial
     *
     * Devuelve el historial completo de cambios de
     * un proyecto específico, incluyendo cambios en
     * sus entidades relacionadas:
     *   - El proyecto mismo
     *   - Sus hitos (HitoProyecto)
     *   - Sus emblemáticos relacionados
     *   - Sus documentos
     *
     * Ordenado por fecha descendente (más reciente
     * primero) para funcionar como línea de tiempo.
     */
    public function historial(Request $request, string $id): JsonResponse
    {
        $proyecto = Proyecto::findOrFail($id);

        if (!$request->user()->can('verAuditoria', \App\Models\User::class) && !$request->user()->can('view', $proyecto)) {
            abort(403, 'Sin permiso para ver el historial');
        }

        $perPage = $request->integer('per_page', 15);

        $hitosIds = $proyecto->hitos()->pluck('id')->toArray();
        
        $emblematicosIds = \App\Models\ProyectoEmblematico::where('proyecto_id', $id)
            ->pluck('id')
            ->toArray();

        $registros = RegistroAuditoria::query()
            ->with(['usuario:id,name,email'])
            ->where(function ($q) use ($id, $hitosIds, $emblematicosIds) {
                $q->where(function ($sub) use ($id) {
                    $sub->where('modelo_id', $id)
                        ->where('modelo_tipo', 'like', '%Proyecto');
                });

                if (!empty($hitosIds)) {
                    $q->orWhere(function ($sub) use ($hitosIds) {
                        $sub->whereIn('modelo_id', $hitosIds)
                            ->where('modelo_tipo', 'like', '%Hito%');
                    });
                }

                if (!empty($emblematicosIds)) {
                    $q->orWhere(function ($sub) use ($emblematicosIds) {
                        $sub->whereIn('modelo_id', $emblematicosIds)
                            ->where('modelo_tipo', 'like', '%Emblematico%');
                    });
                }
            })
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => "Historial del proyecto",
            'data'    => $registros->map(
                fn($r) => [
                    'id'      => $r->id,
                    'accion'  => $r->accion,
                    'entidad' => $this->nombreAmigable($r->modelo_tipo),
                    'modelo_tipo'        => $r->modelo_tipo,
                    'modelo_id'          => $r->modelo_id,
                    'valores_anteriores' => $r->valores_anteriores,
                    'valores_nuevos'     => $r->valores_nuevos,
                    'ip_address'         => $r->ip_address,
                    'created_at'         => $r->created_at ? \Carbon\Carbon::parse($r->created_at)->format('Y-m-d H:i:s') : null,
                    'usuario' => $r->usuario ? [
                        'id'    => $r->usuario->id,
                        'name'  => $r->usuario->name,
                        'email' => $r->usuario->email,
                    ] : null,
                ]
            ),
            'meta' => [
                'current_page' => $registros->currentPage(),
                'last_page'    => $registros->lastPage(),
                'per_page'     => $registros->perPage(),
                'total'        => $registros->total(),
            ],
            'proyecto' => [
                'id'     => $proyecto->id,
                'codigo' => $proyecto->codigo ?? null,
                'nombre' => $proyecto->nombre,
            ],
        ]);
    }

    /**
     * Convierte el namespace PHP de un modelo
     * en un nombre amigable para mostrar al usuario.
     */
    private function nombreAmigable(string $modeloTipo): string
    {
        $nombre = class_basename($modeloTipo);
        return match ($nombre) {
            'Proyecto'           => 'Proyecto',
            'HitoProyecto'       => 'Hito',
            'ProyectoEmblematico'=> 'Emblemático',
            'Documento'          => 'Documento',
            'ProyectoOds'        => 'ODS',
            'ProyectoProvincia'  => 'Provincia',
            default              => $nombre,
        };
    }
}
