<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Table('red_miembros')]
#[Fillable('red_id', 'actor_id', 'rol_miembro', 'fecha_ingreso')]
class RedMiembro extends BaseModel
{
    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'date',
        ];
    }
    public function red()
    {
        return $this->belongsTo(Red::class, 'red_id');
    }

    public function actor()
    {
        return $this->belongsTo(ActorCooperacion::class, 'actor_id');
    }
}
