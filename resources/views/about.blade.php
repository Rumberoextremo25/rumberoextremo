{{-- resources/views/about.blade.php --}}

@extends('layouts.app')

@section('title', 'Sobre Nosotros - Rumbero Extremo App')

@push('styles')
    {{-- Agregamos el CSS específico de esta vista --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Poppins:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/about.css') }}">
@endpush

@section('content')
    <section class="hero-about">
        <div class="container">
            <h1>Conoce la Esencia de Rumbero Extremo</h1>
            <p>Somos el motor que impulsa la vida nocturna en Venezuela, conectando pasión por la fiesta con experiencias
                inolvidables. En Rumbero Extremo, creemos que cada noche es una oportunidad para crear recuerdos únicos y vivir al máximo.</p>
        </div>
    </section>

    ---

    <section class="container purpose-section">
        <h2>Nuestro Propósito</h2>
        <p class="purpose-description">En Rumbero Extremo, nos impulsa una misión clara y una visión ambiciosa para transformar la manera en que disfrutas la vida nocturna.</p>
        <div class="purpose-cards-grid">
            <div class="purpose-card">
                <i class="fas fa-hand-holding-heart"></i>
                <h3>Nuestra Misión</h3>
                <p>Brindar una plataforma que facilite la búsqueda de lugares para salir y conocer,
                    transformando la experiencia social al conectar a los usuarios con los mejores
                    eventos y lugares de entretenimiento en la ciudad.</p>
            </div>
            <div class="purpose-card">
                <i class="fas fa-eye"></i>
                <h3>Nuestra Visión</h3>
                <p>Ofrecer a un target adulto contemporáneo la única opción de una aplicación que reúne los locales y
                    lugares en tendencia, ofreciendo diversión con descuentos.
                    -Aspiramos a ser la aplicación líder en la movida del entretenimiento, fomentando una comunidad
                    vibrante y activa, que promueve locales y espacios más exclusivos y brindando diferentes alternativas. </p>
            </div>
            <div class="purpose-card">
                <i class="fas fa-lightbulb"></i>
                <h3>¿Qué Ofrecemos?</h3>
                <p><strong>Para locales afiliados:</strong> Incremento en la captación
                    del público y mayor afluencia referido por Rumbero
                    Extremo.</p>
                <p><strong>Para afiliados:</strong> Uso de la app para optar por los
                    descuentos mediante la aplicación código QR en
                    los diferentes aliados comerciales. </p>
            </div>
        </div>
    </section>

    ---

    <section class="container team-section">
        <h2>La Fuerza Detrás de Nuestro Éxito</h2>
        <p class="team-description">
            En el corazón de nuestra organización reside un equipo de profesionales dedicados y apasionados, cuyo talento y
            compromiso son la base de todo lo que logramos. Cada miembro aporta una riqueza de experiencia, habilidades
            diversas y una visión unificada para superar expectativas y entregar resultados excepcionales. Creemos
            firmemente que la colaboración, la innovación y el deseo constante de aprender y mejorar son los pilares que nos
            impulsan hacia adelante. Es la sinergia de nuestro equipo lo que nos permite abordar desafíos complejos,
            desarrollar soluciones creativas y, en última instancia, ofrecer un valor inigualable a nuestros usuarios y aliados.
        </p>
        <div class="team-grid">
            {{-- Puedes agregar aquí imágenes y nombres de los miembros del equipo si lo deseas --}}
            {{-- Ejemplo:
            <div class="team-member-card">
                <img src="{{ asset('images/team/member1.jpg') }}" alt="Nombre Miembro 1">
                <h4>Nombre Miembro 1</h4>
                <p>CEO</p>
            </div>
            --}}
        </div>
    </section>
@endsection

@push('scripts')
    {{-- Agregamos el JS específico de esta vista --}}
    <script src="{{ asset('js/about.js') }}"></script>
@endpush
