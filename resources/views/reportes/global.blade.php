@extends('reportes.layout')

@section('title', 'Reporte Global')

@section('content')
    <h2>Resumen General</h2>
    <table>
        <tr>
            <th>Total de Proyectos</th>
            <td>{{ count($proyectos) }}</td>
        </tr>
    </table>

    <h2>Proyectos Filtrados</h2>
    @if(count($proyectos) > 0)
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Estado</th>
                <th>Monto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($proyectos as $proyecto)
            <tr>
                <td>{{ $proyecto->codigo_proyecto }}</td>
                <td>{{ $proyecto->nombre }}</td>
                <td>{{ $proyecto->estado }}</td>
                <td>${{ number_format($proyecto->monto_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p>No se encontraron proyectos para los filtros seleccionados.</p>
    @endif
@endsection
