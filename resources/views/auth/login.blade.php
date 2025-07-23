@extends('layouts.guest') {{-- ¡Asegúrate de que extienda el layout minimalista! --}}

@section('title', 'Iniciar Sesión - Rumbero Extremo')

@section('content')
<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            {{-- Logo de Rumbero Extremo (opcional) --}}
            {{-- Puedes añadir un logo aquí si tienes uno, con un diseño de neón o brillante si es posible. --}}
            {{-- <img src="{{ asset('images/logo_rumbero_extremo_neon.png') }}" alt="Rumbero Extremo Logo" class="login-logo"> --}}
            <h2>¡Únete a la Rumba!</h2> {{-- Mensaje más acorde a la temática --}}
            <p>Ingresa tus credenciales para encender la noche.</p>
        </div>

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="login-form">
            @csrf

            {{-- Campo Email --}}
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       class="form-control @error('email') is-invalid @enderror" placeholder="tu.correo@rumbero.com">
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            {{-- Campo Contraseña --}}
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                       class="form-control @error('password') is-invalid @enderror" placeholder="••••••••">
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            {{-- Recordarme y Olvidé mi Contraseña --}}
            <div class="form-options">
                <label for="remember_me" class="remember-me">
                    <input id="remember_me" type="checkbox" name="remember">
                    <span>Recuérdame</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="forgot-password-link" href="{{ route('password.request') }}">
                        ¿Olvidaste la clave de la rumba?
                    </a>
                @endif
            </div>

            {{-- Botón de Iniciar Sesión --}}
            <button type="submit" class="btn btn-primary btn-block">
                ¡Que Empiece la Fiesta!
            </button>
        </form>

        {{-- Opciones adicionales: Registrarse --}}
        <div class="login-footer">
            <p>¿No tienes acceso VIP? <a href="{{ route('register') }}">Regístrate y únete</a></p>
        </div>
    </div>
</div>
@endsection