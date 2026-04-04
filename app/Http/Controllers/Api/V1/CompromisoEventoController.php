<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use App\Models\Evento;
use App\Models\CompromisoEvento;
use App\Services\CompromisoEventoService;
use App\Http\Requests\Compromiso\StoreCompromisoRequest;
use App\Http\Resources\CompromisoResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CompromisoEventoController extends ApiController
{
    use AuthorizesRequests;

    public function __construct(
        protected CompromisoEventoService $compromisoService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Evento $evento): JsonResponse
    {
        $this->authorize('viewAny', Evento::class);

        $compromisos = $this->compromisoService->listar($evento);

        return $this->respondSuccess(
            CompromisoResource::collection($compromisos),
            'Compromisos obtenidos'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompromisoRequest $request, Evento $evento): JsonResponse
    {
        $this->authorize('create', CompromisoEvento::class);

        $compromiso = $this->compromisoService->crear($evento, $request->validated());

        return $this->respondCreated(
            new CompromisoResource($compromiso),
            'Compromiso registrado'
        );
    }

    /**
     * Mark the commitment as resolved.
     */
    public function resolver(Evento $evento, CompromisoEvento $compromiso): JsonResponse
    {
        $this->authorize('resolver', $compromiso);

        $compromiso = $this->compromisoService->resolver($compromiso);

        return $this->respondSuccess(
            new CompromisoResource($compromiso),
            'Compromiso marcado como resuelto'
        );
    }

    /**
     * Get pending commitments for the authenticated user.
     */
    public function misPendientes(Request $request): JsonResponse
    {
        $compromisos = $this->compromisoService->listarPendientesUsuario($request->user());

        return $this->respondSuccess(
            CompromisoResource::collection($compromisos),
            'Compromisos pendientes'
        );
    }
}
