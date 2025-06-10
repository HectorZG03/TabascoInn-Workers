<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contrato Individual de Trabajo</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            margin: 50px;
        }
        h1 {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .section {
            margin-top: 20px;
        }
        .clausula {
            text-align: justify;
            margin-bottom: 15px;
        }
        .bold {
            font-weight: bold;
        }
        .center {
            text-align: center;
        }
        .uppercase {
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <h1>CONTRATO INDIVIDUAL DE TRABAJO<br>POR TIEMPO DETERMINADO</h1>
    <p class="clausula">
        CONTRATO INDIVIDUAL DE TRABAJO QUE CELEBRAN POR UNA PARTE LA EMPRESA <span class="bold uppercase">TABASCO INN, S.A. DE C.V.</span> REPRESENTADA POR EL LIC. GUSTAVO ENRIQUE ZURITA GONZÁLEZ, A LA CUAL EN LO SUCESIVO SE LE DENOMINARÁ "PATRÓN", Y POR LA OTRA EL C. <span class="bold uppercase">{{ $trabajador->nombre_completo }}</span>, EN SU CALIDAD DE "TRABAJADOR", DENOMINACIÓN QUE RECIBIRÁ EN LO SUCESIVO AL TENOR DE LAS SIGUIENTES:
    </p>
    <div class="section">
        <p class="clausula"><span class="bold">CLÁSULA I:</span>Para los efectos de los artículos 24 y 25 de la Ley Federal del Trabajo, declara el representante de la empresa TABASCO INN, S.A. DE C.V., representada por el LIC. GUSTAVO ENRIQUE ZURITA GONZALEZ, que dicha empresa está constituida conforme a las leyes Mexicanas, dedicándose entre otras actividades a la prestación y desarrollo de servicios de hotelería, restaurante, bar, salón de eventos, servicios de banquetes, y toda clases de actividades propias de la industria. 
            La operación compra – venta, renta de hoteles, moteles y cualquier establecimiento destinado al negocio de las hospitalidades. 
            Así como las diferentes áreas o necesidades de los mismos para su operación, compraventa, renta de video, televisiones, antenas parabólicas, equipo de proyección y sonido, la explotación comercial de salas de juegos, billares, bares, salones de fiestas, restaurantes, discotecas, cafeterías, dulcerías, fuentes de sodas, neverías, tabaquerías, ventas de regalos, y todos los actos, y contratos civiles y mercantiles encaminados a la realización de su objeto social e igualmente a la adquisición de maquinarias, equipos y aparatos necesarios para el desarrollo del objeto social. 
            Y que tiene establecido su domicilio social en la Avenida José Pagues Llergo Número 150, de la Colonia Arboledas, de esta Ciudad de Villahermosa, Tabasco; con C.P. 86079 y con su RFC TIN080522-A59.</p>


        <p class="clausula"><span class="bold">CLÁUSULA II:</span> EL TRABAJADOR manifiesta llamarse C. <span class="bold uppercase">{{$trbajador->nombre_completo}}</span>, tener una edad de <span class="bold">{{ \Carbon\Carbon::parse($trabajador->fecha_nacimiento)->age }} años</span>, 
        haber nacido el día <span class="bold">{{ \Carbon\Carbon::parse($trabajador->fecha_nacimiento)->format('d') }}</span> del mes de <span class="bold">{{ \Carbon\Carbon::parse($trabajador->fecha_nacimiento)->locale('es')->monthName }}</span> del año <span class="bold">{{ \Carbon\Carbon::parse($trabajador->fecha_nacimiento)->format('Y') }}</span> 
        Villahermosa, Centro, Tabasco; y que su estado civil es soltero y que su domicilio actual es: Fracc. Gardenias Edif. B 1, dpto. 104, Col. Indeco del municipio de Centro, Tabasco, C.P. 86017, nacionalidad Mexicana y que su Clave Única del Registro de Población es ROGR020507HTCDZCA8, y que cuenta también con su Registró Federal de Contribuyente, siendo este ROGR020507AM8.</p>




        <p class="clausula"><span class="bold">CLÁUSULA II:</span> EL TRABAJADOR manifiesta llamarse C. <span class="bold uppercase">{{ $trabajador->nombre_completo }}</span>, tener una edad de <span class="bold">{{ \Carbon\Carbon::parse($trabajador->fecha_nacimiento)->age }} años</span>, 
            haber nacido el día <span class="bold">{{ \Carbon\Carbon::parse($trabajador->fecha_nacimiento)->format('d') }}</span> del mes de <span class="bold">{{ \Carbon\Carbon::parse($trabajador->fecha_nacimiento)->locale('es')->monthName }}</span> del año <span class="bold">{{ \Carbon\Carbon::parse($trabajador->fecha_nacimiento)->format('Y') }}</span> 
            
            en {{ $trabajador->direccion ?? 'Villahermosa, Centro, Tabasco' }}; y que su CURP es <span class="bold">{{ $trabajador->curp ?? 'NO ESPECIFICADO' }}</span> y su RFC es <span class="bold">{{ $trabajador->rfc ?? 'NO ESPECIFICADO' }}</span>, y que su domicilio actual es: {{ $trabajador->direccion ?? 'NO ESPECIFICADO' }}.</p>
        <p class="clausula"><span class="bold">CLÁUSULA III:</span> EL TRABAJADOR se obliga a prestar sus servicios subordinados jurídicamente a la empresa TABASCO INN, S.A. DE C.V., en los términos de los artículos 20 y 21 de la Ley Federal del Trabajo, consistiendo las actividades en todas aquellas propias del puesto que se le asigne dentro de la organización, comprometiéndose a desempeñar sus labores con la diligencia, eficiencia y responsabilidad que el caso requiera.</p>

    </div>
    
</body>
</html>