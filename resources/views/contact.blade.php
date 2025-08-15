{{-- resources/views/contact.blade.php --}}

@extends('layouts.app')

@section('title', 'Contacto - Rumbero Extremo App')

@push('styles')
    {{-- Agregamos el CSS específico de esta vista --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Play:wght@400;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('css/contact.css') }}">
@endpush

@section('content')
    <main class="main-content">
        <section class="hero-contact">
            <div class="container">
                <h1>Hablemos, estamos listos para escucharte</h1>
                <p>Ya sea que tengas una pregunta, una sugerencia o quieras asociarte con nosotros, estamos a un mensaje de distancia.</p>
            </div>
        </section>

        <section class="content-section">
            <div class="container">
                <h1>Ponte en Contacto</h1>
                <div class="contact-form-container">
                    <form action="{{ route('contact.store') }}" method="POST">
                        @csrf

                        {{-- Mensajes de éxito/error --}}
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <label for="name">Nombre:</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                            placeholder="Tu nombre completo">
                        @error('name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror

                        <label for="email">Correo Electrónico:</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                            placeholder="tu.correo@ejemplo.com">
                        @error('email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror

                        <label for="subject">Asunto:</label>
                        <input type="text" id="subject" name="subject" value="{{ old('subject') }}"
                            placeholder="Ej: Consulta general, Soporte técnico">
                        @error('subject')
                            <span class="error-message">{{ $message }}</span>
                        @enderror

                        <label for="message">Mensaje:</label>
                        <textarea id="message" name="message_content" required placeholder="Describe tu consulta aquí...">{{ old('message_content') }}</textarea>
                        @error('message_content')
                            <span class="error-message">{{ $message }}</span>
                        @enderror

                        <button type="submit">Enviar Mensaje</button>
                    </form>
                </div>

                <p class="contact-info">
                    También puedes enviarnos un correo directamente a
                    <a href="mailto:info@rumberoextremo.com">info@rumberoextremo.com</a>
                </p>

                <section class="social-media">
                    <h2>Encuéntranos en Redes Sociales</h2>
                    <div class="social-links">
                        <a href="https://facebook.com/rumberoextremo" target="_blank" aria-label="Facebook" class="facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com/rumberoextremo" target="_blank" aria-label="Twitter" class="twitter">
                            <i class="fab fa-x-twitter"></i>
                        </a>
                        <a href="https://instagram.com/rumberoextremo" target="_blank" aria-label="Instagram" class="instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://wa.me/XXXXXXXXXXX" target="_blank" aria-label="WhatsApp" class="whatsapp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="https://youtube.com/rumberoextremo" target="_blank" aria-label="YouTube" class="youtube">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </section>
            </div>
        </section>

        {{-- Google Map Section --}}
        <section class="google-map-section">
            <div class="container">
                <h2>Nuestra Ubicación</h2>
                <div class="map-container">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3923.1131231379854!2d-66.85645962548615!3d10.491747864381233!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8c2a595859fbfec3%3A0x59894936d2e9473!2sMulticentro%20Empresarial%20del%20Este!5e0!3m2!1ses-419!2sve!4v1753243832930!5m2!1ses-419!2sve"
                        width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
                <p>
                    Visítanos en nuestra oficina principal o encuéntranos en los eventos más exclusivos.
                </p>
            </div>
        </section>
    </main>
@endsection

@push('scripts')
    {{-- Agregamos el JS específico de esta vista --}}
    <script src="{{ asset('js/contact.js') }}"></script>
@endpush