<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documento de Amortización de Vacaciones</title>
    <style>
        @page {
            size: letter portrait;
            margin: 2cm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            max-width: 800px;
            margin: 0 auto;
            padding: 120px 20px 40px 20px;
            line-height: 1.5;
            background-color: #ffffff;
            position: relative;
        }

        .watermark-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: -1;
            overflow: hidden;
        }

        .watermark-background img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .content-wrapper {
            position: relative;
            z-index: 1;
            background-color: transparent;
            margin-top: 80px;
        }

        .header-container {
            position: relative;
            margin-bottom: 40px;
            min-height: 60px;
        }

        .header {
            text-align: center;
            padding-left: 0px;
        }

        .header h2 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .content {
            margin-bottom: 25px;
        }

        .field {
            margin-bottom: 15px;
            font-weight: bold;
        }

        .underline {
            text-decoration: underline;
            font-weight: normal;
            min-width: 150px;
            display: inline-block;
        }

        .table-title {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            margin: 25px 0 15px 0;
            text-transform: uppercase;
        }

        .vacation-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
            background-color: rgba(255, 255, 255, 0.9);
        }

        .vacation-table th,
        .vacation-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: center;
            font-weight: bold;
            background-color: rgba(255, 255, 255, 0.9);
            font-size: 10px;
        }

        .vacation-table th {
            background-color: rgba(240, 240, 240, 0.95);
            font-weight: bold;
            font-size: 10px;
        }

        .vacation-table .text-left {
            text-align: left;
        }

        .vacation-table .text-right {
            text-align: right;
        }

        .total-row {
            background-color: rgba(233, 236, 239, 0.95);
            font-weight: bold;
        }

        .signatures {
            margin-top: 60px;
            width: 100%;
        }

        .signature-row {
            width: 100%;
            margin-bottom: 50px;
            display: table;
            table-layout: fixed;
        }

        .signature-box {
            display: table-cell;
            text-align: center;
            width: 50%;
            padding: 0 15px;
            vertical-align: top;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-bottom: 8px;
            height: 1px;
            width: 100%;
        }

        .signature-name {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 3px;
        }

        .signature-title {
            font-size: 12px;
            margin-top: 2px;
            font-weight: normal;
        }

        @media print {
            body {
                padding: 20px;
            }

            .signature-row {
                margin-bottom: 35px;
            }

            .header-container {
                margin-bottom: 20px;
            }

            .watermark-background {
                position: absolute;
                width: 100%;
                height: 100%;
            }

            .vacation-table th,
            .vacation-table td {
                padding: 4px 6px;
                font-size: 9px;
            }
        }
    </style>
