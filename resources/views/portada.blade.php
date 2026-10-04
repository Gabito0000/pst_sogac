<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portal del Departamento de Control de Estudios de la Universidad Politécnica Territorial del Estado Portuguesa Juan de Jesús Montilla (UPTP).">
    <title>Control de Estudios | UPTP Juan de Jesús Montilla</title>
    <link rel="icon" type="image/png" href="{{ asset('portada/logo.png') }}" sizes="32x32">
    <link rel="shortcut icon" href="{{ asset('portada/logo.png') }}" type="image/x-icon">
    <link rel="apple-touch-icon" href="{{ asset('portada/logo.png') }}" sizes="180x180">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('portada/style.css') }}">
</head>
<body>

    <header class="bg-white py-2 border-bottom shadow-sm">
        <div class="container d-flex align-items-center justify-content-between">
            <a class="navbar-brand d-flex align-items-center" href="#inicio">
                <img src="{{ asset('portada/logo.png') }}" alt="Logo UPTP" class="me-3" style="height: 65px;">
                <div>
                    <span class="d-block fw-bold fs-4 text-dark lh-1">UPTP</span>
                    <small class="text-muted fw-semibold">Juan de Jesús Montilla</small>
                </div>
            </a>
            <div class="d-none d-md-block">
                <span class="badge bg-uptp-red p-2">Control de Estudios</span>
            </div>
        </div>
    </header>

    <nav class="navbar navbar-expand-lg navbar-dark bg-uptp-red sticky-top shadow">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuNav" aria-controls="menuNav" aria-expanded="false" aria-label="Abrir menú">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="menuNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1 w-100 justify-content-between">
                    <div class="d-md-flex">
                        <li class="nav-item"><a class="nav-link" href="#inicio"><i class="bi bi-house-door"></i> Inicio</a></li>
                        <li class="nav-item"><a class="nav-link" href="#nosotros"><i class="bi bi-info-circle"></i> Nosotros</a></li>
                        <li class="nav-item"><a class="nav-link" href="#servicios"><i class="bi bi-gear"></i> Servicios</a></li>
                        <li class="nav-item"><a class="nav-link" href="#carreras"><i class="bi bi-book"></i> Carreras</a></li>
                        <li class="nav-item"><a class="nav-link" href="#tramites"><i class="bi bi-file-earmark-text"></i> Trámites</a></li>
                        <li class="nav-item"><a class="nav-link" href="#contacto"><i class="bi bi-telephone"></i> Contacto</a></li>
                    </div>
{{-- El boton "Portal" externo se conserva tal cual. Este sistema entra por su
                         propio boton, que ademas cambia segun haya sesion o no. --}}
                    <div class="d-flex gap-2">
                        @auth
                            <li class="nav-item">
                                <a class="btn btn-blanco btn-sm px-3" href="{{ route('dashboard') }}"><i class="bi bi-grid-1x2"></i> Mi panel</a>
                            </li>
                        @else
                            <li class="nav-item">
                                <a class="btn btn-blanco btn-sm px-3" href="{{ route('login') }}"><i class="bi bi-person-badge"></i> Entrar al sistema</a>
                            </li>
                        @endauth
                        <li class="nav-item">
                            <a class="btn btn-uptp-red btn-sm px-3" href="https://uptp.sytes.net/daece/"><i class="bi bi-box-arrow-in-right"></i> Portal</a>
                        </li>
                        <li class="nav-item">
                            <button id="btnTema" class="btn btn-outline-light btn-sm" type="button" aria-label="Cambiar tema">
                                <i id="iconoTema" class="bi bi-moon-stars-fill"></i>
                                <span id="textoTema" class="d-none d-lg-inline">Oscuro</span>
                            </button>
                        </li>
                    </div>
                </ul>
            </div>
        </div>
    </nav>

    <main>

        <section id="inicio">
            <div id="carruselUPTP" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">

                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#carruselUPTP" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Diapositiva 1"></button>
                    <button type="button" data-bs-target="#carruselUPTP" data-bs-slide-to="1" aria-label="Diapositiva 2"></button>
                    <button type="button" data-bs-target="#carruselUPTP" data-bs-slide-to="2" aria-label="Diapositiva 3"></button>
                </div>

                <div class="carousel-inner">

                    <div class="carousel-item active">
                        <div class="carrusel-slide slide-1">
                            <div class="carrusel-overlay"></div>
                            
                        </div>
                    </div>

                    <div class="carousel-item">
                        <div class="carrusel-slide slide-2">
                            <div class="carrusel-overlay"></div>
                            
                        </div>
                    </div>

                    <div class="carousel-item">
                        <div class="carrusel-slide slide-3">
                            <div class="carrusel-overlay"></div>
                            
                        </div>
                    </div>
                </div>

                <button class="carousel-control-prev" type="button" data-bs-target="#carruselUPTP" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Anterior</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carruselUPTP" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Siguiente</span>
                </button>
            </div>
        </section>

        <section id="nosotros" class="py-5 bg-body-tertiary">
            <div class="container">
                <h2 class="text-center mb-4 titulo-seccion">Acerca de Nosotros</h2>
                <p class="lead text-center mx-auto" style="max-width: 850px;">
                    La <strong>Universidad Politécnica Territorial del Estado Portuguesa</strong> se dedica a brindar una educación
                    de calidad en diversas disciplinas técnicas y científicas. El Departamento de Control de Estudios
                    asegura la gestión eficiente de todos los procesos académicos de la comunidad universitaria.
                </p>
            </div>
        </section>

        <section class="py-5">
            <div class="container">
                <section id="nosotros-historia" class="py-5">
            <div class="container">
                <h2 class="text-center mb-4 titulo-seccion">Reseña Histórica</h2>

                <div class="bloque-historia mx-auto p-4 bg-body-tertiary rounded-3 shadow-sm" style="max-width: 900px;">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-bank fs-4 text-uptp-red"></i>
                        <h3 class="h5 mb-0 fw-bold">Nuestra Trayectoria Institucional</h3>
                    </div>
                    
                    <p class="text-secondary">
                        La <strong>UPTP</strong>, antes <em>Instituto Universitario de Tecnología del Estado Portuguesa (IUTEP)</em>,
                        fue fundada en <strong>1975</strong> con el objetivo de ofrecer educación superior tecnológica en la región
                        centroccidental de Venezuela. Desde sus inicios ha estado comprometida con la formación de profesionales
                        integrales, capaces de responder a las necesidades socioeconómicas del estado Portuguesa y del país.
                    </p>

                    <p class="text-secondary mb-3">
                        A lo largo de más de 45 años, la UPTP ha ampliado su oferta académica, modernizado sus instalaciones
                        y fortalecido sus programas de extensión e investigación, consolidándose como referente de la educación
                        politécnica venezolana.
                    </p>

                    <blockquote class="blockquote border-start border-uptp-red border-4 ps-3 fst-italic text-muted small mb-0">
                        "Forjando el futuro de la ingeniería y las ciencias aplicadas en Venezuela."
                    </blockquote>
                </div>
            </div>
        </section>

        <section id="mision-vision" class="py-5 bg-body-tertiary">
            <div class="container">
                <div class="row g-4 justify-content-center">
                    <div class="col-md-6">
                        <div class="card card-filosofia h-100 p-4 border-0 shadow-sm">
                            <div class="d-flex align-items-start gap-3">
                                <div class="icon-filosofia d-flex align-items-center justify-content-center rounded-3 p-3 bg-danger-subtle text-uptp-red">
                                    <i class="bi bi-rocket-takeoff-fill fs-3"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="text-uppercase text-uptp-red small fw-bold tracking-wider mb-1 d-block">Dirección Estratégica</span>
                                    <h2 class="h4 fw-bold mb-3 text-body">Misión</h2>
                                    <p class="text-secondary small lh-base mb-0">
                                        Garantizar la administración, resguardo y certificación oportuna del historial académico de la comunidad estudiantil de la <strong>UPTP</strong>, mediante un sistema de gestión eficiente, transparente y tecnológico, que optimice los procesos de matriculación, prosecución y egreso, respaldando con rigor legal y humano la formación de los profesionales de la patria.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card card-filosofia h-100 p-4 border-0 shadow-sm">
                            <div class="d-flex align-items-start gap-3">
                                <div class="icon-filosofia d-flex align-items-center justify-content-center rounded-3 p-3 bg-danger-subtle text-uptp-red">
                                    <i class="bi bi-eye-fill fs-3"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="text-uppercase text-uptp-red small fw-bold tracking-wider mb-1 d-block">Proyección de Futuro</span>
                                    <h2 class="h4 fw-bold mb-3 text-body">Visión</h2>
                                    <p class="text-secondary small lh-base mb-0">
                                        Consolidar al departamento de Control de Estudios como un referente nacional de vanguardia tecnológica y excelencia administrativa dentro del sistema universitario politécnico, caracterizado por la automatización inteligente de sus servicios, la transparencia de sus auditorías y una atención con altos estándares de calidad humana para nuestros estudiantes y egresados.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section id="servicios" class="py-5">
            <div class="container">
                <div class="text-center mb-5">
                    <span class="badge bg-danger-subtle text-uptp-red px-3 py-2 rounded-pill fw-semibold mb-2 text-uppercase tracking-wider">Competencias Institucionales</span>
                    <h2 class="fw-bold titulo-seccion mb-2">Servicios Departamentales</h2>
                    <p class="text-muted mx-auto" style="max-width: 750px;">
                        Conoce las funciones esenciales y los procesos académicos respaldados por el departamento de Control de Estudios para garantizar tu trayectoria universitaria.
                    </p>
                </div>

                <div class="row g-4">
                    <div class="col-lg-4 col-md-6">
                        <div class="card card-servicio-premium h-100 p-4 border-0 shadow-sm">
                            <div class="icon-contenedor mb-3 d-flex align-items-center justify-content-center rounded-3">
                                <i class="bi bi-shield-check fs-3 text-uptp-red"></i>
                            </div>
                            <h3 class="h5 fw-bold mb-2">Procesos de Matriculación</h3>
                            <p class="text-secondary small mb-0">
                                Planificación y ejecución del registro formal de estudiantes de nuevo ingreso, asignación estructurada de secciones académicas y control de cupos por cada PNF según los lineamientos del ministerio.
                            </p>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="card card-servicio-premium h-100 p-4 border-0 shadow-sm">
                            <div class="icon-contenedor mb-3 d-flex align-items-center justify-content-center rounded-3">
                                <i class="bi bi-archive-fill fs-3 text-uptp-red"></i>
                            </div>
                            <h3 class="h5 fw-bold mb-2">Historial y Calificaciones</h3>
                            <p class="text-secondary small mb-0">
                                Custodia, procesamiento y actualización permanente de las calificaciones cargadas por el cuerpo docente, velando por la integridad del récord histórico y las unidades de crédito aprobadas.
                            </p>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="card card-servicio-premium h-100 p-4 border-0 shadow-sm">
                            <div class="icon-contenedor mb-3 d-flex align-items-center justify-content-center rounded-3">
                                <i class="bi bi-file-earmark-lock-fill fs-3 text-uptp-red"></i>
                            </div>
                            <h3 class="h5 fw-bold mb-2">Documentación y Certificados</h3>
                            <p class="text-secondary small mb-0">
                                Emisión y validación de constancias de estudio, récords de notas históricos y documentos acreditables indispensables para la prosecución del estudiante o para trámites legales externos.
                            </p>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="card card-servicio-premium h-100 p-4 border-0 shadow-sm">
                            <div class="icon-contenedor mb-3 d-flex align-items-center justify-content-center rounded-3">
                                <i class="bi bi-person-badge-fill fs-3 text-uptp-red"></i>
                            </div>
                            <h3 class="h5 fw-bold mb-2">Acreditación Estudiantil</h3>
                            <p class="text-secondary small mb-0">
                                Gestión de la data maestra para los procesos de carnetización institucional, garantizando que cada miembro activo cuente con los registros requeridos para su identificación en la planta física.
                            </p>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="card card-servicio-premium h-100 p-4 border-0 shadow-sm">
                            <div class="icon-contenedor mb-3 d-flex align-items-center justify-content-center rounded-3">
                                <i class="bi bi-calendar3-event fs-3 text-uptp-red"></i>
                            </div>
                            <h3 class="h5 fw-bold mb-2">Cronogramas y Horarios</h3>
                            <p class="text-secondary small mb-0">
                                Sincronización de la oferta académica junto a las coordinaciones de PNF para la publicación oficial de la distribución de aulas, laboratorios, horarios matutinos y nocturnos.
                            </p>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="card card-servicio-premium h-100 p-4 border-0 shadow-sm">
                            <div class="icon-contenedor mb-3 d-flex align-items-center justify-content-center rounded-3">
                                <i class="bi bi-award-fill fs-3 text-uptp-red"></i>
                            </div>
                            <h3 class="h5 fw-bold mb-2">Verificación de Grados</h3>
                            <p class="text-secondary small mb-0">
                                Auditoría técnica y legal de los expedientes de los estudiantes aspirantes a grado, validando solvencias académicas para la otorgación legítima de títulos de TSU, Ingeniería o Licenciatura.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section id="carreras" class="py-5 bg-body-tertiary">
            <div class="container">
                <h2 class="text-center mb-2 titulo-seccion"><i class="bi bi-mortarboard-fill text-uptp-red"></i> Programas Nacionales de Formación (PNF)</h2>
                <p class="text-center text-muted mb-5 mx-auto" style="max-width: 800px;">
                    Explora la malla curricular de nuestras especialidades. Pasa el cursor por encima de cualquier tarjeta (o tócala si estás en un celular) para revelar su pensum por trayectos y recomendaciones de estudio.
                </p>

                <div class="row g-4">
                    <div class="col-xl-4 col-md-6">
                        <div class="card card-interactiva-carrera shadow-sm">
                            <div class="card-frontal p-4 d-flex flex-column justify-content-between text-center">
                                <div class="my-auto">
                                    <div class="bg-danger-subtle d-inline-block p-3 rounded-circle text-uptp-red mb-3">
                                        <i class="bi bi-code-slash fs-3"></i>
                                    </div>
                                    <h3 class="h5 fw-bold mb-1">Informática</h3>
                                    <span class="badge bg-secondary mb-3">TSU / Ingeniería</span>
                                    <p class="text-secondary small mb-0">Desarrollo de software multiplataforma, administración de redes, ciberseguridad, bases de datos y soberanía tecnológica con software libre.</p>
                                </div>
                                <div class="text-uptp-red x-small fw-bold animate-pulse"><i class="bi bi-arrow-repeat"></i> Ver Pensum y Recomendación</div>
                            </div>
                            <div class="card-trasera p-4 bg-uptp-dark text-white d-flex flex-column justify-content-between">
                                <div>
                                    <h4 class="h6 text-uptp-red fw-bold mb-2"><i class="bi bi-journal-code"></i> Malla Curricular</h4>
                                    <ul class="list-unstyled x-small text-light-muted mb-0 lh-sm">
                                        <li class="mb-1"><strong>Trayecto I:</strong> Algorítmica, Matemática I, Arquitectura de Hardware, Proyecto I.</li>
                                        <li class="mb-1"><strong>Trayecto II:</strong> Programación Web, Redes de Datos, Bases de Datos, Proyecto II.</li>
                                        <li class="mb-1"><strong>Trayecto III:</strong> Ingeniería de Software, Programación Avanzada, Redes Avanzadas, Proyecto III.</li>
                                        <li><strong>Trayecto IV:</strong> Auditoría Informática, Inteligencia Artificial, Seguridad, Proyecto IV.</li>
                                    </ul>
                                </div>
                                <div class="border-top border-secondary pt-2">
                                    <h5 class="x-small text-uptp-red fw-bold mb-1"><i class="bi bi-rocket-takeoff"></i> Recomendación:</h5>
                                    <p class="xx-small text-secondary mb-0">El mundo se mueve con código. Practica lógica de programación y lenguajes como Python o JavaScript desde el primer día de clases; el autoaprendizaje te hará un profesional imparable.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6">
                        <div class="card card-interactiva-carrera shadow-sm">
                            <div class="card-frontal p-4 d-flex flex-column justify-content-between text-center">
                                <div class="my-auto">
                                    <div class="bg-danger-subtle d-inline-block p-3 rounded-circle text-uptp-red mb-3">
                                        <i class="bi bi-calculator-fill fs-3"></i>
                                    </div>
                                    <h3 class="h5 fw-bold mb-1">Administración</h3>
                                    <span class="badge bg-secondary mb-3">TSU / Licenciatura</span>
                                    <p class="text-secondary small mb-0">Planificación estratégica, contabilidad financiera, presupuestos públicos y privados, talento humano y modelos de economía comunitaria.</p>
                                </div>
                                <div class="text-uptp-red x-small fw-bold animate-pulse"><i class="bi bi-arrow-repeat"></i> Ver Pensum y Recomendación</div>
                            </div>
                            <div class="card-trasera p-4 bg-uptp-dark text-white d-flex flex-column justify-content-between">
                                <div>
                                    <h4 class="h6 text-uptp-red fw-bold mb-2"><i class="bi bi-journal-check"></i> Malla Curricular</h4>
                                    <ul class="list-unstyled x-small text-light-muted mb-0 lh-sm">
                                        <li class="mb-1"><strong>Trayecto I:</strong> Teoría de Administración, Contabilidad I, Matemática Financiera, Proyecto I.</li>
                                        <li class="mb-1"><strong>Trayecto II:</strong> Talento Humano, Costos, Presupuesto Público y Privado, Proyecto II.</li>
                                        <li class="mb-1"><strong>Trayecto III:</strong> Administración Pública, Análisis Financiero, Marco Legal, Proyecto III.</li>
                                        <li><strong>Trayecto IV:</strong> Planificación Estratégica, Auditoría Admin, Gestión de Calidad, Proyecto IV.</li>
                                    </ul>
                                </div>
                                <div class="border-top border-secondary pt-2">
                                    <h5 class="x-small text-uptp-red fw-bold mb-1"><i class="bi bi-lightning-fill"></i> Recomendación:</h5>
                                    <p class="xx-small text-secondary mb-0">Lidera equipos eficientes. Desarrolla habilidades de oratoria, negociación y domina herramientas digitales como Excel avanzado; la visión gerencial te abrirá todas las puertas.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6">
                        <div class="card card-interactiva-carrera shadow-sm">
                            <div class="card-frontal p-4 d-flex flex-column justify-content-between text-center">
                                <div class="my-auto">
                                    <div class="bg-danger-subtle d-inline-block p-3 rounded-circle text-uptp-red mb-3">
                                        <i class="bi bi-tree-fill fs-3"></i>
                                    </div>
                                    <h3 class="h5 fw-bold mb-1">Agroalimentación</h3>
                                    <span class="badge bg-secondary mb-3">TSU / Ingeniería</span>
                                    <p class="text-secondary small mb-0">Sistemas de producción agrícola sustentable, manejo integrado de suelos y cultivos, soberanía alimentaria y tecnologías de procesamiento vegetal.</p>
                                </div>
                                <div class="text-uptp-red x-small fw-bold animate-pulse"><i class="bi bi-arrow-repeat"></i> Ver Pensum y Recomendación</div>
                            </div>
                            <div class="card-trasera p-4 bg-uptp-dark text-white d-flex flex-column justify-content-between">
                                <div>
                                    <h4 class="h6 text-uptp-red fw-bold mb-2"><i class="bi bi-flower1"></i> Malla Curricular</h4>
                                    <ul class="list-unstyled x-small text-light-muted mb-0 lh-sm">
                                        <li class="mb-1"><strong>Trayecto I:</strong> Morfología Vegetal, Química Agrícola, Agroecología, Proyecto I.</li>
                                        <li class="mb-1"><strong>Trayecto II:</strong> Fitotecnia, Edafología (Suelos), Sanidad Vegetal, Proyecto II.</li>
                                        <li class="mb-1"><strong>Trayecto III:</strong> Riegos y Drenajes, Mecanización Agrícola, Bioestadística, Proyecto III.</li>
                                        <li><strong>Trayecto IV:</strong> Planificación Agropecuaria, Procesamiento de Alimentos, Proyecto IV.</li>
                                    </ul>
                                </div>
                                <div class="border-top border-secondary pt-2">
                                    <h5 class="x-small text-uptp-red fw-bold mb-1"><i class="bi bi-heart-fill"></i> Recomendación:</h5>
                                    <p class="xx-small text-secondary mb-0">Portuguesa es el granero de Venezuela. Conéctate con el campo desde el inicio, comprende la química orgánica y asiste a las prácticas de campo con mentalidad de innovación biotecnológica.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6">
                        <div class="card card-interactiva-carrera shadow-sm">
                            <div class="card-frontal p-4 d-flex flex-column justify-content-between text-center">
                                <div class="my-auto">
                                    <div class="bg-danger-subtle d-inline-block p-3 rounded-circle text-uptp-red mb-3">
                                        <i class="bi bi-tools fs-3"></i>
                                    </div>
                                    <h3 class="h5 fw-bold mb-1">Mantenimiento</h3>
                                    <span class="badge bg-secondary mb-3">TSU / Ingeniería</span>
                                    <p class="text-secondary small mb-0">Gestión de mantenimiento industrial, confiabilidad de sistemas mecánicos y eléctricos, seguridad industrial y optimización de plantas productivas.</p>
                                </div>
                                <div class="text-uptp-red x-small fw-bold animate-pulse"><i class="bi bi-arrow-repeat"></i> Ver Pensum y Recomendación</div>
                            </div>
                            <div class="card-trasera p-4 bg-uptp-dark text-white d-flex flex-column justify-content-between">
                                <div>
                                    <h4 class="h6 text-uptp-red fw-bold mb-2"><i class="bi bi-shield-fill-check"></i> Malla Curricular</h4>
                                    <ul class="list-unstyled x-small text-light-muted mb-0 lh-sm">
                                        <li class="mb-1"><strong>Trayecto I:</strong> Dibujo Técnico, Metrología, Higiene y Seguridad, Proyecto I.</li>
                                        <li class="mb-1"><strong>Trayecto II:</strong> Mantenimiento Preventivo, Resistencia de Materiales, Electricidad Industrial, Proyecto II.</li>
                                        <li class="mb-1"><strong>Trayecto III:</strong> Mantenimiento Predictivo, Gestión de Activos, Termodinámica, Proyecto III.</li>
                                        <li><strong>Trayecto IV:</strong> Confiabilidad Operacional, Automatización, Costos de Mantenimiento, Proyecto IV.</li>
                                    </ul>
                                </div>
                                <div class="border-top border-secondary pt-2">
                                    <h5 class="x-small text-uptp-red fw-bold mb-1"><i class="bi bi-wrench"></i> Recomendación:</h5>
                                    <p class="xx-small text-secondary mb-0">Evitar fallas es un arte rentable. Estudia a fondo los planos técnicos y la automatización por autómatas programables (PLC); la prevención ahorra millones a las industrias locales.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6">
                        <div class="card card-interactiva-carrera shadow-sm">
                            <div class="card-frontal p-4 d-flex flex-column justify-content-between text-center">
                                <div class="my-auto">
                                    <div class="bg-danger-subtle d-inline-block p-3 rounded-circle text-uptp-red mb-3">
                                        <i class="bi bi-gear-wide-connected fs-3"></i>
                                    </div>
                                    <h3 class="h5 fw-bold mb-1">Mecánica</h3>
                                    <span class="badge bg-secondary mb-3">TSU / Ingeniería</span>
                                    <p class="text-secondary small mb-0">Diseño de elementos de máquinas, procesos de manufactura, sistemas térmicos, hidráulicos y transformación mecánica de materiales.</p>
                                </div>
                                <div class="text-uptp-red x-small fw-bold animate-pulse"><i class="bi bi-arrow-repeat"></i> Ver Pensum y Recomendación</div>
                            </div>
                            <div class="card-trasera p-4 bg-uptp-dark text-white d-flex flex-column justify-content-between">
                                <div>
                                    <h4 class="h6 text-uptp-red fw-bold mb-2"><i class="bi bi-nut-fill"></i> Malla Curricular</h4>
                                    <ul class="list-unstyled x-small text-light-muted mb-0 lh-sm">
                                        <li class="mb-1"><strong>Trayecto I:</strong> Álgebra y Geometría, Física Mecánica, Mecánica Rígida, Proyecto I.</li>
                                        <li class="mb-1"><strong>Trayecto II:</strong> Mecánica de Fluidos, Procesos de Fabricación, Elementos de Máquinas, Proyecto II.</li>
                                        <li class="mb-1"><strong>Trayecto III:</strong> Transferencia de Calor, Mecanismos, Turbomaquinaria, Proyecto III.</li>
                                        <li><strong>Trayecto IV:</strong> Diseño Mecánico Asistido (CAD/CAM), Vibraciones Mecánicas, Planta Mecánica, Proyecto IV.</li>
                                    </ul>
                                </div>
                                <div class="border-top border-secondary pt-2">
                                    <h5 class="x-small text-uptp-red fw-bold mb-1"><i class="bi bi-cpu"></i> Recomendación:</h5>
                                    <p class="xx-small text-secondary mb-0">El movimiento y la fuerza estructural te esperan. Domina programas informáticos de diseño en 3D como AutoCAD o SolidWorks en tus ratos libres; marcará una diferencia brutal en tus proyectos.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6">
                        <div class="card card-interactiva-carrera shadow-sm">
                            <div class="card-frontal p-4 d-flex flex-column justify-content-between text-center">
                                <div class="my-auto">
                                    <div class="bg-danger-subtle d-inline-block p-3 rounded-circle text-uptp-red mb-3">
                                        <i class="bi bi-lightning-charge-fill fs-3"></i>
                                    </div>
                                    <h3 class="h5 fw-bold mb-1">Electricidad</h3>
                                    <span class="badge bg-secondary mb-3">TSU / Ingeniería</span>
                                    <p class="text-secondary small mb-0">Análisis de circuitos eléctricos, sistemas de potencia, subestaciones, instalaciones eléctricas residenciales e industriales y energías renovables.</p>
                                </div>
                                <div class="text-uptp-red x-small fw-bold animate-pulse"><i class="bi bi-arrow-repeat"></i> Ver Pensum y Recomendación</div>
                            </div>
                            <div class="card-trasera p-4 bg-uptp-dark text-white d-flex flex-column justify-content-between">
                                <div>
                                    <h4 class="h6 text-uptp-red fw-bold mb-2"><i class="bi bi-activity"></i> Malla Curricular</h4>
                                    <ul class="list-unstyled x-small text-light-muted mb-0 lh-sm">
                                        <li class="mb-1"><strong>Trayecto I:</strong> Circuitos Eléctricos I, Matemáticas Aplicadas, Mediciones Eléctricas, Proyecto I.</li>
                                        <li class="mb-1"><strong>Trayecto II:</strong> Circuitos II, Máquinas Eléctricas I, Electrónica Básica, Proyecto II.</li>
                                        <li class="mb-1"><strong>Trayecto III:</strong> Sistemas de Potencia, Protecciones Eléctricas, Control Industrial, Proyecto III.</li>
                                        <li><strong>Trayecto IV:</strong> Subestaciones, Líneas de Transmisión, Energías Alternativas, Proyecto IV.</li>
                                    </ul>
                                </div>
                                <div class="border-top border-secondary pt-2">
                                    <h5 class="x-small text-uptp-red fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill"></i> Recomendación:</h5>
                                    <p class="xx-small text-secondary mb-0">La energía mueve la civilización. Ponle máxima atención a las leyes de campos electromagnéticos y matemáticas aplicadas. Respeta las normas de seguridad siempre; el rigor técnico es tu vida.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6">
                        <div class="card card-interactiva-carrera shadow-sm">
                            <div class="card-frontal p-4 d-flex flex-column justify-content-between text-center">
                                <div class="my-auto">
                                    <div class="bg-danger-subtle d-inline-block p-3 rounded-circle text-uptp-red mb-3">
                                        <i class="bi bi-truck fs-3"></i>
                                    </div>
                                    <h3 class="h5 fw-bold mb-1">Distribución y Logística</h3>
                                    <span class="badge bg-secondary mb-3">TSU / Licenciatura</span>
                                    <p class="text-secondary small mb-0">Administración de cadenas de suministro, gestión de inventarios y almacenes, canales de distribución físicos y operaciones globales de transporte.</p>
                                </div>
                                <div class="text-uptp-red x-small fw-bold animate-pulse"><i class="bi bi-arrow-repeat"></i> Ver Pensum y Recomendación</div>
                            </div>
                            <div class="card-trasera p-4 bg-uptp-dark text-white d-flex flex-column justify-content-between">
                                <div>
                                    <h4 class="h6 text-uptp-red fw-bold mb-2"><i class="bi bi-diagram-3-fill"></i> Malla Curricular</h4>
                                    <ul class="list-unstyled x-small text-light-muted mb-0 lh-sm">
                                        <li class="mb-1"><strong>Trayecto I:</strong> Fundamentos de Logística, Geografía Económica, Inventarios I, Proyecto I.</li>
                                        <li class="mb-1"><strong>Trayecto II:</strong> Almacenamiento, Modos de Transporte, Costos Logísticos, Proyecto II.</li>
                                        <li class="mb-1"><strong>Trayecto III:</strong> Cadena de Suministro (SCM), Modelos Estadísticos, Compras, Proyecto III.</li>
                                        <li><strong>Trayecto IV:</strong> Logística Internacional, Comercio Exterior, Simulación de Operaciones, Proyecto IV.</li>
                                    </ul>
                                </div>
                                <div class="border-top border-secondary pt-2">
                                    <h5 class="x-small text-uptp-red fw-bold mb-1"><i class="bi bi-geo-alt-fill"></i> Recomendación:</h5>
                                    <p class="xx-small text-secondary mb-0">El producto correcto en el lugar indicado. Estudia sobre flujos y modelos matemáticos de inventario; la eficiencia en rutas y despachos es clave en un estado productor agrario.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6 mx-auto">
                        <div class="card card-interactiva-carrera shadow-sm">
                            <div class="card-frontal p-4 d-flex flex-column justify-content-between text-center">
                                <div class="my-auto">
                                    <div class="bg-danger-subtle d-inline-block p-3 rounded-circle text-uptp-red mb-3">
                                        <i class="bi bi-heart-pulse fs-3"></i>
                                    </div>
                                    <h3 class="h5 fw-bold mb-1">Medicina Veterinaria</h3>
                                    <span class="badge bg-secondary mb-3">Ingeniería / Doctorado</span>
                                    <p class="text-secondary small mb-0">Salud animal, medicina preventiva, diagnóstico clínico, cirugía veterinaria, zootecnia y mejoramiento genético de especies productivas.</p>
                                </div>
                                <div class="text-uptp-red x-small fw-bold animate-pulse"><i class="bi bi-arrow-repeat"></i> Ver Pensum y Recomendación</div>
                            </div>
                            <div class="card-trasera p-4 bg-uptp-dark text-white d-flex flex-column justify-content-between">
                                <div>
                                    <h4 class="h6 text-uptp-red fw-bold mb-2"><i class="bi bi-capsule"></i> Malla Curricular</h4>
                                    <ul class="list-unstyled x-small text-light-muted mb-0 lh-sm">
                                        <li class="mb-1"><strong>Trayecto I:</strong> Anatomía Animal, Histología y Embriología, Bioquímica, Proyecto I.</li>
                                        <li class="mb-1"><strong>Trayecto II:</strong> Fisiología Animal, Microbiología, Nutrición Animal, Proyecto II.</li>
                                        <li class="mb-1"><strong>Trayecto III:</strong> Patología General, Farmacología, Semiología y Diagnóstico, Proyecto III.</li>
                                        <li><strong>Trayecto IV:</strong> Cirugía, Enfermedades Infecciosas, Zootecnia Aplicada, Proyecto IV.</li>
                                    </ul>
                                </div>
                                <div class="border-top border-secondary pt-2">
                                    <h5 class="x-small text-uptp-red fw-bold mb-1"><i class="bi bi-shield-plus"></i> Recomendación:</h5>
                                    <p class="xx-small text-secondary mb-0">La voz de los que no tienen voz. Dedícale horas rigurosas a la anatomía descriptiva y los mecanismos biológicos; el bienestar de los animales y la sanidad pecuaria dependerán de ti.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="tramites" class="py-5">
            <div class="container">
                <h2 class="text-center mb-2 titulo-seccion"><i class="bi bi-file-earmark-text text-uptp-red"></i> Solicitudes Disponibles</h2>
                <p class="text-center mb-5">Pasa el cursor por encima o presiona la tarjeta si estás en celular para revelar los requisitos del trámite.</p>

                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card card-interactiva shadow-sm">
                            <div class="card-frontal text-center p-4 d-flex flex-column justify-content-center align-items-center">
                                <i class="bi bi-arrow-left-right display-4 text-uptp-red"></i>
                                <h3 class="h5 mt-3">Cambio de Carrera</h3>
                                <p class="mb-0">Solicita el cambio a otra carrera ofertada por la universidad.</p>
                            </div>
                            <div class="card-trasera p-4 bg-uptp-dark text-white d-flex flex-column justify-content-center text-center">
                                <h4 class="h6 text-uptp-red fw-bold mb-3"><i class="bi bi-file-earmark-check-fill"></i> Requisitos:</h4>
                                <ul class="list-unstyled small mb-0 text-start px-2">
                                    <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i> Haber aprobado el Trayecto I.</li>
                                    <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i> Carta de exposición de motivos.</li>
                                    <li><i class="bi bi-check2 text-success me-2"></i> Récord de notas histórico en PDF.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card card-interactiva shadow-sm">
                            <div class="card-frontal text-center p-4 d-flex flex-column justify-content-center align-items-center">
                                <i class="bi bi-clock-fill display-4 text-uptp-red"></i>
                                <h3 class="h5 mt-3">Cambio de Turno</h3>
                                <p class="mb-0">Solicita cambio entre turno diurno, nocturno o mixto.</p>
                            </div>
                            <div class="card-trasera p-4 bg-uptp-dark text-white d-flex flex-column justify-content-center text-center">
                                <h4 class="h6 text-uptp-red fw-bold mb-3"><i class="bi bi-file-earmark-check-fill"></i> Requisitos:</h4>
                                <ul class="list-unstyled small mb-0 text-start px-2">
                                    <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i> Constancia de trabajo (si aplica).</li>
                                    <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i> Carta justificativa de fuerza mayor.</li>
                                    <li><i class="bi bi-check2 text-success me-2"></i> Sujeto a disponibilidad de cupos.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card card-interactiva shadow-sm">
                            <div class="card-frontal text-center p-4 d-flex flex-column justify-content-center align-items-center">
                                <i class="bi bi-mortarboard-fill display-4 text-uptp-red"></i>
                                <h3 class="h5 mt-3">Solicitud de Pregrado</h3>
                                <p class="mb-0">Inscripción, equivalencias, constancias o certificados de estudio.</p>
                            </div>
                            <div class="card-trasera p-4 bg-uptp-dark text-white d-flex flex-column justify-content-center text-center">
                                <h4 class="h6 text-uptp-red fw-bold mb-3"><i class="bi bi-file-earmark-check-fill"></i> Información:</h4>
                                <p class="small mb-0">Para constancias de estudio vigentes, el sistema las valida automáticamente si posees la condición de alumno regular en el periodo actual.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card card-interactiva shadow-sm">
                            <div class="card-frontal text-center p-4 d-flex flex-column justify-content-center align-items-center">
                                <i class="bi bi-bug-fill display-4 text-uptp-red"></i>
                                <h3 class="h5 mt-3">Problemas con SOGAC</h3>
                                <p class="mb-0">Reporta inconvenientes con el sistema SOGAC (notas, inscripción, usuario).</p>
                            </div>
                            <div class="card-trasera p-4 bg-uptp-dark text-white d-flex flex-column justify-content-center text-center">
                                <h4 class="h6 text-uptp-red fw-bold mb-3"><i class="bi bi-file-earmark-check-fill"></i> Indicaciones:</h4>
                                <p class="small mb-0">Adjunta captura de pantalla del error, tu cédula de identidad y código de sección, y repórtalo en taquilla presencial.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card card-interactiva shadow-sm">
                            <div class="card-frontal text-center p-4 d-flex flex-column justify-content-center align-items-center">
                                <i class="bi bi-question-circle-fill display-4 text-uptp-red"></i>
                                <h3 class="h5 mt-3">Otros Problemas</h3>
                                <p class="mb-0">Cualquier otra incidencia o consulta general que no esté en la lista.</p>
                            </div>
                            <div class="card-trasera p-4 bg-uptp-dark text-white d-flex flex-column justify-content-center text-center">
                                <h4 class="h6 text-uptp-red fw-bold mb-3"><i class="bi bi-file-earmark-check-fill"></i> Atención General:</h4>
                                <p class="small mb-0">Envía un correo detallado a <strong class="text-uptp-red">info@uptp.edu.ve</strong> indicando tus nombres, cédula y PNF correspondiente.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card card-interactiva shadow-sm">
                            <div class="card-frontal text-center p-4 d-flex flex-column justify-content-center align-items-center">
                                <i class="bi bi-life-preserver display-4 text-uptp-red"></i>
                                <h3 class="h5 mt-3">Centro de Ayuda</h3>
                                <p class="mb-0">Guías, tutoriales y preguntas frecuentes sobre trámites.</p>
                            </div>
                            <div class="card-trasera p-4 bg-uptp-dark text-white d-flex flex-column justify-content-center text-center">
                                <h4 class="h6 text-uptp-red fw-bold mb-3"><i class="bi bi-file-earmark-check-fill"></i> Recursos:</h4>
                                <p class="small mb-0">Las guías descargables en PDF y los cronogramas oficiales se encuentran publicados en las carteleras informativas del bloque central.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section id="ubicacion" class="py-5 bg-body-tertiary">
            <div class="container">
                <h2 class="text-center mb-4 titulo-seccion"><i class="bi bi-geo-alt-fill text-uptp-red"></i> Nuestra Ubicación</h2>

                <article class="row g-4 align-items-center">
                    <div class="col-lg-5">
                        <h3 class="h4">UPTP "Juan de Jesús Montilla"</h3>
                        <p>
                            Nuestra sede principal se encuentra ubicada en la ciudad de <strong>Acarigua, estado Portuguesa</strong>,
                            en una zona de fácil acceso para estudiantes, docentes y comunidad en general.
                        </p>
                        <address class="mb-3">
                            <i class="bi bi-pin-map-fill text-uptp-red"></i>
                            <strong>Dirección:</strong><br>
                            Diagonal a la Cruz Roja, Sector Bellas Artes,<br>
                            Avenida Circunvalación Sur,<br>
                            Acarigua 3301, Portuguesa, Venezuela.
                        </address>
                        <a href="https://www.google.com/maps?q=Universidad+Politecnica+Territorial+del+Estado+Portuguesa+Acarigua"
                           target="_blank" rel="noopener" class="btn btn-uptp-red">
                            <i class="bi bi-map"></i> Ver en Google Maps
                        </a>
                    </div>

                    <div class="col-lg-7">
                        <div class="ratio ratio-16x9 shadow rounded overflow-hidden">
                            <iframe
                                src="https://www.google.com/maps?q=Universidad+Politecnica+Territorial+del+Estado+Portuguesa+Acarigua&output=embed"
                                style="border:0;"
                                allowfullscreen=""
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                title="Mapa de la UPTP Acarigua">
                            </iframe>
                        </div>
                    </div>
                </article>
            </div>
        </section>

    </main>

    <footer id="contacto">
        <div class="footer-top">
            <div class="container">
                <div class="footer-grid">
                    <div class="footer-column">
                        <div class="footer-logo">
                            <img src="{{ asset('portada/logo.png') }}" alt="Logo UPTP" height="70" width="95.16">
                            <p>Universidad Politécnica Territorial del estado Portuguesa "Juan de Jesús Montilla". Comprometidos con la excelencia académica y el desarrollo regional.</p>
                            <div class="social-links">
                                <a href="https://www.instagram.com/uptpjjmontillaacarigua/"><i class="fab fa-instagram"></i></a>
                                <a href="https://www.facebook.com/profile.php?id=100064113455547"><i class="fab fa-facebook-f"></i></a>
                                <a href="https://x.com/UptpJuandeJesus/with_replies"><i class="fab fa-twitter"></i></a>
                                <a href="https://www.youtube.com/@educaciondigital-uptpjjmon6898"><i class="fab fa-youtube"></i></a>
                                <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="footer-column">
                        <h3>Enlaces útiles</h3>
                        <ul>
                            <li><a href="{{ route('login') }}" class="text-white text-decoration-none"><i class="bi bi-person-badge"></i> Sistema de Solicitudes</a></li>
                            <li><a href="#inicio" class="text-white text-decoration-none"><i class="fas fa-sitemap"></i> Mapa del Sitio</a></li>
                            <li><a href="#" class="text-white text-decoration-none"><i class="fas fa-shield-alt"></i> Política de Privacidad</a></li>
                            <li><a href="#" class="text-white text-decoration-none"><i class="fas fa-file-contract"></i> Términos y Condiciones</a></li>
                        </ul>
                    </div>
                    <div class="footer-column">
                        <h3>Contacto</h3>
                        <ul>
                            <li><i class="fas fa-map-marker-alt"></i> Diagonal a la Cruz Roja, Sector Bellas Artes, con Avenida Circunvalación Sur, Acarigua, Portuguesa.</li>
                            <li><i class="fas fa-phone"></i> +58 255-1234567</li>
                            <li><i class="fas fa-envelope"></i> info@uptp.edu.ve</li>
                            <li><i class="fas fa-clock"></i> Lunes a Viernes: 8:00 AM - 5:00 PM</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p>© 2025 Derechos reservados. Universidad Politécnica Territorial del estado Portuguesa "Juan de Jesús Montilla".</p>
                <p>Web diseñado por el Grupo 4 de Proyecto Sociotecnológico Año 2 Trayecto 3 Sección 332 - Créditos startbootstrap-one-page-wonder-gh-pages</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('portada/script.js') }}"></script>
</body>
</html>
