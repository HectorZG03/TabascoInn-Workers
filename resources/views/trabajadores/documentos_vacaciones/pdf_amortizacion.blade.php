{{-- resources/views/trabajadores/documentos_vacaciones/pdf_amortizacion.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documento de Amortización de Vacaciones</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, Arial;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
            line-height: 1.4;
            background-color: #ffffff;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
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
            min-width: 200px;
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
        }
        
        .vacation-table th,
        .vacation-table td {
            border: 1px solid #000;
            padding: 8px 12px;
            text-align: center;
            font-weight: bold;
        }
        
        .vacation-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .vacation-table .text-left {
            text-align: left;
        }
        
        .vacation-table .text-right {
            text-align: right;
        }
        
        .total-row {
            background-color: #e9ecef;
            font-weight: bold;
        }
        
        .signatures {
            margin-top: 60px;
            width: 100%;
        }
        
        .signature-row {
            width: 100%;
            margin-bottom: 60px;
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
            font-size: 11px;
            margin-top: 2px;
            font-weight: normal;
        }
        
        @media print {
            body {
                padding: 20px;
            }
            
            .signature-row {
                margin-bottom: 40px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Gerencia de Recursos Humanos</h2>
        <h2>Documento de Amortización de Vacaciones</h2>
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
            <strong>CATEGORIA:</strong> <span class="underline">{{ $trabajador->fichaTecnica->categoria->nombre_categoria ?? 'Sin categoría' }}</span>, 
            <strong>FECHA DE INGRESO:</strong> <span class="underline">{{ $trabajador->fecha_ingreso->format('d/m/Y') }}</span>
        </div>
        
        <div class="field">
            <strong>ÁREA:</strong> <span class="underline">{{ $trabajador->fichaTecnica->categoria->area->nombre_area ?? 'Sin área' }}</span>, 
            <strong>ANTIGÜEDAD:</strong> <span class="underline">{{ $trabajador->antiguedad }} años</span>
        </div>
        
        <div class="field">
            <strong>DIAS QUE CORRESPONDEN:</strong> <span class="underline">{{ $trabajador->dias_vacaciones_correspondientes }} días</span>, 
            <strong>DIAS RESTANTES:</strong> <span class="underline">{{ $trabajador->dias_vacaciones_restantes_este_año }} días</span>
        </div>
        
        <div class="field">
            <strong>TOTAL DIAS SOLICITADOS:</strong> <span class="underline">{{ $total_dias }} días</span>, 
            <strong>AÑO CORRESPONDIENTE:</strong> <span class="underline">{{ $año_actual }}</span>
        </div>
    </div>
    
    <div class="table-title">Vacaciones Pendientes de Amortización</div>
    
    <table class="vacation-table">
        <thead>
            <tr>
                <th>Período</th>
                <th>Días Solicitados</th>
                <th>Fecha Inicio</th>
                <th>Fecha Fin</th>
                <th>Año Correspondiente</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($vacaciones as $vacacion)
                <tr>
                    <td>{{ $vacacion->periodo_vacacional }}</td>
                    <td>{{ $vacacion->dias_solicitados }}</td>
                    <td>{{ $vacacion->fecha_inicio->format('d/m/Y') }}</td>
                    <td>{{ $vacacion->fecha_fin->format('d/m/Y') }}</td>
                    <td>{{ $vacacion->año_correspondiente }}</td>
                </tr>
            @endforeach
            
            <!-- Fila de totales -->
            <tr class="total-row">
                <td><strong>TOTAL</strong></td>
                <td><strong>{{ $total_dias }} días</strong></td>
                <td colspan="3">-</td>
            </tr>
        </tbody>
    </table>
    
    <div class="content">
        <div class="field">
            <strong>NOTA:</strong> Este documento lista las vacaciones pendientes que requieren amortización. 
            Una vez firmado, debe ser devuelto a Recursos Humanos para su procesamiento.
        </div>
    </div>
    
    <div class="signatures">
        <div class="signature-row">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-name">Firma del trabajador</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-name">Lic. Irma Yuliana Muñoz López</div>
                <div class="signature-title">Subgerente Administrativo</div>
            </div>
        </div>
        
        <div class="signature-row">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-name">L.C. Cecilia del C. Velázquez del Valle</div>
                <div class="signature-title">Recursos Humanos</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-name">Lic. Justo Encalada González</div>
                <div class="signature-title">Gerente General</div>
            </div>
        </div>
    </div>
</body>
</html>