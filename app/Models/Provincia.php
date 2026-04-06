<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Table('provincias')]
#[Fillable('nombre', 'codigo', 'capital', 'geom')]
class Provincia extends BaseModel
{
    public function usuariosAdministradores()
    {
        return $this->belongsToMany(User::class, 'usuario_provincia');
    }

    public function proyectos()
    {
        return $this->belongsToMany(Proyecto::class, 'proyecto_provincia')
            ->withPivot(['rol', 'porcentaje_avance', 'beneficiarios_directos', 'beneficiarios_indirectos']);
    }

    public function cantones()
    {
        return $this->hasMany(Canton::class);
    }

    public function buenasPracticas()
    {
        return $this->hasMany(BuenaPractica::class);
    }

    public function proyectosEmblematicos()
    {
        return $this->hasMany(ProyectoEmblematico::class);
    }

    public function scopeConGeometria($query)
    {
        return $query->select('*', 'geom');
    }
}
