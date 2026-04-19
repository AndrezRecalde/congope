<?php

namespace App\Services;

use App\Models\User;
use App\Mail\CredencialesUsuarioMail;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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

    private function generarPassword(): string
    {
        $year = date('Y');
        $letras = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $especiales = '!@#$%&*?';
        $letra = $letras[rand(0, strlen($letras) - 1)];
        $especial = $especiales[rand(0, strlen($especiales) - 1)];
        
        return "CONGOPE{$year}{$letra}{$especial}";
    }

    public function crear(array $datos): User
    {
        return DB::transaction(function () use ($datos) {
            $passwordGenerada = $this->generarPassword();

            $user = User::create([
                'name' => $datos['name'],
                'email' => $datos['email'],
                'password' => Hash::make($passwordGenerada),
                'telefono' => $datos['telefono'],
                'cargo' => $datos['cargo'],
                'activo' => $datos['activo'] ?? false,
                'entidad' => $datos['entidad'] ?? null,
                'dni' => $datos['dni'] ?? null,
                'requires_password_change' => true,
            ]);

            $user->assignRole($datos['rol']);

            if (isset($datos['provincia_ids'])) {
                $user->provincias()->sync($datos['provincia_ids']);
            }

            if (!empty($datos['enviar_correo'])) {
                Mail::to($user->email)->send(new CredencialesUsuarioMail($user, $passwordGenerada));
            }

            // Expose the generated password directly on the model instance temporarily
            // so the controller can return it in the response if needed.
            $user->password_generada = $passwordGenerada;

            return $user->fresh(['roles', 'provincias']);
        });
    }

    public function actualizar(User $user, array $datos): User
    {
        return DB::transaction(function () use ($user, $datos) {
            $camposActualizar = array_filter([
                'name' => $datos['name'] ?? null,
                'email' => $datos['email'] ?? null,
                'telefono' => $datos['telefono'] ?? null,
                'cargo' => $datos['cargo'] ?? null,
                'entidad' => $datos['entidad'] ?? null,
                'dni' => $datos['dni'] ?? null,
            ], fn($value) => !is_null($value));

            if (isset($datos['activo'])) {
                $camposActualizar['activo'] = $datos['activo'];
            }

            $user->update($camposActualizar);

            return $user->fresh(['roles', 'provincias']);
        });
    }

    public function resetearContrasena(User $user, bool $enviarCorreo): User
    {
        $passwordGenerada = $this->generarPassword();
        $user->update([
            'password' => Hash::make($passwordGenerada),
            'requires_password_change' => true,
        ]);

        if ($enviarCorreo) {
            Mail::to($user->email)->send(new CredencialesUsuarioMail($user, $passwordGenerada));
        }

        $user->password_generada = $passwordGenerada;
        return $user;
    }

    public function actualizarContrasena(User $user, array $datos): void
    {
        if (!Hash::check($datos['current_password'], $user->password)) {
            throw new \Exception('La contraseña actual es incorrecta', 400);
        }

        $user->update([
            'password' => Hash::make($datos['password']),
            'requires_password_change' => false,
        ]);
    }

    public function cambiarEstado(User $user): User
    {
        $user->update([
            'activo' => !$user->activo,
        ]);
        
        return $user;
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
