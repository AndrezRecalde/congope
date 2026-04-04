@extends('reportes.layout')

@section('title', 'Reporte de Provincia: ' . $provincia->nombre)

@section('content')
    <h2>Información de la Provincia</h2>
    <table>
        <tr>
            <th>Nombre</th>
            <td>{{ $provincia->nombre }}</td>
        </tr>
    </table>

    <h2>Métricas</h2>
    <table>
        <tr>
            <th>Monto Total de Cooperación</th>
            <td>${{ number_format($monto_total, 2) }}</td>
        </tr>
        <tr>
            <th>Proyectos Activos</th>
            <td>{{ $total_activos }}</td>
        </tr>
        <tr>
            <th>Proyectos Finalizados</th>
            <td>{{ $total_finalizados }}</td>
        </tr>
    </table>

    <h2>Proyectos en la Provincia</h2>
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
    <p>No se encontraron proyectos para esta provincia.</p>
    @endif
@endsection
