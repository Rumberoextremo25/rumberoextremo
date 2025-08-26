{{-- resources/views/about.blade.php --}}

@extends('layouts.app')

@section('title', 'Sobre Nosotros - Rumbero Extremo App')

@push('styles')
    {{-- Agregamos el CSS específico de esta vista --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Play:wght@400;700&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/about.css') }}">
@endpush

@section('content')
    <main class="main-content">
        <section class="hero-about">
            <div class="container">
                <h1>Conoce la Esencia de Rumbero Extremo</h1>
                <p>Somos tu planificador de recreación y entretenimiento, que cubre tus necesidades para divertirte y
                    disfrutar de las actividades, productos y servicios en el sitio que tu desees de manera interactiva,
                    disfrutando de los beneficios que nuestros Aliados Comerciales tienen para ti.
                </p>
            </div>
        </section>

        <section class="container purpose-section">
            <h2>Nuestro Propósito</h2>
            <p class="purpose-description">Ofrecer a los usuarios afiliados una opción interactiva que reúne los locales y
                lugares en tendencia, ofreciendo recreación y entretenimiento con beneficios que solo un Rumbero puede
                disfrutar. </p>
            <div class="purpose-cards-grid">
                <div class="purpose-card">
                    <i class="fa-solid fa-bullseye"></i>
                    <h3>Nuestra Misión</h3>
                    <p>Brindar una plataforma que facilite la búsqueda de lugares para salir, conocer y comprar,
                        transformando la experiencia del recreación y entretenimiento al conectar a los Rumberos con los
                        mejores eventos, lugares, productos y servicios de la ciudad.</p>
                </div>
                <div class="purpose-card">
                    <i class="fa-solid fa-eye"></i>
                    <h3>Nuestra Visión</h3>
                    <p>Aspiramos a ser la aplicación líder en entretenimiento recreación, fomentando una comunidad vibrante
                        y activa, que promueve locales y espacios más exclusivos y brindando diferentes alternativas en
                        actividades, productos y servicios con atractivos beneficios.</p>
                </div>
                <div class="purpose-card">
                    <i class="fa-solid fa-handshake"></i>
                    <h3>¿Qué Ofrecemos?</h3>
                    <p><strong>Para locales Comerciales:</strong> Incremento en la captación del público y mayor afluencia
                        referido por Rumbero Extremo.</p>
                    <p><strong>Para afiliados:</strong> Uso de la app para optar por atractivos beneficios mediante la
                        aplicación en los diferentes Aliados Comerciales. </p>
                </div>
            </div>
        </section>

        <section class="container team-section">
            <h2>La Fuerza Detrás de Nuestro Éxito</h2>
            <p class="team-description">
                En el corazón de nuestra organización reside un equipo de profesionales dedicados y apasionados, cuyo
                talento y
                compromiso son la base de todo lo que logramos. Cada miembro aporta una riqueza de experiencia, habilidades
                diversas y una visión unificada para superar expectativas y entregar resultados excepcionales. Creemos
                firmemente que la colaboración, la innovación y el deseo constante de aprender y mejorar son los pilares que
                nos
                impulsan hacia adelante. Es la sinergia de nuestro equipo lo que nos permite abordar desafíos complejos,
                desarrollar soluciones creativas y, en última instancia, ofrecer un valor inigualable a nuestros usuarios y
                aliados.
            </p>
        </section>
    </main>
@endsection

@push('scripts')
    {{-- Agregamos el JS específico de esta vista --}}
    <script src="{{ asset('js/about.js') }}"></script>
@endpush
