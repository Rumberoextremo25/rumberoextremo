<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Rumbero Extremo'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/public-modern.css') }}?v={{ filemtime(public_path('css/public-modern.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}?v={{ filemtime(public_path('css/auth.css')) }}">

    @stack('styles')
</head>

<body class="px-auth">
    <div class="px-auth__shell">

        {{-- ===================== PANEL DE MARCA ===================== --}}
        <aside class="px-auth__brand">
            <a href="{{ url('/') }}" class="px-auth__logo" aria-label="Rumbero Extremo — Inicio">
                <img src="{{ asset('assets/img/IMG_4254.png') }}" alt="Rumbero Extremo">
            </a>

            <div class="px-auth__brand-content">
                <span class="px-auth__kicker">
                    <i class="fa-solid fa-bolt"></i> Recreación y entretenimiento
                </span>

                <h2 class="px-auth__brand-title">
                    Tu próxima salida <span>empieza aquí</span>
                </h2>

                <p class="px-auth__brand-text">
                    Descubre los lugares en tendencia, activa descuentos exclusivos y disfruta los
                    beneficios de nuestros aliados comerciales.
                </p>

                <ul class="px-auth__bullets">
                    <li><i class="fa-solid fa-circle-check"></i> Descuentos y promociones exclusivas</li>
                    <li><i class="fa-solid fa-circle-check"></i> Eventos y locales en tendencia</li>
                    <li><i class="fa-solid fa-circle-check"></i> Todo desde tu teléfono, con QR</li>
                </ul>
            </div>

            <p class="px-auth__brand-foot">
                <i class="fa-solid fa-heart"></i> Hecho en Venezuela
            </p>
        </aside>

        {{-- ===================== PANEL DEL FORMULARIO ===================== --}}
        <main class="px-auth__panel">
            <div class="px-auth__card">
                @yield('content')
            </div>

            <p class="px-auth__legal">
                <a href="{{ url('/terms') }}">Términos</a> ·
                <a href="{{ url('/privacy') }}">Privacidad</a> ·
                <a href="{{ url('/cookies') }}">Cookies</a>
            </p>
        </main>
    </div>

    @stack('scripts')
</body>

</html>
