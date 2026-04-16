<?php

namespace App\Services;

use App\Models\CategoriaBeneficiario;
use Illuminate\Database\Eloquent\Collection;

class CategoriaBeneficiarioService
{
    public function listar(array $filtros, bool $soloActivos = false): Collection
    {
        $query = CategoriaBeneficiario::query()->withCount('proyectos');

        if ($soloActivos) {
            $query->activos();
        }

        if (!empty($filtros['grupo'])) {
            $query->porGrupo($filtros['grupo']);
        }

        if (!empty($filtros['search'])) {
            $query->where('nombre', 'LIKE', '%' . $filtros['search'] . '%');
        }

        return $query->orderBy('grupo')->orderBy('nombre')->get();
    }

    public function listarAgrupados(): array
    {
        $categorias = CategoriaBeneficiario::activos()
            ->orderBy('grupo')
            ->orderBy('nombre')
            ->get();

        return $categorias->groupBy('grupo')->map(function ($items, $grupo) {
            return [
                'grupo'      => $grupo,
                'categorias' => $items->map(fn($c) => ['id' => $c->id, 'nombre' => $c->nombre]),
            ];
        })->values()->toArray();
    }

    public function obtener(CategoriaBeneficiario $categoria): CategoriaBeneficiario
    {
        return $categoria->loadCount('proyectos');
    }

    public function crear(array $datos): CategoriaBeneficiario
    {
        return CategoriaBeneficiario::create($datos);
    }

    public function actualizar(CategoriaBeneficiario $categoria, array $datos): CategoriaBeneficiario
    {
        $categoria->update($datos);
        return $categoria->fresh();
    }

    public function eliminar(CategoriaBeneficiario $categoria): void
    {
        if ($categoria->proyectos()->exists()) {
            abort(422, 'No se puede eliminar una categoría que está siendo usada en proyectos.');
        }
        $categoria->delete();
    }
}
