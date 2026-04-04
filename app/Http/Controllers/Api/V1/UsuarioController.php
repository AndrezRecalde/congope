<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Usuario\AsignarProvinciasRequest;
use App\Http\Requests\Usuario\AsignarRolRequest;
use App\Http\Requests\Usuario\StoreUsuarioRequest;
use App\Http\Requests\Usuario\UpdateUsuarioRequest;
use App\Http\Resources\UsuarioResource;
use App\Models\RegistroAuditoria;
use App\Models\User;
use App\Services\UsuarioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UsuarioController extends ApiController
{
    public function __construct(private readonly UsuarioService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', User::class);

        $filtros = $request->only(['rol', 'provincia_id', 'search']);
        $usuarios = $this->service->listar($filtros);

        return $this->respondPaginated($usuarios, 'Usuarios listados correctamente');
    }

    public function store(StoreUsuarioRequest $request): JsonResponse
    {
        $usuario = $this->service->crear($request->validated());

        return $this->respondCreated(
            new UsuarioResource($usuario),
            'Usuario creado correctamente'
        );
    }

    public function show(User $usuario): JsonResponse
    {
        Gate::authorize('view', $usuario);

        $usuario = $this->service->obtener($usuario->id);

        return $this->respondSuccess(
            new UsuarioResource($usuario),
            'Usuario obtenido correctamente'
        );
    }

    public function update(UpdateUsuarioRequest $request, User $usuario): JsonResponse
    {
        $usuario = $this->service->actualizar($usuario, $request->validated());

        return $this->respondSuccess(
            new UsuarioResource($usuario),
            'Usuario actualizado correctamente'
        );
    }

    public function destroy(User $usuario): JsonResponse
    {
        Gate::authorize('delete', $usuario);

        $this->service->eliminar($usuario);

        return $this->respondSuccess(null, 'Usuario eliminado');
    }

    public function asignarRol(AsignarRolRequest $request, User $usuario): JsonResponse
    {
        $usuario = $this->service->asignarRol($usuario, $request->rol);

        return $this->respondSuccess(
            new UsuarioResource($usuario),
            'Rol asignado correctamente'
        );
    }

    public function asignarProvincias(AsignarProvinciasRequest $request, User $usuario): JsonResponse
    {
        $usuario = $this->service->asignarProvincias($usuario, $request->provincia_ids);

        return $this->respondSuccess(
            new UsuarioResource($usuario),
            'Provincias asignadas correctamente'
        );
    }

    public function auditoria(Request $request): JsonResponse
    {
        Gate::authorize('verAuditoria', User::class);

        $logs = RegistroAuditoria::query()
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->modelo_tipo, fn($q) => $q->where('modelo_tipo', $request->modelo_tipo))
            ->when($request->accion, fn($q) => $q->where('accion', $request->accion))
            ->when($request->fecha_desde, fn($q) => $q->whereDate('created_at', '>=', $request->fecha_desde))
            ->when($request->fecha_hasta, fn($q) => $q->whereDate('created_at', '<=', $request->fecha_hasta))
            ->with(['usuario:id,name,email'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return $this->respondPaginated($logs, 'Registros de auditoría');
    }
}
