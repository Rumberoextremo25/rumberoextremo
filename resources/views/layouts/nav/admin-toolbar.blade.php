{{-- resources/views/partials/admin-toolbar.blade.php --}}

<div class="topbar">
    <h1>@yield('page_title_toolbar', 'Panel Administractivo')</h1>
    <div class="search-profile">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Buscar..." id="globalSearchInput">
        </div>
        <div class="profile-info">
            {{-- Muestra el nombre del usuario autenticado (ejemplo) --}}
            <span style="margin-right: 10px; color: var(--secondary-color);">Hola, {{ Auth::user()->name ?? 'Administrador' }}</span>
            {{-- Puedes usar la imagen de perfil del usuario o un placeholder --}}
            <img src="{{ Auth::user()->avatar_url ?? 'assets/img/logos/usuario.png' }}" alt="Avatar de Usuario" class="avatar">
        </div>
    </div>
</div>