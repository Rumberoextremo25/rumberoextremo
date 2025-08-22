{{-- resources/views/demo.blade.php --}}

@extends('layouts.app')

@section('title', 'Demo - Rumbero Extremo App')

@push('styles')
    {{-- Agregamos el CSS específico de esta vista --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Play:wght@400;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('css/demo.css') }}">
@endpush

@section('content')
    <main class="main-content">
        <section class="hero-demo">
            <div class="container">
                <h1>Vive la Experiencia Rumbero Extremo</h1>
                <p>Nuestra aplicación conecta a la comunidad fiestera de Venezuela con los lugares y eventos más exclusivos, ¡con beneficios increíbles!</p>
            </div>
        </section>

        <section class="content-section">
            <div class="container">
                <h1>Descubre Rumbero Extremo en Acción</h1>
                <p>Mira un breve video para entender cómo nuestra aplicación te ayuda a encontrar los mejores eventos y a
                    planificar tus noches de fiesta.</p>

                <div class="form-buttons">
                    <a href="{{ route('demo.aliado') }}" class="form-button">
                        <i class="fas fa-handshake"></i> Regístrate como Aliado Comercial
                    </a>
                    <a href="{{ route('demo.afiliado') }}" class="form-button">
                        <i class="fas fa-user-plus"></i> Regístrate como Rumbero
                    </a>
                </div>

                <div class="video-container">
                    <iframe width="560" height="315" src="https://www.youtube.com/embed/dQw4w9WgXcQ"
                        title="YouTube video player" frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                </div>
                <p style="margin-top: 30px;">¡Explora todas las funciones tú mismo después de registrarte!</p>
            </div>
        </section>
    </main>
@endsection

@push('scripts')
    {{-- Agregamos el JS específico de esta vista --}}
    <script src="{{ asset('js/demo.js') }}"></script>
@endpush

