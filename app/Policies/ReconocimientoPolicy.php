<?php

namespace App\Policies;

use App\Models\Reconocimiento;
use App\Models\User;

class ReconocimientoPolicy
{
    public function create(User $user): bool
    {
        return $user->can('reconocimientos.crear');
    }

    public function update(User $user, Reconocimiento $reconocimiento): bool
    {
        return $user->can('reconocimientos.editar');
    }

    public function delete(User $user, Reconocimiento $reconocimiento): bool
    {
        return $user->can('reconocimientos.eliminar');
    }
}
