<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ActorCooperacion;

class ActorCooperacionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('actores.ver');
    }

    public function view(User $user, ActorCooperacion $actor): bool
    {
        return $user->can('actores.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('actores.crear');
    }

    public function update(User $user, ActorCooperacion $actor): bool
    {
        return $user->can('actores.editar');
    }

    public function delete(User $user, ActorCooperacion $actor): bool
    {
        return $user->can('actores.eliminar');
    }

    public function exportar(User $user): bool
    {
        return $user->can('actores.exportar');
    }
}
