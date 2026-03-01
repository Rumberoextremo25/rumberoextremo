@extends('layouts.guest')

@section('title', 'Iniciar Sesión - Rumbero Extremo')

@section('content')
<div class="login-wrapper">
    @vite(['resources/css/login.css'])
    
    {{-- Elementos decorativos de fondo --}}
    <div class="bg-particles"></div>
    <div class="bg-glow"></div>
    
    {{-- Tarjeta de login principal --}}
    <div class="login-card-modern">
        
        {{-- Header con logo y título --}}
        <div class="login-header-modern">
            <div class="logo-container">
                <img src="{{ asset('assets/img/login/IMG_4254.png') }}" alt="Rumbero Extremo" class="login-logo-modern">
            </div>
            <h1 class="login-title">
                <span class="title-extremo">EXTREMO</span>
                <span class="title-welcome">Bienvenido de vuelta</span>
            </h1>
            <p class="login-subtitle">Ingresa tus credenciales para continuar la fiesta</p>
        </div>
        
        {{-- Mensaje de estado --}}
        @if (session('status'))
            <div class="alert-modern alert-success">
                <svg class="alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('status') }}
            </div>
        @endif
        
        {{-- Formulario de login --}}
        <form method="POST" action="{{ route('login') }}" class="form-modern">
            @csrf
            
            {{-- Campo Email --}}
            <div class="form-group-modern">
                <label for="email" class="form-label">
                    <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Correo Electrónico
                </label>
                <div class="input-wrapper-modern">
                    <input id="email" type="email" name="email" value="{{ old('email') }}" 
                           required autofocus autocomplete="username"
                           class="form-input-modern @error('email') is-invalid @enderror" 
                           placeholder="tu.correo@rumbero.com">
                    <div class="input-border"></div>
                </div>
                @error('email')
                    <span class="error-message">
                        <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $message }}
                    </span>
                @enderror
            </div>
            
            {{-- Campo Contraseña --}}
            <div class="form-group-modern">
                <label for="password" class="form-label">
                    <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Contraseña
                </label>
                <div class="input-wrapper-modern">
                    <input id="password" type="password" name="password" 
                           required autocomplete="current-password"
                           class="form-input-modern @error('password') is-invalid @enderror" 
                           placeholder="••••••••">
                    <button type="button" class="password-toggle-modern" onclick="togglePassword()">
                        <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                    <div class="input-border"></div>
                </div>
                @error('password')
                    <span class="error-message">
                        <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $message }}
                    </span>
                @enderror
            </div>
            
            {{-- Opciones del formulario --}}
            <div class="form-options-modern">
                <label for="remember_me" class="checkbox-modern">
                    <input type="checkbox" id="remember_me" name="remember" class="checkbox-input">
                    <span class="checkbox-custom"></span>
                    <span class="checkbox-label">Recuérdame</span>
                </label>
                
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="forgot-link-modern">
                        ¿Olvidaste tu contraseña?
                        <svg class="link-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                @endif
            </div>
            
            {{-- Botón de inicio de sesión --}}
            <button type="submit" class="btn-login-modern">
                <span>Iniciar Sesión</span>
                <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </button>
            
            {{-- Link de registro --}}
            <div class="register-link-modern">
                <p>¿No tienes cuenta? <a href="{{ route('register') }}">Únete a la rumba</a></p>
            </div>
        </form>
    </div>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    
    // Animar el botón (opcional)
    const button = event.currentTarget;
    button.classList.add('active');
    setTimeout(() => button.classList.remove('active'), 200);
}
</script>
@endsection