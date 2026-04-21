<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Documento\SubirDocumentoRequest;
use App\Http\Requests\Documento\UpdateDocumentoRequest;
use App\Http\Resources\DocumentoResource;
use App\Models\Documento;
use App\Services\DocumentoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DocumentoController extends ApiController
{
    protected DocumentoService $service;

    public function __construct(DocumentoService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'entidad_tipo' => 'required|in:proyecto,actor,red,evento',
            'entidad_id' => 'required|uuid',
        ]);

        $docs = $this->service->listar(
            $request->entidad_tipo,
            $request->entidad_id,
            $request->user()
        );

        return $this->respondSuccess(
            DocumentoResource::collection($docs),
            'Documentos obtenidos'
        );
    }

    public function store(SubirDocumentoRequest $request): JsonResponse
    {
        $doc = $this->service->subir(
            $request->entidad_tipo,
            $request->entidad_id,
            $request->file('archivo'),
            $request->provincia_id,
            $request->validated(),
            $request->user()
        );

        return $this->respondCreated(
            new DocumentoResource($doc),
            'Documento subido exitosamente'
        );
    }

    public function show(Documento $documento): JsonResponse
    {
        Gate::authorize('view', $documento);

        return $this->respondSuccess(
            new DocumentoResource($documento),
            'Documento obtenido'
        );
    }

    public function update(UpdateDocumentoRequest $request, Documento $documento): JsonResponse
    {
        Gate::authorize('update', $documento);

        $doc = $this->service->actualizar($documento, $request->validated());

        return $this->respondSuccess(
            new DocumentoResource($doc),
            'Documento actualizado'
        );
    }

    public function destroy(Documento $documento): JsonResponse
    {
        Gate::authorize('delete', $documento);

        $this->service->eliminar($documento);

        return $this->respondSuccess(null, 'Documento eliminado');
    }

    public function descargar(Documento $documento)
    {
        Gate::authorize('descargar', $documento);

        return $this->service->descargar($documento, auth()->user());
    }

    public function publicar(Request $request, Documento $documento): JsonResponse
    {
        Gate::authorize('publicar', $documento);

        $estado = $request->boolean('es_publico', true);

        $doc = $this->service->publicar($documento, $estado);

        return $this->respondSuccess(
            new DocumentoResource($doc),
            $estado ? 'Documento publicado' : 'Documento privado'
        );
    }
    public function subirVersion(Request $request, string $id): JsonResponse
    {
        Gate::authorize('documentos.subir');

        $documentoActual = Documento::findOrFail($id);
        $padreId = $documentoActual->documento_padre_id ?? $documentoActual->id;
        $padre = Documento::findOrFail($padreId);

        $request->validate([
            'archivo' => ['required', 'file', 'max:20480'],
            'titulo' => ['nullable', 'string', 'max:300'],
            'fecha_vencimiento' => ['nullable', 'date', 'after_or_equal:today'],
            'es_publico' => ['nullable', 'boolean'],
        ]);

        $versionActual = Documento::where(function ($q) use ($padreId) {
            $q->where('id', $padreId)->orWhere('documento_padre_id', $padreId);
        })->max('version') ?? 1;

        $nuevaVersion = $versionActual + 1;

        Documento::where(function ($q) use ($padreId) {
            $q->where('id', $padreId)->orWhere('documento_padre_id', $padreId);
        })->update(['version_activa' => false]);

        $archivo = $request->file('archivo');
        $extension = $archivo->getClientOriginalExtension() ?: $archivo->extension();
        $nombreBase = pathinfo($archivo->getClientOriginalName(), PATHINFO_FILENAME);
        $timestamp = time();
        $nombreFinal = "{$timestamp}_{$nombreBase}.{$extension}";

        $carpeta = dirname($padre->ruta_archivo);
        $ruta = $archivo->storeAs($carpeta, $nombreFinal, 'local');

        $nuevoDocumento = Documento::create([
            'documentable_type' => $padre->documentable_type,
            'documentable_id' => $padre->documentable_id,
            'titulo' => $request->titulo ?? $padre->titulo,
            'categoria' => $padre->categoria,
            'ruta_archivo' => $ruta,
            'nombre_archivo' => $archivo->getClientOriginalName(),
            'mime_type' => $archivo->getMimeType(),
            'tamano_bytes' => $archivo->getSize(),
            'es_publico' => $request->has('es_publico') ? (bool) $request->es_publico : $padre->es_publico,
            'fecha_vencimiento' => $request->fecha_vencimiento ?? $padre->fecha_vencimiento,
            'provincia_id' => $padre->provincia_id,
            'version' => $nuevaVersion,
            'documento_padre_id' => $padreId,
            'version_activa' => true,
            'subido_por' => $request->user()->id,
        ]);

        \Illuminate\Support\Facades\Log::info("Nueva versión {$nuevaVersion} de documento {$padreId} subida por " . $request->user()->email);

        return $this->respondCreated(
            new DocumentoResource($nuevoDocumento),
            "Versión {$nuevaVersion} subida correctamente"
        );
    }

    public function listarVersiones(Request $request, string $id): JsonResponse
    {
        Gate::authorize('documentos.ver');

        $documento = Documento::findOrFail($id);
        $padreId = $documento->documento_padre_id ?? $documento->id;

        $versiones = Documento::where(function ($q) use ($padreId) {
            $q->where('id', $padreId)->orWhere('documento_padre_id', $padreId);
        })->orderByDesc('version')->get();

        return response()->json([
            'success' => true,
            'message' => 'Versiones del documento',
            'data' => DocumentoResource::collection($versiones),
            'meta' => [
                'total' => $versiones->count(),
                'version_actual' => $versiones->max('version'),
                'documento_padre_id' => $padreId,
            ],
        ]);
    }
}
