{{-- resources/views/privacy.blade.php --}}

@extends('layouts.app')

@section('title', 'Políticas de Privacidad - Rumbero Extremo')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/public-modern.css') }}?v={{ filemtime(public_path('css/public-modern.css')) }}">
@endpush

@section('content')
    <div class="px-page">

        <section class="px-hero px-hero--compact">
            <div class="px-container">
                <span class="px-hero__kicker">
                    <i class="fa-solid fa-user-shield"></i> Legal
                </span>

                <h1 class="px-hero__title">Políticas de Privacidad</h1>

                <p class="px-hero__text">Cómo recopilamos, usamos y protegemos tu información personal en Rumbero Extremo.</p>
            </div>
        </section>

        <section class="px-section">
            <div class="px-container">
                <div class="px-legal__grid">
                    <aside class="px-legal__aside px-legal__aside--toc">
                        <nav class="px-toc" aria-label="Contenido">
                            <p class="px-toc__title">Contenido</p>
                            <ol class="px-toc__list">
                            <li><a href="#1-informacion-que-recopilamos">Información que Recopilamos</a></li>
                            <li><a href="#2-como-utilizamos-tu-informacion">Cómo Utilizamos tu Información</a></li>
                            <li><a href="#3-compartir-tu-informacion">Compartir tu Información</a></li>
                            <li><a href="#4-seguridad-de-los-datos">Seguridad de los Datos</a></li>
                            <li><a href="#5-tus-derechos-de-privacidad">Tus Derechos de Privacidad</a></li>
                            <li><a href="#6-cambios-en-esta-politica-de-privacidad">Cambios en esta Política de Privacidad</a></li>
                            </ol>
                        </nav>
                    </aside>

                    <div class="px-legal__content">
                    <section class="px-legal__section" id="1-informacion-que-recopilamos">
                        <h2 class="px-legal__title">
                            <span class="px-legal__num">1</span>
                            Información que Recopilamos
                        </h2>
                        <div class="px-legal__body">
<p>En Rumbero Extremo, recopilamos información para proporcionarte y mejorar nuestros servicios. Esto puede incluir:</p>
            <ul>
                <li><strong>Información de Registro:</strong> Nombre, correo electrónico, número de teléfono, fecha de nacimiento, al crear una cuenta.</li>
                <li><strong>Datos de Uso:</strong> Información sobre cómo interactúas con nuestra aplicación, incluyendo las páginas visitadas, funciones utilizadas y el tiempo de permanencia.</li>
                <li><strong>Información de Dispositivo:</strong> Tipo de dispositivo, sistema operativo, identificadores únicos del dispositivo, información de red móvil.</li>
                <li><strong>Datos de Ubicación:</strong> Con tu consentimiento, podemos recopilar datos de ubicación precisa para ofrecerte eventos y locales cercanos.</li>
                <li><strong>Contenido Generado por el Usuario:</strong> Fotos, comentarios, reseñas y cualquier otra información que publiques en nuestra plataforma.</li>
            </ul>
                        </div>
                    </section>

                    <section class="px-legal__section" id="2-como-utilizamos-tu-informacion">
                        <h2 class="px-legal__title">
                            <span class="px-legal__num">2</span>
                            Cómo Utilizamos tu Información
                        </h2>
                        <div class="px-legal__body">
<p>Utilizamos la información recopilada para:</p>
            <ul>
                <li>Proveer, operar y mantener nuestros servicios.</li>
                <li>Mejorar, personalizar y expandir nuestros servicios.</li>
                <li>Comprender y analizar cómo utilizas nuestros servicios.</li>
                <li>Desarrollar nuevos productos, servicios, características y funcionalidades.</li>
                <li>Comunicarnos contigo, directamente o a través de uno de nuestros socios, incluyendo para servicio al cliente, para proporcionarte actualizaciones y otra información relacionada con el servicio, y con fines de marketing y promocionales.</li>
                <li>Enviarte correos electrónicos y notificaciones push.</li>
                <li>Detectar y prevenir fraudes.</li>
            </ul>
                        </div>
                    </section>

                    <section class="px-legal__section" id="3-compartir-tu-informacion">
                        <h2 class="px-legal__title">
                            <span class="px-legal__num">3</span>
                            Compartir tu Información
                        </h2>
                        <div class="px-legal__body">
<p>No compartimos tu información personal con terceros, excepto en las siguientes circunstancias:</p>
            <ul>
                <li><strong>Con tu Consentimiento:</strong> Podemos compartir tu información cuando nos des tu consentimiento explícito.</li>
                <li><strong>Proveedores de Servicios:</strong> Podemos emplear empresas y personas de terceros para facilitar nuestros servicios, para proporcionar el servicio en nuestro nombre, para realizar servicios relacionados con el servicio o para ayudarnos a analizar cómo se utilizan nuestros servicios.</li>
                <li><strong>Fines Legales:</strong> Podemos divulgar tu información cuando sea requerido por ley, citación o cualquier proceso legal.</li>
                <li><strong>Transferencias de Negocios:</strong> Si Rumbero Extremo se involucra en una fusión, adquisición o venta de activos, tu información personal puede ser transferida.</li>
            </ul>
                        </div>
                    </section>

                    <section class="px-legal__section" id="4-seguridad-de-los-datos">
                        <h2 class="px-legal__title">
                            <span class="px-legal__num">4</span>
                            Seguridad de los Datos
                        </h2>
                        <div class="px-legal__body">
<p>Nos esforzamos por utilizar medios comercialmente aceptables para proteger tu información personal, pero recuerda que ningún método de transmisión por Internet o método de almacenamiento electrónico es 100% seguro y confiable, y no podemos garantizar su seguridad absoluta.</p>
                        </div>
                    </section>

                    <section class="px-legal__section" id="5-tus-derechos-de-privacidad">
                        <h2 class="px-legal__title">
                            <span class="px-legal__num">5</span>
                            Tus Derechos de Privacidad
                        </h2>
                        <div class="px-legal__body">
<p>Dependiendo de tu ubicación, puedes tener ciertos derechos con respecto a tu información personal, como el derecho a acceder, corregir o eliminar tus datos personales.</p>
            <p>Para ejercer estos derechos, por favor contáctanos a <a href="mailto:rumberoextremo@gmail.com">rumberoextremo@gmail.com</a>.</p>
                        </div>
                    </section>

                    <section class="px-legal__section" id="6-cambios-en-esta-politica-de-privacidad">
                        <h2 class="px-legal__title">
                            <span class="px-legal__num">6</span>
                            Cambios en esta Política de Privacidad
                        </h2>
                        <div class="px-legal__body">
<p>Podemos actualizar nuestra Política de Privacidad de vez en cuando. Te notificaremos cualquier cambio publicando la nueva Política de Privacidad en esta página. Se te aconseja revisar esta Política de Privacidad periódicamente para cualquier cambio.</p>
        


        

            <p>Si tienes alguna pregunta sobre estas Políticas de Privacidad, puedes contactarnos:</p>
            <ul>
                <li>Por correo electrónico: <a href="mailto:rumberoextremo@gmail.com">rumberoextremo@gmail.com</a></li>
                <li>Visitando esta página en nuestro sitio web: <a href="{{ route('contact') }}">contactanos</a></li>
            </ul>
                        </div>
                    </section>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
