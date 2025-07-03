<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard de Administración')</title> {{-- Título dinámico --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    {{-- Enlaza tus estilos CSS del dashboard. ESTE ARCHIVO DEBE CONTENER AHORA LOS ESTILOS DE LA TOOLBAR. --}}
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @yield('styles') {{-- Para estilos adicionales específicos de alguna página --}}
</head>

<body>
    {{-- ¡Aquí incluimos el sidebar desde su archivo parcial! --}}
    @include('layouts.nav.admin-sidebar')

    <div class="main-content">
        {{-- ¡Aquí es donde incluyes tu toolbar como un parcial! --}}
        @include('layouts.nav.admin-toolbar')

        {{-- El contenido específico de cada página se renderizará aquí --}}
        @yield('content')
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // *******************************************************************
            // Lógica para la barra de búsqueda GLOBAL en la toolbar
            // (Si la búsqueda del topbar es global y no local de cada tabla)
            // *******************************************************************
            const globalSearchInput = document.getElementById('globalSearchInput');
            if (globalSearchInput) {
                globalSearchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        const searchTerm = this.value.trim();
                        if (searchTerm) {
                            // Aquí puedes redirigir a una página de resultados de búsqueda global
                            // o disparar una función JavaScript para filtrar contenido dinámicamente.
                            // Ejemplo:
                            // window.location.href = `/admin/search?q=${encodeURIComponent(searchTerm)}`;
                            console.log('Realizando búsqueda global para:', searchTerm);
                            // En un entorno real, harías una llamada AJAX o una redirección.
                            // Para filtrar dinámicamente tablas, necesitarías un JS más avanzado
                            // que pueda comunicarse con los componentes de la página.
                        }
                    }
                });
            }
        });
    </script>
    {{-- Script para Chart.js (si aún no lo tienes) --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    {{-- ¡Tu script principal para el admin ahora se enlaza aquí! --}}
    <script src="{{ asset('js/admin-dashboard.js') }}"></script>

    @yield('scripts')
</body>

</html>
