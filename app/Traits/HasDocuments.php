<?php

namespace App\Traits;

use App\Models\Documento;

trait HasDocuments
{
    public function documentos()
    {
        return $this->morphMany(Documento::class, 'documentable');
    }

    public function documentosPublicos()
    {
        return $this->documentos()->where('es_publico', true);
    }

    public function documentosPorCategoria(string $categoria)
    {
        return $this->documentos()->where('categoria', $categoria);
    }

    public function documentosProximosAVencer(int $dias = 30)
    {
        $hoy = now();
        $fechaVencimiento = now()->addDays($dias);

        return $this->documentos()
                    ->whereBetween('fecha_vencimiento', [$hoy, $fechaVencimiento]);
    }
}
