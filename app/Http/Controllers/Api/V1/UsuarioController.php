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

    public function cambiarEstado(User $usuario): JsonResponse
    {
        Gate::authorize('update', $usuario);

        $usuario = $this->service->cambiarEstado($usuario);

        return $this->respondSuccess(
            new UsuarioResource($usuario),
            'Estado de usuario actualizado correctamente'
        );
    }

    public function resetPassword(Request $request, User $usuario): JsonResponse
    {
        Gate::authorize('update', $usuario);
        
        $request->validate(['enviar_correo' => 'boolean']);
        $enviarCorreo = $request->input('enviar_correo', false);

        $usuario = $this->service->resetearContrasena($usuario, $enviarCorreo);

        return $this->respondSuccess(
            new UsuarioResource($usuario),
            'Contraseña reseteada correctamente'
        );
    }

    public function updatePassword(\App\Http\Requests\Usuario\UpdatePasswordRequest $request): JsonResponse
    {
        $usuario = $request->user();
        
        try {
            $this->service->actualizarContrasena($usuario, $request->validated());
            
            return $this->respondSuccess(
                null,
                'Contraseña actualizada correctamente'
            );
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage(), 400);
        }
    }

    public function auditoria(Request $request): JsonResponse
    {
        Gate::authorize('verAuditoria', User::class);

        $query = RegistroAuditoria::query()
            ->with(['usuario:id,name,email'])
            ->orderByDesc('created_at');

        if ($request->filled('modelo_id')) {
            $query->where('modelo_id', $request->modelo_id);
        }

        if ($request->filled('modelo_tipo')) {
            $tipo = $request->modelo_tipo;
            if (!str_contains($tipo, '\\')) {
                $query->where('modelo_tipo', 'like', "%\\{$tipo}");
            } else {
                $query->where('modelo_tipo', $tipo);
            }
        }

        if ($request->filled('accion')) {
            $query->where('accion', $request->accion);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        } elseif ($request->filled('desde')) {
            $query->whereDate('created_at', '>=', $request->desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        } elseif ($request->filled('hasta')) {
            $query->whereDate('created_at', '<=', $request->hasta);
        }

        $perPage = $request->integer('per_page', 20);
        $registros = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Registros de auditoría',
            'data'    => $registros->map(
                fn($r) => [
                    'id'      => $r->id,
                    'user_id' => $r->user_id,
                    'accion'  => $r->accion,
                    'modelo_tipo' => $r->modelo_tipo,
                    'modelo_id'   => $r->modelo_id,
                    'valores_anteriores' => $r->valores_anteriores,
                    'valores_nuevos' => $r->valores_nuevos,
                    'ip_address' => $r->ip_address,
                    'user_agent' => $r->user_agent,
                    'created_at' => $r->created_at ? \Carbon\Carbon::parse($r->created_at)->format('Y-m-d H:i:s') : null,
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
        ]);
    }
}
