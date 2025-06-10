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
            margin-bottom: 30px;
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
        .clausula-numero {
            font-weight: bold;
            text-decoration: underline;
        }
        .clausula-final {
            margin-top: 30px;
            text-align: center;
        }
        .firmas {
            margin-top: 80px;
            display: table;
            width: 100%;
        }
        .firma-seccion {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .linea-firma {
            border-top: 1px solid black;
            margin: 60px 20px 10px 20px;
        }
    </style>
</head>
<body>
    <h1>CONTRATO INDIVIDUAL DE TRABAJO<br>POR TIEMPO DETERMINADO</h1>
    
    <p class="clausula">
        CONTRATO INDIVIDUAL DE TRABAJO QUE CELEBRAN POR UNA PARTE LA EMPRESA <span class="bold uppercase">TABASCO INN, S.A. DE C.V.</span> REPRESENTADA POR EL LIC. GUSTAVO ENRIQUE ZURITA GONZÁLEZ, A LA CUAL EN LO SUCESIVO SE LE DENOMINARÁ "PATRÓN", 
        Y POR LA OTRA EL C. <span class="bold uppercase">{{ $trabajador->nombre_completo }}</span>, EN SU CALIDAD DE "TRABAJADOR", DENOMINACIÓN QUE RECIBIRÁ EN LO SUCESIVO AL TENOR DE LAS SIGUIENTES:
    </p>
    
    <div class="section">
        <p class="clausula">
            <span class="clausula-numero">CLÁUSULA I:</span> Para los efectos de los artículos 24 y 25 de la Ley Federal del Trabajo, declara el representante de la empresa TABASCO INN, 
            S.A. DE C.V., representada por el LIC. GUSTAVO ENRIQUE ZURITA GONZALEZ, que dicha empresa está constituida conforme a las leyes Mexicanas, dedicándose entre otras actividades a la prestación y desarrollo de servicios de hotelería, 
            restaurante, bar, salón de eventos, servicios de banquetes, y toda clases de actividades propias de la industria. 
        </p>
        <p class="clausula">
            La operación compra – venta, renta de hoteles, moteles y cualquier establecimiento destinado al negocio de las hospitalidades. Así como las diferentes áreas o necesidades de los mismos para su operación, compraventa, renta de video, 
            televisiones, antenas parabólicas, equipo de proyección y sonido, la explotación comercial de salas de juegos, billares, bares, salones de fiestas, restaurantes, discotecas, cafeterías, dulcerías, fuentes de sodas, neverías, tabaquerías, 
            ventas de regalos, y todos los actos, y contratos civiles y mercantiles encaminados a la realización de su objeto social e igualmente a la adquisición de maquinarias, equipos y aparatos necesarios para el desarrollo del objeto social.
        </p>
        <p class="clausula">
            Y que tiene establecido su domicilio social en la Avenida José Pagues Llergo Número 150, de la Colonia Arboledas, de esta Ciudad de Villahermosa, Tabasco; con C.P. 86079 y con su RFC TIN080522-A59.
        </p>

        <p class="clausula">
            <span class="clausula-numero">CLÁUSULA II:</span> EL TRABAJADOR manifiesta llamarse C. <span class="bold uppercase">{{ $trabajador->nombre_completo }}</span>, tener una edad de <span class="bold">{{ \Carbon\Carbon::parse($trabajador->fecha_nacimiento)->age }} años</span>, 
            haber nacido el día <span class="bold">{{ \Carbon\Carbon::parse($trabajador->fecha_nacimiento)->format('d') }}</span> del mes de <span class="bold">{{ \Carbon\Carbon::parse($trabajador->fecha_nacimiento)->locale('es')->monthName }}</span> del año <span class="bold">{{ \Carbon\Carbon::parse($trabajador->fecha_nacimiento)->format('Y') }}</span> 
            en {{ $trabajador->lugar_nacimiento ?? ($trabajador->ciudad_actual && $trabajador->estado_actual ? $trabajador->ciudad_actual . ', ' . $trabajador->estado_actual : $trabajador->estado_actual ?? 'Villahermosa, Centro, Tabasco') }}; y que su CURP es <span class="bold">{{ $trabajador->curp ?? 'NO ESPECIFICADO' }}</span> 
            y su RFC es <span class="bold">{{ $trabajador->rfc ?? 'NO ESPECIFICADO' }}</span>, y que su domicilio actual es: {{ $trabajador->direccion ?? ($trabajador->ciudad_actual && $trabajador->estado_actual ? $trabajador->ciudad_actual . ', ' . $trabajador->estado_actual : 'NO ESPECIFICADO') }}.
        </p>

        <p class="clausula">
            <span class="clausula-numero">CLÁUSULA III:</span> EL TRABAJADOR se obliga a prestar sus servicios subordinados jurídicamente a la empresa <span class="bold">TABASCO INN, S.A. DE C.V.,</span> 
            en los términos de los artículos 20 y 21 de la Ley Federal del Trabajo, consistiendo las actividades de EL TRABAJADOR en revisar el cumplimiento de los procedimientos y políticas de control interno de las diferentes operaciones de la empresa en 
            base a riesgos, verificando la fiabilidad de la operación en las diferentes áreas de la empresa, de acuerdo a la categoría o puesto de <span class="bold">{{ $trabajador->fichaTecnica->categoria->nombre_categoria ?? 'CATEGORÍA A ASIGNAR' }}.</span>
        </p>
        <p class="clausula">
            Este trabajo será desempeñado con la intensidad, esmero y cuidado apropiado, así como con la eficiencia adecuada, manifestando EL TRABAJADOR tener todos los conocimientos, aptitudes y experiencias necesarias para el desempeño de su trabajo. 
            De igual manera está totalmente de acuerdo en acatar todas las órdenes, disposiciones y circulares que el patrón emita o su representante, acatar el Reglamento Interior de Trabajo del cual recibe copia en esta fecha, y acatar todas las disposiciones legales que sean aplicables.
        </p>

        <p class="clausula">
            <span class="clausula-numero">CLÁUSULA IV:</span> Para hacer más racional el empleo de la mano de obra disponible, EL TRABAJADOR conviene en que el patrón podrá asignarle labores distintas a su categoría y especialidad básica, procurando que éste retorne a ella a la brevedad posible.
        </p>

        <p class="clausula">
            <span class="clausula-numero">CLÁUSULA V:</span> EL TRABAJADOR se obliga a prestar sus servicios con la intensidad y esmero apropiados, en la forma y condiciones que la empresa le indique ejecutando todas las labores inherentes al puesto mencionado en la cláusula anterior y todas 
            aquellas que se relacionen directa o indirectamente con ese puesto.
        </p>
        <p class="clausula">
            Así mismo se obliga a partir de la firma del presente contrato a no enajenar, arrendar, prestar, grabar, negociar, revelar, publicar, enseñar, dar a conocer, transmitir o de alguna forma divulgar o proporcionarla por cualquier medio, aunque cuando se trate de incluir o entregar en 
            otros documentos como estudios, reportes, propuestas u ofertas, ni en todo ni en parte, por ningún motivo la información confidencial que se le haya proporcionado o de la cual tenga conocimiento, a sociedades de las cuales EL TRABAJADOR, sea accionista, asesor, causahabiente, apoderado, 
            consejero, comisario, tenedor de acciones, y en general, tenga alguna relación de índole cualquiera por sí o por terceras personas.
        </p>

        <p class="clausula">
            <span class="clausula-numero">CLÁUSULA VI:</span> La propiedad de los bienes originados de la relación laboral, EL TRABAJADOR reconoce que son propiedad exclusiva de la empresa, todos los documentos, programas, lista de clientes, artículos, estudios, información, folletos, publicaciones, 
            manuales, dibujos, trazos, software, hardware, fotografías, diseños o cualquier otro trabajo intelectual o información que se le proporcione con motivo de la relación de trabajo, así como los que EL TRABAJADOR prepare o formule en relación o conexión con sus servicios, por lo que se obliga 
            a conservarlo en buen estado y entregarlos a la empresa en el momento en que este lo requiera, o bien al terminarse el presente contrato.
        </p>

        <p class="clausula">
            <span class="clausula-numero">CLÁUSULA VII:</span> La propiedad de las herramientas de trabajo, EL TRABAJADOR reconoce que son propiedad de la empresa, en todo tiempo, los vehículos, instrumentos, herramientas, aparatos, maquinarias, artículos software, hardware, manuales de operación, y en 
            general, todos los instrumentos de trabajo, datos, diseños, e información verbal, que se le proporcionen con motivo de la relación laboral.
        </p>

        <p class="clausula">
            <span class="clausula-numero">CLÁUSULA VIII:</span> El uso del Software se compromete EL TRABAJADOR a utilizar los que exclusivamente pertenecen y son autorizados por la empresa, en el manejo de los equipos de cómputos que se utilicen en y para el desarrollo de su trabajo.
            Serán propiedad de la empresa la creación, modificación, de nuevos programas de cómputo o en su perfeccionamiento hechas por EL TRABAJADOR, con motivo del desempeño de su puesto de trabajo, ya que estos están cubiertos por el salario que este percibe.
        </p>

          <p class="clausula">
            <span class="clausula-numero">CLÁUSULA IX:</span> Confidencialidad.- Tanto dentro de la vigencia de este contrato como después de la terminación del mismo, EL TRABAJADOR se obliga a no divulgar ni a utilizar en su propio beneficio cualquier aspecto o información relacionada 
            con las actividades y operaciones de la empresa, o de las personas con los que esta tuviese relación de negocios que fueron de su conocimiento, no proporcionara a terceros, directa e indirectamente información verbal o por escrito, de los métodos, sistemas o actividades de cualquier 
            clase que se relacione con los servicios prestados, durante el desarrollo de sus actividades. Así mismo tampoco divulgará el contenido de los documentos, estudios, programas, propuesta, y en general cualquier documento que se hubiera proporcionado o facilitado durante su desempeño de sus servicios. 
            Igualmente queda obligado a no servirse para su provecho personal o de terceros de la patente, marcas y derechos, de autor de propiedad de la empresa, o de las personas con la que esta tuviese relación de negocios.
        </p>
        <p class="clausula">
            La empresa manifiesta que EL TRABAJADOR, tendrá estrictamente prohibido hacer uso del Software no autorizado o que no sea propiedad de la empresa, así como el uso de equipo y software, para fines estrictamente personales, el uso de juego de cómputo, la extracción o copia no autorizada de información 
            contenida en los sistemas de la empresa. La Introducción de los discos, USB u otro medio no autorizado por la empresa.
        </p>
        <p class="clausula">
            Toda la información de clientes a la que EL TRABAJADOR tenga acceso, se deberá de tratar bajo los más altos estándares de confidencialidad, razón por la cual EL TRABAJADOR reconoce y acepta tener expresamente prohibido divulgar, sustraer, copiar, vender o enajenar a cualquier tercera persona, sin el previo 
            consentimiento de la empresa, ya sea parcial o total cualquier tipo de información relativa a la finanzas de la empresa y/o clientes de la empresa, sus subsidiarias y afiliados, proveedores, afiliadas a la empresa, por lo cual se obliga a cumplir todas las disposiciones relativas a la confidencialidad.
        </p>
        <p class="clausula">
            EL TRABAJADOR queda además obligado a respetar el reglamento interno de la empresa, las normas, las políticas internas, procedimiento, equipo, herramientas, maquinaria, mobiliario y equipo de oficina. Así como las disposiciones que comunica la empresa con el objeto de mantener la seguridad, higiene, y medio ambiente laboral.
        </p>

        <p class="clausula">
            <span class="clausula-numero">CLÁUSULA X:</span> EL TRABAJADOR está de acuerdo en prestar sus servicios al patrón en el domicilio señalado en la cláusula primera del presente contrato, así como en cualquier otro que el patrón indique, pudiendo ser en cualquier otra Entidad Federativa de la República Mexicana.
        </p>

        <p class="clausula">
            <span class="clausula-numero">CLÁUSULA XI:</span> El presente Contrato Individual de Trabajo se celebra por tiempo determinado de {{ $duracion_texto }}, del período comprendido del <span class="bold">{{ $fecha_inicio }}</span> al <span class="bold">{{ $fecha_fin }}</span>, en términos de los artículos 35 y 40 de la Ley Federal del Trabajo.
        </p>

        <p class="clausula">
            <span class="clausula-numero">CLÁUSULA XII:</span> EL TRABAJADOR percibirá como salario diario la cantidad de <span class="bold">${{ number_format($trabajador->fichaTecnica->sueldo_diarios ?? 0, 2) }} ({{ $salario_texto ?? 'CANTIDAD A DETERMINAR' }}) PESOS MEXICANOS</span>; el cual se le pagará los días quince y último de cada mes incluyéndose 
            en dicho pago el séptimo día, días festivos y descansos obligatorios que por Ley existan, con fundamento en los artículos 69, 70, 71, 72, 74, 88, 108 y 109 de la Ley Federal del Trabajo, debiendo EL TRABAJADOR firmar los comprobantes respectivos.
        </p>

    </div>
</body>
</html>