<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class UsuarioService
{
    public function listar(array $filtros): LengthAwarePaginator
    {
        $query = User::query()->with(['roles', 'provincias']);

        if (!empty($filtros['search'])) {
            $query->where(function ($q) use ($filtros) {
                $q->where('name', 'ilike', '%' . $filtros['search'] . '%')
                  ->orWhere('email', 'ilike', '%' . $filtros['search'] . '%');
            });
        }

        if (!empty($filtros['rol'])) {
            $query->role($filtros['rol']);
        }

        if (!empty($filtros['provincia_id'])) {
            $query->whereHas('provincias', function ($q) use ($filtros) {
                $q->where('provincias.id', $filtros['provincia_id']);
            });
        }

        return $query->orderBy('name')->paginate(15);
    }

    public function obtener(string $id): User
    {
        return User::with(['roles', 'provincias', 'permissions'])->findOrFail($id);
    }

    public function crear(array $datos): User
    {
        return DB::transaction(function () use ($datos) {
            $user = User::create([
                'name' => $datos['name'],
                'email' => $datos['email'],
                'password' => bcrypt($datos['password']),
                'two_factor_enabled' => false,
            ]);

            $user->assignRole($datos['rol']);

            if (isset($datos['provincia_ids'])) {
                $user->provincias()->sync($datos['provincia_ids']);
            }

            return $user->fresh(['roles', 'provincias']);
        });
    }

    public function actualizar(User $user, array $datos): User
    {
        return DB::transaction(function () use ($user, $datos) {
            $camposActualizar = array_filter([
                'name' => $datos['name'] ?? null,
                'email' => $datos['email'] ?? null,
            ]);

            if (isset($datos['password'])) {
                $camposActualizar['password'] = bcrypt($datos['password']);
            }

            $user->update($camposActualizar);

            return $user->fresh(['roles', 'provincias']);
        });
    }

    public function asignarRol(User $user, string $rol): User
    {
        $user->syncRoles([$rol]);
        return $user->fresh(['roles']);
    }

    public function asignarProvincias(User $user, array $provinciaIds): User
    {
        $user->provincias()->sync($provinciaIds);
        return $user->fresh(['provincias']);
    }

    public function eliminar(User $user): void
    {
        if ($user->esSuperAdmin()) {
            throw new \Exception('No se puede eliminar al Super Administrador');
        }

        $user->tokens()->delete();
        $user->delete();
    }
}
