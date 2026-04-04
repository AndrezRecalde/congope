<?php

namespace App\Models;

use App\Traits\HasDocuments;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Table('redes')]
#[Fillable('nombre', 'tipo', 'objetivo', 'rol_congope', 'fecha_adhesion')]
class Red extends BaseModel
{
    protected function casts(): array
    {
        return [
            'fecha_adhesion' => 'date',
        ];
    }
    use SoftDeletes, HasDocuments;

    public function miembros()
    {
        return $this->belongsToMany(ActorCooperacion::class, 'red_miembros', 'red_id', 'actor_id')
                    ->withPivot(['rol_miembro', 'fecha_ingreso']);
    }
}
