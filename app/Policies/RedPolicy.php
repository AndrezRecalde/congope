<?php

namespace App\Policies;

use App\Models\Red;
use App\Models\User;

class RedPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('redes.ver');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Red $red): bool
    {
        return $user->can('redes.ver');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('redes.crear');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Red $red): bool
    {
        return $user->can('redes.editar');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Red $red): bool
    {
        return $user->can('redes.eliminar');
    }

    /**
     * Determine whether the user can manage the network's members.
     */
    public function gestionarMiembros(User $user, Red $red): bool
    {
        return $user->can('redes.gestionar_miembros');
    }
}
