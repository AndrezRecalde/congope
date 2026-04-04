<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use App\Models\Evento;
use App\Services\EventoService;
use App\Http\Requests\Evento\StoreEventoRequest;
use App\Http\Requests\Evento\UpdateEventoRequest;
use App\Http\Requests\Evento\GestionarParticipantesRequest;
use App\Http\Resources\EventoResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class EventoController extends ApiController
{
    use AuthorizesRequests;

    public function __construct(
        protected EventoService $eventoService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Evento::class);

        $eventos = $this->eventoService->listar($request->all());

        return $this->respondWithPagination(
            $eventos,
            EventoResource::collection($eventos),
            'Eventos obtenidos exitosamente'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventoRequest $request): JsonResponse
    {
        $evento = $this->eventoService->crear($request->validated(), $request->user());

        return $this->respondCreated(
            new EventoResource($evento),
            'Evento creado exitosamente'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $evento = $this->eventoService->obtener($id);
        
        $this->authorize('view', $evento);

        return $this->respondSuccess(
            new EventoResource($evento),
            'Evento obtenido exitosamente'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventoRequest $request, Evento $evento): JsonResponse
    {
        $evento = $this->eventoService->actualizar($evento, $request->validated());

        return $this->respondSuccess(
            new EventoResource($evento),
            'Evento actualizado exitosamente'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Evento $evento): JsonResponse
    {
        $this->authorize('delete', $evento);

        $this->eventoService->eliminar($evento);

        return $this->respondSuccess(
            null,
            'Evento eliminado exitosamente'
        );
    }

    /**
     * Manage participants of an event.
     */
    public function gestionarParticipantes(GestionarParticipantesRequest $request, Evento $evento): JsonResponse
    {
        $evento = $this->eventoService->gestionarParticipantes($evento, $request->validated());

        return $this->respondSuccess(
            new EventoResource($evento),
            'Participantes actualizados'
        );
    }
}
