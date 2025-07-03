{{-- resources/views/about.blade.php --}}

@extends('layouts.app')

@section('title', 'Sobre Nosotros - Rumbero Extremo App')

@section('content')
    <section class="hero-about">
        <div class="container">
            <h1>Conoce la Esencia de Rumbero Extremo</h1>
            <p>Somos el motor que impulsa la vida nocturna en Venezuela, conectando pasión por la fiesta con experiencias
                inolvidables.</p>
        </div>
    </section>

    <section class="container about-sections">
        <div class="about-card">
            <i class="fas fa-hand-holding-heart"></i>
            <h2>Nuestra Misión</h2>
            <p>Brindar una plataforma que facilite la busqueda de lugares para salir de noche, transformando la experiencia
                nocturna al conectar a los usuarios con los mejores eventos y lugares de entretenimientos en la ciudad.</p>
        </div>
        <div class="about-card">
            <i class="fas fa-eye"></i>
            <h2>Nuestra Visión</h2>
            <p>Ofrecer a un target adulto comtemporaneo la unica opcion de una aplicación que reune los locales nocturnos en
                tendencias, ofreciendo diversion con descuentos </p>

            <p> aspiramos ser la aplicación lider en la movida nocturna, fomentando una comunidad vibrante y activa, que
                promueve el entretenimiento en los locales más exclusivos y brindando diferentes alternativas.
            </p>
        </div>
        <div class="about-card">
            <i class="fas fa-lightbulb"></i>
            <h2>¿Que Ofrecemos?</h2>
            <p>Para Locales Afiliados: Incremento en la Captación del publico y mayor afluencia referido por Rumbero
                Extremo.</p>
            <p>Afiliados: Uso de la App para optar por los descuentos mediante la aplicación de Escaneo de Codigo QR en los
                diferentes aliados comerciales. </p>
        </div>
    </section>

    <section class="container team-section">
        <h2>Nuestro Equipo Apasionado</h2>
        <div class="team-grid">
            <div class="team-member">
                <h3>Julio Fermín</h3>
                <p>Fundador</p>
                <div class="member-social">
                    <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
            <div class="team-member">
                <h3>Andreina Romero</h3>
                <p>CEO</p>
                <div class="member-social">
                    <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
            <div class="team-member">
                <h3>Ing. Herbert Diaz</h3>
                <p>Ingeniero de Software</p>
                <div class="member-social">
                    <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
            <div class="team-member">
                <h3>Delia Marquez</h3>
                <p>Estrategia de Marketing</p>
                <div class="member-social">
                    <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
        </div>
    </section>
@endsection
