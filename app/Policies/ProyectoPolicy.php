<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Proyecto;

class ProyectoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('proyectos.ver');
    }

    public function view(User $user, Proyecto $proyecto): bool
    {
        if (!$user->can('proyectos.ver')) {
            return false;
        }

        return $this->tieneAccesoProvincial($user, $proyecto);
    }

    public function create(User $user): bool
    {
        return $user->can('proyectos.crear');
    }

    public function update(User $user, Proyecto $proyecto): bool
    {
        if (!$user->can('proyectos.editar')) {
            return false;
        }

        return $this->tieneAccesoProvincial($user, $proyecto);
    }

    public function delete(User $user, Proyecto $proyecto): bool
    {
        if (!$user->can('proyectos.eliminar')) {
            return false;
        }

        return $this->tieneAccesoProvincial($user, $proyecto);
    }

    public function cambiarEstado(User $user, Proyecto $proyecto): bool
    {
        if (!$user->can('proyectos.cambiar_estado')) {
            return false;
        }

        return $this->tieneAccesoProvincial($user, $proyecto);
    }

    public function exportar(User $user): bool
    {
        return $user->can('proyectos.exportar');
    }

    protected function tieneAccesoProvincial(User $user, Proyecto $proyecto): bool
    {
        if ($user->can('proyectos.ver_todas_provincias')) {
            return true;
        }

        $userProvincias = $user->provincias()->pluck('provincias.id')->toArray();
        $proyectoProvincias = $proyecto->provincias()->pluck('provincias.id')->toArray();

        return count(array_intersect($userProvincias, $proyectoProvincias)) > 0;
    }
}
