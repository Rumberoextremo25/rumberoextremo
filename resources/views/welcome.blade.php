{{-- resources/views/welcome.blade.php (o home.blade.php si la usas como página de inicio) --}}

@extends('layouts.app')

@section('title', 'Rumbero Extremo App - Tu Noche Perfecta Comienza Aquí')

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
                    <img src="https://source.unsplash.com/random/900x400/?nightclub,lights,dj"
                        alt="Rumba Extrema - Luces de Discoteca">
                    <img src="https://source.unsplash.com/random/900x400/?concert,crowd,music"
                        alt="Concierto - Multitud Disfrutando">
                    <img src="https://source.unsplash.com/random/900x400/?cocktails,bar,friends"
                        alt="Amigos en el Bar - Cócteles">
                    <img src="https://source.unsplash.com/random/900x400/?festival,stage,fireworks"
                        alt="Festival - Escenario con Fuegos Artificiales">
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
                    <img src="https://via.placeholder.com/80x80/FF4B4B/FFFFFF?text=JD" alt="Foto de Juan David">
                    <p>"¡Increíble! Desde que uso Rumbero Extremo no me pierdo ni un evento. Es la mejor forma de saber
                        dónde está la rumba."</p>
                    <span class="author">Juan David M.</span>
                    <span class="location">Caracas, Venezuela</span>
                </div>
                <div class="testimonial-card">
                    <img src="https://via.placeholder.com/80x80/333333/FFFFFF?text=MV" alt="Foto de María Virginia">
                    <p>"Me encanta descubrir nuevos locales y DJs. La app es súper fácil de usar y me ha conectado con
                        la movida nocturna."</p>
                    <span class="author">María Virginia L.</span>
                    <span class="location">Valencia, Venezuela</span>
                </div>
                <div class="testimonial-card">
                    <img src="https://via.placeholder.com/80x80/555555/FFFFFF?text=RP" alt="Foto de Ricardo Pérez">
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
@endsection {{-- Cierra la sección de contenido principal --}}

@section('scripts') {{-- Define los scripts específicos para esta vista (el carrusel) --}}
    <script src="{{ asset('js/admin-dashboard.js') }}"></script>
@endsection
