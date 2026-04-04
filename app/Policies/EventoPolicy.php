<?php

namespace App\Policies;

use App\Models\Evento;
use App\Models\User;

class EventoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('eventos.ver');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Evento $evento): bool
    {
        return $user->can('eventos.ver');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('eventos.crear');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Evento $evento): bool
    {
        return $user->can('eventos.editar') &&
               ($user->esSuperAdmin() || $evento->creado_por === $user->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Evento $evento): bool
    {
        return $user->can('eventos.eliminar');
    }

    /**
     * Determine whether the user can manage participants.
     */
    public function gestionarParticipantes(User $user, Evento $evento): bool
    {
        return $user->can('eventos.gestionar_participantes');
    }
}
