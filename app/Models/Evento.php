<?php

namespace App\Models;

use App\Traits\HasDocuments;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Table('eventos')]
#[Fillable('titulo', 'tipo_evento', 'fecha_evento', 'lugar', 'es_virtual', 'url_virtual', 'descripcion', 'creado_por')]
class Evento extends BaseModel
{
    protected function casts(): array
    {
        return [
            'fecha_evento' => 'date',
            'es_virtual' => 'boolean',
        ];
    }
    use SoftDeletes, HasDocuments;

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function participantes()
    {
        return $this->belongsToMany(User::class, 'evento_participantes')
                    ->withPivot(['asistio', 'confirmado_en']);
    }

    public function compromisos()
    {
        return $this->hasMany(CompromisoEvento::class);
    }
}
