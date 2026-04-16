<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Table('proyecto_beneficiario')]
#[Fillable('proyecto_id', 'provincia_id', 'categoria_beneficiario_id', 'cantidad_directos', 'cantidad_indirectos', 'observaciones')]
class ProyectoBeneficiario extends Model
{
    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function provincia()
    {
        return $this->belongsTo(\App\Models\Provincia::class);
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaBeneficiario::class, 'categoria_beneficiario_id');
    }
}
