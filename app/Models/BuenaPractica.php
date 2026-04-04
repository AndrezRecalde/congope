<?php

namespace App\Models;

use App\Traits\FiltroProvincia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Table('buenas_practicas')]
#[Fillable('provincia_id', 'proyecto_id', 'titulo', 'descripcion_problema', 'metodologia', 'resultados', 'replicabilidad', 'calificacion_promedio', 'es_destacada', 'registrado_por')]
class BuenaPractica extends BaseModel
{
    protected function casts(): array
    {
        return [
            'calificacion_promedio' => 'decimal:2',
            'es_destacada' => 'boolean',
        ];
    }
    use SoftDeletes, FiltroProvincia;

    public function provincia()
    {
        return $this->belongsTo(Provincia::class);
    }

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function valoraciones()
    {
        return $this->hasMany(ValoracionPractica::class, 'practica_id');
    }

    public function actualizarCalificacion(): void
    {
        $this->calificacion_promedio = $this->valoraciones()->avg('puntuacion') ?? 0;
        $this->save();
    }

    public function scopeDestacadas($query)
    {
        return $query->where('es_destacada', true);
    }

    public function scopePublicas($query)
    {
        return $query; // Assumption since there is no field es_publico.
    }

    public function scopeDeProvinci($query, $provinciaId)
    {
        return $query->where('provincia_id', $provinciaId);
    }
}
