<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Table('hitos_proyecto')]
#[Fillable('proyecto_id', 'titulo', 'descripcion', 'fecha_limite', 'completado', 'completado_en')]
class HitoProyecto extends BaseModel
{
    protected function casts(): array
    {
        return [
            'fecha_limite' => 'date',
            'completado' => 'boolean',
            'completado_en' => 'date',
        ];
    }
    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function scopePendientes($query)
    {
        return $query->where('completado', false);
    }

    public function scopeVencidos($query)
    {
        return $query->where('completado', false)->where('fecha_limite', '<', now()->toDateString());
    }

    public function scopeProximosAVencer($query, int $dias = 7)
    {
        return $query->where('completado', false)
                     ->whereBetween('fecha_limite', [now()->toDateString(), now()->addDays($dias)->toDateString()]);
    }
}
