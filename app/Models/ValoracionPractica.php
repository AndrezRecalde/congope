<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Table('valoracion_practica')]
#[Fillable('practica_id', 'user_id', 'puntuacion', 'comentario')]
class ValoracionPractica extends BaseModel
{
    protected function casts(): array
    {
        return [
            'puntuacion' => 'integer',
        ];
    }
    public function practica()
    {
        return $this->belongsTo(BuenaPractica::class, 'practica_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
