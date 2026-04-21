<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $diasVencimiento = $this->fecha_vencimiento ? now()->diffInDays($this->fecha_vencimiento, false) : null;

        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'categoria' => $this->categoria,
            'nombre_archivo' => $this->nombre_archivo,
            'mime_type' => $this->mime_type,
            'tamano_bytes' => $this->tamano_bytes,
            'tamano_legible' => $this->formatBytes($this->tamano_bytes),
            'es_publico' => $this->es_publico,
            'fecha_vencimiento' => $this->fecha_vencimiento ? $this->fecha_vencimiento->format('Y-m-d') : null,
            'provincia' => $this->whenLoaded('provincia', function() {
                return ['id' => $this->provincia->id, 'nombre' => $this->provincia->nombre];
            }),
            'provincia_id' => $this->provincia_id,
            'version' => $this->version,
            'version_activa' => (bool)$this->version_activa,
            'documento_padre_id' => $this->documento_padre_id,
            'subido_por' => new UserResumenResource($this->whenLoaded('subidoPor')),
            'dias_para_vencer' => $diasVencimiento !== null && $diasVencimiento > 0 ? (int)$diasVencimiento : 0,
            'vencido' => $diasVencimiento !== null && $diasVencimiento < 0,
            'url_descarga' => route('documentos.descargar', current(explode('?', $this->id ?? ''))), // basic safety for id
            'created_at' => $this->created_at ? $this->created_at->format('d/m/Y H:i') : null,
        ];
    }

    private function formatBytes($bytes, $precision = 2)
    {
        if (!$bytes) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