</head>
<body>
    {{-- Marca de agua de fondo (opcional) --}}
    @if(isset($watermark_empresa) && $watermark_empresa)
        <div class="watermark-background">
            <img src="{{ $watermark_empresa }}" alt="Marca de Agua">
        </div>
    @endif

    <div class="content-wrapper">
        <div class="header-container">
            <div class="header">
                <h2>Gerencia de Recursos Humanos</h2>
                <h2>Documento de Amortización de Vacaciones</h2>
            </div>
        </div>

        <div class="content">
            <div class="field">
                <strong>FECHA:</strong> <span class="underline">{{ $fecha_generacion }}</span>
            </div>
            <div class="field">
                <strong>NOMBRE DE LA EMPRESA:</strong> <strong>TABASCO INN S.A. DE C.V.</strong>
            </div>
            <div class="field">
                <strong>NOMBRE DEL TRABAJADOR:</strong> <span class="underline">{{ $trabajador->nombre_completo }}</span>
            </div>
            <div class="field">
                <strong>PUESTO:</strong> <span class="underline">{{ $trabajador->fichaTecnica->categoria->nombre_categoria ?? 'Sin categoría' }}</span> 
                <strong>FECHA DE INGRESO:</strong> <span class="underline">{{ $trabajador->fecha_ingreso->format('d/m/Y') }}</span>
            </div>
            <div class="field">
                <strong>PERIODO SOLICITADO:</strong> <span class="underline">{{ $periodo_vacacional }}</span>
                <strong>ANTIGÜEDAD:</strong> <span class="underline">{{ $trabajador->antiguedad }} AÑO(S)</span>
            </div>
            <div class="field">
                <strong>DIAS QUE CORRESPONDEN:</strong> <span class="underline">{{ $trabajador->dias_vacaciones_correspondientes }} DIAS</span>
                <strong>DIAS DISFRUTADOS:</strong> <span class="underline">{{ $dias_disfrutados }} DIAS</span>
            </div>
            <div class="field">
                <strong>DIAS SOLICITADOS:</strong> <span class="underline">{{ $total_dias }} DIAS</span>
                <strong>DIAS PENDIENTES DEL PERIODO {{ $periodo_vacacional }}:</strong> <span class="underline">{{ $dias_pendientes }} DIAS</span>
            </div>
            <div class="field">
                <strong>DÍAS DE DESCANSO SEMANAL:</strong> <span class="underline">{{ $dias_descanso }}</span>
            </div>
        </div>

        <div class="table-title">Tabla de Amortización</div>

        <table class="vacation-table">
            <thead>
                <tr>
                    <th>Período</th>
                    <th>Días<br>Solicitados</th>
                    <th>Días<br>Descanso</th>
                    <th>Fecha<br>Inicio</th>
                    <th>Fecha<br>Fin</th>
                    <th>Fecha<br>Reintegro</th>
                    <th>Año<br>Corresp.</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($vacaciones as $vacacion)
                    <tr>
                        <td>{{ $vacacion->periodo_vacacional }}</td>
                        <td>{{ $vacacion->dias_solicitados }}</td>
                        <td>{{ $dias_descanso }}</td>
                        <td>{{ $vacacion->fecha_inicio->format('d/m/Y') }}</td>
                        <td>{{ $vacacion->fecha_fin->format('d/m/Y') }}</td>
                        <td>{{ $vacacion->fecha_reintegro ? $vacacion->fecha_reintegro->format('d/m/Y') : 'N/A' }}</td>
                        <td>{{ $vacacion->año_correspondiente }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td><strong>TOTAL</strong></td>
                    <td><strong>{{ $total_dias }} días</strong></td>
                    <td colspan="5">-</td>
                </tr>
            </tbody>
        </table>

        <div class="signatures">
            <!-- Primera fila: Trabajador y Primer Gerente -->
            <div class="signature-row">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-name">{{ $trabajador->nombre_completo }}</div>
                    <div class="signature-title">Firma del trabajador</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-name">
                        @if(isset($firmas['gerente_1']))
                            {{ $firmas['gerente_1']->nombre_completo }}
                        @else
                            Primer Gerente
                        @endif
                    </div>
                    <div class="signature-title">
                        @if(isset($firmas['gerente_1']))
                            {{ $firmas['gerente_1']->cargo }}
                        @endif
                    </div>
                </div>
            </div>

            <!-- Segunda fila: Segundo y Tercer Gerente -->
            <div class="signature-row">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-name">
                        @if(isset($firmas['gerente_2']))
                            {{ $firmas['gerente_2']->nombre_completo }}
                        @else
                            Segundo Gerente
                        @endif
                    </div>
                    <div class="signature-title">
                        @if(isset($firmas['gerente_2']))
                            {{ $firmas['gerente_2']->cargo }}
                        @endif
                    </div>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-name">
                        @if(isset($firmas['gerente_3']))
                            {{ $firmas['gerente_3']->nombre_completo }}
                        @else
                            Tercer Gerente
                        @endif
                    </div>
                    <div class="signature-title">
                        @if(isset($firmas['gerente_3']))
                            {{ $firmas['gerente_3']->cargo }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>