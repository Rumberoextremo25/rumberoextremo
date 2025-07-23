{{-- resources/views/layouts/partials/admin-toolbar.blade.php --}}

<header class="topbar">
    <h1>@yield('page_title_toolbar', 'Panel Administrativo')</h1>
    <div class="right-section"> {{-- Nuevo contenedor para flexibilidad --}}
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Buscar..." id="globalSearchInput">
        </div>
        {{-- Enlace clickeable para el perfil --}}
        <a href="{{ route('profile') }}" class="profile-link">
            <span class="user-name">Hola, {{ Auth::user()->name ?? 'Administrador' }}</span>
            {{-- Asegúrate de que Auth::user()->profile_photo_path exista y sea una URL válida --}}
            {{-- Si no tienes una foto de perfil, puedes usar un avatar predeterminado o las iniciales --}}
            <img src="{{ Auth::user()->profile_photo_path ?? asset('assets/img/default-avatar.png') }}" alt="Avatar de Usuario" class="avatar">
        </a>
    </div>
</header>