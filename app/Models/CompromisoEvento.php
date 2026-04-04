<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Table('compromisos_evento')]
#[Fillable('evento_id', 'descripcion', 'responsable_id', 'fecha_limite', 'resuelto', 'resuelto_en')]
class CompromisoEvento extends BaseModel
{
    protected function casts(): array
    {
        return [
            'fecha_limite' => 'date',
            'resuelto' => 'boolean',
            'resuelto_en' => 'date',
        ];
    }
    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function scopePendientes($query)
    {
        return $query->where('resuelto', false);
    }

    public function scopeVencidos($query)
    {
        return $query->where('resuelto', false)->where('fecha_limite', '<', now()->toDateString());
    }
}
