<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;

#[Table('ods')]
#[Fillable('numero', 'nombre', 'descripcion', 'color_hex', 'icono_url')]
#[WithoutTimestamps]
class Ods extends Model
{
    public $incrementing = true;
    protected $keyType = 'int';

    public function proyectos()
    {
        return $this->belongsToMany(Proyecto::class, 'proyecto_ods', 'ods_id', 'proyecto_id');
    }
}
