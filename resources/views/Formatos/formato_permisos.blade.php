<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud de Permiso - {{ $trabajador['nombre_completo'] }}</title>
    <style>
        body {
            font-family: Helvetica;
            font-size: 12pt;
            margin: 60px;
            line-height: 1.6;
        }
        .espacios-iniciales {
            height: 150px; /* Espacios en blanco arriba */
        }
        .fecha-lugar {
            text-align: right;
            margin-bottom: 80px; /* ✅ Más espacio abajo (era 60px) */
            font-size: 10pt; /* ✅ Más pequeña (era 12pt del body) */
        }
        .titulo {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            margin: 40px 0 80px 0;
            font-size: 14pt;
        }
        .contenido-principal {
            text-align: justify;
            margin-bottom: 40px;
            line-height: 1.8;
        }
        .despedida {
            text-align: justify;
            margin: 40px 0 100px 0;
        }
        .firmas-container {
            margin-top: 120px;
            font-size: 12pt; /* ✅ Tamaño 12 para toda la sección de firmas */
        }
        .firmas-header {
            width: 100%;
            margin-bottom: 100px;
            font-size: 12pt; /* ✅ Tamaño 12 para headers de firmas */
        }
        .firmas-header table {
            width: 100%;
            border-collapse: collapse;
        }
        .firma-izq, .firma-der {
            width: 50%;
            text-align: center;
            font-weight: bold;
            padding: 0;
            font-size: 12pt; /* ✅ Tamaño 12 para ATENTAMENTE/AUTORIZA */
        }
        .firmas-nombres {
            width: 100%;
            font-size: 12pt; /* ✅ Tamaño 12 para nombres */
        }
        .firmas-nombres table {
            width: 100%;
            border-collapse: collapse;
        }
        .nombre-trabajador, .nombre-director {
            width: 50%;
            text-align: center;
            padding: 0;
            vertical-align: top;
            font-size: 12pt; /* ✅ Tamaño 12 para nombres */
        }
        .cargo {
            font-weight: normal;
            margin-top: 10px;
            font-size: 12pt; /* ✅ Tamaño 12 para cargos */
        }
        /* ✅ MARCA DE AGUA QUE ABARCA TODA LA HOJA - HASTA LOS MÁRGENES */
        .watermark-image {
            position: fixed;
            top: -60px; /* ✅ Extender hacia arriba cubriendo margen superior */
            left: -60px; /* ✅ Extender hacia la izquierda cubriendo margen izquierdo */
            width: calc(100% + 120px); /* ✅ Ancho total + márgenes (60px x 2) */
            height: calc(100% + 120px); /* ✅ Alto total + márgenes (60px x 2) */
            opacity: 1; /* SIN transparencia - imagen completa */
            z-index: -1; /* Detrás de todo el contenido */
            pointer-events: none; /* No interfiere con el contenido */
            object-fit: cover; /* La imagen se ajusta cubriendo toda el área */
        }
    </style>
</head>
<body>
    {{-- ✅ MARCA DE AGUA CON IMAGEN --}}
    @if($watermark ?? null)
        <img 
            src="{{ $watermark }}" 
            alt="Marca de Agua" 
            class="watermark-image"
        >
    @endif

    {{-- ✅ LOGO DESDE CONTROLADOR --}>
    <div style="text-align: center; margin-bottom: 20px;">
        @if($logo)
            <img 
                src="{{ $logo }}" 
                alt="Logo" 
                style="width: 120px; height: auto;"
            >
        @else
            <div style="width: 200px; height: 200px; border: 1px solid #ccc; display: inline-block; text-align: center; line-height: 60px; font-size: 10px; color: #999;">
                LOGO
            </div>
        @endif
    </div>

    {{-- ✅ ESPACIOS EN BLANCO INICIALES --}}
    <div class="espacios-iniciales"></div>

    {{-- ✅ FECHA Y LUGAR --}}
    <div class="fecha-lugar">
        {{ $lugar }}, {{ $fecha_actual }}.
    </div>

    {{-- ✅ TÍTULO SIMPLE CON MOTIVO ESPECÍFICO --}}
    <div class="titulo">
        SOLICITUD DE {{ strtoupper($permiso['motivo_texto']) }}
    </div>

    {{-- ✅ CONTENIDO PRINCIPAL CON MOTIVO ESPECÍFICO --}}
    <div class="contenido-principal">
        Por medio de la presente, solicito un {{ strtolower($permiso['motivo_texto']) }} por <strong>{{ $permiso['dias_totales'] }} días ({{ $permiso['dias_texto'] }})</strong>, 
        
        @if($permiso['dias_totales'] <= 31)
            mencionando las siguientes fechas {{ $permiso['fechas_especificas'] }}, 
        @else
            comprendiendo del {{ $permiso['fecha_inicio'] }} al {{ $permiso['fecha_fin'] }}, 
        @endif
        
        presentándome a laborar el día {{ $permiso['fecha_regreso'] }} del presente año

        @if($permiso['observaciones'] && strlen(trim($permiso['observaciones'])) > 0)
            , debido a que {{ $permiso['observaciones'] }}
        @else
            .
        @endif
    </div>

    {{-- ✅ DESPEDIDA --}}
    <div class="despedida">
        Sin más por el momento me despido de usted con un cordial saludo.
    </div>

    {{-- ✅ FIRMAS --}}
    <div class="firmas-container">
        <div class="firmas-header">
            <table>
                <tr>
                    <td class="firma-izq">ATENTAMENTE</td>
                    <td class="firma-der">AUTORIZA</td>
                </tr>
            </table>
        </div>

        <div class="firmas-nombres">
            <table>
                <tr>
                    <td class="nombre-trabajador">
                        <strong>C. {{ $trabajador['nombre_completo'] }}</strong>
                        <div class="cargo">Trabajador{{ substr($trabajador['nombre_completo'], -1) === 'a' ? 'a' : '' }}</div>
                    </td>
                    <td class="nombre-director">
                        <strong>{{ $firmas['director'] }}</strong>
                        <div class="cargo">Director General</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

</body>
</html>