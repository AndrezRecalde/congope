<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Parroquia extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'parroquias';

    protected $fillable = [
        'canton_id',
        'codigo',
        'nombre',
    ];

    public function canton(): BelongsTo
    {
        return $this->belongsTo(Canton::class);
    }

    public function proyectos(): BelongsToMany
    {
        return $this->belongsToMany(Proyecto::class, 'proyecto_parroquia');
    }
}
