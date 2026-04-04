<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Table('actor_area_tematica')]
#[Fillable('actor_id', 'area')]
class ActorAreaTematica extends BaseModel
{
    public $timestamps = false;
    public function actor()
    {
        return $this->belongsTo(ActorCooperacion::class, 'actor_id');
    }
}
