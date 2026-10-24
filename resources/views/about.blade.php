{{-- resources/views/about.blade.php --}}

@extends('layouts.app')

@section('title', 'Sobre Nosotros - Rumbero Extremo App')

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
                        <i class="fa-solid fa-fire"></i> Sobre nosotros
                    </span>

                    <h1 class="px-hero__title">
                        Conoce la esencia de <span>Rumbero Extremo</span>
                    </h1>

                    <p class="px-hero__text">
                        Somos tu planificador de recreación y entretenimiento: reunimos los lugares, productos y
                        servicios que buscas, con los beneficios que nuestros aliados comerciales tienen para ti.
                    </p>

                    <div class="px-hero__actions">
                        <a href="{{ route('register') }}" class="px-btn px-btn--primary">
                            <i class="fa-solid fa-user-plus"></i> Quiero ser Rumbero
                        </a>
                        <a href="{{ route('demo.aliado') }}" class="px-btn px-btn--ghost">
                            <i class="fa-solid fa-handshake"></i> Soy un local
                        </a>
                    </div>
                </div>

                <div class="px-hero__card">
                    <h3><i class="fa-solid fa-heart"></i> Lo que nos mueve</h3>
                    <ul class="px-hero__list">
                        <li><i class="fa-solid fa-circle-check"></i> Recreación y entretenimiento con beneficios reales</li>
                        <li><i class="fa-solid fa-circle-check"></i> Impulso a los locales y emprendimientos de la ciudad</li>
                        <li><i class="fa-solid fa-circle-check"></i> Una comunidad activa que disfruta y recomienda</li>
                    </ul>
                </div>
            </div>
        </section>

        {{-- ============================ PROPÓSITO ============================ --}}
        <section class="px-section">
            <div class="px-container">
                <div class="px-head">
                    <span class="px-head__eyebrow">Nuestro propósito</span>
                    <h2 class="px-head__title">Conectamos ganas de salir con los mejores lugares</h2>
                    <p class="px-head__text">
                        Ofrecer a los usuarios afiliados una opción interactiva que reúne los locales y lugares en
                        tendencia, con recreación, entretenimiento y beneficios que solo un Rumbero puede disfrutar.
                    </p>
                </div>

                <div class="px-grid">
                    <article class="px-card">
                        <span class="px-card__icon"><i class="fa-solid fa-bullseye"></i></span>
                        <h3 class="px-card__title">Nuestra misión</h3>
                        <p class="px-card__text">
                            Facilitar la búsqueda de lugares para salir, conocer y comprar, transformando la
                            experiencia de recreación al conectar a los Rumberos con los mejores eventos, locales,
                            productos y servicios de la ciudad.
                        </p>
                    </article>

                    <article class="px-card">
                        <span class="px-card__icon"><i class="fa-solid fa-eye"></i></span>
                        <h3 class="px-card__title">Nuestra visión</h3>
                        <p class="px-card__text">
                            Ser la aplicación líder en entretenimiento y recreación, fomentando una comunidad vibrante
                            que promueve locales y espacios más exclusivos, con alternativas atractivas en actividades,
                            productos y servicios.
                        </p>
                    </article>

                    <article class="px-card">
                        <span class="px-card__icon px-card__icon--gold"><i class="fa-solid fa-gift"></i></span>
                        <h3 class="px-card__title">¿Qué ofrecemos?</h3>
                        <p class="px-card__text">
                            <strong>Para locales comerciales:</strong> mayor captación de público y afluencia
                            referida por Rumbero Extremo.
                        </p>
                        <p class="px-card__text">
                            <strong>Para afiliados:</strong> una app para acceder a beneficios atractivos en los
                            distintos aliados comerciales.
                        </p>
                    </article>
                </div>
            </div>
        </section>

        {{-- ============================ EL EQUIPO ============================ --}}
        <section class="px-section px-section--muted">
            <div class="px-container">
                <div class="px-hero__inner" style="grid-template-columns:minmax(0,1fr) minmax(0,1fr);">
                    <div>
                        <div class="px-head px-head--left" style="margin-bottom:1rem;">
                            <span class="px-head__eyebrow">Nuestro equipo</span>
                            <h2 class="px-head__title">La fuerza detrás de nuestro éxito</h2>
                        </div>

                        <p class="px-hero__text" style="color:var(--px-muted);margin-bottom:.9rem;">
                            En el corazón de la organización hay un equipo de profesionales dedicados y apasionados,
                            cuyo talento y compromiso son la base de todo lo que logramos.
                        </p>

                        <p class="px-hero__text" style="color:var(--px-muted);margin-bottom:.9rem;">
                            Creemos firmemente que la colaboración, la innovación y el deseo constante de aprender
                            son los pilares que nos impulsan hacia adelante.
                        </p>

                        <p class="px-hero__text" style="color:var(--px-muted);">
                            Es la sinergia de nuestro equipo lo que nos permite abordar desafíos complejos y ofrecer
                            un valor inigualable a nuestros usuarios y aliados.
                        </p>
                    </div>

                    <div class="px-grid" style="grid-template-columns:repeat(auto-fit,minmax(min(100%,150px),1fr));">
                        <article class="px-card">
                            <span class="px-card__icon"><i class="fa-solid fa-users"></i></span>
                            <h3 class="px-card__title">Equipo</h3>
                            <p class="px-card__text">Talento diverso con una visión unificada.</p>
                        </article>

                        <article class="px-card">
                            <span class="px-card__icon"><i class="fa-solid fa-lightbulb"></i></span>
                            <h3 class="px-card__title">Innovación</h3>
                            <p class="px-card__text">Soluciones creativas para desafíos reales.</p>
                        </article>

                        <article class="px-card">
                            <span class="px-card__icon"><i class="fa-solid fa-handshake-angle"></i></span>
                            <h3 class="px-card__title">Colaboración</h3>
                            <p class="px-card__text">Crecemos junto a nuestros aliados.</p>
                        </article>

                        <article class="px-card">
                            <span class="px-card__icon"><i class="fa-solid fa-star"></i></span>
                            <h3 class="px-card__title">Excelencia</h3>
                            <p class="px-card__text">Superamos expectativas en cada detalle.</p>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        {{-- =============================== CTA =============================== --}}
        <section class="px-section">
            <div class="px-container">
                <div class="px-cta">
                    <h2>Sé parte de la comunidad Rumbera</h2>
                    <p>
                        Descubre lugares, activa descuentos y vive la ciudad con beneficios que solo un Rumbero
                        puede disfrutar.
                    </p>
                    <a href="{{ route('register') }}" class="px-btn px-btn--gold">
                        <i class="fa-solid fa-rocket"></i> Crear mi cuenta
                    </a>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/about.js') }}"></script>
@endpush
