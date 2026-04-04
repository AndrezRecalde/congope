<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Table('proyectos_emblematicos')]
#[Fillable('proyecto_id', 'provincia_id', 'titulo', 'descripcion_impacto', 'periodo', 'es_publico')]
class ProyectoEmblematico extends BaseModel
{
    protected function casts(): array
    {
        return [
            'es_publico' => 'boolean',
        ];
    }
    use SoftDeletes;

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function provincia()
    {
        return $this->belongsTo(Provincia::class);
    }

    public function reconocimientos()
    {
        return $this->hasMany(Reconocimiento::class, 'emblematico_id');
    }

    public function scopePublicos($query)
    {
        return $query->where('es_publico', true);
    }
}
