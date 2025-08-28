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
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/toolbar.css') }}">


    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/profile.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/users.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/aliados.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/banner.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/commercial.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/promotion.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/payout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/reports.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/settings.css') }}">

    <link rel="stylesheet" href="{{ asset('css/admin/add-user-redesign.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/user-details.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/edit-user.css') }}">

    <link rel="stylesheet" href="{{ asset('css/admin/profile-update.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/aliados-form.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/banner-form.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/banner-edit.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/commercial.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/commercial-form.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/commercial-edit-form.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/promotion-create.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/promotion-edit.css') }}">

    {{-- Aquí se inyectarán los estilos específicos de cada página --}}
    @stack('styles')
</head>

<body> {{-- Puedes quitar 'dark-mode' para probar el modo claro --}}

    {{-- Contenido Principal --}}
    <main class="main-content">
        {{-- Sidebar --}}
        @include('layouts.nav.admin-sidebar')
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
