<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Table('cantones')]
#[Fillable('provincia_id', 'codigo', 'nombre')]
class Canton extends BaseModel
{
    public function provincia(): BelongsTo
    {
        return $this->belongsTo(Provincia::class);
    }

    public function parroquias(): HasMany
    {
        return $this->hasMany(Parroquia::class);
    }

    public function ubicaciones(): HasMany
    {
        return $this->hasMany(ProyectoUbicacion::class);
    }
}
