@extends('reportes.layout')

@section('title', 'Reporte ODS: ' . $ods->numero . ' - ' . $ods->nombre)

@section('content')
    <h2>Métricas del ODS</h2>
    <table>
        <tr>
            <th>Proyectos Alineados</th>
            <td>{{ count($proyectos) }}</td>
        </tr>
        <tr>
            <th>Monto Total Asociado</th>
            <td>${{ number_format($monto_total, 2) }}</td>
        </tr>
    </table>

    <h2>Proyectos Alineados</h2>
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
    <p>No se encontraron proyectos para este ODS.</p>
    @endif
@endsection
