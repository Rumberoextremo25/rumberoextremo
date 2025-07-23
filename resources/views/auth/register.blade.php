@extends('layouts.guest') {{-- Asegúrate de que esto apunta a tu layout principal sin navbar ni footer --}}

@section('title', 'Registro - Rumbero Extremo') {{-- Título específico para esta página --}}

@section('content')
{{-- Enlaza el CSS para esta vista específica --}}

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            {{-- Logo de Rumbero Extremo (opcional) --}}
            {{-- <img src="{{ asset('assets/img/IMG_4254.png') }}" alt="Logo Rumbero Extremo" class="login-logo"> --}}
            <h2>¡Crea tu Cuenta VIP!</h2>
            <p>Únete a la comunidad de Rumbero Extremo y vive la noche.</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="login-form">
            @csrf

            {{-- Campo Nombre --}}
            <div class="form-group">
                <label for="name">Nombre Completo</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                       class="form-control @error('name') is-invalid @enderror" placeholder="Tu nombre y apellido">
                @error('name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            {{-- Campo Correo Electrónico --}}
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
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
                <input id="password" type="password" name="password" required autocomplete="new-password"
                       class="form-control @error('password') is-invalid @enderror" placeholder="••••••••">
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            {{-- Campo Confirmar Contraseña --}}
            <div class="form-group">
                <label for="password_confirmation">Confirmar Contraseña</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                       class="form-control" placeholder="Repite tu contraseña">
                @error('password_confirmation')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            {{-- Botón de Registrarse y Enlace a Login --}}
            <div class="form-actions">
                <a class="already-registered-link" href="{{ route('login') }}">
                    ¿Ya tienes cuenta? Inicia sesión
                </a>

                <button type="submit" class="btn btn-primary">
                    Registrarme
                </button>
            </div>
        </form>

        {{-- Pie de página de registro (opcional) --}}
        <div class="login-footer">
            <p>Al registrarte, aceptas nuestros <a href="{{ url('/terms') }}">Términos de Servicio</a> y <a href="{{ url('/privacy') }}">Política de Privacidad</a>.</p>
        </div>
    </div>
</div>
@endsection