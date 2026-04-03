<?php

namespace App\Traits;

use App\Models\Documento;

trait HasDocuments
{
    public function documents()
    {
        return $this->morphMany(Documento::class, 'documentable');
    }

    public function documentosPublicos()
    {
        return $this->documents()->where('es_publico', true);
    }

    public function documentosPorCategoria(string $categoria)
    {
        return $this->documents()->where('categoria', $categoria);
    }

    public function documentosProximosAVencer(int $dias = 30)
    {
        $hoy = now();
        $fechaVencimiento = now()->addDays($dias);

        return $this->documents()
                    ->whereBetween('fecha_vencimiento', [$hoy, $fechaVencimiento]);
    }
}
