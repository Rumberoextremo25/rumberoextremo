{{-- resources/views/layouts/partials/admin-toolbar.blade.php --}}

<!-- El `header` principal con el nuevo diseño -->
<header class="admin-toolbar">
    <link rel="stylesheet" href="{{ asset('css/toolbar.css') }}">
    <div class="left-section">
        <h1 class="page-title-toolbar">@yield('page_title', 'Panel Administrativo')</h1>
    </div>
    <div class="right-section">
        <!-- El cuadro de búsqueda con un diseño moderno -->
        <div class="search-box-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" placeholder="Buscar..." id="globalSearchInput" class="search-input">
        </div>
        
        <!-- Enlace de perfil con la imagen y el nombre del usuario -->
        <a href="{{ route('profile') }}" class="profile-link">
            <span class="user-name">Hola, {{ Auth::user()->name ?? 'Administrador' }}</span>
            <!-- Usa la ruta de la foto de perfil o un avatar predeterminado -->
            <img src="{{ Auth::user()->profile_photo_path ?? asset('assets/img/default-avatar.png') }}" alt="Avatar de Usuario" class="avatar">
        </a>
    </div>
</header>