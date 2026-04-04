<?php

namespace App\Policies;

use App\Models\CompromisoEvento;
use App\Models\User;

class CompromisoEventoPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('compromisos.crear');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CompromisoEvento $compromiso): bool
    {
        return $user->can('compromisos.crear'); // mismo permiso para simplicidad
    }

    /**
     * Determine whether the user can resolve the model.
     */
    public function resolver(User $user, CompromisoEvento $compromiso): bool
    {
        return $user->can('compromisos.resolver') &&
               ($user->esSuperAdmin() || $compromiso->responsable_id === $user->id);
    }
}
