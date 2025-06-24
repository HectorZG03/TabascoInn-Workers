<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud de Permiso - {{ $trabajador['nombre_completo'] }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12pt;
            margin: 60px;
            line-height: 1.6;
        }
        .titulo {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            margin: 40px 0;
            font-size: 14pt;
        }
        .justificado {
            text-align: justify;
            margin-bottom: 20px;
        }
        .firma-container {
            display: flex;
            justify-content: space-between;
            margin-top: 80px;
        }
        .firma {
            text-align: center;
            width: 45%;
        }
        .ccp {
            margin-top: 60px;
        }
        .fecha-lugar {
            text-align: right;
            margin-bottom: 40px;
        }
        .datos-trabajador {
            margin-bottom: 20px;
        }
        .observaciones {
            margin-top: 20px;
            padding: 10px;
            border-left: 3px solid #007bff;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>

    {{-- ✅ FECHA Y LUGAR DINÁMICOS --}}
    <p class="fecha-lugar">{{ $lugar }}, {{ $fecha_actual }}.</p>

    {{-- ✅ TÍTULO DINÁMICO SEGÚN TIPO DE PERMISO --}}
    <h2 class="titulo">SOLICITUD DE {{ $permiso['tipo'] }}</h2>

    {{-- ✅ CUERPO PRINCIPAL CON DATOS DINÁMICOS --}}
    <p class="justificado">
        Por medio de la presente, <strong>{{ $trabajador['nombre_completo'] }}</strong>, 
        quien labora en el área de <strong>{{ $trabajador['area'] }}</strong> 
        como <strong>{{ $trabajador['categoria'] }}</strong>, 
        solicito un permiso por <strong>{{ $permiso['dias_totales'] }} días ({{ $permiso['dias_texto'] }})</strong>, 
        comprendiendo del <strong>{{ $permiso['fecha_inicio'] }}</strong> 
        al <strong>{{ $permiso['fecha_fin'] }}</strong>, 
        presentándome a laborar el día <strong>{{ $permiso['fecha_regreso'] }}</strong>, 
        {{ $permiso['motivo'] }}
    </p>

    {{-- ✅ DETALLES DE FECHAS (si son muchos días, mostrar rango; si son pocos, mostrar lista) --}}
    @if($permiso['dias_totales'] <= 10)
        <p class="justificado">
            <strong>Fechas específicas del permiso:</strong><br>
            @foreach($permiso['fechas_detalle'] as $index => $fecha)
                {{ $fecha }}@if(!$loop->last), @endif
            @endforeach
        </p>
    @endif

    {{-- ✅ OBSERVACIONES ADICIONALES SI EXISTEN --}}
    @if($permiso['observaciones'] && strlen($permiso['observaciones']) > 100)
        <div class="observaciones">
            <strong>Observaciones adicionales:</strong><br>
            {{ $permiso['observaciones'] }}
        </div>
    @endif

    <p class="justificado">
        Sin más por el momento me despido de usted con un cordial saludo, 
        agradeciendo de antemano la atención prestada a la presente solicitud.
    </p>

    {{-- ✅ FIRMAS DINÁMICAS --}}
    <div class="firma-container">
        <div class="firma">
            <p><strong>ATENTAMENTE</strong></p>
            <br><br><br>
            <p><strong>{{ $firmas['trabajador'] }}</strong><br>Trabajador(a)</p>
        </div>
        <div class="firma">
            <p><strong>AUTORIZA</strong></p>
            <br><br><br>
            <p><strong>{{ $firmas['director'] }}</strong><br>Director General</p>
        </div>
    </div>

    {{-- ✅ COPIAS --}}
    <div class="ccp">
        <p><strong>C.C.P.</strong> Recursos Humanos - Grupo Zurita</p>
        <p><strong>C.C.P.</strong> Recursos Humanos - Hotel Tabasco Inn</p>
        <p><strong>C.C.P.</strong> Expediente del Trabajador</p>
    </div>

    {{-- ✅ PIE DE PÁGINA CON INFORMACIÓN ADICIONAL --}}
    <div style="position: fixed; bottom: 20px; left: 60px; right: 60px; font-size: 10pt; color: #666; text-align: center; border-top: 1px solid #ccc; padding-top: 10px;">
        Documento generado el {{ now()->locale('es')->translatedFormat('d \d\e F \d\e Y \a \l\a\s H:i') }} hrs. | 
        Permiso ID: {{ $permiso['id'] ?? 'N/A' }} | 
        Tipo: {{ $permiso['tipo'] }}
    </div>

</body>
</html>