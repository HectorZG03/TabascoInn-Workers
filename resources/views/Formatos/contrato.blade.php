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
            color: #000;
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
        .firmas {
            margin-top: 60px;
            width: 100%;
            display: table;
            table-layout: fixed;
        }
        .firma-seccion {
            width: 50%;
            text-align: center;
            vertical-align: top;
            display: table-cell;
            padding: 0 10px;
        }
        .linea-firma {
            border-top: 1px solid black;
            margin: 40px 20px 10px 20px;
        }
        .contenedor-firmas {
            margin-top: 80px;
            width: 100%;
        }
        .separador-firmas {
            margin-top: 40px;
        }
        .small-text {
            font-size: 10px;
            line-height: 1.3;
        }
        ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        li {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <img src="{{asset('img/images.png')}}" alt="Log">
    <h1>
        CONTRATO INDIVIDUAL DE TRABAJO<br>
        @if(isset($tipo_contrato) && $tipo_contrato === 'indeterminado')
            POR TIEMPO INDETERMINADO
        @else
            POR TIEMPO DETERMINADO
        @endif
    </h1>
    
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
            en {{ $trabajador->lugar_nacimiento ?? ($trabajador->ciudad_actual && $trabajador->estado_actual ? $trabajador->ciudad_actual . ', ' . $trabajador->estado_actual : 'Villahermosa, Centro, Tabasco') }}; y que su CURP es <span class="bold">{{ $trabajador->curp ?? 'NO ESPECIFICADO' }}</span> 
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
            <span class="clausula-numero">CLÁUSULA XI:</span> 
            @if(isset($tipo_contrato) && $tipo_contrato === 'indeterminado')
                El presente Contrato Individual de Trabajo se celebra por <span class="bold">tiempo indeterminado</span>, en términos de los artículos 35 y 40 de la Ley Federal del Trabajo.
            @else
                El presente Contrato Individual de Trabajo se celebra por <span class="bold">tiempo determinado de {{ $duracion_texto ?? 'duración a determinar' }}</span>, del período comprendido del <span class="bold">{{ $fecha_inicio ? $fecha_inicio->format('d \d\e F \d\e\l Y') : 'fecha a determinar' }}</span> al <span class="bold">{{ $fecha_fin ? $fecha_fin->format('d \d\e F \d\e\l Y') : 'fecha a determinar' }}</span>, en términos de los artículos 35 y 40 de la Ley Federal del Trabajo.
            @endif
        </p>
        <p class="clausula">
            <span class="clausula-numero">CLÁUSULA XII:</span> EL TRABAJADOR percibirá como salario diario la cantidad de <span class="bold">${{ number_format($trabajador->fichaTecnica->sueldo_diarios ?? 0, 2) }} ({{ $salario_texto ?? 'CANTIDAD A DETERMINAR' }}) PESOS MEXICANOS</span>; el cual se le pagará los días quince y último de cada mes incluyéndose 
            en dicho pago el séptimo día, días festivos y descansos obligatorios que por Ley existan, con fundamento en los artículos 69, 70, 71, 72, 74, 88, 108 y 109 de la Ley Federal del Trabajo, debiendo EL TRABAJADOR firmar los comprobantes respectivos.
        </p>

        {{-- ✅ CLÁUSULA XIII: DINÁMICA CON HORARIOS DE LA FICHA TÉCNICA --}}
        <p class="clausula">
            <span class="clausula-numero">CLÁUSULA XIII:</span> La duración de la jornada 
            @if($trabajador->fichaTecnica)
                @php
                    $fichaTecnica = $trabajador->fichaTecnica;
                    $horasSemanales = $fichaTecnica->horas_semanales_calculadas ?? $fichaTecnica->horas_semanales ?? 0;
                    $horasDiarias = $fichaTecnica->horas_trabajadas_calculadas ?? $fichaTecnica->horas_trabajo ?? 0;
                    $turno = $fichaTecnica->turno_calculado ?? $fichaTecnica->turno ?? 'mixto';
                    $horaEntrada = $fichaTecnica->hora_entrada ? \Carbon\Carbon::parse($fichaTecnica->hora_entrada)->format('H:i') : '08:00';
                    $horaSalida = $fichaTecnica->hora_salida ? \Carbon\Carbon::parse($fichaTecnica->hora_salida)->format('H:i') : '17:00';
                    $diasLaborables = $fichaTecnica->dias_laborables ?? ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
                    $diasDescanso = $fichaTecnica->dias_descanso ?? ['sabado', 'domingo'];
                    
                    // Convertir días a texto
                    $diasLaborablesTexto = collect($diasLaborables)->map(function($dia) {
                        return \App\Models\FichaTecnica::DIAS_SEMANA[$dia] ?? ucfirst($dia);
                    })->join(', ');
                    
                    $diasDescansoTexto = collect($diasDescanso)->map(function($dia) {
                        return \App\Models\FichaTecnica::DIAS_SEMANA[$dia] ?? ucfirst($dia);
                    })->join(', ');
                    
                    // Textos descriptivos
                    $tipoJornada = $turno === 'nocturno' ? 'nocturna' : ($turno === 'diurno' ? 'diurna' : 'mixta');
                    $descripcionTurno = match($turno) {
                        'nocturno' => 'por tratarse de jornada Nocturna',
                        'diurno' => 'por tratarse de jornada Diurna',
                        default => 'por tratarse de jornada Mixta'
                    };
                @endphp
                {{ $tipoJornada }} de trabajo será de <span class="bold">{{ $horasSemanales }} horas a la semana</span> {{ $descripcionTurno }} debiendo EL TRABAJADOR de entrar a sus labores a las <span class="bold">{{ $horaEntrada }} horas</span>, finalizando su jornada de trabajo a las <span class="bold">{{ $horaSalida }} horas</span>, es decir, EL TRABAJADOR laborará <span class="bold">{{ $horasDiarias }} horas diarias</span> los días <span class="bold">{{ $diasLaborablesTexto }}</span> de cada semana, disfrutando EL TRABAJADOR de media hora descanso comprendida de las {{ $turno === 'nocturno' ? '02:00 horas a las 02:30 horas' : '12:30 horas a las 13:00 horas' }}, recibiendo el pago de su respectivo séptimo día a que tiene derecho, el tiempo que EL TRABAJADOR use como descanso y para comida no se computara como tiempo efectivo de trabajo, dado que el mismo lo utilizará fuera de las instalaciones del centro de trabajo, el presente horario y jornada de trabajo se pacta con fundamento en los artículos 58 y 59 de la Ley Federal del Trabajo, siendo {{ count($diasDescanso) === 1 ? 'el día' : 'los días' }} de descanso semanal {{ count($diasDescanso) === 1 ? 'el' : 'los' }} <span class="bold">{{ $diasDescansoTexto }}</span> de cada semana.
            @else
                descontinua de trabajo será de 42 horas a la semana por tratarse de jornada Nocturna debiendo EL TRABAJADOR de entrar a sus labores a las 22:00 horas o diez de la noche, finalizando su jornada de trabajo a las 06:00 horas o a las 06:00 de la mañana, es decir, EL TRABAJADOR laborará 07 horas diarias de domingo a viernes de cada semana, disfrutando EL TRABAJADOR de media hora descanso comprendida de las 02:00 horas a las 02:30 horas, recibiendo el pago de su respectivo séptimo día a que tiene derecho, el tiempo que EL TRABAJADOR use como descanso y para comida no se computara como tiempo efectivo de trabajo, dado que el mismo lo utilizará fuera de las instalaciones del centro de trabajo, el presente horario y jornada de trabajo se pacta con fundamento en los artículos 58 y 59 de la Ley Federal del Trabajo, siendo el día de descanso semanal el día sábado de cada semana.
            @endif
        </p>

        <p class="clausula">
            <span class="clausula-numero">CLÁUSULA XIV:</span> Se prohíbe a EL TRABAJADOR laborar tiempo extraordinario por su cuenta y sólo podrá hacerlo cuando exista autorización previa y por escrito del patrón debiendo estar dicha autorización firmado por el patrón y por el jefe inmediato de EL TRABAJADOR y/o jefe de personal, independientemente de estarse a lo dispuesto sobre dicho particular por los artículos 65, 66, 67 y 68 de la Ley Federal del Trabajo. Dicha prohibición se hace extensiva a lo referente a séptimos días, descansos obligatorios y días festivos, debiendo de estarse a lo señalado por los artículos 74 y 75 del citado Ordenamiento Legal. EL TRABAJADOR tiene la obligación de reportarse por escrito diariamente al patrón todas las actividades que realice, cualquier violación al presente Contrato o a la Ley por parte de EL TRABAJADOR, será causa de rescisión de la relación de trabajo, imputable al mismo y sin responsabilidad para el patrón.
        </p>

        <p class="clausula">
            <span class="clausula-numero">CLÁUSULA XV:</span> EL TRABAJADOR tendrá derecho al disfrute y pago de vacaciones, prima vacacional y aguinaldo en los términos de los artículos 76, 77, 78, 79. 80 y 87 de la Ley Federal del Trabajo. EL TRABAJADOR será adiestrado y capacitado en los términos de Ley y con lineamientos ordenados por la Ley de la Materia, conforme al capítulo III Bis del Título Cuarto de la Ley precitada. Son días de descansos obligatorios los siguientes: 1o. de enero, el primer lunes de febrero en conmemoración del 5 de febrero, el tercer lunes de marzo en conmemoración del 21 de marzo, 1o. de mayo, 16 de septiembre, el tercer lunes de noviembre en conmemoración del 20 de noviembre, 1o. de diciembre de cada seis años, cuando corresponda a la transmisión del Poder Ejecutivo Federal y el 25 de diciembre de cada año, de acuerdo al artículo 74 del multicitado Ordenamiento Legal. EL TRABAJADOR percibirá en esos días el importe de sus salarios conforme a derecho.
        </p>

        <p class="clausula">
            <span class="clausula-numero">CLÁUSULA XVI:</span> En los términos del artículo 135 de la Ley Federal del Trabajo, queda prohibido al trabajador lo siguiente:
        </p>
        <ul class="small-text">
            <li><strong>a).-</strong> Faltar al trabajo sin permiso del patrón o sin causa justificada.</li>
            <li><strong>b).-</strong> Ejecutar cualquier acto que pueda poner en peligro su vida, la de sus compañeros de trabajo, la de terceras personas, así como la de los establecimientos o lugares en que se desempeñe el trabajo.</li>
            <li><strong>c).-</strong> Sustraer de la empresa o establecimiento útiles de trabajo o materia prima o elaborada.</li>
            <li><strong>d).-</strong> Presentarse al trabajo en estado de embriaguez.</li>
            <li><strong>e).-</strong> Presentarse al trabajo bajo la influencia de algún narcótico o droga enervante, salvo prescripción médica. Antes de la iniciación del servicio EL TRABAJADOR deberá de ponerlo en conocimiento del patrón y presentar la prescripción suscrita por el médico.</li>
            <li><strong>f).-</strong> Portar armas de cualquier clase durante las horas de trabajo, salvo que la naturaleza de éste lo exija.</li>
            <li><strong>g).-</strong> Suspender las labores sin permiso del patrón.</li>
            <li><strong>h).-</strong> Hacer colectas en el establecimiento o lugar de trabajo.</li>
            <li><strong>i).-</strong> Usar los útiles de trabajo y las herramientas respectivas para objeto distinto de aquel al que están destinados.</li>
            <li><strong>j).-</strong> Hacer cualquier clase de propaganda en las horas de trabajo, dentro del establecimiento.</li>
        </ul>

        <p class="clausula">
            <span class="clausula-numero">CLÁUSULA XVII:</span> Para cumplir con lo establecido por la NOM – 035 –STPS- 2018, el patrón debe asegurarse de prevenir los riesgos psicosociales que se puedan generar en sus organizaciones esto para cumplir; es decir aquellos factores que pueden provocar trastornos de ansiedad, no orgánicos del ciclo sueño-vigilia, de estrés grave y de adaptación, derivado de la naturaleza de las funciones del puesto de trabajo, el tipo de jornada de trabajo y la exposición a acontecimientos traumáticos severos o a actos de violencia laboral. De acuerdo a lo anterior algunos de los factores que pueden provocar riesgos psicosociales son los siguientes:
        </p>
        <ul class="small-text">
            <li>Cargas de trabajo cuando exceden la capacidad del TRABAJADOR.</li>
            <li>Jornadas de trabajo superiores a las previstas en la Ley Federal del Trabajo.</li>
            <li>Rotación de turnos que incluyan turno nocturno sin periodos de recuperación y descanso.</li>
            <li>Interferencia en la relación trabajo-familia.</li>
            <li>Liderazgo negativo.</li>
            <li>Relaciones negativas en el trabajo.</li>
        </ul>

        <p class="clausula">
            <span class="clausula-numero">CLÁUSULA XVIII:</span> Para evitar este tipo de riesgos es necesario que en el centro de trabajo se realicen las siguientes acciones:
        </p>
        <ul class="small-text">
            <li>Establecer y difundir en el centro de trabajo una política de prevención de riesgos psicosociales.</li>
            <li>Evaluar el entorno organizacional.</li>
            <li>Practicar exámenes médicos a los trabajadores expuestos a violencia laboral y/o a los factores de riesgo psicosocial, cuando existan signos o síntomas que denoten alguna alteración a su salud.</li>
            <li>Difundir y proporcionar información a los trabajadores.</li>
        </ul>

        <p class="clausula">
            <span class="clausula-numero">CLÁUSULA XIX:</span> Para el debido cumplimiento de la NOM – 035 –STPS- 2018, son obligaciones de EL TRABAJADOR las siguientes:
        </p>
        <ul class="small-text">
            <li>Observar las medidas de prevención y, en su caso, de control que dispone esta Norma, así como las que establezca el patrón para: controlar los factores de riesgo psicosocial, colaborar para contar con un entorno organizacional favorable y prevenir actos de violencia laboral.</li>
            <li>Abstenerse de realizar prácticas contrarias al entorno organizacional favorable y actos de violencia laboral.</li>
            <li>Participar en la identificación de los factores de riesgo psicosocial y, en su caso, en la evaluación del entorno organizacional.</li>
            <li>Informar sobre prácticas opuestas al entorno organizacional favorable y denunciar actos de violencia laboral, utilizando los mecanismos que establezca el patrón para tal efecto y/o a través de la comisión de seguridad e higiene.</li>
            <li>Informar por escrito al patrón directamente, a través de los servicios preventivos de seguridad y salud en el trabajo o de la comisión de seguridad e higiene; haber presenciado o sufrido un acontecimiento traumático severo.</li>
            <li>Participar en los eventos de información que proporcione el patrón.</li>
            <li>Someterse a los exámenes médicos y evaluaciones psicológicas que determinan la presente Norma y/o las normas oficiales mexicanas que al respecto emitan la Secretaría de Salud y/o la Secretaría del Trabajo y Previsión Social, y a falta de éstas, los que indique la institución de seguridad social o privada, o el médico o psicólogo o psiquiatra del centro de trabajo o de la empresa.</li>
        </ul>

        <p class="clausula">
            <span class="clausula-numero">CLÁUSULA XX:</span> EL TRABAJADOR o empleado queda obligado a cumplir las disposiciones de las normas de trabajo que le sean aplicables, a observar las medidas preventivas e higiénicas que acuerden las autoridades competentes y las que indiquen los patrones para seguridad de los trabajadores y al efecto a someterse a los reconocimientos médicos que periódicamente ordene el patrón, a ejecutar el trabajo bajo la dirección del patrón o de su representante cuya Autoridad estarán subordinados en todo lo concerniente al trabajo, a ejecutar el trabajo con la intensidad, cuidado y esmero apropiado y en la forma, tiempo y lugar convenidos, dar aviso de inmediato al patrón de las causas justificadas que le impiden concurrir a su trabajo; restituir al patrón los materiales no usados y conservar en buen estado los instrumentos y útiles que les hayan dado para el trabajo, a observar buenas costumbres durante el servicio, prestar auxilio en cualquier tiempo que se necesite, cuando por siniestro o riesgo inminente peligren las personas o los intereses del patrón y de sus compañeros de trabajo, a poner en conocimiento del patrón las enfermedades contagiosas que padezcan tan pronto tengan conocimiento de las mismas; a comunicar al patrón o su representante las deficiencias que adviertan a fin de evitar daños y perjuicios a los intereses y vidas de sus compañeros de trabajo o a los patrones, y a guardar escrupulosamente los secretos técnicos, comerciales y de fabricación de los cuales tengan conocimiento por razón del trabajo así como de los asuntos administrativos reservados cuya divulgación pueda causar perjuicio a la empresa, quedando prohibido al trabajador sustraer útiles de trabajo o materia prima o elaborada, presentarse al trabajo en estado de embriaguez o bajo la influencia de narcótico o drogas enervantes, portar armas durante las horas del trabajo, salvo que la naturaleza de ésta lo exija y usar los útiles y herramientas suministradas por el patrón u objetos distintos de aquel a que estén destinados y todos los demás actos que a los trabajadores veta el artículo 135 de la Ley de la Materia.
        </p>

        <p class="clausula">
            <span class="clausula-numero">CLÁUSULA XXI:</span> EL TRABAJADOR se reconoce y se obliga a respetar en todas y cada una de sus partes el reglamento interior de trabajo de la empresa que se encuentra depositado ante las Autoridades de Trabajo del Estado de Tabasco y aceptando que su inobservancia al mismo podría hacerlo acreedor a sanciones.
        </p>

        <p class="clausula">
            <span class="clausula-numero">CLÁUSULA XXII:</span> Patrón y trabajador están totalmente de acuerdo con el contenido del presente contrato y de que todo lo no previsto en el mismo, se esté a lo dispuesto en la Ley Federal del Trabajo.
        </p>

        {{-- ✅ CLÁUSULA XXIII: DINÁMICA CON ANTIGÜEDAD REAL --}}
        <p class="clausula">
            <span class="clausula-numero">CLÁUSULA XXIII:</span> Se reconoce al trabajador por parte del patrón una antigüedad a partir del día <span class="bold">{{ \Carbon\Carbon::parse($trabajador->fecha_ingreso)->format('d') }} de {{ \Carbon\Carbon::parse($trabajador->fecha_ingreso)->locale('es')->monthName }} del año {{ \Carbon\Carbon::parse($trabajador->fecha_ingreso)->format('Y') }}</span>.
        </p>

        {{-- ✅ CLÁUSULA XXIV: DINÁMICA CON BENEFICIARIO DE LA FICHA TÉCNICA --}}
        <p class="clausula">
            <span class="clausula-numero">CLÁUSULA XXIV:</span> En cumplimiento con la fracción X del artículo 25 de la Ley Federal del Trabajo en este acto EL TRABAJADOR señala como su beneficiario 
            @if($trabajador->fichaTecnica && $trabajador->fichaTecnica->beneficiario_nombre)
                a {{ $trabajador->fichaTecnica->beneficiario_parentesco === 'esposa' || $trabajador->fichaTecnica->beneficiario_parentesco === 'madre' || $trabajador->fichaTecnica->beneficiario_parentesco === 'hija' || $trabajador->fichaTecnica->beneficiario_parentesco === 'hermana' || $trabajador->fichaTecnica->beneficiario_parentesco === 'abuela' ? 'la C.' : 'el C.' }} <span class="bold uppercase">{{ $trabajador->fichaTecnica->beneficiario_nombre }}</span>, {{ $trabajador->fichaTecnica->beneficiario_parentesco ? 'la cual tiene como parentesco o filiación que es su ' . (\App\Models\FichaTecnica::PARENTESCOS_BENEFICIARIO[$trabajador->fichaTecnica->beneficiario_parentesco] ?? $trabajador->fichaTecnica->beneficiario_parentesco) : 'con parentesco por especificar' }},
            @else
                a la C. <span class="bold uppercase">BENEFICIARIO POR ESPECIFICAR</span>, la cual tiene como parentesco o filiación que es su <span class="bold">PARENTESCO POR ESPECIFICAR</span>,
            @endif
            lo anterior para facilitar la declaración de beneficiarios que señala el artículo 501 de la ley antes citada por si EL TRABAJADOR falleciera por alguna enfermedad natural, accidente de trabajo o fuera víctima de una desaparición forzada o secuestro.
        </p>

        <p class="clausula clausula-final">
            Leído que fue el presente Contrato Individual de Trabajo por el patrón y por EL TRABAJADOR, documento que va en siete fojas útiles escritas por una sola cara, quedando el original en poder del patrón y la copia en poder de EL TRABAJADOR, se firma por ambas partes y por los testigos que intervinieron en la celebración del mismo, en la Ciudad de Villahermosa, Tabasco el <span class="bold">{{ \Carbon\Carbon::parse($fecha_inicio)->format('d') }} de {{ \Carbon\Carbon::parse($fecha_inicio)->locale('es')->monthName }} del año {{ \Carbon\Carbon::parse($fecha_inicio)->format('Y') }}</span>.
        </p>

        <p class="center bold" style="margin-top: 50px;">A T E N T A M E N T E</p>
        <div class="contenedor-firmas">
            <!-- Primera fila: Patrón y Trabajador -->
            <div class="firmas">
                <div class="firma-seccion">
                    <div class="linea-firma"></div>
                    <p class="center"><strong>TABASCO INN, S.A. DE C.V.</strong><br>PATRÓN</p>
                </div>
                <div class="firma-seccion">
                    <div class="linea-firma"></div>
                    <p class="center"><strong>{{ strtoupper($trabajador->nombre_completo) }}</strong><br>TRABAJADOR</p>
                </div>
            </div>

            <!-- Segunda fila: Testigos -->
            <div class="firmas separador-firmas">
                <div class="firma-seccion">
                    <div class="linea-firma"></div>
                    <p class="center"><strong>TESTIGO</strong></p>
                </div>
                <div class="firma-seccion">
                    <div class="linea-firma"></div>
                    <p class="center"><strong>TESTIGO</strong></p>
                </div>
            </div>
        </div>
</body>
</html>