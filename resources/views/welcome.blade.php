{{-- resources/views/welcome.blade.php --}}

@extends('layouts.app')

@section('title', 'Rumbero Extremo App - Tu Noche Perfecta Comienza Aquí')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/public-modern.css') }}?v={{ filemtime(public_path('css/public-modern.css')) }}">
@endpush

@section('content')
    <div class="px-page">

        {{-- ============================== HERO ==============================
             Banner grande con la imagen original como fondo --}}
        <section class="px-hero px-hero--home">
            <div class="px-container px-hero__inner">
                    <div>
                        <span class="px-hero__kicker">
                            <i class="fa-solid fa-bolt"></i> Recreación y entretenimiento
                        </span>

                        <h1 class="px-hero__title">
                            Tu próxima aventura <span>te está esperando</span>
                        </h1>

                        <p class="px-hero__text">
                            Conecta, explora y vive sin límites. Descubre los lugares en tendencia, activa descuentos
                            exclusivos y comparte la rumba con tu gente.
                        </p>

                        <div class="px-home-actions">
                            <a href="{{ route('register') }}" class="px-btn px-btn--primary">
                                <i class="fa-solid fa-fire"></i> ¡Yo soy Rumbero!
                            </a>

                            <a href="https://play.google.com/store/apps/details?id=com.sistema.rumberoextremooficial"
                                class="px-store" target="_blank" rel="noopener noreferrer">
                                <i class="fa-brands fa-google-play"></i>
                                <span class="px-store__text">
                                    <small>Descárgala en</small>
                                    <strong>Google Play</strong>
                                </span>
                            </a>

                            <span class="px-store is-disabled" aria-disabled="true">
                                <i class="fa-brands fa-apple"></i>
                                <span class="px-store__text">
                                    <small>Próximamente en</small>
                                    <strong>App Store</strong>
                                </span>
                                <span class="px-store__soon">Pronto</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ===================== FRANJA DE BENEFICIOS =====================
             (antes era la tarjeta del hero, que tapaba la cara del banner) --}}
        <section class="px-trust">
            <div class="px-container">
                <ul class="px-trust__grid">
                    <li class="px-trust__item">
                        <span class="px-trust__icon"><i class="fa-solid fa-bell"></i></span>
                        <span>
                            <strong>Entérate primero</strong>
                            <small>Eventos y novedades antes que nadie</small>
                        </span>
                    </li>
                    <li class="px-trust__item">
                        <span class="px-trust__icon"><i class="fa-solid fa-tags"></i></span>
                        <span>
                            <strong>Descuentos exclusivos</strong>
                            <small>En tus lugares favoritos</small>
                        </span>
                    </li>
                    <li class="px-trust__item">
                        <span class="px-trust__icon"><i class="fa-solid fa-user-group"></i></span>
                        <span>
                            <strong>Salidas con amigos</strong>
                            <small>Coordina y planeen juntos</small>
                        </span>
                    </li>
                    <li class="px-trust__item">
                        <span class="px-trust__icon"><i class="fa-solid fa-compass"></i></span>
                        <span>
                            <strong>Rincones nuevos</strong>
                            <small>Descubre otra cara de la ciudad</small>
                        </span>
                    </li>
                </ul>
            </div>
        </section>

        {{-- =========================== BENEFICIOS =========================== --}}
        <section id="features" class="px-section">
            <div class="px-container">
                <div class="px-head">
                    <span class="px-head__eyebrow">¿Qué puedes hacer?</span>
                    <h2 class="px-head__title">¡Hoy los Rumberos quieren…!</h2>
                    <p class="px-head__text">
                        Todo lo que necesitas para tus salidas, en una sola app.
                    </p>
                </div>

                <div class="px-grid">
                    <article class="px-card px-feature">
                        <div class="px-feature__media">
                            <img src="{{ asset('assets/img/home/eventos_1.jpg') }}" alt="Eventos Rumberos"
                                loading="lazy">
                        </div>
                        <div class="px-feature__body">
                            <h3>Eventos Rumberos</h3>
                            <p>Nuevos aliados, conexión con otros Rumberos y promociones de los locales.</p>
                        </div>
                    </article>

                    <article class="px-card px-feature">
                        <div class="px-feature__media">
                            <img src="{{ asset('assets/img/home/nuevos_lugares.jpg') }}" alt="Descubre lugares nuevos"
                                loading="lazy">
                        </div>
                        <div class="px-feature__body">
                            <h3>Descubre lugares nuevos</h3>
                            <p>Bares, discotecas y lounges de moda, o ese rincón especial que aún no conocías.</p>
                        </div>
                    </article>

                    <article class="px-card px-feature">
                        <div class="px-feature__media">
                            <img src="{{ asset('assets/img/home/conecta_con_amigos.webp') }}" alt="Conecta con amigos"
                                loading="lazy">
                        </div>
                        <div class="px-feature__body">
                            <h3>Conecta con amigos</h3>
                            <p>Ve a dónde van, coordina la salida y planeen experiencias inolvidables.</p>
                        </div>
                    </article>

                    <article class="px-card px-feature">
                        <div class="px-feature__media">
                            <img src="{{ asset('assets/img/home/ofertas_exclusivas.webp') }}" alt="Ofertas exclusivas"
                                loading="lazy">
                        </div>
                        <div class="px-feature__body">
                            <h3>Ofertas exclusivas</h3>
                            <p>Descuentos y promociones especiales solo por ser parte de Rumbero Extremo.</p>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        {{-- ============================ GALERÍA ============================ --}}
        <section class="px-section px-section--muted">
            <div class="px-container">
                <div class="px-head">
                    <span class="px-head__eyebrow">La movida</span>
                    <h2 class="px-head__title">Así se vive la experiencia</h2>
                </div>

                <div class="px-carousel">
                    {{-- El JS del carrusel necesita este id y las imágenes como hijos directos --}}
                    <div class="px-carousel__track" id="carouselSlide">
                        <img src="{{ asset('assets/img/home/discoteca_5.jpg') }}" alt="Rumbero Extremo - Discoteca"
                            loading="lazy">
                        <img src="{{ asset('assets/img/home/cafeteria_4.webp') }}" alt="Rumbero Extremo - Cafetería"
                            loading="lazy">
                        <img src="{{ asset('assets/img/home/parque_3.jpg') }}" alt="Rumbero Extremo - Parque"
                            loading="lazy">
                        <img src="{{ asset('assets/img/home/posada_2.jpeg') }}" alt="Rumbero Extremo - Posada"
                            loading="lazy">
                        <img src="{{ asset('assets/img/home/fiesta_1.jpg') }}" alt="Rumbero Extremo - Fiesta"
                            loading="lazy">
                    </div>

                    <button type="button" class="px-carousel__btn prev carousel-nav-button prev-button"
                        onclick="moveSlide(-1)" aria-label="Anterior">&#10094;</button>
                    <button type="button" class="px-carousel__btn next carousel-nav-button next-button"
                        onclick="moveSlide(1)" aria-label="Siguiente">&#10095;</button>
                </div>
            </div>
        </section>

        {{-- ========================== TESTIMONIOS ========================== --}}
        <section class="px-section">
            <div class="px-container">
                <div class="px-head">
                    <span class="px-head__eyebrow">Comunidad</span>
                    <h2 class="px-head__title">Ellos son Rumberos y comparten su experiencia</h2>
                </div>

                <div class="px-grid">
                    <article class="px-testimonial">
                        <div class="px-testimonial__stars" aria-label="5 de 5 estrellas">★★★★★</div>
                        <p class="px-testimonial__text">
                            "Desde que uso Rumbero Extremo no me pierdo ni un evento. Es la mejor forma de saber
                            dónde está la rumba."
                        </p>
                        <div class="px-testimonial__author">
                            <span class="px-testimonial__avatar" aria-hidden="true">JD</span>
                            <span>
                                <span class="px-testimonial__name">Juan David M.</span>
                                <span class="px-testimonial__city">Caracas, Venezuela</span>
                            </span>
                        </div>
                    </article>

                    <article class="px-testimonial">
                        <div class="px-testimonial__stars" aria-label="5 de 5 estrellas">★★★★★</div>
                        <p class="px-testimonial__text">
                            "Me encanta descubrir nuevos locales y DJs. La app es súper fácil de usar y me ha
                            conectado con la movida nocturna."
                        </p>
                        <div class="px-testimonial__author">
                            <span class="px-testimonial__avatar" aria-hidden="true">MV</span>
                            <span>
                                <span class="px-testimonial__name">María Virginia L.</span>
                                <span class="px-testimonial__city">Valencia, Venezuela</span>
                            </span>
                        </div>
                    </article>

                    <article class="px-testimonial">
                        <div class="px-testimonial__stars" aria-label="5 de 5 estrellas">★★★★★</div>
                        <p class="px-testimonial__text">
                            "Perfecta para planificar mis noches. Siempre encuentro algo emocionante que hacer.
                            ¡Rumbero Extremo es la clave!"
                        </p>
                        <div class="px-testimonial__author">
                            <span class="px-testimonial__avatar" aria-hidden="true">RP</span>
                            <span>
                                <span class="px-testimonial__name">Ricardo P.</span>
                                <span class="px-testimonial__city">Maracaibo, Venezuela</span>
                            </span>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        {{-- =============================== CTA =============================== --}}
        <section class="px-section" style="padding-top:0;">
            <div class="px-container">
                <div class="px-cta">
                    <h2>¿Qué esperas para ser un Rumbero?</h2>
                    <p>Afíliate gratis y empieza a disfrutar los beneficios de nuestros aliados comerciales.</p>
                    <a href="{{ route('register') }}" class="px-btn px-btn--gold">
                        <i class="fa-solid fa-user-plus"></i> Quiero afiliarme
                    </a>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    {{-- El JS del carrusel (moveSlide / autoplay) se mantiene igual --}}
    <script src="{{ asset('js/welcome.js') }}"></script>
@endpush
