{{-- resources/views/welcome.blade.php (o home.blade.php si la usas como página de inicio) --}}

@extends('layouts.app')

@section('title', 'Rumbero Extremo App - Tu Noche Perfecta Comienza Aquí')

@push('styles')
    {{-- Agregamos el CSS específico de esta vista --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Poppins:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
@endpush

@section('content')
    <section class="hero">
        <div class="container">
            <h1>¡Tu Noche Perfecta Comienza Aquí!</h1>
            <p>Descubre los mejores eventos, fiestas y lugares para rumbear en Venezuela. Conéctate con amigos y no te
                pierdas nada.</p>
            <a href="{{ route('register') }}" class="cta-button">¡Regístrate y Explora!</a>
        </div>
    </section>

    <section id="features" class="features">
        <div class="container">
            <h2>¿Por qué Rumbero Extremo es tu mejor aliado?</h2>
            <div class="feature-grid">
                <div class="feature-item">
                    <i class="fas fa-calendar-alt"></i>
                    <h3>Eventos Actualizados</h3>
                    <p>Mantente al día con los eventos más calientes en tu ciudad. Desde conciertos a festivales, nunca
                        te perderás una rumba.</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <h3>Descubre Lugares Nuevos</h3>
                    <p>Encuentra los bares, discotecas y lounges de moda, o ese rincón especial que aún no conocías.</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-users"></i>
                    <h3>Conecta con Amigos</h3>
                    <p>Ve a dónde van tus amigos, coordina tus salidas y planea experiencias inolvidables juntos
                        fácilmente.</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-tags"></i>
                    <h3>Ofertas Exclusivas</h3>
                    <p>Accede a descuentos y promociones especiales en tus lugares favoritos, solo por ser parte de
                        Rumbero Extremo.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="image-carousel-section">
        <div class="container">
            <h2>Momentos Inolvidables en Rumbero Extremo</h2>
            <div class="carousel-container">
                <div class="carousel-slide" id="carouselSlide">
                    <img src="{{ asset('assets/img/home/restaurante_lujo.jpg') }}" alt="Rumba Extrema - Luces de Discoteca">
                    <img src="{{ asset('assets/img/home/parque_lujo.jpg') }}" alt="Concierto - Multitud Disfrutando">
                    <img src="{{ asset('assets/img/home/fiesta-discoteca.jpg') }}" alt="Amigos en el Bar - Cócteles">
                </div>
                <button class="carousel-nav-button prev-button" onclick="moveSlide(-1)">&#10094;</button>
                <button class="carousel-nav-button next-button" onclick="moveSlide(1)">&#10095;</button>
            </div>
        </div>
    </section>
    <section class="testimonials-section">
        <div class="container">
            <h2>Lo Que Dicen Nuestros Rumberos</h2>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <p>"¡Increíble! Desde que uso Rumbero Extremo no me pierdo ni un evento. Es la mejor forma de saber
                        dónde está la rumba."</p>
                    <span class="author">Juan David M.</span>
                    <span class="location">Caracas, Venezuela</span>
                </div>
                <div class="testimonial-card">
                    <p>"Me encanta descubrir nuevos locales y DJs. La app es súper fácil de usar y me ha conectado con
                        la movida nocturna."</p>
                    <span class="author">María Virginia L.</span>
                    <span class="location">Valencia, Venezuela</span>
                </div>
                <div class="testimonial-card">
                    <p>"Perfecta para planificar mis noches. Siempre encuentro algo emocionante que hacer. ¡Rumbero
                        Extremo es la clave!"</p>
                    <span class="author">Ricardo P.</span>
                    <span class="location">Maracaibo, Venezuela</span>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-alt">
        <div class="container">
            <h2>¿Listo para la Mejor Rumba de tu Vida?</h2>
            <p>Únete a la comunidad de Rumbero Extremo hoy mismo y empieza a vivir experiencias nocturnas inolvidables.
            </p>
            <a href="{{ route('register') }}" class="cta-button">Regístrate Gratis Ahora</a>
        </div>
    </section>
@endsection

@push('scripts')
    {{-- Agregamos el JS específico de esta vista --}}
    <script src="{{ asset('js/welcome.js') }}"></script>
@endpush
