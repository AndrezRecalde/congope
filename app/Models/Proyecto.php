<?php

namespace App\Models;

use App\Traits\HasDocuments;
use App\Traits\FiltroProvincia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Table('proyectos')]
#[Fillable('codigo', 'nombre', 'descripcion', 'actor_id', 'estado', 'monto_total', 'moneda', 'fecha_inicio', 'fecha_fin_planificada', 'fecha_fin_real', 'sector_tematico', 'flujo_direccion', 'modalidad_cooperacion', 'creado_por')]
class Proyecto extends BaseModel
{
    protected function casts(): array
    {
        return [
            'monto_total' => 'decimal:2',
            'fecha_inicio' => 'date',
            'fecha_fin_planificada' => 'date',
            'fecha_fin_real' => 'date',
            'modalidad_cooperacion' => 'array',
        ];
    }
    use SoftDeletes, HasDocuments, FiltroProvincia;

    public function actor()
    {
        return $this->belongsTo(ActorCooperacion::class, 'actor_id');
    }

    public function provincias()
    {
        return $this->belongsToMany(Provincia::class, 'proyecto_provincia')
            ->withPivot(['rol', 'porcentaje_avance', 'beneficiarios_directos', 'beneficiarios_indirectos']);
    }

    public function cantones()
    {
        return $this->belongsToMany(Canton::class, 'proyecto_canton');
    }

    public function parroquias()
    {
        return $this->belongsToMany(Parroquia::class, 'proyecto_parroquia');
    }

    public function ubicaciones()
    {
        return $this->hasMany(ProyectoUbicacion::class);
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
