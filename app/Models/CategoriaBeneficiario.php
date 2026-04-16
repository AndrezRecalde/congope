<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Table('categorias_beneficiario')]
#[Fillable('nombre', 'grupo', 'activo')]
class CategoriaBeneficiario extends Model
{
    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function proyectos()
    {
        return $this->belongsToMany(Proyecto::class, 'proyecto_beneficiario', 'categoria_beneficiario_id', 'proyecto_id')
                    ->withPivot('cantidad_directos', 'cantidad_indirectos', 'observaciones')
                    ->withTimestamps();
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorGrupo($query, string $grupo)
    {
        return $query->where('grupo', $grupo);
    }
}
