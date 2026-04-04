@extends('reportes.layout')

@section('title', 'Reporte Anual: ' . $anio)

@section('content')
    <h2>Resumen Ejecutivo</h2>
    <table>
        <tr>
            <th>Proyectos Iniciados</th>
            <td>{{ $iniciados }}</td>
        </tr>
        <tr>
            <th>Proyectos Finalizados</th>
            <td>{{ $finalizados }}</td>
        </tr>
        <tr>
            <th>Monto Total Estimado</th>
            <td>${{ number_format($monto_total, 2) }}</td>
        </tr>
    </table>

    <h2>Proyectos del Año</h2>
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
    <p>No hay proyectos en este periodo.</p>
    @endif
@endsection
