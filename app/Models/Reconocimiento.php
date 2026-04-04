<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Table('reconocimientos')]
#[Fillable('emblematico_id', 'titulo', 'organismo_otorgante', 'ambito', 'anio', 'descripcion')]
class Reconocimiento extends BaseModel
{
    protected function casts(): array
    {
        return [
            'anio' => 'integer',
        ];
    }
    public function emblematico()
    {
        return $this->belongsTo(ProyectoEmblematico::class, 'emblematico_id');
    }
}
