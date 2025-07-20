<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrato de Trabajo - Tiempo Determinado</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.4;
            font-size: 11px;
            margin: 20px;
            color: #000;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .subtitle {
            font-size: 12px;
            font-weight: bold;
            color: #666;
        }
        .content {
            text-align: justify;
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 15px;
        }
        .clause {
            margin-bottom: 12px;
            text-indent: 20px;
        }
        .data {
            font-weight: bold;
            text-decoration: underline;
        }
        .signatures {
            margin-top: 50px;
            display: table;
            width: 100%;
        }
        .signature-block {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 20px;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 60px;
            padding-top: 5px;
            font-size: 10px;
        }
        .page-break {
            page-break-before: always;
        }
        .info-box {
            border: 1px solid #333;
            padding: 10px;
            margin: 15px 0;
            background-color: #f9f9f9;
        }
        .highlight {
            background-color: #ffff99;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- ENCABEZADO -->
    <div class="header">
        <div class="title">CONTRATO INDIVIDUAL DE TRABAJO</div>
        <div class="subtitle">POR TIEMPO DETERMINADO</div>
        <div style="margin-top: 10px; font-size: 10px;">
            Contrato No. {{ $trabajador->id_trabajador ?? 'N/A' }}-{{ $fecha_inicio->format('Y') }}
        </div>
    </div>

    <!-- INFORMACIÓN DEL CONTRATO -->
    <div class="info-box">
        <strong>TIPO DE CONTRATO:</strong> <span class="highlight">POR TIEMPO DETERMINADO</span><br>
        <strong>DURACIÓN:</strong> {{ $duracion_texto }}<br>
        <strong>VIGENCIA:</strong> Del {{ $fecha_inicio->format('d/m/Y') }} al {{ $fecha_fin->format('d/m/Y') }}
    </div>

    <!-- CONTENIDO DEL CONTRATO -->
    <div class="content">
        <div class="section">
            <p>
                Contrato Individual de Trabajo por Tiempo Determinado que celebran por una parte 
                <span class="data">{{ config('app.company_name', 'LA EMPRESA') }}</span>, 
                representada por <span class="data">{{ config('app.legal_representative', '[REPRESENTANTE LEGAL]') }}</span>, 
                a quien en lo sucesivo se le denominará <strong>"EL PATRÓN"</strong>, y por la otra parte 
                <span class="data">{{ strtoupper($trabajador->nombre_completo ?? '') }}</span>, 
                a quien en lo sucesivo se le denominará <strong>"EL TRABAJADOR"</strong>, 
                al tenor de las siguientes:
            </p>
        </div>

        <div style="text-align: center; font-weight: bold; margin: 20px 0;">
            D E C L A R A C I O N E S
        </div>

        <div class="section">
            <p><strong>I. DECLARA "EL PATRÓN":</strong></p>
            <div class="clause">
                a) Que es una empresa legalmente constituida conforme a las leyes mexicanas y que se dedica a 
                {{ config('app.business_description', '[DESCRIPCIÓN DEL GIRO DE LA EMPRESA]') }}.
            </div>
            <div class="clause">
                b) Que tiene su domicilio en {{ config('app.company_address', '[DIRECCIÓN DE LA EMPRESA]') }}.
            </div>
            <div class="clause">
                c) Que requiere los servicios del TRABAJADOR por tiempo determinado para las labores que se especifican en este contrato.
            </div>
        </div>

        <div class="section">
            <p><strong>II. DECLARA "EL TRABAJADOR":</strong></p>
            <div class="clause">
                a) Llamarse <span class="data">{{ strtoupper($trabajador->nombre_completo ?? '') }}</span>.
            </div>
            <div class="clause">
                b) Ser de nacionalidad Mexicana, tener <span class="data">{{ $trabajador->fecha_nacimiento ? $trabajador->fecha_nacimiento->age : '[EDAD]' }}</span> años de edad.
            </div>
            @if($trabajador->fecha_nacimiento)
            <div class="clause">
                c) Haber nacido el {{ $trabajador->fecha_nacimiento->format('d/m/Y') }}
                @if($trabajador->lugar_nacimiento)
                    en {{ $trabajador->lugar_nacimiento }}.
                @else
                    .
                @endif
            </div>
            @endif
            @if($trabajador->curp)
            <div class="clause">
                d) Su CURP es: <span class="data">{{ $trabajador->curp }}</span>.
            </div>
            @endif
            @if($trabajador->rfc)
            <div class="clause">
                e) Su RFC es: <span class="data">{{ $trabajador->rfc }}</span>.
            </div>
            @endif
            @if($trabajador->direccion)
            <div class="clause">
                f) Tener su domicilio en: <span class="data">{{ strtoupper($trabajador->direccion) }}</span>
                @if($trabajador->ciudad_actual || $trabajador->estado_actual)
                    , {{ $trabajador->ciudad_actual ? strtoupper($trabajador->ciudad_actual) : '' }}
                    {{ $trabajador->estado_actual ? ', ' . strtoupper($trabajador->estado_actual) : '' }}.
                @else
                    .
                @endif
            </div>
            @endif
        </div>

        <div style="text-align: center; font-weight: bold; margin: 20px 0;">
            C L Á U S U L A S
        </div>

        <div class="section">
            <p><strong>PRIMERA.- OBJETO:</strong></p>
            <div class="clause">
                El presente contrato tiene por objeto la prestación de servicios del TRABAJADOR a favor del PATRÓN, 
                en el puesto de <span class="data">{{ strtoupper($trabajador->fichaTecnica->categoria->nombre_categoria ?? '[CATEGORIA]') }}</span> 
                en el área de <span class="data">{{ strtoupper($trabajador->fichaTecnica->categoria->area->nombre_area ?? '[AREA]') }}</span>.
            </div>
        </div>

        <div class="section">
            <p><strong>SEGUNDA.- DURACIÓN DEL CONTRATO:</strong></p>
            <div class="clause">
                <span class="highlight">El presente contrato es por TIEMPO DETERMINADO</span>, iniciando el 
                <span class="data">{{ $fecha_inicio->format('d/m/Y') }}</span> y concluyendo el 
                <span class="data">{{ $fecha_fin->format('d/m/Y') }}</span>, 
                con una duración total de <span class="data">{{ $duracion_texto }}</span>.
            </div>
            <div class="clause">
                Al término de la duración pactada, el presente contrato se dará por terminado de pleno derecho, 
                sin responsabilidad para ninguna de las partes, salvo que se convenga por escrito su renovación.
            </div>
        </div>

        <div class="section">
            <p><strong>TERCERA.- LUGAR DE TRABAJO:</strong></p>
            <div class="clause">
                El TRABAJADOR prestará sus servicios en las instalaciones del PATRÓN ubicadas en 
                {{ config('app.work_address', config('app.company_address', '[DIRECCIÓN DEL TRABAJO]')) }}, 
                sin perjuicio de que pueda ser comisionado a otros lugares cuando las necesidades del servicio así lo requieran.
            </div>
        </div>

        <div class="section">
            <p><strong>CUARTA.- JORNADA DE TRABAJO:</strong></p>
            <div class="clause">
                La jornada de trabajo será de 
                <span class="data">{{ number_format($trabajador->fichaTecnica->horas_trabajadas_calculadas ?? 8, 1) }}</span> horas diarias, 
                en horario <span class="data">{{ strtoupper($trabajador->fichaTecnica->turno_texto ?? 'A ASIGNAR') }}</span>, 
                de <span class="data">{{ $trabajador->fichaTecnica->hora_entrada ?? 'XX:XX' }}</span> 
                a <span class="data">{{ $trabajador->fichaTecnica->hora_salida ?? 'XX:XX' }}</span> horas.
            </div>
            @if($trabajador->fichaTecnica && $trabajador->fichaTecnica->dias_laborables)
            <div class="clause">
                Los días de trabajo serán: 
                @foreach($trabajador->fichaTecnica->dias_laborables as $index => $dia)
                    {{ ucfirst($dia) }}@if($index < count($trabajador->fichaTecnica->dias_laborables) - 1), @endif
                @endforeach.
            </div>
            @endif
            @if($trabajador->fichaTecnica && $trabajador->fichaTecnica->dias_descanso)
            <div class="clause">
                Los días de descanso serán: 
                @foreach($trabajador->fichaTecnica->dias_descanso as $index => $dia)
                    {{ ucfirst($dia) }}@if($index < count($trabajador->fichaTecnica->dias_descanso) - 1), @endif
                @endforeach.
            </div>
            @endif
        </div>

        <div class="section">
            <p><strong>QUINTA.- SALARIO:</strong></p>
            <div class="clause">
                El PATRÓN pagará al TRABAJADOR por los servicios objeto de este contrato, un salario de 
                <span class="data">${{ number_format($trabajador->fichaTecnica->sueldo_diarios ?? 0, 2) }}</span> 
                ({{ $salario_texto }}) diarios.
            </div>
            <div class="clause">
                El salario se pagará en efectivo o mediante depósito bancario, de manera semanal, 
                los días que determine el PATRÓN, previa entrega del comprobante correspondiente.
            </div>
        </div>

        @if($trabajador->fichaTecnica && ($trabajador->fichaTecnica->beneficiario_nombre || $trabajador->fichaTecnica->beneficiario_parentesco))
        <div class="section">
            <p><strong>SEXTA.- BENEFICIARIOS:</strong></p>
            <div class="clause">
                En caso de fallecimiento del TRABAJADOR, los beneficiarios serán:
                @if($trabajador->fichaTecnica->beneficiario_nombre)
                <span class="data">{{ strtoupper($trabajador->fichaTecnica->beneficiario_nombre) }}</span>
                @if($trabajador->fichaTecnica->beneficiario_parentesco)
                    ({{ ucfirst($trabajador->fichaTecnica->beneficiario_parentesco) }})
                @endif
                @else
                Los que designe el TRABAJADOR por escrito.
                @endif
            </div>
        </div>
        @endif

        <div class="section">
            <p><strong>SÉPTIMA.- OBLIGACIONES DEL TRABAJADOR:</strong></p>
            <div class="clause">
                a) Cumplir con las disposiciones de trabajo que dicte el PATRÓN.
            </div>
            <div class="clause">
                b) Observar las medidas preventivas e higiénicas que acuerden las autoridades competentes y las que indique el PATRÓN.
            </div>
            <div class="clause">
                c) Desempeñar el servicio bajo la dirección del PATRÓN o de su representante.
            </div>
            <div class="clause">
                d) Guardar escrupulosamente los secretos técnicos, comerciales y de fabricación de los productos a cuya elaboración concurra.
            </div>
        </div>

        <div class="section">
            <p><strong>OCTAVA.- TERMINACIÓN:</strong></p>
            <div class="clause">
                <span class="highlight">Este contrato terminará automáticamente el {{ $fecha_fin->format('d/m/Y') }}</span>, 
                sin necesidad de aviso previo ni responsabilidad para ninguna de las partes.
            </div>
            <div class="clause">
                También podrá darse por terminado por cualquiera de las causas previstas en los artículos 46, 47, 51 y 52 de la Ley Federal del Trabajo.
            </div>
        </div>

        <div class="section">
            <p><strong>NOVENA.- LEY APLICABLE:</strong></p>
            <div class="clause">
                En todo lo no previsto en este contrato, se aplicarán las disposiciones contenidas en la Ley Federal del Trabajo, 
                su Reglamento y demás disposiciones legales aplicables.
            </div>
        </div>

        <div class="section">
            <p>
                Leído que fue el presente contrato y enteradas las partes de su contenido y alcance legal, 
                lo firman por duplicado en {{ config('app.company_city', 'Villahermosa, Tabasco') }}, 
                el {{ $fecha_inicio->format('d \d\e F \d\e Y') }}.
            </p>
        </div>
    </div>

    <!-- FIRMAS -->
    <div class="signatures">
        <div class="signature-block">
            <div class="signature-line">
                <strong>EL PATRÓN</strong><br>
                {{ strtoupper(config('app.legal_representative', '[REPRESENTANTE LEGAL]')) }}<br>
                {{ strtoupper(config('app.company_name', '[NOMBRE DE LA EMPRESA]')) }}
            </div>
        </div>
        <div class="signature-block">
            <div class="signature-line">
                <strong>EL TRABAJADOR</strong><br>
                {{ strtoupper($trabajador->nombre_completo ?? '') }}
            </div>
        </div>
    </div>

    <!-- PIE DE PÁGINA -->
    <div style="margin-top: 30px; text-align: center; font-size: 9px; color: #666;">
        Contrato generado el {{ now()->format('d/m/Y \a \l\a\s H:i') }} hrs.<br>
        <strong>CONTRATO POR TIEMPO DETERMINADO</strong> - Duración: {{ $duracion_texto }}
    </div>
</body>
</html>