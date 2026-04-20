<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Proyectos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #1A3A5C;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0 0 5px 0;
            color: #1A3A5C;
            text-transform: uppercase;
        }
        .header p {
            margin: 0;
            color: #666;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            table-layout: fixed;
            word-wrap: break-word;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #1A3A5C;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
        }
        /* Configuración de anchos de columna */
        .col-codigo { width: 10%; }
        .col-nombre { width: 22%; }
        .col-estado { width: 8%; }
        .col-prov { width: 15%; }
        .col-monto { width: 10%; }
        .col-flujo { width: 10%; }
        .col-mod { width: 10%; }
        .col-ods { width: 8%; }
        .col-fecha { width: 7%; }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            position: fixed;
            bottom: -20px;
            width: 100%;
        }
        .badge {
            display: inline-block;
            padding: 2px 4px;
            border-radius: 3px;
            background: #eee;
            margin: 1px 0;
            font-size: 8px;
        }
        .estado {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Proyectos</h1>
        <p>CONGOPE - Gestión de Cooperación Internacional</p>
        <p>Fecha de generación: {{ date('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-codigo">Código</th>
                <th class="col-nombre">Nombre del Proyecto</th>
                <th class="col-estado">Estado</th>
                <th class="col-prov">Provincias y Avance</th>
                <th class="col-monto">Monto Total</th>
                <th class="col-flujo">Flujo de Coop.</th>
                <th class="col-mod">Modalidad</th>
                <th class="col-ods">ODS</th>
                <th class="col-fecha">Inicio</th>
            </tr>
        </thead>
        <tbody>
            @foreach($proyectos as $p)
                <tr>
                    <td>{{ $p->codigo }}</td>
                    <td>{{ $p->nombre }}</td>
                    <td class="estado">{{ $p->estado }}</td>
                    <td>
                        @foreach($p->provincias as $prov)
                            <div style="margin-bottom:2px;">
                                &bull; {{ $prov->nombre }}
                                <span style="color:#666; font-size: 8px;">({{ $prov->pivot->porcentaje_avance ?? 0 }}%)</span>
                            </div>
                        @endforeach
                    </td>
                    <td>{{ number_format((float) $p->monto_total, 2, '.', ',') }} {{ $p->moneda }}</td>
                    <td>{{ $p->flujo_direccion }}</td>
                    <td>
                        @if(is_array($p->modalidad_cooperacion))
                            {{ implode(', ', $p->modalidad_cooperacion) }}
                        @else
                            {{ $p->modalidad_cooperacion }}
                        @endif
                    </td>
                    <td>
                        @foreach($p->ods as $ods)
                            <span class="badge">ODS {{ $ods->numero }}</span>
                        @endforeach
                    </td>
                    <td>{{ $p->fecha_inicio ? $p->fecha_inicio->format('d/m/Y') : 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Documento generado automáticamente por el Sistema de Gestión de Cooperación Internacional (CONGOPE)
    </div>
</body>
</html>
