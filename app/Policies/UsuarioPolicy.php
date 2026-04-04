<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UsuarioPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('usuarios.ver');
    }

    public function view(User $user, User $targetUser): bool
    {
        return $user->can('usuarios.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('usuarios.crear');
    }

    public function update(User $user, User $targetUser): bool
    {
        return $user->can('usuarios.editar') && $targetUser->id !== $user->id;
    }

    public function delete(User $user, User $targetUser): bool
    {
        return $user->can('usuarios.eliminar') && !$targetUser->esSuperAdmin();
    }

    public function asignarRol(User $user, User $targetUser): bool
    {
        return $user->can('usuarios.asignar_rol');
    }

    public function asignarProvincias(User $user, User $targetUser): bool
    {
        return $user->can('usuarios.asignar_provincia');
    }

    public function verAuditoria(User $user): bool
    {
        return $user->can('usuarios.ver_auditoria');
    }
}
