{{-- resources/views/demo.blade.php --}}

@extends('layouts.app')

@section('title', 'Demo - Rumbero Extremo App')

@section('content')
    <section class="content-section">
        <div class="container">
            <h1>Descubre Rumbero Extremo en Acción</h1>
            <p>Mira un breve video para entender cómo nuestra aplicación te ayuda a encontrar los mejores eventos y a
                planificar tus noches de fiesta.</p>

            <div class="form-buttons">
                <a href="{{ route('demo.aliado') }}" class="form-button">
                    <i class="fas fa-handshake"></i> Conviértete en Aliado
                </a>
                <a href="{{ route('demo.afiliado') }}" class="form-button">
                    <i class="fas fa-user-plus"></i> Regístrate como Afiliado
                </a>
            </div>

            <div class="video-container">
                <iframe width="560" height="315" src="https://www.youtube.com/embed/92ONIEfifGc?si=25olLhg_WeKYG8wd"
                    title="YouTube video player" frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            </div>
            <p style="margin-top: 30px;">¡Explora todas las funciones tú mismo después de registrarte!</p>
        </div>
    </section>
@endsection
