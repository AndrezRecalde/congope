<?php

namespace App\Observers;

use App\Models\RegistroAuditoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class AuditoriaObserver
{
    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        $this->registrar($model, 'crear', null, $model->getAttributes());
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        if (!empty($model->getDirty())) {
            $this->registrar(
                $model, 
                'editar', 
                $model->getOriginal(), 
                $model->getDirty() // Solo guarda los valores que cambiaron para optimizar, o todo el set de nuevos. El requerimiento dice valores nuevos. $model->getAttributes() podría ser. Usemos getDirty() para los valores nuevos y getOriginal() para los correspondientes, o guardemos todo. Guardemos todo lo nuevo para simplicidad o intersequemos.
                // Ajuste: si dice $valoresAnteriores y $valoresNuevos
            );
        }
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $this->registrar($model, 'eliminar', $model->getOriginal(), null);
    }

    /**
     * Handle the Model "restored" event.
     */
    public function restored(Model $model): void
    {
        $this->registrar($model, 'restaurar', null, $model->getAttributes());
    }

    /**
     * Registra el cambio en la base de datos de manera segura.
     */
    private function registrar(Model $model, string $accion, ?array $valoresAnteriores, ?array $valoresNuevos): void
    {
        try {
            // Filtrar contraseñas u otros datos sensibles si es necesario
            if (isset($valoresAnteriores['password'])) unset($valoresAnteriores['password']);
            if (isset($valoresNuevos['password'])) unset($valoresNuevos['password']);

            RegistroAuditoria::create([
                'user_id'            => auth()->id(),
                'accion'             => $accion,
                'modelo_tipo'        => get_class($model),
                'modelo_id'          => $model->id,
                'valores_anteriores' => $valoresAnteriores,
                'valores_nuevos'     => $valoresNuevos,
                'ip_address'         => request()?->ip(),
                'user_agent'         => request()?->userAgent(),
            ]);
        } catch (\Throwable $e) {
            // Un fallo en auditoría NUNCA debe interrumpir la operación principal
            Log::error('Fallo al registrar auditoría: ' . $e->getMessage(), [
                'modelo' => get_class($model),
                'id' => $model->id,
                'accion' => $accion,
            ]);
        }
    }
}
