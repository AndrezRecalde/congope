<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class ProyectoUbicacion extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'proyecto_ubicaciones';

    protected $fillable = [
        'proyecto_id',
        'canton_id',
        'nombre',
        // 'ubicacion' is managed via DB::raw PostGIS expressions
    ];

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function canton(): BelongsTo
    {
        return $this->belongsTo(Canton::class);
    }

    // Helper para parsear la longitud y latitud desde PostGIS.
    public function getCoordenadasAttribute()
    {
        if (!$this->id) return null;
        $geom = DB::selectOne(
            "SELECT ST_X(ubicacion::geometry) as lng, ST_Y(ubicacion::geometry) as lat FROM proyecto_ubicaciones WHERE id = ?",
            [$this->id]
        );
        return $geom ? ['lat' => (float) $geom->lat, 'lng' => (float) $geom->lng] : null;
    }
}
