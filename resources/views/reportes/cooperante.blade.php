@extends('reportes.layout')

@section('title', 'Reporte de Actor Cooperante: ' . $actor->nombre)

@section('content')
    <h2>Información del Actor</h2>
    <table>
        <tr>
            <th>Nombre</th>
            <td>{{ $actor->nombre }}</td>
        </tr>
        <tr>
            <th>Tipo</th>
            <td>{{ $actor->tipo }}</td>
        </tr>
        <tr>
            <th>Estado</th>
            <td>{{ $actor->estado }}</td>
        </tr>
    </table>

    <h2>Proyectos Involucrados</h2>
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
                <td>{{ $proyecto->codigo }}</td>
                <td>{{ $proyecto->nombre }}</td>
                <td>{{ $proyecto->estado }}</td>
                <td>${{ number_format($proyecto->monto_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p>No se encontraron proyectos para este actor.</p>
    @endif
@endsection
