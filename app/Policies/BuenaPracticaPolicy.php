<?php

namespace App\Policies;

use App\Models\BuenaPractica;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BuenaPracticaPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('practicas.ver');
    }

    public function view(User $user, BuenaPractica $practica): bool
    {
        return $user->can('practicas.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('practicas.crear');
    }

    public function update(User $user, BuenaPractica $practica): bool
    {
        if (!$user->can('practicas.editar')) {
            return false;
        }

        return $user->hasRole('super_admin') || $user->esDeProvinciaId($practica->provincia_id);
    }

    public function delete(User $user, BuenaPractica $practica): bool
    {
        return $user->can('practicas.eliminar');
    }

    public function destacar(User $user, BuenaPractica $practica): bool
    {
        return $user->can('practicas.destacar');
    }

    public function valorar(User $user, BuenaPractica $practica): bool
    {
        return $user->can('practicas.valorar') && $practica->provincia_id !== $user->provincias()->first()?->id;
    }

    public function exportar(User $user): bool
    {
        return $user->can('practicas.exportar');
    }
}
