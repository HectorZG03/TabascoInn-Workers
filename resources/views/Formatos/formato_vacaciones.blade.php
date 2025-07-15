<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Vacaciones</title>
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
        
        .field-inline {
            display: inline-block;
            margin-right: 30px;
        }
        
        .underline {
            text-decoration: underline;
            font-weight: normal;
            min-width: 200px;
            display: inline-block;
        }
        
        .editable {
            border: none;
            border-bottom: 1px solid #000;
            background: transparent;
            font-family: inherit;
            font-size: inherit;
            min-width: 150px;
            padding: 2px 5px;
        }
        
        .editable:focus {
            outline: none;
            background-color: #f0f8ff;
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
        
        .vacation-table td {
            border: 1px solid #000;
            padding: 8px 12px;
            text-align: center;
            font-weight: bold;
        }
        
        .vacation-table .day-number {
            width: 60px;
        }
        
        .vacation-table .day-name {
            width: 120px;
        }
        
        .vacation-table .date {
            width: 120px;
        }
        
        .vacation-table .status {
            width: 200px;
        }
        
        .signatures {
            margin-top: 60px;
        }
        
        .signature-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 50px;
        }
        
        .signature-box {
            text-align: center;
            width: 45%;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            margin-bottom: 5px;
            height: 1px;
            width: 100%;
        }
        
        .signature-name {
            font-weight: bold;
            font-size: 14px;
        }
        
        .signature-title {
            font-size: 12px;
            margin-top: 2px;
        }
        
        @media print {
            body {
                padding: 20px;
            }
            
            .editable {
                border-bottom: 1px solid #000;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Gerencia de Recursos Humanos</h2>
        <h2>Solicitud de Vacaciones</h2>
    </div>
    
    <div class="content">
        <div class="field">
            <strong>FECHA:</strong> <span class="underline"> </span>
        </div>
        
        <div class="field">
            <strong>NOMBRE DE LA EMPRESA:</strong> <strong>TABASCO INN S.A. DE C.V.</strong>
        </div>
        
        <div class="field">
            <strong>NOMBRE DEL TRABAJADOR:</strong> <span class="underline"></span>
        </div>
        
        <div class="field">
            <strong>CATEGORIA:</strong> <span class="underline"></span>, <strong>FECHA DE INGRESO:</strong> <span class="underline"></span>
        </div>
        
        <div class="field">
            <strong>PERIODO SOLICITADO:</strong> <span class="underline"></span>, <strong>ANTIGÜEDAD:</strong> <span class="underline"></span>
        </div>
        
        <div class="field">
            <strong>DIAS QUE CORRESPONDEN:</strong> <span class="underline"></span>, <strong>DIAS DISFRUTADOS:</strong> <span class="underline"></span>
        </div>
        
        <div class="field">
            <strong>DIAS SOLICITADOS:</strong> <span class="underline"></span>, <strong>DIAS PENDIENTES DEL PERIODO $periodo:</strong> <span class="underline"></span>
        </div>
    </div>
    
    <div class="table-title">TABLA DE AMORTIZACIÓN</div>
    
        <table class="vacation-table">
            @foreach ($vacaciones as $vacacion)
                @php
                    $dias = \Carbon\CarbonPeriod::create($vacacion->fecha_inicio, $vacacion->fecha_fin);
                    $contador = 1;
                @endphp

                @foreach ($dias as $dia)
                    <tr>
                        <td class="day-number">{{ $contador }}</td>
                        <td class="day-name">{{ \Carbon\Carbon::parse($dia)->locale('es')->isoFormat('dddd') }}</td>
                        <td class="date">{{ $dia->format('d/m/Y') }}</td>
                        <td class="status">VACACIONES</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td class="day-name">{{ \Carbon\Carbon::parse($dia)->addDay()->locale('es')->isoFormat('dddd') }}</td>
                        <td class="date">{{ $dia->addDay()->format('d/m/Y') }}</td>
                        <td class="status">REGRESA A TRABAJAR</td>
                    </tr>
                    @php $contador++; @endphp
                @endforeach
            @endforeach
        </table>
    
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