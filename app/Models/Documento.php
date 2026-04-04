<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Table('documentos')]
#[Fillable('documentable_type', 'documentable_id', 'titulo', 'categoria', 'ruta_archivo', 'nombre_archivo', 'mime_type', 'tamano_bytes', 'version', 'es_publico', 'fecha_vencimiento', 'subido_por')]
class Documento extends BaseModel
{
    protected function casts(): array
    {
        return [
            'es_publico' => 'boolean',
            'tamano_bytes' => 'integer',
            'version' => 'integer',
            'fecha_vencimiento' => 'date',
        ];
    }
    use SoftDeletes;

    public function documentable()
    {
        return $this->morphTo();
    }

    public function subidoPor()
    {
        return $this->belongsTo(User::class, 'subido_por');
    }

    public function scopePublicos($query)
    {
        return $query->where('es_publico', true);
    }

    public function scopeProximosAVencer($query, int $dias = 30)
    {
        return $query->whereBetween('fecha_vencimiento', [now()->toDateString(), now()->addDays($dias)->toDateString()]);
    }

    public function scopeVencidos($query)
    {
        return $query->where('fecha_vencimiento', '<', now()->toDateString());
    }

    public function scopePorCategoria($query, string $categoria)
    {
        return $query->where('categoria', $categoria);
    }
}
