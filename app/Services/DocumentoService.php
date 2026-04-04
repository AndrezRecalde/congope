<?php

namespace App\Services;

use App\Models\ActorCooperacion;
use App\Models\Documento;
use App\Models\Evento;
use App\Models\Proyecto;
use App\Models\Red;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class DocumentoService
{
    public const TIPOS_MODELO = [
        'proyecto' => Proyecto::class,
        'actor'    => ActorCooperacion::class,
        'red'      => Red::class,
        'evento'   => Evento::class,
    ];

    /**
     * Resuelve el modelo entidad a partir del tipo y su ID.
     */
    public function resolverEntidad(string $tipo, string $id): Model
    {
        if (!isset(self::TIPOS_MODELO[$tipo])) {
            throw new InvalidArgumentException("Tipo de entidad no válido: {$tipo}");
        }

        $clase = self::TIPOS_MODELO[$tipo];
        return $clase::findOrFail($id);
    }

    /**
     * Lista los documentos de una entidad según los permisos del usuario.
     */
    public function listar(string $tipo, string $entidadId, mixed $usuario = null): Collection
    {
        // En Laravel $usuario puede ser una instancia de User o similar
        // Pero usamos parametro genérico para acoplar validaciones
        $usuario = func_get_args()[2] ?? auth()->user();

        $entidad = $this->resolverEntidad($tipo, $entidadId);
        $query = $entidad->documentos ?? $entidad->documents();

        // En caso de que el método sea documents()
        if (method_exists($entidad, 'documents')) {
             $query = $entidad->documents();
        } else if (method_exists($entidad, 'documentos')) {
             $query = $entidad->documentos();
        } else {
             // as fallback since Documento uses morphTo as documentable which maps to 'documentacion' usually,
             // let's just query manually if relationship name is unknown
             $query = Documento::where('documentable_type', get_class($entidad))
                 ->where('documentable_id', $entidad->id);
        }

        if (!$usuario || !$usuario->can('documentos.ver_confidencial')) {
            $query->where(function ($q) use ($usuario) {
                $q->where('es_publico', true);
                if ($usuario) {
                    $q->orWhere('subido_por', $usuario->id);
                }
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Lista solo los documentos públicos de una entidad.
     */
    public function listarPublicos(string $tipo, string $entidadId): Collection
    {
        $entidad = $this->resolverEntidad($tipo, $entidadId);
        
        $query = Documento::where('documentable_type', get_class($entidad))
                 ->where('documentable_id', $entidad->id);
                 
        return $query->publicos()->orderBy('created_at', 'desc')->get();
    }

    /**
     * Sube un nuevo documento y lo asocia a una entidad.
     */
    public function subir(string $tipo, string $entidadId, UploadedFile $archivo, array $metadata, $usuario): Documento
    {
        return DB::transaction(function () use ($tipo, $entidadId, $archivo, $metadata, $usuario) {
            $entidad = $this->resolverEntidad($tipo, $entidadId);

            $extensionesPermitidas = [
                'pdf', 'docx', 'xlsx', 'doc', 'xls', 'pptx', 'ppt', 'jpg', 'jpeg', 'png', 'zip'
            ];

            $extension = strtolower($archivo->getClientOriginalExtension() ?: $archivo->extension());

            if (!in_array($extension, $extensionesPermitidas)) {
                throw new \Exception('Tipo de archivo no permitido');
            }

            $ruta = "{$tipo}s/{$entidadId}/" . now()->year . '/' . now()->month;
            $nombreArchivo = time() . '_' . Str::slug($metadata['titulo']) . '.' . $extension;

            $rutaGuardada = $archivo->storeAs($ruta, $nombreArchivo, 'local');

            $documento = new Documento([
                'titulo'            => $metadata['titulo'],
                'categoria'         => $metadata['categoria'],
                'ruta_archivo'      => $rutaGuardada,
                'nombre_archivo'    => $archivo->getClientOriginalName(),
                'mime_type'         => $archivo->getMimeType(),
                'tamano_bytes'      => $archivo->getSize(),
                'es_publico'        => $metadata['es_publico'] ?? false,
                'fecha_vencimiento' => $metadata['fecha_vencimiento'] ?? null,
                'subido_por'        => $usuario->id,
            ]);

            $documento->documentable()->associate($entidad);
            $documento->save();

            return $documento;
        });
    }

    /**
     * Descarga el archivo de un documento, verificando permisos.
     */
    public function descargar(Documento $documento, $usuario)
    {
        if (!$documento->es_publico) {
            if (!$usuario || (!$usuario->can('documentos.ver_confidencial') && $documento->subido_por !== $usuario->id)) {
                abort(403, 'No tienes permiso para descargar este documento.');
            }
        }

        if (!Storage::disk('local')->exists($documento->ruta_archivo)) {
            throw new ModelNotFoundException('Archivo no encontrado físicamente');
        }

        return Storage::disk('local')->download($documento->ruta_archivo, $documento->nombre_archivo);
    }

    /**
     * Actualiza los metadatos de un documento.
     */
    public function actualizar(Documento $documento, array $metadata): Documento
    {
        $documento->update([
            'titulo'            => $metadata['titulo'] ?? $documento->titulo,
            'categoria'         => $metadata['categoria'] ?? $documento->categoria,
            'es_publico'        => $metadata['es_publico'] ?? $documento->es_publico,
            'fecha_vencimiento' => $metadata['fecha_vencimiento'] ?? $documento->fecha_vencimiento,
        ]);

        return $documento->fresh();
    }

    /**
     * Elimina permanentemente un documento y su archivo.
     */
    public function eliminar(Documento $documento): void
    {
        DB::transaction(function () use ($documento) {
            if (Storage::disk('local')->exists($documento->ruta_archivo)) {
                Storage::disk('local')->delete($documento->ruta_archivo);
            }
            $documento->forceDelete();
        });
    }

    /**
     * Cambia el estado público/privado de un documento.
     */
    public function publicar(Documento $documento, bool $estado): Documento
    {
        $documento->update(['es_publico' => $estado]);
        return $documento->fresh();
    }
}
