<?php

namespace App\Models;

use App\Traits\HasDocuments;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Table('actores_cooperacion')]
#[Fillable('nombre', 'tipo', 'pais_origen', 'estado', 'contacto_nombre', 'contacto_email', 'contacto_telefono', 'sitio_web', 'notas')]
class ActorCooperacion extends BaseModel
{
    use SoftDeletes, HasDocuments;

    public function proyectos()
    {
        return $this->hasMany(Proyecto::class, 'actor_id');
    }

    public function areasTematicas()
    {
        return $this->hasMany(ActorAreaTematica::class, 'actor_id');
    }

    public function redes()
    {
        return $this->belongsToMany(Red::class, 'red_miembros', 'actor_id', 'red_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', 'Activo');
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePorPais($query, string $pais)
    {
        return $query->where('pais_origen', $pais);
    }

    public function scopeBuscar($query, string $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('nombre', 'LIKE', '%' . $termino . '%')
              ->orWhere('contacto_email', 'LIKE', '%' . $termino . '%');
        });
    }
}
