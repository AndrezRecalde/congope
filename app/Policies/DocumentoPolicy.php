<?php

namespace App\Policies;

use App\Models\Documento;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DocumentoPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Documento $documento): bool
    {
        return $user->can('documentos.ver') || 
               $user->can('documentos.ver_confidencial') || 
               $documento->subido_por === $user->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Documento $documento): bool
    {
        return $user->can('documentos.editar') && 
               ($user->hasRole('super_admin') || clone $user->esSuperAdmin() ?? false || $documento->subido_por === $user->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Documento $documento): bool
    {
        return $user->can('documentos.eliminar');
    }

    /**
     * Determine whether the user can publish the model.
     */
    public function publicar(User $user, Documento $documento): bool
    {
        return $user->can('documentos.publicar');
    }

    /**
     * Determine whether the user can download the model.
     */
    public function descargar(User $user, Documento $documento): bool
    {
        return $this->view($user, $documento);
    }
}
