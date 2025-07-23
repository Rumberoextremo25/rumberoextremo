<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Título de la página --}}
    <title>@yield('title', config('app.name', 'Laravel App'))</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    {{-- Styles --}}
    {{-- La directiva @vite se encarga de incluir resources/css/app.css --}}
    @vite(['public/css/app.css', 'public/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body class="antialiased">
    <div id="app">

        {{-- Importar la Navbar --}}
        @include('layouts.nav.navbar') {{-- Asumo que la ruta es 'layouts.partials.navbar' --}}

        {{-- Contenido principal de la página --}}
        <main class="py-4">
            @yield('content')
        </main>

        {{-- Importar el Footer --}}
        @include('layouts.footer') {{-- Asumo que la ruta es 'layouts.partials.footer' --}}

    </div>

    {{-- Scripts --}}
    @stack('scripts')
</body>
</html>
