{{-- resources/views/contact.blade.php --}}

@extends('layouts.app')

@section('title', 'Contacto - Rumbero Extremo App')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/public-modern.css') }}?v={{ filemtime(public_path('css/public-modern.css')) }}">
@endpush

@section('content')
    <div class="px-page">

        {{-- ============================== HERO ============================== --}}
        <section class="px-hero">
            <div class="px-container px-hero__inner">
                <div>
                    <span class="px-hero__kicker">
                        <i class="fa-solid fa-headset"></i> Estamos para escucharte
                    </span>

                    <h1 class="px-hero__title">
                        Hablemos, <span>tu opinión nos mueve</span>
                    </h1>

                    <p class="px-hero__text">
                        ¿Tienes una idea, una sugerencia o una duda? Escríbenos y el equipo de Rumbero Extremo
                        te responderá a la brevedad posible.
                    </p>

                    <div class="px-hero__actions">
                        <a href="#formulario" class="px-btn px-btn--primary">
                            <i class="fa-solid fa-paper-plane"></i> Escribir un mensaje
                        </a>
                        <a href="mailto:rumberoextremo@gmail.com" class="px-btn px-btn--ghost">
                            <i class="fa-solid fa-envelope"></i> Correo directo
                        </a>
                    </div>
                </div>

                <div class="px-hero__card">
                    <h3><i class="fa-solid fa-clock"></i> Tiempos de respuesta</h3>
                    <ul class="px-hero__list">
                        <li><i class="fa-solid fa-circle-check"></i> Correo: menos de 24 horas hábiles</li>
                        <li><i class="fa-solid fa-circle-check"></i> Chat en vivo: unos minutos</li>
                        <li><i class="fa-solid fa-circle-check"></i> WhatsApp: atención inmediata</li>
                    </ul>
                </div>
            </div>
        </section>

        {{-- ======================= FORMULARIO + CONTACTO ======================= --}}
        <section class="px-section" id="formulario">
            <div class="px-container">
                <div class="px-form-grid">

                    {{-- Formulario --}}
                    <div class="px-form-card">
                        <div class="px-head px-head--left" style="margin-bottom:1.5rem;">
                            <span class="px-head__eyebrow">Formulario de contacto</span>
                            <h2 class="px-head__title">¡Tu experiencia merece ser compartida!</h2>
                            <p class="px-head__text">
                                Anímate a escribir tu comentario, sugerencia o idea. Leemos cada mensaje.
                            </p>
                        </div>

                        @if (session('success'))
                            <div class="px-alert px-alert--success">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>{{ session('success') }}</span>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="px-alert px-alert--error">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                <span>
                                    Revisa los campos marcados:
                                    <ul style="margin:.35rem 0 0;padding-left:1.1rem;">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </span>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('contact.store') }}" novalidate>
                            @csrf

                            <div class="px-row">
                                <div class="px-field">
                                    <label for="name">Nombre <span class="req">*</span></label>
                                    <input type="text" id="name" name="name" class="px-input @error('name') is-invalid @enderror"
                                        value="{{ old('name') }}" placeholder="Tu nombre" required>
                                    @error('name')
                                        <span class="px-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="px-field">
                                    <label for="email">Correo electrónico <span class="req">*</span></label>
                                    <input type="email" id="email" name="email" class="px-input @error('email') is-invalid @enderror"
                                        value="{{ old('email') }}" placeholder="tucorreo@ejemplo.com" required>
                                    @error('email')
                                        <span class="px-error">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="px-field">
                                <label for="subject">Asunto</label>
                                <input type="text" id="subject" name="subject"
                                    class="px-input @error('subject') is-invalid @enderror"
                                    value="{{ old('subject') }}" placeholder="¿Sobre qué quieres escribirnos?">
                                @error('subject')
                                    <span class="px-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="px-field">
                                <label for="message_content">Mensaje <span class="req">*</span></label>
                                <textarea id="message_content" name="message_content"
                                    class="px-textarea @error('message_content') is-invalid @enderror"
                                    placeholder="Cuéntanos con detalle…" required>{{ old('message_content') }}</textarea>
                                @error('message_content')
                                    <span class="px-error">{{ $message }}</span>
                                @enderror
                                <span class="px-hint">Responderemos al correo que nos dejes aquí.</span>
                            </div>

                            <button type="submit" class="px-btn px-btn--primary px-btn--block">
                                <i class="fa-solid fa-paper-plane"></i> Enviar mensaje
                            </button>
                        </form>
                    </div>

                    {{-- Datos de contacto directo --}}
                    <aside>
                        <h3 class="px-card__title" style="margin-bottom:.9rem;">Contacto directo</h3>

                        <ul class="px-contact-list">
                            <li class="px-contact-item">
                                <i class="fa-solid fa-envelope"></i>
                                <div>
                                    <strong>Correo</strong>
                                    <a href="mailto:rumberoextremo@gmail.com">rumberoextremo@gmail.com</a>
                                </div>
                            </li>

                            <li class="px-contact-item">
                                <i class="fa-solid fa-phone"></i>
                                <div>
                                    <strong>Teléfono</strong>
                                    <a href="tel:+584124992125">+58 412-499-2125</a>
                                </div>
                            </li>

                            <li class="px-contact-item">
                                <i class="fa-brands fa-whatsapp"></i>
                                <div>
                                    <strong>WhatsApp</strong>
                                    <a href="https://wa.me/584124992125?text=Hola%2C%20vengo%20de%20la%20web%20de%20Rumbero%20Extremo"
                                        target="_blank" rel="noopener">Escribir por WhatsApp</a>
                                </div>
                            </li>

                            <li class="px-contact-item">
                                <i class="fa-solid fa-location-dot"></i>
                                <div>
                                    <strong>Oficina</strong>
                                    <span>Multicentro Empresarial del Este, Caracas</span>
                                </div>
                            </li>
                        </ul>

                        <div class="px-social">
                            <a href="https://facebook.com/rumberoextremo" target="_blank" rel="noopener">
                                <i class="fa-brands fa-facebook-f"></i> Facebook
                            </a>
                            <a href="https://instagram.com/rumberoextremo" target="_blank" rel="noopener">
                                <i class="fa-brands fa-instagram"></i> Instagram
                            </a>
                        </div>
                    </aside>
                </div>
            </div>
        </section>

        {{-- =============================== MAPA =============================== --}}
        <section class="px-section px-section--muted">
            <div class="px-container">
                <div class="px-head">
                    <span class="px-head__eyebrow">Visítanos</span>
                    <h2 class="px-head__title">Nuestra ubicación</h2>
                </div>

                <div class="px-media px-media--map">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3923.1131231379854!2d-66.85645962548615!3d10.491747864381233!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8c2a595859fbfec3%3A0x59894936d2e9473!2sMulticentro%20Empresarial%20del%20Este!5e0!3m2!1ses-419!2sve!4v1753243832930!5m2!1ses-419!2sve"
                        title="Ubicación de Rumbero Extremo" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/contact.js') }}"></script>
@endpush
