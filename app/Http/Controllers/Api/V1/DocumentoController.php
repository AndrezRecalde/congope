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
}
