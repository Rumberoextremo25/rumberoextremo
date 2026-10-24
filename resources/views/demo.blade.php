{{-- resources/views/demo.blade.php --}}

@extends('layouts.app')

@section('title', 'Demo - Rumbero Extremo App')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/public-modern.css') }}?v={{ filemtime(public_path('css/public-modern.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/demo-videos.css') }}?v={{ filemtime(public_path('css/demo-videos.css')) }}">
@endpush

@section('content')
    <div class="px-page">

        {{-- ============================== HERO ============================== --}}
        <section class="px-hero">
            <div class="px-container px-hero__inner">
                <div>
                    <span class="px-hero__kicker">
                        <i class="fa-solid fa-bolt"></i> Demo interactiva
                    </span>

                    <h1 class="px-hero__title">
                        Vive la experiencia <span>Rumbero Extremo</span>
                    </h1>

                    <p class="px-hero__text">
                        Mira cómo funciona la app: descubre lugares, activa descuentos
                        y disfruta los beneficios de nuestros aliados comerciales.
                    </p>

                    <div class="px-hero__actions">
                        <a href="#videos" class="px-btn px-btn--primary">
                            <i class="fa-solid fa-play"></i> Ver el demo
                        </a>
                        <a href="{{ route('register') }}" class="px-btn px-btn--ghost">
                            <i class="fa-solid fa-user-plus"></i> Crear mi cuenta
                        </a>
                    </div>
                </div>

                <div class="px-hero__card">
                    <h3><i class="fa-solid fa-list-check"></i> Lo que verás</h3>
                    <ul class="px-hero__list">
                        <li><i class="fa-solid fa-circle-check"></i> Cómo buscar lugares y eventos cerca de ti</li>
                        <li><i class="fa-solid fa-circle-check"></i> Activación de descuentos en segundos</li>
                        <li><i class="fa-solid fa-circle-check"></i> Registro de aliados comerciales</li>
                        <li><i class="fa-solid fa-circle-check"></i> Beneficios exclusivos para Rumberos</li>
                    </ul>
                </div>
            </div>
        </section>

        {{-- ============================ ELIGE TU ROL ============================ --}}
        <section class="px-section">
            <div class="px-container">
                <div class="px-head">
                    <span class="px-head__eyebrow">Elige tu camino</span>
                    <h2 class="px-head__title">¿Cómo quieres empezar?</h2>
                    <p class="px-head__text">
                        Regístrate como aliado para atraer más público, o como rumbero para empezar a disfrutar
                        los descuentos.
                    </p>
                </div>

                <div class="px-grid px-grid--2">
                    <a href="{{ route('demo.aliado') }}" class="px-card">
                        <span class="px-card__icon"><i class="fa-solid fa-handshake"></i></span>
                        <h3 class="px-card__title">Soy Aliado Comercial</h3>
                        <p class="px-card__text">
                            Suma tu local a Rumbero Extremo, atrae nuevo público y gestiona tus propias promociones.
                        </p>
                        <span class="px-btn px-btn--light px-btn--block" style="margin-top:auto;">
                            <i class="fa-solid fa-arrow-right"></i> Ver demo de aliado
                        </span>
                    </a>

                    <a href="{{ route('demo.afiliado') }}" class="px-card">
                        <span class="px-card__icon px-card__icon--gold"><i class="fa-solid fa-user-group"></i></span>
                        <h3 class="px-card__title">Soy Rumbero</h3>
                        <p class="px-card__text">
                            Descubre locales, activa descuentos y lleva tus salidas al siguiente nivel.
                        </p>
                        <span class="px-btn px-btn--light px-btn--block" style="margin-top:auto;">
                            <i class="fa-solid fa-arrow-right"></i> Ver demo de rumbero
                        </span>
                    </a>
                </div>
            </div>
        </section>

        {{-- ============================== VIDEOS ============================== --}}
        <section class="px-section px-section--muted" id="videos">
            <div class="px-container">
                <div class="px-head">
                    <span class="px-head__eyebrow">Un minuto de tu tiempo</span>
                    <h2 class="px-head__title">Descubre Rumbero Extremo en acción</h2>
                    <p class="px-head__text">Cómo registrarte como aliado comercial, como rumbero y todo lo que puedes hacer.</p>
                </div>

                @php $firstVideo = $videos->first(); @endphp

                <div class="px-video-carousel" data-video-carousel>
                    <div class="px-video-carousel__stage">
                        @foreach ($videos as $video)
                            @php $isFirst = $loop->first; @endphp
                            <div class="px-video-slide {{ $isFirst ? 'is-active' : '' }}"
                                data-video-slide
                                data-index="{{ $loop->index }}"
                                data-title="{{ $video->title }}"
                                data-description="{{ $video->description ?? '' }}">
                                @if ($video->is_youtube && $video->embed_url)
                                    <iframe data-src="{{ $video->embed_url }}"
                                        @if ($isFirst) src="{{ $video->embed_url }}" @endif
                                        title="{{ $video->title }}"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                        referrerpolicy="strict-origin-when-cross-origin"
                                        allowfullscreen loading="lazy"></iframe>
                                @elseif ($video->video_src)
                                    <video data-src="{{ $video->video_src }}" controls playsinline preload="none"
                                        @if ($isFirst) src="{{ $video->video_src }}" @endif
                                        @if ($video->thumbnail_src) poster="{{ $video->thumbnail_src }}" @endif>
                                        Tu navegador no soporta la reproducción de video.
                                    </video>
                                @else
                                    <div class="px-video-empty">
                                        <i class="fa-solid fa-video-slash" aria-hidden="true"></i>
                                        <p>Este video no está disponible por ahora.</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="px-video-carousel__bar">
                        <div class="px-video-carousel__info" aria-live="polite">
                            <span class="px-video-carousel__eyebrow">
                                <i class="fa-solid fa-circle-play" aria-hidden="true"></i>
                                Ahora viendo
                                <span data-video-counter>1 / {{ $videos->count() }}</span>
                            </span>
                            <h3 data-video-title>{{ $firstVideo?->title }}</h3>
                            <p data-video-description>{{ $firstVideo?->description }}</p>
                        </div>

                        <div class="px-video-carousel__controls">
                            <button type="button" class="px-video-nav" data-video-prev
                                aria-label="Video anterior">
                                <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                            </button>

                            <div class="px-video-dots" role="tablist" aria-label="Seleccionar video">
                                @foreach ($videos as $video)
                                    <button type="button" role="tab"
                                        class="px-video-dot {{ $loop->first ? 'is-active' : '' }}"
                                        data-video-dot="{{ $loop->index }}"
                                        aria-label="Ver video {{ $loop->iteration }}: {{ $video->title }}"
                                        aria-selected="{{ $loop->first ? 'true' : 'false' }}"></button>
                                @endforeach
                            </div>

                            <button type="button" class="px-video-nav" data-video-next
                                aria-label="Video siguiente">
                                <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>

                    <div class="px-video-carousel__thumbs" aria-label="Videos de la demo">
                        @foreach ($videos as $video)
                            <button type="button"
                                class="px-video-thumb {{ $loop->first ? 'is-active' : '' }}"
                                data-video-thumb="{{ $loop->index }}"
                                aria-label="Ver {{ $video->title }}">
                                <span class="px-video-thumb__media">
                                    @if ($video->thumbnail_src)
                                        <img src="{{ $video->thumbnail_src }}" alt="" loading="lazy">
                                    @else
                                        <i class="fa-solid fa-video" aria-hidden="true"></i>
                                    @endif
                                    <span class="px-video-thumb__play">
                                        <i class="fa-solid fa-play" aria-hidden="true"></i>
                                    </span>
                                </span>
                                <span class="px-video-thumb__title">{{ $video->title }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- ============================== PASOS ============================== --}}
        <section class="px-section">
            <div class="px-container">
                <div class="px-head">
                    <span class="px-head__eyebrow">Así de fácil</span>
                    <h2 class="px-head__title">Tu cuenta en tres pasos</h2>
                </div>

                <div class="px-grid">
                    <article class="px-step">
                        <span class="px-step__number">01</span>
                        <h3><i class="fa-solid fa-user-plus"></i> Regístrate</h3>
                        <p>Crea tu cuenta como rumbero o aliado comercial en menos de dos minutos.</p>
                    </article>

                    <article class="px-step">
                        <span class="px-step__number">02</span>
                        <h3><i class="fa-solid fa-tags"></i> Activa beneficios</h3>
                        <p>Explora promociones y activa los descuentos disponibles en cada local.</p>
                    </article>

                    <article class="px-step">
                        <span class="px-step__number">03</span>
                        <h3><i class="fa-solid fa-music"></i> Disfruta</h3>
                        <p>Presenta tu QR en el aliado y aprovecha los beneficios que solo un Rumbero tiene.</p>
                    </article>
                </div>
            </div>
        </section>

        {{-- =============================== CTA =============================== --}}
        <section class="px-section" style="padding-top:0;">
            <div class="px-container">
                <div class="px-cta">
                    <h2>¿Listo para la rumba?</h2>
                    <p>Explora todas las funciones tú mismo después de registrarte. Es gratis y toma menos de dos minutos.</p>
                    <a href="{{ route('register') }}" class="px-btn px-btn--gold">
                        <i class="fa-solid fa-rocket"></i> Empezar ahora
                    </a>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/demo.js') }}"></script>
    <script src="{{ asset('js/demo-videos.js') }}?v={{ filemtime(public_path('js/demo-videos.js')) }}"></script>
@endpush
