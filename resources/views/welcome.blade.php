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
            <h1>Tu Próxima Aventura te está esperando
                Conecta, explora y vive sin limites
            </h1>
            <p>Rumbero Extremo una app donde los eventos, actividades, productos y servicios que forman parte de tu
                recreación y entretenimiento están a tu disposición

                ¡Qué esperas para ser un Rumbero afíliate!
            </p>
            <a href="{{ route('register') }}" class="cta-button-banner">¡Yo Soy Rumbero !</a>
        </div>
    </section>

    <section id="features" class="features">
        <div class="container">
            <h2>¡Hoy los Rumberos quieren …!</h2>
            <div class="feature-grid">
                <div class="feature-item">
                    <img src="assets/img/home/eventos_1.jpg" alt="Eventos Actualizados">
                    <h3>Eventos <br> Actualizados</h3>
                    <p>Mantente al día con los eventos más calientes en tu ciudad. Desde conciertos a festivales, nunca te
                        perderás una rumba.</p>
                </div>
                <div class="feature-item">
                    <img src="assets/img/home/nuevos_lugares.jpg" alt="Descubre Lugares Nuevos">
                    <h3>Descubre lugares <br> nuevos</h3>
                    <p>Encuentra los bares, discotecas y lounges de moda, o ese rincón especial que aún no conocías.</p>
                </div>
                <div class="feature-item">
                    <img src="assets/img/home/conecta_con_amigos.webp" alt="Conecta con Amigos">
                    <h3>Conecta con <br> amigos</h3>
                    <p>Ve a dónde van tus amigos, coordina tus salidas y planea experiencias inolvidables juntos fácilmente.
                    </p>
                </div>
                <div class="feature-item">
                    <img src="assets/img/home/ofertas_exclusivas.webp" alt="Ofertas Exclusivas">
                    <h3>Ofertas <br> exclusivas</h3>
                    <p>Accede a descuentos y promociones especiales en tus lugares favoritos, solo por ser parte de Rumbero
                        Extremo.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="image-carousel-section">
        <div class="carousel-container">
            <div class="carousel-slide" id="carouselSlide">
                <img src="assets/img/home/discoteca_5.jpg" alt="Rumbero Extremo - Discoteca">
                <img src="assets/img/home/cafeteria_4.webp" alt="Rumbero Extremo - Cafeteria">
                <img src="assets/img/home/parque_3.jpg" alt="Rumbero Extremo - Parque">
                <img src="assets/img/home/posada_2.jpeg" alt="Rumbero Extremo - Posada">
                <img src="assets/img/home/fiesta_1.jpg" alt="Rumbero Extremo - Fiesta">
            </div>
            <button class="carousel-nav-button prev-button" onclick="moveSlide(-1)">&#10094;</button>
            <button class="carousel-nav-button next-button" onclick="moveSlide(1)">&#10095;</button>
        </div>
    </section>
    <section class="testimonials-section">
        <div class="container">
            <h2>¡Ellos son Rumberos! Y Comparten sus Experiencias!</h2>
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
@endsection

@push('scripts')
    {{-- Agregamos el JS específico de esta vista --}}
    <script src="{{ asset('js/welcome.js') }}"></script>
@endpush
