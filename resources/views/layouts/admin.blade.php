@php
    /**
     * Layout base del panel administrativo.
     *
     * Modo oscuro: se resuelve en el servidor (sin parpadeo y sin errores de JS,
     * porque antes se intentaba tocar document.body desde el <head>).
     */
    $darkMode = auth()->check() && (bool) auth()->user()->dark_mode_enabled;

    /**
     * CSS del shell. Se versionan con filemtime para invalidar caché en cada
     * despliegue y se cargan en orden de capas:
     *   design-system -> shell -> CSS de página -> dark-mode -> modern
     */
    $shellStyles = [
        'css/admin/design-system.css',
        'css/admin.css',
        'css/sidebar.css',
        'css/toolbar.css',
        'css/admin/bootstrap-compat.css',
        'css/dashboard.css',
        'css/admin/profile.css',
        'css/admin/users.css',
        'css/admin/aliados.css',
        'css/admin/banner.css',
        'css/admin/commercial.css',
        'css/admin/promotion.css',
        'css/admin/payout.css',
        'css/admin/reports.css',
        'css/admin/settings.css',
        'css/admin/add-user-redesign.css',
        'css/admin/user-details.css',
        'css/admin/edit-user.css',
        'css/admin/profile-update.css',
        'css/admin/aliados-form.css',
        'css/admin/banner-create.css',
        'css/admin/banner-edit.css',
        'css/admin/commercial-form.css',
        'css/admin/commercial-ally-edit.css',
        'css/admin/promotion-create.css',
        'css/admin/promotion-edit.css',
    ];

    $assetVersion = static function (string $relative): string {
        $path = public_path($relative);

        return is_file($path) ? (string) filemtime($path) : '1';
    };
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $darkMode ? 'dark-mode' : '' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Barra de estado del iPhone con el color de marca --}}
    <meta name="theme-color" content="#1A123C">
    <meta name="color-scheme" content="{{ $darkMode ? 'dark' : 'light' }}">
    <meta name="robots" content="noindex, nofollow, noarchive">

    {{-- PWA / iOS --}}
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Rumbero Admin">
    <meta name="format-detection" content="telephone=no">

    <title>@yield('title', 'Panel') · Rumbero Extremo</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/logo_sidebar.png') }}">

    {{-- Conexiones tempranas: recortan el primer render --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>

    {{-- Iconografía + tipografía (una sola carga para todo el panel) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        referrerpolicy="no-referrer">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap">

    {{-- Capa 1 y 2: tokens, reset, shell y hojas por módulo --}}
    @foreach ($shellStyles as $style)
        <link rel="stylesheet" href="{{ asset($style) }}?v={{ $assetVersion($style) }}">
    @endforeach

    {{-- Estilos propios de cada página --}}
    @stack('styles')

    {{-- Capa 3: modo oscuro (compatibilidad con el toggle existente) --}}
    <link rel="stylesheet" href="{{ asset('css/admin/dark-mode.css') }}?v={{ $assetVersion('css/admin/dark-mode.css') }}">

    {{-- Capa 4: sistema de componentes + responsive + ajustes iOS --}}
    <link rel="stylesheet" href="{{ asset('css/admin/modern.css') }}?v={{ $assetVersion('css/admin/modern.css') }}">

    {{-- Capa 5: ajustes por módulo (asistente, ficha de aliado, resumen…) --}}
    <link rel="stylesheet" href="{{ asset('css/admin/pages.css') }}?v={{ $assetVersion('css/admin/pages.css') }}">

    {{-- Comportamiento del shell (drawer, dropdowns, tablas, foco) --}}
    <script src="{{ asset('js/admin-shell.js') }}?v={{ $assetVersion('js/admin-shell.js') }}" defer></script>
</head>

<body class="admin-body {{ $darkMode ? 'dark-mode' : '' }} @yield('body_class')">

    {{-- Accesibilidad: salta el sidebar con el teclado --}}
    <a href="#pageContent" class="rx-skip-link">Saltar al contenido principal</a>

    <div class="admin-shell">
        {{-- Sidebar / drawer --}}
        @include('layouts.nav.admin-sidebar')

        <div class="main-content">
            {{-- Barra superior --}}
            @include('layouts.nav.admin-toolbar')

            {{-- Contenido de la página --}}
            <main id="pageContent" class="page-content" tabindex="-1">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- Scripts propios de cada página --}}
    @stack('scripts')
</body>

</html>
