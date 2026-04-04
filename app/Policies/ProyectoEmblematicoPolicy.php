<?php

namespace App\Policies;

use App\Models\ProyectoEmblematico;
use App\Models\User;

class ProyectoEmblematicoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('emblematicos.ver');
    }

    public function view(User $user, ProyectoEmblematico $emblematico): bool
    {
        return $user->can('emblematicos.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('emblematicos.crear');
    }

    public function update(User $user, ProyectoEmblematico $emblematico): bool
    {
        if (!$user->can('emblematicos.editar')) {
            return false;
        }

        return $user->esSuperAdmin() || $user->esDeProvinciaId($emblematico->provincia_id);
    }

    public function delete(User $user, ProyectoEmblematico $emblematico): bool
    {
        return $user->can('emblematicos.eliminar');
    }

    public function publicar(User $user, ProyectoEmblematico $emblematico): bool
    {
        return $user->can('emblematicos.publicar');
    }
}
