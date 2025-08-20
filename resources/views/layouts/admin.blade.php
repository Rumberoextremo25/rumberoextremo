<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - Rumbero Extremo</title>

    {{-- Font Awesome para iconos --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    {{-- Google Font: Inter --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Enlaza tu archivo CSS principal sin Laravel Mix --}}
    <link rel="stylesheet" href="{{ asset('css/admin/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/users.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/banner.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/toolbar.css') }}">

    {{-- Aquí se inyectarán los estilos específicos de cada página --}}
    @stack('styles')
</head>
<body> {{-- Puedes quitar 'dark-mode' para probar el modo claro --}}

    {{-- Sidebar --}}
    @include('layouts.nav.admin-sidebar')

    {{-- Contenido Principal --}}
    <main class="main-content">
        {{-- Barra Superior (Toolbar) --}}
        @include('layouts.nav.admin-toolbar')

        {{-- Contenido Específico de la Página --}}
        <section class="page-content">
            @yield('content')
        </section>
    </main>

    {{-- Aquí se inyectarán los scripts específicos de cada página --}}
    @stack('scripts')
</body>
</html>