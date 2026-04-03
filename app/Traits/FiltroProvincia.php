<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait FiltroProvincia
{
    public function scopeDeProvinci($query, $provinciaId)
    {
        return $query->whereHas('provincias', function ($q) use ($provinciaId) {
            $q->where('provincias.id', $provinciaId);
        });
    }

    public function scopeDeProvinciasDelUsuario($query, User $user)
    {
        $provinciaIds = $user->provincias()->pluck('provincias.id');
        return $query->whereHas('provincias', function ($q) use ($provinciaIds) {
            $q->whereIn('provincias.id', $provinciaIds);
        });
    }

    public function esDeProvinciaDelUsuario(Model $model, User $user)
    {
        $usuarioProvinciaIds = $user->provincias()->pluck('provincias.id')->toArray();
        $modeloProvinciaIds = $model->provincias()->pluck('provincias.id')->toArray();

        return count(array_intersect($usuarioProvinciaIds, $modeloProvinciaIds)) > 0;
    }
}
