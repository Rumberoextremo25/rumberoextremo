{{-- resources/views/cookies.blade.php --}}

@extends('layouts.app')

@section('title', 'Política de Cookies - Rumbero Extremo')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/public-modern.css') }}?v={{ filemtime(public_path('css/public-modern.css')) }}">
@endpush

@section('content')
    <div class="px-page">

        <section class="px-hero px-hero--compact">
            <div class="px-container">
                <span class="px-hero__kicker">
                    <i class="fa-solid fa-cookie-bite"></i> Legal
                </span>

                <h1 class="px-hero__title">
                    Política de <span>cookies</span>
                </h1>

                <p class="px-hero__text">
                    Qué son las cookies, cuáles usamos en Rumbero Extremo y cómo puedes controlarlas.
                    Última actualización: {{ date('d/m/Y') }}.
                </p>
            </div>
        </section>

        <section class="px-section">
            <div class="px-container">
                <div class="px-legal__grid">

                    <aside class="px-legal__aside px-legal__aside--toc">
                        <nav class="px-toc" aria-label="Contenido">
                            <p class="px-toc__title">Contenido</p>
                            <ol class="px-toc__list">
                                <li><a href="#que-son-las-cookies">Qué son las cookies</a></li>
                                <li><a href="#tipos-de-cookies">Tipos que utilizamos</a></li>
                                <li><a href="#cookies-teceros">Cookies de terceros</a></li>
                                <li><a href="#control-de-cookies">Cómo controlarlas</a></li>
                                <li><a href="#cambios">Cambios en esta política</a></li>
                                <li><a href="#contacto-cookies">Contacto</a></li>
                            </ol>
                        </nav>
                    </aside>

                    <div class="px-legal__content">

                        <section class="px-legal__section" id="que-son-las-cookies">
                            <h2 class="px-legal__title"><span class="px-legal__num">1</span> Qué son las cookies</h2>
                            <div class="px-legal__body">
                                <p>Las cookies son pequeños archivos de texto que un sitio web guarda en tu
                                    dispositivo cuando lo visitas. Sirven para recordar información sobre tu visita:
                                    mantener tu sesión iniciada, recordar tus preferencias o entender cómo se usa el
                                    sitio para mejorarlo.</p>
                                <p>También usamos tecnologías similares, como el almacenamiento local del navegador
                                    (<em>localStorage</em> y <em>sessionStorage</em>), con las mismas finalidades.</p>
                            </div>
                        </section>

                        <section class="px-legal__section" id="tipos-de-cookies">
                            <h2 class="px-legal__title"><span class="px-legal__num">2</span> Tipos que utilizamos</h2>
                            <div class="px-legal__body">
                                <div class="px-table-wrap">
                                    <table class="px-table">
                                        <thead>
                                            <tr>
                                                <th>Tipo</th>
                                                <th>Para qué sirve</th>
                                                <th>Duración</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><strong>Esenciales</strong></td>
                                                <td>Sesión del usuario, inicio de sesión y protección contra
                                                    falsificación de peticiones (CSRF). Sin ellas el sitio no funciona.</td>
                                                <td>Sesión</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Preferencias</strong></td>
                                                <td>Recuerdan tus elecciones, como el modo oscuro del panel o el estado
                                                    del chat de soporte.</td>
                                                <td>Hasta 1 año</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Seguridad</strong></td>
                                                <td>Ayudan a detectar usos indebidos y limitan peticiones automatizadas.</td>
                                                <td>Hasta 1 año</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Analíticas</strong></td>
                                                <td>Nos permiten saber qué páginas se visitan más y mejorar la
                                                    experiencia. Son datos agregados y anónimos.</td>
                                                <td>Hasta 2 años</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <p>No utilizamos cookies para vender tus datos ni para mostrarte publicidad de terceros.</p>
                            </div>
                        </section>

                        <section class="px-legal__section" id="cookies-teceros">
                            <h2 class="px-legal__title"><span class="px-legal__num">3</span> Cookies de terceros</h2>
                            <div class="px-legal__body">
                                <p>Algunas páginas incluyen contenido de servicios externos que pueden instalar sus
                                    propias cookies cuando interactúas con él:</p>
                                <ul>
                                    <li><strong>Google Maps</strong> (mapa de ubicación en la página de contacto).</li>
                                    <li><strong>YouTube</strong> (video de demostración en la página de demo).</li>
                                    <li><strong>Google Fonts y Font Awesome</strong> (tipografías e iconos servidos
                                        desde CDN).</li>
                                </ul>
                                <p>Estos proveedores tienen sus propias políticas de privacidad, que puedes consultar
                                    en sus sitios oficiales.</p>
                            </div>
                        </section>

                        <section class="px-legal__section" id="control-de-cookies">
                            <h2 class="px-legal__title"><span class="px-legal__num">4</span> Cómo controlarlas</h2>
                            <div class="px-legal__body">
                                <p>Puedes eliminar o bloquear las cookies desde la configuración de tu navegador.
                                    Ten en cuenta que, si desactivas las cookies esenciales, algunas funciones —como
                                    iniciar sesión o enviar formularios— dejarán de funcionar.</p>
                                <ul>
                                    <li><strong>Chrome:</strong> Configuración → Privacidad y seguridad → Cookies.</li>
                                    <li><strong>Safari:</strong> Preferencias → Privacidad.</li>
                                    <li><strong>Firefox:</strong> Preferencias → Privacidad y seguridad.</li>
                                    <li><strong>Edge:</strong> Configuración → Cookies y permisos del sitio.</li>
                                </ul>
                                <p>En dispositivos móviles, revisa la sección de privacidad de los ajustes del
                                    sistema o del navegador que uses.</p>
                            </div>
                        </section>

                        <section class="px-legal__section" id="cambios">
                            <h2 class="px-legal__title"><span class="px-legal__num">5</span> Cambios en esta política</h2>
                            <div class="px-legal__body">
                                <p>Podemos actualizar esta política cuando cambiemos las cookies que utilizamos o
                                    cuando la normativa lo requiera. Publicaremos siempre la versión vigente en esta
                                    página, con su fecha de actualización.</p>
                            </div>
                        </section>

                        <section class="px-legal__section" id="contacto-cookies">
                            <h2 class="px-legal__title"><span class="px-legal__num">6</span> Contacto</h2>
                            <div class="px-legal__body">
                                <p>Si tienes dudas sobre el uso de cookies, escríbenos:</p>
                                <ul>
                                    <li>Correo: <a href="mailto:rumberoextremo@gmail.com">rumberoextremo@gmail.com</a></li>
                                    <li>Formulario: <a href="{{ route('contact') }}">página de contacto</a></li>
                                </ul>
                                <p>También puedes consultar nuestra
                                    <a href="{{ url('/privacy') }}">política de privacidad</a> y nuestros
                                    <a href="{{ url('/terms') }}">términos y condiciones</a>.
                                </p>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
