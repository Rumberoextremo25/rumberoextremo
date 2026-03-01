@extends('layouts.guest')

@section('title', 'Registro - Rumbero Extremo')

@section('content')
<div class="register-wrapper">
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
    
    {{-- Elementos decorativos de fondo --}}
    <div class="bg-particles"></div>
    <div class="bg-glow"></div>
    
    {{-- Tarjeta de registro principal --}}
    <div class="register-card-modern">
        
        {{-- Header con logo y título --}}
        <div class="register-header-modern">
            <div class="logo-container">
                <img src="{{ asset('assets/img/login/IMG_4254.png') }}" alt="Rumbero Extremo" class="register-logo-modern">
            </div>
            <h1 class="register-title">
                <span class="title-extremo">EXTREMO</span>
                <span class="title-welcome">Únete a la Rumba</span>
            </h1>
            <p class="register-subtitle">Completa tus datos para ser parte de la comunidad</p>
        </div>
        
        {{-- Formulario de registro --}}
        <form method="POST" action="{{ route('register') }}" class="form-modern">
            @csrf
            
            {{-- Campo Nombre Completo --}}
            <div class="form-group-modern">
                <label for="name" class="form-label">
                    <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Nombre Completo
                </label>
                <div class="input-wrapper-modern">
                    <input id="name" type="text" name="name" value="{{ old('name') }}" 
                           required autofocus autocomplete="name"
                           class="form-input-modern @error('name') is-invalid @enderror" 
                           placeholder="Tu nombre y apellido">
                    <div class="input-border"></div>
                </div>
                @error('name')
                    <span class="error-message">
                        <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $message }}
                    </span>
                @enderror
            </div>
            
            {{-- Campo Correo Electrónico --}}
            <div class="form-group-modern">
                <label for="email" class="form-label">
                    <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Correo Electrónico
                </label>
                <div class="input-wrapper-modern">
                    <input id="email" type="email" name="email" value="{{ old('email') }}" 
                           required autocomplete="username"
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
            
            {{-- Campo desplegable para tipo de usuario --}}
            <div class="form-group-modern">
                <label for="user_type" class="form-label">
                    <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    ¿Eres un Rumbero o Aliado?
                </label>
                <div class="select-wrapper-modern">
                    <select id="user_type" name="user_type" class="form-select-modern @error('user_type') is-invalid @enderror" required>
                        <option value="" disabled selected>Selecciona una opción</option>
                        <option value="user" {{ old('user_type') == 'user' ? 'selected' : '' }}>🔥 Usuario Rumbero</option>
                        <option value="partner" {{ old('user_type') == 'partner' ? 'selected' : '' }}>🏢 Aliado (Empresa/Local)</option>
                    </select>
                    <svg class="select-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M19 9l-7 7-7-7"/>
                    </svg>
                    <div class="select-border"></div>
                </div>
                @error('user_type')
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
                           required autocomplete="new-password"
                           class="form-input-modern @error('password') is-invalid @enderror" 
                           placeholder="••••••••">
                    <button type="button" class="password-toggle-modern" onclick="togglePassword('password')">
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
            
            {{-- Campo Confirmar Contraseña --}}
            <div class="form-group-modern">
                <label for="password_confirmation" class="form-label">
                    <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Confirmar Contraseña
                </label>
                <div class="input-wrapper-modern">
                    <input id="password_confirmation" type="password" name="password_confirmation" 
                           required autocomplete="new-password"
                           class="form-input-modern @error('password_confirmation') is-invalid @enderror" 
                           placeholder="Repite tu contraseña">
                    <button type="button" class="password-toggle-modern" onclick="togglePassword('password_confirmation')">
                        <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                    <div class="input-border"></div>
                </div>
                @error('password_confirmation')
                    <span class="error-message">
                        <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $message }}
                    </span>
                @enderror
            </div>
            
            {{-- Indicador de seguridad de contraseña (opcional) --}}
            <div class="password-strength" id="password-strength">
                <div class="strength-bars">
                    <div class="strength-bar"></div>
                    <div class="strength-bar"></div>
                    <div class="strength-bar"></div>
                </div>
                <span class="strength-text">Utiliza al menos 8 caracteres con números y símbolos</span>
            </div>
            
            {{-- Botón de Registro y Enlace a Login --}}
            <div class="form-actions-modern">
                <button type="submit" class="btn-register-modern">
                    <span>Registrarme</span>
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </button>
                
                <a class="login-link-modern" href="{{ route('login') }}">
                    <svg class="link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
                    </svg>
                    ¿Ya tienes cuenta? Inicia sesión
                </a>
            </div>
        </form>
        
        {{-- Pie de página con términos --}}
        <div class="register-footer-modern">
            <p>Al registrarte, aceptas nuestros 
                <a href="{{ url('/terms') }}">Términos de Servicio</a> y 
                <a href="{{ url('/privacy') }}">Política de Privacidad</a>.
            </p>
        </div>
    </div>
</div>

<script>
// Toggle para mostrar/ocultar contraseña
function togglePassword(fieldId) {
    const passwordInput = document.getElementById(fieldId);
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    
    // Animar el botón
    const button = event.currentTarget;
    button.classList.add('active');
    setTimeout(() => button.classList.remove('active'), 200);
}

// Medidor de fortaleza de contraseña (opcional)
document.getElementById('password')?.addEventListener('input', function(e) {
    const password = e.target.value;
    const strengthBars = document.querySelectorAll('.strength-bar');
    const strengthText = document.querySelector('.strength-text');
    
    let strength = 0;
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/) && password.match(/[^a-zA-Z0-9]/)) strength++;
    
    strengthBars.forEach((bar, index) => {
        if (index < strength) {
            bar.style.background = index === 0 ? '#ff4444' : index === 1 ? '#ffaa00' : '#00c851';
            bar.style.opacity = '1';
        } else {
            bar.style.background = 'rgba(255,255,255,0.1)';
            bar.style.opacity = '0.3';
        }
    });
    
    const messages = ['Débil', 'Media', 'Fuerte'];
    if (strength > 0) {
        strengthText.textContent = `Contraseña ${messages[strength-1]}`;
    } else {
        strengthText.textContent = 'Utiliza al menos 8 caracteres con números y símbolos';
    }
});
</script>
@endsection