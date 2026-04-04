<?php

namespace App\Models;

use App\Traits\HasDocuments;
use App\Traits\FiltroProvincia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Table('proyectos')]
#[Fillable('codigo', 'nombre', 'actor_id', 'estado', 'monto_total', 'moneda', 'fecha_inicio', 'fecha_fin_planificada', 'fecha_fin_real', 'porcentaje_avance', 'beneficiarios_directos', 'beneficiarios_indirectos', 'sector_tematico', 'ubicacion', 'creado_por')]
class Proyecto extends BaseModel
{
    protected function casts(): array
    {
        return [
            'monto_total' => 'decimal:2',
            'porcentaje_avance' => 'integer',
            'fecha_inicio' => 'date',
            'fecha_fin_planificada' => 'date',
            'fecha_fin_real' => 'date',
        ];
    }
    use SoftDeletes, HasDocuments, FiltroProvincia;

    public function actor()
    {
        return $this->belongsTo(ActorCooperacion::class, 'actor_id');
    }

    public function provincias()
    {
        return $this->belongsToMany(Provincia::class, 'proyecto_provincia')->withPivot(['rol']);
    }

    public function ods()
    {
        return $this->belongsToMany(Ods::class, 'proyecto_ods', 'proyecto_id', 'ods_id');
    }

    public function hitos()
    {
        return $this->hasMany(HitoProyecto::class);
    }

    public function buenasPracticas()
    {
        return $this->hasMany(BuenaPractica::class);
    }

    public function emblematico()
    {
        return $this->hasOne(ProyectoEmblematico::class);
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function scopeEstado($query, string $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', 'En ejecución');
    }

    public function scopeDeProvinci($query, $provinciaId)
    {
        return $query->whereHas('provincias', function ($q) use ($provinciaId) {
            $q->where('provincias.id', $provinciaId);
        });
    }

    public function scopeConMapa($query)
    {
        return $query->whereNotNull('ubicacion');
    }

    public function getMontoFormateadoAttribute(): string
    {
        return number_format($this->monto_total ?? 0, 2) . ' ' . $this->moneda;
    }
}
