<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('provincias')]
#[Fillable('nombre', 'codigo', 'capital', 'geom')]
class Provincia extends BaseModel
{
    public function usuariosAdministradores(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'usuario_provincia');
    }

    public function proyectos(): BelongsToMany
    {
        return $this->belongsToMany(Proyecto::class, 'proyecto_provincia')
            ->withPivot(['rol', 'porcentaje_avance']);
    }

    public function cantones(): HasMany
    {
        return $this->hasMany(Canton::class);
    }

    public function buenasPracticas(): HasMany
    {
        return $this->hasMany(BuenaPractica::class);
    }

    public function proyectosEmblematicos(): HasMany
    {
        return $this->hasMany(ProyectoEmblematico::class);
    }

    public function scopeConGeometria($query)
    {
        return $query->select('*', 'geom');
    }
}
